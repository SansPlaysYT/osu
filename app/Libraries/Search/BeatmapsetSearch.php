<?php

/**
 *    Copyright 2015-2017 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Libraries\Search;

use App\Libraries\Elasticsearch\BoolQuery;
use App\Libraries\Elasticsearch\RecordSearch;
use App\Models\Beatmap;
use App\Models\Beatmapset;
use App\Models\Score;

class BeatmapsetSearch extends RecordSearch
{
    public function __construct(array $options = [])
    {
        parent::__construct(Beatmapset::esIndexName(), Beatmapset::class, $options);

        $this->queryString = $options['query'];
    }

    public function records()
    {
        return $this->response()->records()->with('beatmaps')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function sort(array $sort)
    {
        $this->sort[] = static::normalizeSort($sort);

        return $this;
    }

    public function toArray() : array
    {
        $params = $this->options;
        $query = (new BoolQuery())->shouldMatch(1);

        if ($params['genre'] !== null) {
            $query->must(['match' => ['genre_id' => $params['genre']]]);
        }

        if ($params['language'] !== null) {
            $query->must(['match' => ['language_id' => $params['language']]]);
        }

        if (is_array($params['extra'])) {
            foreach ($params['extra'] as $val) {
                switch ($val) {
                    case 'video':
                        $query->must(['match' => ['video' => true]]);
                        break;
                    case 'storyboard':
                        $query->must(['match' => ['storyboard' => true]]);
                        break;
                }
            }
        }

        if (present($params['query'])) {
            $query->must(['query_string' => ['query' => es_query_escape_with_caveats($params['query'])]]);
        }

        if (!empty($params['rank'])) {
            if ($params['mode'] !== null) {
                $modes = [$params['mode']];
            } else {
                $modes = array_values(Beatmap::MODES);
            }

            $unionQuery = null;
            foreach ($modes as $mode) {
                $newQuery =
                    Score\Best\Model::getClass($mode)
                    ->forUser($params['user'])
                    ->whereIn('rank', $params['rank'])
                    ->select('beatmap_id');

                if ($unionQuery === null) {
                    $unionQuery = $newQuery;
                } else {
                    $unionQuery->union($newQuery);
                }
            }

            $beatmapIds = model_pluck($unionQuery, 'beatmap_id');
            $beatmapsetIds = model_pluck(Beatmap::whereIn('beatmap_id', $beatmapIds), 'beatmapset_id');

            $query->must(['ids' => ['type' => 'beatmaps', 'values' => $beatmapsetIds]]);
        }

        switch ($params['status']) {
            case 0: // Ranked & Approved
                $query->should([
                    ['match' => ['approved' => Beatmapset::STATES['ranked']]],
                    ['match' => ['approved' => Beatmapset::STATES['approved']]],
                ]);
                break;
            case 1: // Approved
                $query->must(['match' => ['approved' => Beatmapset::STATES['approved']]]);
                break;
            case 8: // Loved
                $query->must(['match' => ['approved' => Beatmapset::STATES['loved']]]);
                break;
            case 2: // Favourites
                $favs = model_pluck($params['user']->favouriteBeatmapsets(), 'beatmapset_id', Beatmapset::class);
                $query->must(['ids' => ['type' => 'beatmaps', 'values' => $favs]]);
                break;
            case 3: // Qualified
                $query->should([
                    ['match' => ['approved' => Beatmapset::STATES['qualified']]],
                ]);
                break;
            case 4: // Pending
                $query->should([
                    ['match' => ['approved' => Beatmapset::STATES['wip']]],
                    ['match' => ['approved' => Beatmapset::STATES['pending']]],
                ]);
                break;
            case 5: // Graveyard
                $query->must(['match' => ['approved' => Beatmapset::STATES['graveyard']]]);
                break;
            case 6: // My Maps
                $maps = model_pluck($params['user']->beatmapsets(), 'beatmapset_id');
                $query->must(['ids' => ['type' => 'beatmaps', 'values' => $maps]]);
                break;
            case 7: // Explicit Any
                break;
            default: // null, etc
                break;
        }

        if ($params['mode'] !== null) {
            $query->must(['match' => ['difficulties.playmode' => $params['mode']]]);
        }

        $this->query($query);

        return parent::toArray();
    }

    public static function search(array $params = []) : self
    {
        $startTime = microtime(true);
        $params = static::searchParams($params);

        $search = static::searchES($params);

        return $search;
    }

    public static function searchParams(array $params = [])
    {
        // simple stuff
        $params['query'] = presence($params['query'] ?? null);
        $params['status'] = get_int($params['status'] ?? null) ?? 0;
        $params['genre'] = get_int($params['genre'] ?? null);
        $params['language'] = get_int($params['language'] ?? null);
        $params['extra'] = explode('.', $params['extra'] ?? null);
        $params['limit'] = clamp(get_int($params['limit'] ?? config('osu.beatmaps.max')), 1, config('osu.beatmaps.max'));
        $params['page'] = max(1, get_int($params['page'] ?? 1));

        // mode
        $params['mode'] = get_int($params['mode'] ?? null);
        if (!in_array($params['mode'], Beatmap::MODES, true)) {
            $params['mode'] = null;
        }

        // rank
        $validRanks = ['A', 'B', 'C', 'D', 'S', 'SH', 'X', 'XH'];
        $params['rank'] = array_intersect(explode('.', $params['rank'] ?? null), $validRanks);

        // sort_order, sort_field (and clear up sort)
        $sort = explode('_', array_pull($params, 'sort'));

        $validSortFields = [
            'artist' => 'artist',
            'creator' => 'creator',
            'difficulty' => 'difficulties.difficultyrating',
            'nominations' => 'nominations',
            'plays' => 'play_count',
            'ranked' => 'approved_date',
            'rating' => 'rating',
            'relevance' => '_score',
            'title' => 'title',
            'updated' => 'last_update',
        ];
        $params['sort_field'] = $validSortFields[$sort[0] ?? null] ?? null;

        $params['sort_order'] = $sort[1] ?? null;
        if (!in_array($params['sort_order'], ['asc', 'desc'], true)) {
            $params['sort_order'] = 'desc';
        }

        if ($params['sort_field'] === null) {
            if (present($params['query'])) {
                $params['sort_field'] = '_score';
                $params['sort_order'] = 'desc';
            } else {
                if (in_array($params['status'], [4, 5, 6], true)) {
                    $params['sort_field'] = 'last_update';
                    $params['sort_order'] = 'desc';
                } else {
                    $params['sort_field'] = 'approved_date';
                    $params['sort_order'] = 'desc';
                }
            }
        }

        return $params;
    }

    public static function searchES(array $params = [])
    {
        // extract sort-related keys
        $sort = [
            'sort_field' => $params['sort_field'],
            'sort_order' => $params['sort_order'],
        ];

        return (new static($params))
            ->size($params['limit'])
            ->page($params['page'])
            ->sort($sort)
            ->source('_id');
    }

    /**
     * Generate sort parameters for the elasticsearch query.
     */
    public static function normalizeSort(array $params)
    {
        static $fields = [
            'artist' => 'artist.raw',
            'creator' => 'creator.raw',
            'title' => 'title.raw',
        ];

        // additional options
        static $orderOptions = [
            'difficulties.difficultyrating' => [
                'asc' => ['mode' => 'min'],
                'desc' => ['mode' => 'max'],
            ],
        ];

        $sortField = $params['sort_field'];
        $sortOrder = $params['sort_order'];

        $field = $fields[$sortField] ?? $sortField;
        $options = ($orderOptions[$sortField] ?? [])[$sortOrder] ?? [];

        $sortFields = [
            $field => array_merge(
                ['order' => $sortOrder],
                $options
            ),
        ];

        // sub-sorting
        if ($params['sort_field'] === 'nominations') {
            $sortFields['hype'] = ['order' => $params['sort_order']];
        }

        return $sortFields;
    }
}

<?php

/**
 *    Copyright 2015-2018 ppy Pty. Ltd.
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

return [
    'availability' => [
        'disabled' => 'Beatmap นี้ยังไม่สามารถดาวน์โหลดได้',
        'parts-removed' => 'บางส่วนของ beatmap นี้ถูกลบตามคำขอของผู้สร้าง หรือผู้ถือสิทธิบุคคลสาม',
        'more-info' => 'เช็คที่นี่เพื่อดูรายละเอียดเพิ่มเติม',
    ],

    'index' => [
        'title' => 'รายการ Beatmap',
        'guest_title' => 'Beatmaps',
    ],

    'show' => [
        'discussion' => 'การสนทนา',

        'details' => [
            'mapped_by' => '',
            'submitted' => 'ส่งเมื่อ ',
            'updated' => 'แก้ไขล่าสุดเมื่อ ',
            'ranked' => 'จัดอันดับเมื่อ ',
            'approved' => 'อนุมัติเมื่อ ',
            'qualified' => 'ผ่านเกณฑ์เมื่อ ',
            'loved' => 'loved เมื่อ ',
            'logged-out' => 'คุณต้องเข้าสู่ระบบก่อนที่จะดาวน์โหลด beatmaps ใด ๆ',
            'download' => [
                '_' => 'ดาวน์โหลด',
                'video' => 'พร้อม Video',
                'no-video' => 'ไม่พร้อม Video',
                'direct' => 'osu!direct',
            ],
            'favourite' => 'Favourite beatmapset นี้',
            'unfavourite' => 'เลิก Favourite beatmapset นี้',
            'favourited_count' => '+1 ผู้อื่น| + :count ผู้อื่น',
        ],
        'stats' => [
            'cs' => 'ขนาดวงกลม',
            'cs-mania' => 'จำนวนคีย์',
            'drain' => 'การลด HP',
            'accuracy' => 'ความแม่นยำ',
            'ar' => 'อัตราการเข้าใกล้',
            'stars' => 'ระดับความยาก',
            'total_length' => 'ความยาว',
            'bpm' => 'BPM',
            'count_circles' => 'จำนวนวงกลม',
            'count_sliders' => 'จำนวนสไลเดอร์',
            'user-rating' => 'คะแนน',
            'rating-spread' => 'การกระจายความยาก',
            'nominations' => 'การเสนอชื่อ',
            'playcount' => 'จำนวนครั้งที่เล่น',
        ],
        'info' => [
            'description' => 'คำอธิบาย',
            'genre' => 'ประเภท',
            'language' => 'ภาษา',
            'no_scores' => 'ข้อมูลกำลังถูกคำนวณ...',
            'points-of-failure' => 'ความล้มเหลว',
            'source' => 'แหล่งที่มา',
            'success-rate' => 'อัตราการผ่าน',
            'tags' => 'แท็ก',
            'unranked' => 'แมพที่ไม่ได้จัดอันดับ',
        ],
        'scoreboard' => [
            'achieved' => 'ได้รับ :when',
            'country' => 'อันดับประเทศ',
            'friend' => 'อันดับเพื่อน',
            'global' => 'อันดับโลก',
            'supporter-link' => 'คลิก <a href=":link">ที่นี่</a> เพื่อดูสิ่งสวยงามทั้งหมดที่คุณได้รับ!',
            'supporter-only' => 'คุณต้องเป็นผู้สนับสนุนเพื่อเข้าถึงการจัดอันดับเพื่อนและประเทศ!',
            'title' => 'กระดานคะแนน',

            'headers' => [
                'accuracy' => 'ความแม่นยำ',
                'combo' => 'คอมโบสูงสุด',
                'miss' => 'Miss',
                'mods' => 'ม็อด',
                'player' => 'ผู้เล่น',
                'pp' => '',
                'rank' => 'อันดับ',
                'score_total' => 'คะแนนรวม',
                'score' => 'คะแนน',
            ],

            'no_scores' => [
                'country' => 'ยังไม่มีใครในประเทศของคุณที่ทำคะแนนบนแมพนี้ได้!',
                'friend' => 'ยังไม่มีใครในเพือนของคุณที่ทำคะแนนบนแมพนี้ได้!',
                'global' => 'ยังไม่มีคะแนน บางทีคุณอาจจะลองทำดูนะ',
                'loading' => 'กำลังโหลดคะแนน...',
                'unranked' => 'แมพที่ไม่ได้จัดอันดับ.',
            ],
            'score' => [
                'first' => 'นำ',
                'own' => 'คะแนนดีที่สุดของคุณ',
            ],
        ],
    ],
];

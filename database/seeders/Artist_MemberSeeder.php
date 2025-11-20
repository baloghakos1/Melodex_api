<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Artist_MemberSeeder extends Seeder
{
    const ITEMS = [
        [3,1],
        [3,2],
        [3,3],
        [3,4],
        [5,5],
        [5,6],
        [5,7],
        [5,8],
        [5,9],
        [8,10],
        [8,11],
        [8,12],
        [8,13],
        [8,14],
        [9,15],
        [9,16],
        [9,17],
        [9,18]
    ];
    public function run(): void
    {
        foreach (self::ITEMS as $item) {
            DB::table('artists_members')->insert([
                'artist_id' => $item[0],
                'member_id' => $item[1],
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DownloadLinksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('download_links')->insert([
            [
                'download_link' => 'https://github.com/McGillll/CacaoCare_App/releases/download/v.1/CacaoCare.v.1.apk',
                'description' => 'First launch version',
                'version' => 'v1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'download_link' => 'https://github.com/McGillll/CacaoCare_App/releases/download/v2/CacaoCare.apk',
                'description' => 'Second release version',
                'version' => 'v2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

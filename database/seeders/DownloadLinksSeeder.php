<?php

namespace Database\Seeders;

use App\Models\DownloadLinks;
use Illuminate\Database\Seeder;

class DownloadLinksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $downloadLinks = [
            [
                'download_link' => 'https://github.com/McGillll/CacaoCare_App/releases/download/v2/CacaoCare.apk',
                'description' => 'Second release version',
                'version' => 'v2',
            ],
        ];

        foreach ($downloadLinks as $downloadLinkPayload) {
            $downloadLink = DownloadLinks::where('version', $downloadLinkPayload['version'])->first();

            if (!$downloadLink) {
                $downloadLink = new DownloadLinks();
                $downloadLink->version = $downloadLinkPayload['version'];
            }

            $downloadLink->download_link = $downloadLinkPayload['download_link'];
            $downloadLink->description = $downloadLinkPayload['description'];
            $downloadLink->save();
        }
    }
}

<?php

namespace App\Repositories;

use App\Interfaces\DownloadLink\DownloadLinkRepositoryInterface;
use App\Models\DownloadLinks;

class DownloadLinkRepository implements DownloadLinkRepositoryInterface
{
    public function all()
    {
        return DownloadLinks::orderByDesc('created_at')->get();
    }

    public function latest()
    {
        return DownloadLinks::latest('created_at')->first();
    }

    public function latestDate()
    {
        return DownloadLinks::latest('created_at')->select('created_at')->first();
    }

    public function create(object $payload)
    {
        $downloadLink = new DownloadLinks();
        $downloadLink->download_link = $payload->download_link;
        $downloadLink->version = $payload->version;
        $downloadLink->description = $payload->description ?? null;
        $downloadLink->save();

        return $downloadLink;
    }

    public function update(DownloadLinks $downloadLink, object $payload)
    {
        if (!empty($payload->download_link)) {
            $downloadLink->download_link = $payload->download_link;
        }
        if (!empty($payload->version)) {
            $downloadLink->version = $payload->version;
        }
        if (!empty($payload->description)) {
            $downloadLink->description = $payload->description;
        }
        $downloadLink->save();

        return $downloadLink;
    }

    public function delete(DownloadLinks $downloadLink)
    {
        return $downloadLink->delete();
    }
}

<?php

namespace App\Interfaces\DownloadLink;

use App\Models\DownloadLinks;

interface DownloadLinkServiceInterface
{
    public function all();

    public function latest();

    public function latestDate();

    public function create(object $payload);

    public function update(DownloadLinks $downloadLink, object $payload);

    public function delete(DownloadLinks $downloadLink);
}

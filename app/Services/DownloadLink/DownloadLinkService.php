<?php

namespace App\Services\DownloadLink;

use App\Interfaces\DownloadLink\DownloadLinkRepositoryInterface;
use App\Interfaces\DownloadLink\DownloadLinkServiceInterface;
use App\Models\DownloadLinks;

class DownloadLinkService implements DownloadLinkServiceInterface
{
    public function __construct(private DownloadLinkRepositoryInterface $downloadLinkRepository)
    {
    }

    public function all()
    {
        return $this->downloadLinkRepository->all();
    }

    public function latest()
    {
        return $this->downloadLinkRepository->latest();
    }

    public function latestDate()
    {
        return $this->downloadLinkRepository->latestDate();
    }

    public function create(object $payload)
    {
        return $this->downloadLinkRepository->create($payload);
    }

    public function update(DownloadLinks $downloadLink, object $payload)
    {
        return $this->downloadLinkRepository->update($downloadLink, $payload);
    }

    public function delete(DownloadLinks $downloadLink)
    {
        return $this->downloadLinkRepository->delete($downloadLink);
    }
}

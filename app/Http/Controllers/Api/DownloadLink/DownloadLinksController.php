<?php

namespace App\Http\Controllers\Api\DownloadLink;

use App\Http\Requests\DownloadLink\DownloadLinksStoreRequest;
use App\Http\Requests\DownloadLink\DownloadLinksUpdateRequest;
use App\Http\Resources\DownloadLinkResource;
use App\Interfaces\DownloadLink\DownloadLinkServiceInterface;
use App\Models\DownloadLinks;
use App\Traits\ApiResponse;

class DownloadLinksController
{
    use ApiResponse;

    public function __construct(private DownloadLinkServiceInterface $downloadLinkService)
    {
    }

    public function index()
    {
        return $this->success(DownloadLinkResource::collection($this->downloadLinkService->all()));
    }

    public function store(DownloadLinksStoreRequest $request)
    {
        $payload = (object) $request->validated();
        $downloadLink = $this->downloadLinkService->create($payload);

        return $this->success(new DownloadLinkResource($downloadLink), 'Download link created successfully.', 201);
    }

    public function show(DownloadLinks $downloadLink)
    {
        return $this->success(new DownloadLinkResource($downloadLink));
    }

    public function update(DownloadLinksUpdateRequest $request, DownloadLinks $downloadLink)
    {
        $payload = (object) $request->validated();
        $updated = $this->downloadLinkService->update($downloadLink, $payload);

        return $this->success(new DownloadLinkResource($updated));
    }

    public function destroy(DownloadLinks $downloadLink)
    {
        $this->downloadLinkService->delete($downloadLink);

        return $this->success(null, 'Download link deleted successfully.');
    }

    public function getLatest()
    {
        $downloadLink = $this->downloadLinkService->latest();

        return $this->success($downloadLink ? new DownloadLinkResource($downloadLink) : null);
    }

    public function getLatestDate()
    {
        return $this->success($this->downloadLinkService->latestDate());
    }
}

<?php

namespace App\Services\Cacao;

use App\Exceptions\UnauthorizedActionException;
use App\Interfaces\Cacao\CacaoRepositoryInterface;
use App\Interfaces\Cacao\CacaoServiceInterface;
use App\Models\Cacao;
use App\Models\User;
use App\Services\CacaoHeatmapService;
use App\Services\CacaoYearGraphService;
use App\Services\ImageUploadService;
use App\Services\RegionAlertTableDataService;
use App\Services\WeeklyCacaoHeatmapService;
use App\Support\CacaoCache;
use Illuminate\Support\Facades\Storage;

class CacaoService implements CacaoServiceInterface
{
    private const ANALYTICS_TTL = 300;

    private const LIST_TTL = 60;

    public function __construct(
        private CacaoRepositoryInterface $cacaoRepository,
        private CacaoHeatmapService $cacaoHeatmapService,
        private WeeklyCacaoHeatmapService $weeklyCacaoHeatmapService,
        private CacaoYearGraphService $cacaoYearGraphService,
        private RegionAlertTableDataService $regionAlertTableDataService,
        private ImageUploadService $imageUploadService,
    ) {
    }

    public function feed(object $payload)
    {
        $page = request()->query('page', 1);
        $key = "feed:{$payload->order}:{$payload->filter}:{$payload->username}:{$page}";

        return CacaoCache::remember($key, self::LIST_TTL, fn () => $this->cacaoRepository->feed($payload));
    }

    public function create(object $payload)
    {
        if (!empty($payload->photoFile)) {
            $path = $this->imageUploadService->storeAsWebp($payload->photoFile, 'cacao-photos/'.$payload->label, $payload->photoFile->hashName());
            $payload->photo = Storage::disk('r2')->url($path);
        }

        return $this->cacaoRepository->create($payload);
    }

    public function update(User $authUser, Cacao $cacao, object $payload)
    {
        $this->assertOwnerOrAdmin($authUser, $cacao);

        $oldPhoto = $cacao->photo;

        if (!empty($payload->photoFile)) {
            $path = $this->imageUploadService->storeAsWebp($payload->photoFile, 'cacao-photos/'.($payload->label ?? $cacao->label), $payload->photoFile->hashName());
            $payload->photo = Storage::disk('r2')->url($path);
        }

        $updated = $this->cacaoRepository->update($cacao, $payload);

        if (!empty($payload->photoFile)) {
            $this->imageUploadService->deleteFromUrl($oldPhoto);
        }

        return $updated;
    }

    public function delete(User $authUser, Cacao $cacao)
    {
        $this->assertOwnerOrAdmin($authUser, $cacao);

        $photo = $cacao->photo;

        $deleted = $this->cacaoRepository->delete($cacao);

        $this->imageUploadService->deleteFromUrl($photo);

        return $deleted;
    }

    public function recent()
    {
        return CacaoCache::remember('recent', self::LIST_TTL, fn () => $this->cacaoRepository->recent());
    }

    public function statusCount()
    {
        return CacaoCache::remember('status-count', self::ANALYTICS_TTL, fn () => $this->cacaoRepository->statusCount());
    }

    public function uploadedTodayCount()
    {
        return CacaoCache::remember('uploaded-today', self::ANALYTICS_TTL, fn () => $this->cacaoRepository->uploadedTodayCount());
    }

    public function uploadYears()
    {
        return CacaoCache::remember('upload-years', self::ANALYTICS_TTL, fn () => $this->regionAlertTableDataService->years());
    }

    public function uploadCountByDate()
    {
        return CacaoCache::remember('upload-count-by-date', self::ANALYTICS_TTL, fn () => $this->cacaoRepository->uploadCountByDate());
    }

    public function uploadCountByUser(int $userId)
    {
        return CacaoCache::remember("upload-count-by-user:{$userId}", self::LIST_TTL, fn () => $this->cacaoRepository->uploadCountByUser($userId));
    }

    public function cacaoUploadedByUser(int $userId)
    {
        $page = request()->query('page', 1);

        return CacaoCache::remember("cacao-by-user:{$userId}:{$page}", self::LIST_TTL, fn () => $this->cacaoRepository->cacaoUploadedByUser($userId));
    }

    public function highestDiseaseWithinTheWeek()
    {
        return CacaoCache::remember('highest-disease-within-week', self::ANALYTICS_TTL, fn () => $this->cacaoRepository->highestDiseaseWithinTheWeek());
    }

    public function yearGraph(int $year, ?string $region, ?string $city)
    {
        return CacaoCache::remember("year-graph:{$year}:{$region}:{$city}", self::ANALYTICS_TTL, fn () => [
            'blackpod' => $this->cacaoYearGraphService->getYearlyBlackpodData((string) $year, $region, $city),
            'frostypod' => $this->cacaoYearGraphService->getYearlyFrostypodData((string) $year, $region, $city),
        ]);
    }

    public function weeklyHeatmap(string $filter)
    {
        $status = $this->resolveDiseaseStatus($filter);

        return CacaoCache::remember("weekly-heatmap:{$filter}", self::ANALYTICS_TTL, fn () => [
            'uploaded' => $this->weeklyCacaoHeatmapService->result($status),
            'total' => $this->weeklyCacaoHeatmapService->totalCount($status),
        ]);
    }

    public function heatmapForDate(string $filter, string $date)
    {
        $status = $this->resolveDiseaseStatus($filter);

        return CacaoCache::remember("heatmap:{$filter}:{$date}", self::ANALYTICS_TTL, fn () => [
            'uploaded' => $this->cacaoHeatmapService->result($status, $date),
            'total' => $this->cacaoHeatmapService->totalCount($status, $date),
        ]);
    }

    public function regionAlertData(string $year)
    {
        return CacaoCache::remember("region-alert:{$year}", self::ANALYTICS_TTL, function () use ($year) {
            $region = $this->regionAlertTableDataService->result($year);
            $totalCount = $this->regionAlertTableDataService->totalCount($year);
            $totalCity = $this->regionAlertTableDataService->totalCity($year);
            $totalBlackPod = $this->cacaoHeatmapService->totalCount(['Black Pod Rot'], $year);
            $totalFrostyPod = $this->cacaoHeatmapService->totalCount(['Frosty Pod Rot'], $year);

            return [
                'uploaded' => $region,
                'total_users' => $totalCount,
                'total_blackpod_uploads' => $totalBlackPod,
                'total_frostypod_uploads' => $totalFrostyPod,
                'total_diseased_uploads' => $totalBlackPod + $totalFrostyPod,
                'total_city' => $totalCity,
            ];
        });
    }

    private function resolveDiseaseStatus(string $filter)
    {
        switch (strtoupper($filter)) {
            case 'BLACK POD ROT':
                return ['Black Pod Rot'];
            case 'FROSTY POD ROT':
                return ['Frosty Pod Rot'];
            default:
                return ['Black Pod Rot', 'Frosty Pod Rot'];
        }
    }

    private function assertOwnerOrAdmin(User $authUser, Cacao $cacao)
    {
        if ($authUser->id !== $cacao->uploaderId && $authUser->role !== 'admin') {
            throw new UnauthorizedActionException('You may only modify your own cacao entry.');
        }
    }
}

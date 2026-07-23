<?php

namespace App\Services\Cacao;

use App\Exceptions\UnauthorizedActionException;
use App\Interfaces\Cacao\CacaoRepositoryInterface;
use App\Interfaces\Cacao\CacaoServiceInterface;
use App\Models\Cacao;
use App\Models\User;
use App\Services\CacaoHeatmapService;
use App\Services\CacaoYearGraphService;
use App\Services\RegionAlertTableDataService;
use App\Services\WeeklyCacaoHeatmapService;
use Illuminate\Support\Facades\Storage;

class CacaoService implements CacaoServiceInterface
{
    public function __construct(
        private CacaoRepositoryInterface $cacaoRepository,
        private CacaoHeatmapService $cacaoHeatmapService,
        private WeeklyCacaoHeatmapService $weeklyCacaoHeatmapService,
        private CacaoYearGraphService $cacaoYearGraphService,
        private RegionAlertTableDataService $regionAlertTableDataService,
    ) {
    }

    public function feed(object $payload)
    {
        return $this->cacaoRepository->feed($payload);
    }

    public function create(object $payload)
    {
        if (!empty($payload->photoFile)) {
            $path = $payload->photoFile->store('cacao-photos/'.$payload->label, 'r2');
            $payload->photo = Storage::disk('r2')->url($path);
        }

        return $this->cacaoRepository->create($payload);
    }

    public function update(User $authUser, Cacao $cacao, object $payload)
    {
        $this->assertOwnerOrAdmin($authUser, $cacao);

        if (!empty($payload->photoFile)) {
            $path = $payload->photoFile->store('cacao-photos/'.($payload->label ?? $cacao->label), 'r2');
            $payload->photo = Storage::disk('r2')->url($path);
        }

        return $this->cacaoRepository->update($cacao, $payload);
    }

    public function delete(User $authUser, Cacao $cacao)
    {
        $this->assertOwnerOrAdmin($authUser, $cacao);

        return $this->cacaoRepository->delete($cacao);
    }

    public function recent()
    {
        return $this->cacaoRepository->recent();
    }

    public function statusCount()
    {
        return $this->cacaoRepository->statusCount();
    }

    public function uploadedTodayCount()
    {
        return $this->cacaoRepository->uploadedTodayCount();
    }

    public function uploadYears()
    {
        return $this->regionAlertTableDataService->years();
    }

    public function uploadCountByDate()
    {
        return $this->cacaoRepository->uploadCountByDate();
    }

    public function uploadCountByUser(int $userId)
    {
        return $this->cacaoRepository->uploadCountByUser($userId);
    }

    public function cacaoUploadedByUser(int $userId)
    {
        return $this->cacaoRepository->cacaoUploadedByUser($userId);
    }

    public function highestDiseaseWithinTheWeek()
    {
        return $this->cacaoRepository->highestDiseaseWithinTheWeek();
    }

    public function yearGraph(int $year, ?string $region, ?string $city)
    {
        return [
            'blackpod' => $this->cacaoYearGraphService->getYearlyBlackpodData((string) $year, $region, $city),
            'frostypod' => $this->cacaoYearGraphService->getYearlyFrostypodData((string) $year, $region, $city),
        ];
    }

    public function weeklyHeatmap(string $filter)
    {
        $status = $this->resolveDiseaseStatus($filter);

        return [
            'uploaded' => $this->weeklyCacaoHeatmapService->result($status),
            'total' => $this->weeklyCacaoHeatmapService->totalCount($status),
        ];
    }

    public function heatmapForDate(string $filter, string $date)
    {
        $status = $this->resolveDiseaseStatus($filter);

        return [
            'uploaded' => $this->cacaoHeatmapService->result($status, $date),
            'total' => $this->cacaoHeatmapService->totalCount($status, $date),
        ];
    }

    public function regionAlertData(string $year)
    {
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

<?php

namespace App\Http\Controllers\Api\Cacao;

use App\Http\Requests\Cacao\CacaoStoreRequest;
use App\Http\Requests\Cacao\CacaoUpdateRequest;
use App\Http\Resources\CacaoResource;
use App\Interfaces\Cacao\CacaoServiceInterface;
use App\Models\Cacao;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CacaoController
{
    use ApiResponse;

    public function __construct(private CacaoServiceInterface $cacaoService)
    {
    }

    public function store(CacaoStoreRequest $request)
    {
        $payload = (object) $request->validated();
        if ($request->hasFile('photo')) {
            $payload->photoFile = $request->file('photo');
        }

        $result = $this->cacaoService->create($payload);

        if ($result->outcome === 'restored') {
            return $this->success(new CacaoResource($result->cacao), 'Scan restored.', 200);
        }

        return $this->success(new CacaoResource($result->cacao), 'Cacao entry created successfully!', 201);
    }

    public function feed(string $order, string $filter, Request $request)
    {
        $payload = (object) [
            'order' => $order,
            'filter' => $filter,
            'username' => $request->username,
        ];

        $cacao = $this->cacaoService->feed($payload);

        return $this->success($cacao->through(fn ($c) => new CacaoResource($c)), null, 200, [
            'meta' => [
                'current_page' => $cacao->currentPage(),
                'total_pages' => $cacao->lastPage(),
                'per_page' => $cacao->perPage(),
                'total' => $cacao->total(),
            ],
        ]);
    }

    public function show(Cacao $cacao)
    {
        return $this->success(new CacaoResource($cacao));
    }

    public function update(CacaoUpdateRequest $request, Cacao $cacao)
    {
        $payload = (object) $request->validated();

        $updated = $this->cacaoService->update($request->user(), $cacao, $payload);

        return $this->success(new CacaoResource($updated), 'Cacao entry updated successfully.');
    }

    public function destroy(Request $request, Cacao $cacao)
    {
        $this->cacaoService->delete($request->user(), $cacao);

        return $this->success(null, 'Cacao entry deleted successfully.');
    }

    public function purge(Cacao $cacao)
    {
        $result = $this->cacaoService->purge($cacao);

        $message = $result->purgedImage
            ? 'Cacao entry and image purged.'
            : 'Cacao entry deleted; image retained (still referenced by another entry).';

        return $this->success(null, $message);
    }

    public function getRecent()
    {
        return $this->success(['cacao' => CacaoResource::collection($this->cacaoService->recent())]);
    }

    public function getStatusCount()
    {
        return $this->success($this->cacaoService->statusCount());
    }

    public function getUploadedTodayCount()
    {
        return $this->success($this->cacaoService->uploadedTodayCount());
    }

    public function uploadYears()
    {
        return $this->success($this->cacaoService->uploadYears());
    }

    public function getCacaoYearGraph(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $region = $request->input('region');
        $city = $request->input('city');

        return $this->success($this->cacaoService->yearGraph($year, $region, $city));
    }

    public function getUploadCountByDate()
    {
        return $this->success($this->cacaoService->uploadCountByDate());
    }

    public function getWeeklyHeatMapData(string $filter)
    {
        return $this->success($this->cacaoService->weeklyHeatmap($filter));
    }

    public function getCacaoHeatMapData(string $filter, string $date)
    {
        return $this->success($this->cacaoService->heatmapForDate($filter, $date));
    }

    public function regionAlertData(string $year)
    {
        return $this->success($this->cacaoService->regionAlertData($year));
    }

    public function getUploadCountByUser(User $user)
    {
        return $this->success($this->cacaoService->uploadCountByUser($user->id));
    }

    public function getCacaoUploadedByUser(User $user)
    {
        $cacao = $this->cacaoService->cacaoUploadedByUser($user->id);

        return $this->success($cacao->through(fn ($c) => new CacaoResource($c)), null, 200, [
            'meta' => [
                'current_page' => $cacao->currentPage(),
                'total_pages' => $cacao->lastPage(),
                'per_page' => $cacao->perPage(),
                'total' => $cacao->total(),
            ],
        ]);
    }

    public function getHighestDiseaseWithinTheWeek()
    {
        return $this->success(['cacao' => $this->cacaoService->highestDiseaseWithinTheWeek()]);
    }
}

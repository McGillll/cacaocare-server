<?php

namespace App\Interfaces\Cacao;

use App\Models\Cacao;
use App\Models\User;

interface CacaoServiceInterface
{
    public function feed(object $payload);

    public function create(object $payload);

    public function update(User $authUser, Cacao $cacao, object $payload);

    public function delete(User $authUser, Cacao $cacao);

    public function purge(Cacao $cacao);

    public function recent();

    public function statusCount();

    public function uploadedTodayCount();

    public function uploadYears();

    public function uploadCountByDate();

    public function uploadCountByUser(int $userId);

    public function cacaoUploadedByUser(int $userId);

    public function highestDiseaseWithinTheWeek();

    public function yearGraph(int $year, ?string $region, ?string $city);

    public function weeklyHeatmap(string $filter);

    public function heatmapForDate(string $filter, string $date);

    public function regionAlertData(string $year);
}

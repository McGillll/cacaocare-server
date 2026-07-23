<?php

namespace App\Interfaces\Cacao;

use App\Models\Cacao;

interface CacaoRepositoryInterface
{
    public function feed(object $payload);

    public function create(object $payload);

    public function update(Cacao $cacao, object $payload);

    public function delete(Cacao $cacao);

    public function recent();

    public function statusCount();

    public function uploadedTodayCount();

    public function uploadCountByDate();

    public function uploadCountByUser(int $userId);

    public function cacaoUploadedByUser(int $userId);

    public function highestDiseaseWithinTheWeek();
}

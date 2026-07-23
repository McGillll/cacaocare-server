<?php

namespace App\Interfaces\Treatment;

use App\Models\Treatment;

interface TreatmentRepositoryInterface
{
    public function all();

    public function create(object $payload);

    public function update(Treatment $treatment, object $payload);

    public function delete(Treatment $treatment);

    public function replaceKeypoints(Treatment $treatment, array $keypoints);
}

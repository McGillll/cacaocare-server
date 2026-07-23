<?php

namespace App\Services\Treatment;

use App\Exceptions\InvalidPayloadException;
use App\Interfaces\Treatment\TreatmentRepositoryInterface;
use App\Interfaces\Treatment\TreatmentServiceInterface;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

class TreatmentService implements TreatmentServiceInterface
{
    public function __construct(private TreatmentRepositoryInterface $treatmentRepository)
    {
    }

    public function all()
    {
        return $this->treatmentRepository->all();
    }

    public function create(object $payload)
    {
        return DB::transaction(function () use ($payload) {
            $treatment = $this->treatmentRepository->create($payload);

            if (!empty($payload->keypoints)) {
                $this->treatmentRepository->replaceKeypoints($treatment, $this->normalizeKeypoints($payload->keypoints));
            }

            return $treatment->load('keypoints');
        });
    }

    public function update(Treatment $treatment, object $payload)
    {
        return DB::transaction(function () use ($treatment, $payload) {
            $this->treatmentRepository->update($treatment, $payload);

            if (isset($payload->keypoints)) {
                $this->treatmentRepository->replaceKeypoints($treatment, $this->normalizeKeypoints($payload->keypoints));
            }

            return $treatment->load('keypoints');
        });
    }

    public function delete(Treatment $treatment)
    {
        return $this->treatmentRepository->delete($treatment);
    }

    private function normalizeKeypoints($keypoints)
    {
        if (is_string($keypoints)) {
            $decoded = json_decode($keypoints, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidPayloadException('Invalid JSON format for keypoints.');
            }
            return $decoded ?? [];
        }

        return $keypoints;
    }
}

<?php

namespace App\Repositories;

use App\Interfaces\Treatment\TreatmentRepositoryInterface;
use App\Models\Treatment;
use App\Models\TreatmentKeyPoint;

class TreatmentRepository implements TreatmentRepositoryInterface
{
    public function all()
    {
        return Treatment::with('keypoints')->get();
    }

    public function create(object $payload)
    {
        $treatment = new Treatment();
        $treatment->disease = $payload->disease;
        $treatment->short_description = $payload->short_description ?? null;
        $treatment->img_url = $payload->img_url ?? null;
        $treatment->save();

        return $treatment;
    }

    public function update(Treatment $treatment, object $payload)
    {
        if (!empty($payload->disease)) {
            $treatment->disease = $payload->disease;
        }
        if (!empty($payload->short_description)) {
            $treatment->short_description = $payload->short_description;
        }
        if (!empty($payload->img_url)) {
            $treatment->img_url = $payload->img_url;
        }
        $treatment->save();

        return $treatment;
    }

    public function delete(Treatment $treatment)
    {
        return $treatment->delete();
    }

    public function replaceKeypoints(Treatment $treatment, array $keypoints)
    {
        $treatment->keypoints()->delete();

        foreach ($keypoints as $keypoint) {
            if (empty($keypoint['title']) || empty($keypoint['detailed_step'])) {
                continue;
            }

            $treatmentKeyPoint = new TreatmentKeyPoint();
            $treatmentKeyPoint->disease_id = $treatment->id;
            $treatmentKeyPoint->title = $keypoint['title'];
            $treatmentKeyPoint->detailed_step = $keypoint['detailed_step'];
            $treatmentKeyPoint->save();
        }

        return $treatment->keypoints()->get();
    }
}

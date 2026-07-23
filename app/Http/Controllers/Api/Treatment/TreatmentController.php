<?php

namespace App\Http\Controllers\Api\Treatment;

use App\Http\Requests\Treatment\StoreTreatmentRequest;
use App\Http\Requests\Treatment\UpdateTreatmentRequest;
use App\Http\Resources\TreatmentResource;
use App\Interfaces\Treatment\TreatmentServiceInterface;
use App\Models\Treatment;
use App\Traits\ApiResponse;

class TreatmentController
{
    use ApiResponse;

    public function __construct(private TreatmentServiceInterface $treatmentService)
    {
    }

    public function index()
    {
        return $this->success(TreatmentResource::collection($this->treatmentService->all()));
    }

    public function store(StoreTreatmentRequest $request)
    {
        $payload = (object) $request->validated();
        $treatment = $this->treatmentService->create($payload);

        return $this->success(new TreatmentResource($treatment), 'Treatment created successfully.', 201);
    }

    public function show(Treatment $treatment)
    {
        return $this->success(new TreatmentResource($treatment->load('keypoints')));
    }

    public function update(UpdateTreatmentRequest $request, Treatment $treatment)
    {
        $payload = (object) $request->validated();
        $updated = $this->treatmentService->update($treatment, $payload);

        return $this->success(new TreatmentResource($updated), 'Treatment updated successfully.');
    }

    public function destroy(Treatment $treatment)
    {
        $this->treatmentService->delete($treatment);

        return $this->success(null, 'Treatment deleted successfully.');
    }
}

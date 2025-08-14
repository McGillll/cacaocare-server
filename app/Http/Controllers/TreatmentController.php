<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Http\Requests\StoreTreatmentRequest;
use App\Http\Requests\UpdateTreatmentRequest;
use App\Models\TreatmentKeyPoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TreatmentController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $treatments = Treatment::with(['keypoints' => function($query) {
                $query->select('id', 'disease_id', 'title', 'detailed_step as detail');
            }])->select('id', 'disease', 'short_description', 'img_url', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();

            // Transform the data to match frontend expectations
            $treatments = $treatments->map(function ($treatment) {
                return [
                    'id' => $treatment->id,
                    'disease' => $treatment->disease,
                    'short_description' => $treatment->short_description,
                    'keypoints' => $treatment->keypoints->map(function ($keypoint) {
                        return [
                            'id' => $keypoint->id,
                            'disease_id' => $keypoint->disease_id,
                            'title' => $keypoint->title,
                            'detail' => $keypoint->detail
                        ];
                    }),
                    'img_url' => $treatment->img_url,
                    'updated_at' => $treatment->updated_at->format('Y-m-d')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $treatments,
                'message' => 'Treatments retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve treatments',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTreatmentRequest $request)
    {
        DB::beginTransaction();

        try {
            // Handle image upload if file is provided
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('treatments', 'public');
                $imagePath = Storage::url($imagePath);
            } elseif ($request->has('img_url')) {
                $imagePath = $request->img_url;
            }

            // Create treatment
            $treatment = Treatment::create([
                'disease' => $request->disease,
                'short_description' => $request->short_description,
                'img_url' => $imagePath,
            ]);

            // Create keypoints if provided
            if ($request->has('keypoints') && is_array($request->keypoints)) {
                foreach ($request->keypoints as $keypointData) {
                    TreatmentKeyPoint::create([
                        'disease_id' => $treatment->id,
                        'title' => $keypointData['title'] ?? '',
                        'detailed_step' => $keypointData['detail'] ?? $keypointData['detailed_step'] ?? ''
                    ]);
                }
            }

            // Load the created treatment with keypoints
            $treatment->load(['keypoints']);

            // Transform response to match frontend format
            $formattedTreatment = [
                'id' => $treatment->id,
                'disease' => $treatment->disease,
                'short_description' => $treatment->short_description,
                'keypoints' => $treatment->keypoints->map(function ($keypoint) {
                    return [
                        'id' => $keypoint->id,
                        'disease_id' => $keypoint->disease_id,
                        'title' => $keypoint->title,
                        'detail' => $keypoint->detailed_step
                    ];
                }),
                'img_url' => $treatment->img_url,
                'updated_at' => $treatment->updated_at->format('Y-m-d')
            ];

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $formattedTreatment,
                'message' => 'Treatment created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Failed to create treatment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $treatment = Treatment::where($id)->first();
            $treatment->load(['keypoints']);

            $formattedTreatment = [
                'id' => $treatment->id,
                'disease' => $treatment->disease,
                'short_description' => $treatment->short_description,
                'keypoints' => $treatment->keypoints->map(function ($keypoint) {
                    return [
                        'id' => $keypoint->id,
                        'disease_id' => $keypoint->disease_id,
                        'title' => $keypoint->title,
                        'detail' => $keypoint->detailed_step
                    ];
                }),
                'img_url' => $treatment->img_url,
                'updated_at' => $treatment->updated_at
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedTreatment,
                'message' => 'Treatment retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve treatment',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTreatmentRequest $request, int $id)
    {
        DB::beginTransaction();
        try {
            $treatment = Treatment::where($id)->first();
            // Handle image upload if file is provided
            $imagePath = $treatment->img_url;
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($treatment->img_url && Storage::disk('public')->exists(str_replace('/storage/', '', $treatment->img_url))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $treatment->img_url));
                }

                $imagePath = $request->file('image')->store('treatments', 'public');
                $imagePath = Storage::url($imagePath);
            } elseif ($request->has('img_url')) {
                $imagePath = $request->img_url;
            }

            // Update treatment basic info
            $treatment->update([
                'disease' => $request->disease ?? $treatment->disease,
                'short_description' => $request->short_description ?? $treatment->short_description,
                'img_url' => $imagePath,
            ]);

            // Update keypoints if provided
            if ($request->has('keypoints') && is_array($request->keypoints)) {
                // Delete existing keypoints
                $treatment->keypoints()->delete();

                // Create new keypoints
                foreach ($request->keypoints as $keypointData) {
                    TreatmentKeyPoint::create([
                        'disease_id' => $treatment->id,
                        'title' => $keypointData['title'] ?? '',
                        'detailed_step' => $keypointData['detail'] ?? $keypointData['detailed_step'] ?? ''
                    ]);
                }
            }

            // Load the updated treatment with keypoints
            $treatment->load(['keypoints']);

            // Transform response to match frontend format
            $formattedTreatment = [
                'id' => $treatment->id,
                'disease' => $treatment->disease,
                'short_description' => $treatment->short_description,
                'keypoints' => $treatment->keypoints->map(function ($keypoint) {
                    return [
                        'id' => $keypoint->id,
                        'disease_id' => $keypoint->disease_id,
                        'title' => $keypoint->title,
                        'detail' => $keypoint->detailed_step
                    ];
                }),
                'img_url' => $treatment->img_url,
                'updated_at' => $treatment->updated_at
            ];

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $formattedTreatment,
                'message' => 'Treatment updated successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Failed to update treatment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        DB::beginTransaction();

        try {
            $treatment = Treatment::where($id)->first();
            // Delete associated image if exists
            if ($treatment->img_url && Storage::disk('public')->exists(str_replace('/storage/', '', $treatment->img_url))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $treatment->img_url));
            }

            // Delete keypoints (cascade should handle this, but explicit is better)
            $treatment->keypoints()->delete();

            // Delete treatment
            $treatment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Treatment deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete treatment',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

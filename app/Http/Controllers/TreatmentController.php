<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Http\Requests\StoreTreatmentRequest;
use App\Http\Requests\UpdateTreatmentRequest;
use App\Models\TreatmentKeyPoint;
use Illuminate\Support\Facades\DB;

class TreatmentController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $treatments = Treatment::with('keypoints')->get();


        return response()->json([
            'data' => $treatments
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTreatmentRequest $request)
    {
        DB::beginTransaction();
        try {
            $treatment = new Treatment();
            $treatment->disease = $request->disease;
            $treatment->short_description = $request->short_description;
            $treatment->img_url = $request->img_url;
            $treatment->save();

            if($request->keypoints){
                $keys = $request->keypoints;

                foreach ($keys as $key) {
                    $treatment_key_point = new TreatmentKeyPoint();
                    $treatment_key_point->disease_id = $treatment->id;
                    $treatment_key_point->title = $key['title'];
                    $treatment_key_point->detailed_step = $key['detailed_step'];
                    $treatment_key_point->save();
                }
            }
            DB::commit();
            return response()->json([
                'data' => [
                    'keypoints' => $keys,
                    'treatment' => $treatment
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to store treatment data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Treatment $treatment)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTreatmentRequest $request, int $id)
    {
        DB::beginTransaction();
        try {
            $treatment = Treatment::find($id);
            if($request->disease){
                $treatment->disease = $request->disease;
            }
            if($request->short_description){
                $treatment->short_description = $request->short_description;
            }
            if($request->img_url){
                $treatment->img_url = $request->img_url;
            }
            $treatment->save();

            if($request->keypoints){
                // Parse keypoints if it's a JSON string from FormData
                $keypoints_data = $request->keypoints;
                if (is_string($keypoints_data)) {
                    $keys = json_decode($keypoints_data, true);
                } else {
                    $keys = $keypoints_data;
                }
                
                $treatment->keypoints()->delete();

                if (is_array($keys)) {
                    foreach ($keys as $key) {
                        $treatment_key_point = new TreatmentKeyPoint();
                        $treatment_key_point->disease_id = $treatment->id;
                        $treatment_key_point->title = $key['title'];
                        $treatment_key_point->detailed_step = $key['detailed_step'];
                        $treatment_key_point->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'data' => [
                    'keypoints' => $keys,
                    'treatment' => $treatment
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to store treatment data.',
                'message' => $e->getMessage(),
                'data' => $request->keypoints

            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Treatment $treatment)
    {
        //
    }
}

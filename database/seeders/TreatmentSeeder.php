<?php

namespace Database\Seeders;

use App\Models\Treatment;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    public function run()
    {
        $treatments = [
            [
                'disease' => 'Black Pod Rot',
                'short_description' => 'A fungal disease affecting cacao plants.',
                'img_url' => 'https://live.staticflickr.com/1136/4598574864_a1d225d065_b.jpg',
            ],
            [
                'disease' => 'Frosty Pod Rot',
                'short_description' => 'Causes pod deformation and reduces yield.',
                'img_url' => 'https://blog.plantwise.org/wp-content/uploads/sites/7/2016/10/cocoa-pod-with-frosty-pod-rot_for-cabi_org.jpg',
            ],
        ];

        foreach ($treatments as $treatmentPayload) {
            $treatment = Treatment::where('disease', $treatmentPayload['disease'])->first();

            if (!$treatment) {
                $treatment = new Treatment();
                $treatment->disease = $treatmentPayload['disease'];
            }

            $treatment->short_description = $treatmentPayload['short_description'];
            $treatment->img_url = $treatmentPayload['img_url'];
            $treatment->save();
        }
    }
}

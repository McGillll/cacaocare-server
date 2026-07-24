<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        Treatment::insert([
            [
                'disease' => 'Black Pod Rot',
                'short_description' => 'A fungal disease affecting cacao plants.',
                'img_url' => 'https://live.staticflickr.com/1136/4598574864_a1d225d065_b.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease' => 'Frosty Pod Rot',
                'short_description' => 'Causes pod deformation and reduces yield.',
                'img_url' => 'https://blog.plantwise.org/wp-content/uploads/sites/7/2016/10/cocoa-pod-with-frosty-pod-rot_for-cabi_org.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

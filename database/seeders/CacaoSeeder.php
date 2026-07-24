<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Str;

class CacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $photosByLabel = [
            'Healthy Pod' => [
                'https://upload.wikimedia.org/wikipedia/commons/2/21/Cacao_pods.jpg',
            ],
            'Black Pod Rot' => [
                'https://live.staticflickr.com/1136/4598574864_a1d225d065_b.jpg',
            ],
            'Frosty Pod Rot' => [
                'https://blog.plantwise.org/wp-content/uploads/sites/7/2016/10/cocoa-pod-with-frosty-pod-rot_for-cabi_org.jpg',
            ],
        ];

        $labels = array_keys($photosByLabel);

        for ($i = 1; $i <= 10; $i++) {
            $label = $labels[array_rand($labels)];
            $photos = $photosByLabel[$label];

            DB::table('cacaos')->insert([
                'uuid' => Str::random(20),
                'label' => $label,
                'confidence' => rand(70, 99) / 100,
                'photo' => $photos[array_rand($photos)],
                'uploaderId' => 1,
                'caption' => Str::random(20),
                'date_analyzed' => now()->subDays(rand(1, 30)),
                'created_at' => now()->addDay()
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Treatment;
use App\Models\TreatmentKeyPoint;
use Illuminate\Database\Seeder;

class TreatmentKeyPointSeeder extends Seeder
{
    public function run()
    {
        $blackPodRot = Treatment::where('disease', 'Black Pod Rot')->first();
        $frostyPodRot = Treatment::where('disease', 'Frosty Pod Rot')->first();

        if ($blackPodRot) {
            TreatmentKeyPoint::where('disease_id', $blackPodRot->id)->delete();

            $blackPodRotKeyPoints = [
                [
                    'title' => 'Remove and destroy infected pods',
                    'detailed_step' => 'Harvest and remove all black, mummified, or infected pods from the tree and ground at least once a week, then bury or burn them away from the farm to stop spore spread.',
                ],
                [
                    'title' => 'Prune trees and shade cover',
                    'detailed_step' => 'Prune cacao and shade trees to open the canopy, improving air circulation and sunlight penetration so pods dry faster and stay less favorable for Phytophthora growth.',
                ],
                [
                    'title' => 'Apply copper-based fungicide',
                    'detailed_step' => 'Spray a copper-based fungicide (e.g. copper oxychloride or copper hydroxide) on pods and trunks on a preventive schedule, especially at the start of the rainy season when infection risk is highest.',
                ],
                [
                    'title' => 'Improve field drainage',
                    'detailed_step' => 'Clear drainage channels and avoid waterlogged soil, since standing water and high humidity encourage Phytophthora spore production and spread.',
                ],
                [
                    'title' => 'Plant tolerant cacao varieties',
                    'detailed_step' => 'Where replanting is possible, choose cacao clones or hybrids with documented tolerance to Phytophthora pod rot to reduce long-term disease pressure.',
                ],
            ];

            foreach ($blackPodRotKeyPoints as $keyPointPayload) {
                $keyPoint = new TreatmentKeyPoint();
                $keyPoint->disease_id = $blackPodRot->id;
                $keyPoint->title = $keyPointPayload['title'];
                $keyPoint->detailed_step = $keyPointPayload['detailed_step'];
                $keyPoint->save();
            }
        }

        if ($frostyPodRot) {
            TreatmentKeyPoint::where('disease_id', $frostyPodRot->id)->delete();

            $frostyPodRotKeyPoints = [
                [
                    'title' => 'Weekly phytosanitary pod removal',
                    'detailed_step' => 'Inspect and remove all infected pods, including small mummified ones, every 7 to 10 days before they develop the white frosty spore layer, and bury or destroy them on-site.',
                ],
                [
                    'title' => 'Prune for light and airflow',
                    'detailed_step' => 'Prune cacao and shade trees to reduce excess humidity and increase sunlight in the canopy, since Moniliophthora roreri thrives in cool, wet, shaded conditions.',
                ],
                [
                    'title' => 'Harvest ripe pods frequently',
                    'detailed_step' => 'Harvest healthy pods as soon as they ripen and avoid leaving mature pods on the tree, since delayed harvesting increases exposure time to airborne spores.',
                ],
                [
                    'title' => 'Apply protective fungicide',
                    'detailed_step' => 'Use copper-based or other recommended fungicides on young pods during high-risk wet periods to protect them before spores can establish infection.',
                ],
                [
                    'title' => 'Use resistant planting material',
                    'detailed_step' => 'Favor cacao clones with known resistance or tolerance to frosty pod rot when establishing new plots, particularly in regions with high disease pressure.',
                ],
            ];

            foreach ($frostyPodRotKeyPoints as $keyPointPayload) {
                $keyPoint = new TreatmentKeyPoint();
                $keyPoint->disease_id = $frostyPodRot->id;
                $keyPoint->title = $keyPointPayload['title'];
                $keyPoint->detailed_step = $keyPointPayload['detailed_step'];
                $keyPoint->save();
            }
        }
    }
}

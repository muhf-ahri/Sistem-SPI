<?php

namespace Database\Seeders;

use App\Models\RiskCategory;
use Illuminate\Database\Seeder;

class RiskCategoriesSeeder extends Seeder
{
    public function run()
    {
        $riskCategories = [
            [
                'name' => 'Rendah',
                'level' => 'low',
                'description' => 'Risiko dengan dampak minimal dan dapat ditoleransi',
                'is_active' => true,
            ],
            [
                'name' => 'Sedang',
                'level' => 'medium',
                'description' => 'Risiko dengan dampak sedang dan memerlukan perhatian',
                'is_active' => true,
            ],
            [
                'name' => 'Tinggi',
                'level' => 'high',
                'description' => 'Risiko dengan dampak signifikan dan memerlukan tindakan segera',
                'is_active' => true,
            ],
            [
                'name' => 'Kritis',
                'level' => 'critical',
                'description' => 'Risiko dengan dampak sangat serius dan memerlukan tindakan darurat',
                'is_active' => true,
            ],
        ];

        foreach ($riskCategories as $risk) {
            RiskCategory::create($risk);
        }
    }
}
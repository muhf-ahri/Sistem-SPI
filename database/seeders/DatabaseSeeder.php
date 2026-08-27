<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            DivisionsSeeder::class,
            AuditTypesSeeder::class,
            FindingCategoriesSeeder::class,
            RiskCategoriesSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
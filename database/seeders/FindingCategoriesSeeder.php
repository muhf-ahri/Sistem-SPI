<?php

namespace Database\Seeders;

use App\Models\FindingCategory;
use Illuminate\Database\Seeder;

class FindingCategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Administrasi',
                'description' => 'Temuan terkait administrasi dan dokumentasi',
                'is_active' => true,
            ],
            [
                'name' => 'Prosedur',
                'description' => 'Temuan terkait ketidaksesuaian prosedur atau SOP',
                'is_active' => true,
            ],
            [
                'name' => 'Dokumentasi',
                'description' => 'Temuan terkait kelengkapan dan keakuratan dokumen',
                'is_active' => true,
            ],
            [
                'name' => 'Kepatuhan',
                'description' => 'Temuan terkait pelanggaran kebijakan atau regulasi',
                'is_active' => true,
            ],
            [
                'name' => 'Keuangan',
                'description' => 'Temuan terkait transaksi keuangan dan akuntansi',
                'is_active' => true,
            ],
            [
                'name' => 'Operasional',
                'description' => 'Temuan terkait efisiensi dan efektivitas operasional',
                'is_active' => true,
            ],
            [
                'name' => 'Sistem Informasi',
                'description' => 'Temuan terkait keamanan dan kinerja sistem informasi',
                'is_active' => true,
            ],
            [
                'name' => 'Kualitas',
                'description' => 'Temuan terkait kualitas produk atau layanan',
                'is_active' => true,
            ],
            [
                'name' => 'Keselamatan',
                'description' => 'Temuan terkait keselamatan kerja dan lingkungan',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            FindingCategory::create($category);
        }
    }
}
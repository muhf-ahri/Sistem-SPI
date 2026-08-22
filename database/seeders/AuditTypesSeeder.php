<?php

namespace Database\Seeders;

use App\Models\AuditType;
use Illuminate\Database\Seeder;

class AuditTypesSeeder extends Seeder
{
    public function run()
    {
        $auditTypes = [
            [
                'name' => 'Audit Keuangan',
                'description' => 'Pemeriksaan laporan keuangan, transaksi, dan kepatuhan terhadap standar akuntansi',
                'is_active' => true,
            ],
            [
                'name' => 'Audit Operasional',
                'description' => 'Evaluasi efisiensi dan efektivitas proses operasional perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Audit Kepatuhan',
                'description' => 'Pemeriksaan kepatuhan terhadap kebijakan, peraturan, dan SOP perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Audit Sistem Informasi',
                'description' => 'Evaluasi keamanan, integritas, dan kinerja sistem informasi',
                'is_active' => true,
            ],
            [
                'name' => 'Audit SDM',
                'description' => 'Pemeriksaan proses rekrutmen, pengembangan, dan administrasi karyawan',
                'is_active' => true,
            ],
            [
                'name' => 'Audit Kualitas',
                'description' => 'Evaluasi sistem manajemen mutu dan kepatuhan terhadap standar ISO',
                'is_active' => true,
            ],
            [
                'name' => 'Audit Lingkungan',
                'description' => 'Pemeriksaan kepatuhan terhadap regulasi lingkungan dan pengelolaan limbah',
                'is_active' => true,
            ],
            [
                'name' => 'Audit Keselamatan Kerja',
                'description' => 'Evaluasi penerapan K3 (Keselamatan dan Kesehatan Kerja)',
                'is_active' => true,
            ],
        ];

        foreach ($auditTypes as $type) {
            AuditType::create($type);
        }
    }
}
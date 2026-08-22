<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionsSeeder extends Seeder
{
    public function run()
    {
        $divisions = [
            [
                'name' => 'Produksi',
                'code' => 'PROD',
                'description' => 'Divisi yang bertanggung jawab atas proses produksi dan manufaktur',
                'is_active' => true,
            ],
            [
                'name' => 'Keuangan',
                'code' => 'FIN',
                'description' => 'Divisi yang mengelola keuangan, akuntansi, dan anggaran perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Sumber Daya Manusia',
                'code' => 'HR',
                'description' => 'Divisi yang mengelola rekrutmen, pengembangan, dan administrasi karyawan',
                'is_active' => true,
            ],
            [
                'name' => 'Teknologi Informasi',
                'code' => 'IT',
                'description' => 'Divisi yang mengelola sistem informasi, infrastruktur IT, dan keamanan data',
                'is_active' => true,
            ],
            [
                'name' => 'Pemasaran',
                'code' => 'MKT',
                'description' => 'Divisi yang bertanggung jawab atas strategi pemasaran dan penjualan',
                'is_active' => true,
            ],
            [
                'name' => 'Logistik',
                'code' => 'LOG',
                'description' => 'Divisi yang mengelola rantai pasok, pergudangan, dan distribusi',
                'is_active' => true,
            ],
            [
                'name' => 'Penelitian & Pengembangan',
                'code' => 'RND',
                'description' => 'Divisi yang fokus pada inovasi produk dan pengembangan teknologi',
                'is_active' => true,
            ],
            [
                'name' => 'Quality Assurance',
                'code' => 'QA',
                'description' => 'Divisi yang menjamin kualitas produk dan kepatuhan terhadap standar',
                'is_active' => true,
            ],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}
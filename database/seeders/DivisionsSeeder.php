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
                'name' => 'Perusahaan',
                'code' => 'PE',
                'description' => 'Divisi induk yang mewakili entitas perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Direktur Utama',
                'code' => 'DU',
                'description' => 'Divisi pimpinan tertinggi yang menentukan arah strategis perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Direktur Operasi',
                'code' => 'DO',
                'description' => 'Divisi yang mengawasi keseluruhan kegiatan operasional perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Senior Executive Vice Presiden',
                'code' => 'SEVP',
                'description' => 'Divisi eksekutif senior yang mendukung direktur dalam pengelolaan perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Sekretaris Perusahaan',
                'code' => 'SP',
                'description' => 'Divisi yang mengelola legalitas, tata kelola, dan urusan korporat perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Sumber Daya Manusia',
                'code' => 'SDM',
                'description' => 'Divisi yang mengelola rekrutmen, pengembangan, dan administrasi karyawan',
                'is_active' => true,
            ],
            [
                'name' => 'Akuntansi, Keuangan dan Manajemen Resiko',
                'code' => 'AKMR',
                'description' => 'Divisi yang mengelola akuntansi, keuangan, dan manajemen resiko perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Pemasaran dan Penjualan',
                'code' => 'PP',
                'description' => 'Divisi yang bertanggung jawab atas strategi pemasaran dan penjualan produk',
                'is_active' => true,
            ],
            [
                'name' => 'SPI',
                'code' => 'SPI',
                'description' => 'Satuan Audit Intern yang bertanggung jawab atas Audit internal dan pemeriksaan',
                'is_active' => true,
            ],
            [
                'name' => 'Pengadaan',
                'code' => 'ADA',
                'description' => 'Divisi yang mengelola pengadaan barang dan jasa perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Rencana dan Kinerja Perusahaan',
                'code' => 'RKP',
                'description' => 'Divisi yang menyusun rencana strategis dan mengelola kinerja perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Enjiniring',
                'code' => 'ENJ',
                'description' => 'Divisi yang menangani rekayasa, desain, dan pengembangan teknologi produk',
                'is_active' => true,
            ],
            [
                'name' => 'Rendalprod dan Gudang',
                'code' => 'REND',
                'description' => 'Divisi yang mengelola perencanaan dan pengendalian produksi serta pergudangan',
                'is_active' => true,
            ],
            [
                'name' => 'Mutu dan K3LH',
                'code' => 'MUTU',
                'description' => 'Divisi yang menjamin mutu produk serta keselamatan, kesehatan kerja, dan lingkungan hidup',
                'is_active' => true,
            ],
            [
                'name' => 'Produksi dan Proyek',
                'code' => 'PRO',
                'description' => 'Divisi yang bertanggung jawab atas proses produksi dan pelaksanaan proyek',
                'is_active' => true,
            ],
            [
                'name' => 'Produksi Turen',
                'code' => 'PTR',
                'description' => 'Divisi yang mengelola kegiatan produksi di pabrik Turen',
                'is_active' => true,
            ],
            [
                'name' => 'Bisnis Pariwisata',
                'code' => 'BP',
                'description' => 'Divisi yang mengembangkan dan mengelola bisnis pariwisata perusahaan',
                'is_active' => true,
            ],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}

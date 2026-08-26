<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Ambil ID divisi yang sudah dibuat
        $divisions = Division::pluck('id', 'code')->toArray();

        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@spi.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'division_id' => null,
            'is_active' => true,
        ]);

        // SPI / Auditor (beberapa auditor)
        $auditors = [
            ['name' => 'Auditor 1', 'email' => 'auditor1@spi.com'],
            ['name' => 'Auditor 2', 'email' => 'auditor2@spi.com'],
            ['name' => 'Auditor 3', 'email' => 'auditor3@spi.com'],
        ];

        foreach ($auditors as $auditor) {
            User::create([
                'name' => $auditor['name'],
                'email' => $auditor['email'],
                'password' => Hash::make('password'),
                'role' => 'spi',
                'division_id' => null,
                'is_active' => true,
            ]);
        }

        // Kepala Divisi (satu per divisi)
        $kadivs = [
            ['name' => 'Kepala Perusahaan', 'email' => 'kadiv.pe@spi.com', 'division_code' => 'PE'],
            ['name' => 'Kepala Direktorat Utama', 'email' => 'kadiv.du@spi.com', 'division_code' => 'DU'],
            ['name' => 'Kepala Direktorat Operasi', 'email' => 'kadiv.do@spi.com', 'division_code' => 'DO'],
            ['name' => 'Kepala SEVP', 'email' => 'kadiv.sevp@spi.com', 'division_code' => 'SEVP'],
            ['name' => 'Kepala Sekretaris Perusahaan', 'email' => 'kadiv.sp@spi.com', 'division_code' => 'SP'],
            ['name' => 'Kepala SDM', 'email' => 'kadiv.sdm@spi.com', 'division_code' => 'SDM'],
            ['name' => 'Kepala AKMR', 'email' => 'kadiv.akmr@spi.com', 'division_code' => 'AKMR'],
            ['name' => 'Kepala Pemasaran dan Penjualan', 'email' => 'kadiv.pp@spi.com', 'division_code' => 'PP'],
            ['name' => 'Kepala SPI', 'email' => 'kadiv.spi@spi.com', 'division_code' => 'SPI'],
            ['name' => 'Kepala Pengadaan', 'email' => 'kadiv.ada@spi.com', 'division_code' => 'ADA'],
            ['name' => 'Kepala RKP', 'email' => 'kadiv.rkp@spi.com', 'division_code' => 'RKP'],
            ['name' => 'Kepala Enjiniring', 'email' => 'kadiv.enj@spi.com', 'division_code' => 'ENJ'],
            ['name' => 'Kepala Rendalprod dan Gudang', 'email' => 'kadiv.rend@spi.com', 'division_code' => 'REND'],
            ['name' => 'Kepala Mutu dan K3LH', 'email' => 'kadiv.mutu@spi.com', 'division_code' => 'MUTU'],
            ['name' => 'Kepala Produksi dan Proyek', 'email' => 'kadiv.pro@spi.com', 'division_code' => 'PRO'],
            ['name' => 'Kepala Produksi Turen', 'email' => 'kadiv.ptr@spi.com', 'division_code' => 'PTR'],
            ['name' => 'Kepala Bisnis Pariwisata', 'email' => 'kadiv.bp@spi.com', 'division_code' => 'BP'],
        ];

        foreach ($kadivs as $kadiv) {
            User::create([
                'name' => $kadiv['name'],
                'email' => $kadiv['email'],
                'password' => Hash::make('password'),
                'role' => 'kepala_divisi',
                'division_id' => $divisions[$kadiv['division_code']] ?? null,
                'is_active' => true,
            ]);
        }

        // Management
        User::create([
            'name' => 'Management User',
            'email' => 'management@spi.com',
            'password' => Hash::make('password'),
            'role' => 'management',
            'division_id' => null,
            'is_active' => true,
        ]);

        // Tambahan user biasa (untuk PIC di action plan)
        $staff = [
            ['name' => 'Staff Produksi dan Proyek 1', 'email' => 'staff.pro1@spi.com', 'division_code' => 'PRO'],
            ['name' => 'Staff AKMR 1', 'email' => 'staff.akmr1@spi.com', 'division_code' => 'AKMR'],
            ['name' => 'Staff SDM 1', 'email' => 'staff.sdm1@spi.com', 'division_code' => 'SDM'],
            ['name' => 'Staff Pengadaan 1', 'email' => 'staff.ada1@spi.com', 'division_code' => 'ADA'],
            ['name' => 'Staff Pemasaran dan Penjualan 1', 'email' => 'staff.pp1@spi.com', 'division_code' => 'PP'],
        ];

        foreach ($staff as $s) {
            User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'password' => Hash::make('password'),
                'role' => 'spi', // atau bisa dibuat role 'staff' jika diperlukan, tapi kita pakai spi agar bisa jadi auditor juga
                'division_id' => $divisions[$s['division_code']] ?? null,
                'is_active' => true,
            ]);
        }
    }
}
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
            ['name' => 'Kepala Produksi', 'email' => 'kadiv.prod@spi.com', 'division_code' => 'PROD'],
            ['name' => 'Kepala Keuangan', 'email' => 'kadiv.fin@spi.com', 'division_code' => 'FIN'],
            ['name' => 'Kepala HR', 'email' => 'kadiv.hr@spi.com', 'division_code' => 'HR'],
            ['name' => 'Kepala IT', 'email' => 'kadiv.it@spi.com', 'division_code' => 'IT'],
            ['name' => 'Kepala Marketing', 'email' => 'kadiv.mkt@spi.com', 'division_code' => 'MKT'],
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
            ['name' => 'Staff Produksi 1', 'email' => 'staff.prod1@spi.com', 'division_code' => 'PROD'],
            ['name' => 'Staff Keuangan 1', 'email' => 'staff.fin1@spi.com', 'division_code' => 'FIN'],
            ['name' => 'Staff HR 1', 'email' => 'staff.hr1@spi.com', 'division_code' => 'HR'],
            ['name' => 'Staff IT 1', 'email' => 'staff.it1@spi.com', 'division_code' => 'IT'],
            ['name' => 'Staff Marketing 1', 'email' => 'staff.mkt1@spi.com', 'division_code' => 'MKT'],
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
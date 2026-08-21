<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Division;

class DivisionPolicy
{
    public function viewAny(User $user)
    {
        // Semua role yang sudah login boleh lihat daftar divisi (untuk dropdown)
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function create(User $user)
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, Division $division)
    {
        return $user->role === 'super_admin';
    }

    public function delete(User $user, Division $division)
    {
        // Jangan hapus divisi yang memiliki relasi aktif
        return $user->role === 'super_admin' && $division->auditPlans()->count() === 0;
    }
}
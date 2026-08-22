<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RiskCategory;

class RiskCategoryPolicy
{
    public function viewAny(User $user)
    {
        // Semua role boleh lihat daftar kategori risiko (untuk dropdown)
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function view(User $user, RiskCategory $riskCategory)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function create(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function update(User $user, RiskCategory $riskCategory)
    {
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function delete(User $user, RiskCategory $riskCategory)
    {
        // Jangan hapus jika masih memiliki relasi temuan
        return in_array($user->role, ['super_admin', 'spi']) && $riskCategory->findings()->count() === 0;
    }
}

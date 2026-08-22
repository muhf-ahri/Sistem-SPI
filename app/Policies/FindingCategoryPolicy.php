<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FindingCategory;

class FindingCategoryPolicy
{
    public function viewAny(User $user)
    {
        // Semua role boleh lihat daftar kategori temuan (untuk dropdown)
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function view(User $user, FindingCategory $findingCategory)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function create(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function update(User $user, FindingCategory $findingCategory)
    {
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function delete(User $user, FindingCategory $findingCategory)
    {
        // Jangan hapus jika masih memiliki relasi temuan
        return in_array($user->role, ['super_admin', 'spi']) && $findingCategory->findings()->count() === 0;
    }
}

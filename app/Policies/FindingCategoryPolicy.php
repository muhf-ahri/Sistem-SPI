<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FindingCategory;

class FindingCategoryPolicy
{
    public function viewAny(User $user)
    {
        // Matriks: Super Admin CRUD, SPI & Management lihat
        return in_array($user->role, ['super_admin', 'spi', 'management']);
    }

    public function view(User $user, FindingCategory $findingCategory)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management']);
    }

    public function create(User $user)
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, FindingCategory $findingCategory)
    {
        return $user->role === 'super_admin';
    }

    public function delete(User $user, FindingCategory $findingCategory)
    {
        // Jangan hapus jika masih memiliki relasi temuan
        return $user->role === 'super_admin' && $findingCategory->findings()->count() === 0;
    }
}

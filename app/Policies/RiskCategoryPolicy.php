<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RiskCategory;

class RiskCategoryPolicy
{
    public function viewAny(User $user)
    {
        // Matriks: Super Admin CRUD, SPI & Management lihat
        return in_array($user->role, ['super_admin', 'spi', 'management']);
    }

    public function view(User $user, RiskCategory $riskCategory)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management']);
    }

    public function create(User $user)
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, RiskCategory $riskCategory)
    {
        return $user->role === 'super_admin';
    }

    public function delete(User $user, RiskCategory $riskCategory)
    {
        // Jangan hapus jika masih memiliki relasi temuan
        return $user->role === 'super_admin' && $riskCategory->findings()->count() === 0;
    }
}

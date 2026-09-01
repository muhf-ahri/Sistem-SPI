<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RiskCategory;

class RiskCategoryPolicy
{
    public function viewAny(User $user)
    {
        // Matriks: Super Admin CRUD, SPI & Management lihat
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function view(User $user, RiskCategory $riskCategory)
    {
        return in_array($user->role, ['super_admin', 'spi']);
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
        // Guard relasi ditangani di controller agar tombol hapus selalu tampil
        return $user->role === 'super_admin';
    }
}

<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AuditType;

class AuditTypePolicy
{
    public function viewAny(User $user)
    {
        // Matriks: Super Admin CRUD, SPI & Management lihat
        return in_array($user->role, ['super_admin', 'spi', 'management']);
    }

    public function view(User $user, AuditType $auditType)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management']);
    }

    public function create(User $user)
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, AuditType $auditType)
    {
        return $user->role === 'super_admin';
    }

    public function delete(User $user, AuditType $auditType)
    {
        // Jangan hapus jika masih memiliki relasi pengawasan
        return $user->role === 'super_admin' && $auditType->auditPlans()->count() === 0;
    }
}

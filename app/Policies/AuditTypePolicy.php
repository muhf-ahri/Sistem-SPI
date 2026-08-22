<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AuditType;

class AuditTypePolicy
{
    public function viewAny(User $user)
    {
        // Semua role boleh lihat daftar jenis pengawasan (untuk dropdown)
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function view(User $user, AuditType $auditType)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function create(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function update(User $user, AuditType $auditType)
    {
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function delete(User $user, AuditType $auditType)
    {
        // Jangan hapus jika masih memiliki relasi pengawasan
        return in_array($user->role, ['super_admin', 'spi']) && $auditType->auditPlans()->count() === 0;
    }
}

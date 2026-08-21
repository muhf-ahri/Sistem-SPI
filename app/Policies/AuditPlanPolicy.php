<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AuditPlan;

class AuditPlanPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function view(User $user, AuditPlan $auditPlan)
    {
        // Super admin, SPI, Management: lihat semua
        if (in_array($user->role, ['super_admin', 'spi', 'management'])) {
            return true;
        }
        // Kepala Divisi: hanya divisinya sendiri
        if ($user->role === 'kepala_divisi' && $user->division_id === $auditPlan->division_id) {
            return true;
        }
        return false;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function update(User $user, AuditPlan $auditPlan)
    {
        if (!in_array($user->role, ['super_admin', 'spi'])) {
            return false;
        }
        // Hanya bisa diubah jika status draft atau scheduled
        return in_array($auditPlan->status, ['draft', 'scheduled']);
    }

    public function delete(User $user, AuditPlan $auditPlan)
    {
        // Hanya super_admin dan status draft
        return $user->role === 'super_admin' && $auditPlan->status === 'draft';
    }

    public function assignAuditor(User $user, AuditPlan $auditPlan)
    {
        return in_array($user->role, ['super_admin', 'spi']) && $auditPlan->status === 'scheduled';
    }

    public function startInspection(User $user, AuditPlan $auditPlan)
    {
        // SPI bisa mulai pemeriksaan jika status scheduled dan user adalah auditor yang ditugaskan atau super_admin/spi
        if (!in_array($user->role, ['super_admin', 'spi'])) {
            return false;
        }
        // Cek apakah user adalah auditor yang ditugaskan (optional)
        return $auditPlan->status === 'scheduled';
    }
}
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AuditPlan;

class AuditPlanPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi', 'kepala_divisi']);
    }

    public function view(User $user, AuditPlan $auditPlan)
    {
        // Super admin, SPI: lihat semua
        if (in_array($user->role, ['super_admin', 'spi'])) {
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
        // SISTEM.md §4: Super Admin hanya melihat Audit.
        // Rencana Audit dibuat oleh SPI/Auditor.
        return $user->role === 'spi';
    }

    public function update(User $user, AuditPlan $auditPlan)
    {
        if ($user->role !== 'spi') {
            return false;
        }
        // Hanya auditor yang ditugaskan; dan hanya jika status draft atau scheduled
        return $auditPlan->assignedTo($user)
            && in_array($auditPlan->status, ['draft', 'scheduled']);
    }

    public function delete(User $user, AuditPlan $auditPlan)
    {
        // Hanya audit berstatus terjadwal (scheduled) yang dapat dihapus.
        if ($auditPlan->status !== 'scheduled') {
            return false;
        }

        // Super Admin dapat menghapus semua audit terjadwal.
        if ($user->role === 'super_admin') {
            return true;
        }

        // SPI/Auditor hanya dapat menghapus jadwal audit yang ia buat sendiri.
        return $user->role === 'spi' && $auditPlan->created_by === $user->id;
    }

    public function assignAuditor(User $user, AuditPlan $auditPlan)
    {
        // Matriks: Assignment Auditor dikelola SPI
        return $user->role === 'spi' && $auditPlan->status === 'scheduled';
    }

    public function startInspection(User $user, AuditPlan $auditPlan)
    {
        // Pemeriksaan dilakukan oleh SPI/Auditor yang ditugaskan.
        // Hanya dapat dimulai pada/setelah tanggal mulai jadwal, bukan sebelumnya.
        return $user->role === 'spi'
            && $auditPlan->assignedTo($user)
            && in_array($auditPlan->status, ['draft', 'scheduled'])
            && now()->startOfDay()->gte($auditPlan->start_date);
    }

    public function complete(User $user, AuditPlan $auditPlan)
    {
        // Alur §9: Audit diselesaikan oleh auditor yang ditugaskan
        return $user->role === 'spi'
            && $auditPlan->assignedTo($user)
            && $auditPlan->status === 'in_progress';
    }

    public function reactivate(User $user, AuditPlan $auditPlan)
    {
        // Reaktivasi oleh auditor yang ditugaskan
        return $user->role === 'spi'
            && $auditPlan->assignedTo($user)
            && $auditPlan->status === 'completed';
    }
}

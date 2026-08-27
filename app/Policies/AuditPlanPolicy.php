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
        // SISTEM.md §4: Super Admin hanya melihat pengawasan.
        // Rencana pengawasan dibuat oleh SPI/Auditor.
        return $user->role === 'spi';
    }

    public function update(User $user, AuditPlan $auditPlan)
    {
        if ($user->role !== 'spi') {
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
        // Matriks: Assignment Auditor dikelola SPI
        return $user->role === 'spi' && $auditPlan->status === 'scheduled';
    }

    public function startInspection(User $user, AuditPlan $auditPlan)
    {
        // Pemeriksaan dilakukan oleh SPI/Auditor.
        // Rencana baru berstatus scheduled; draft lama tetap bisa dimulai.
        return $user->role === 'spi' && in_array($auditPlan->status, ['draft', 'scheduled']);
    }

    public function complete(User $user, AuditPlan $auditPlan)
    {
        // Alur §9: pengawasan diselesaikan oleh SPI setelah pemeriksaan berakhir
        return $user->role === 'spi' && $auditPlan->status === 'in_progress';
    }

    public function reactivate(User $user, AuditPlan $auditPlan)
    {
        // Reaktivasi: membuka kembali pengawasan yang sudah selesai agar
        // SPI dapat mengedit/menambah data tanpa menghapus data sebelumnya.
        return $user->role === 'spi' && $auditPlan->status === 'completed';
    }
}

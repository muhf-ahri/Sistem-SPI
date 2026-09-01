<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ActionPlan;

class ActionPlanPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi', 'kepala_divisi']);
    }

    public function view(User $user, ActionPlan $actionPlan)
    {
        if (in_array($user->role, ['super_admin', 'spi'])) {
            return true;
        }
        if ($user->role === 'kepala_divisi') {
            // Cek apakah finding milik divisinya
            return $user->division_id === $actionPlan->finding->auditPlan->division_id;
        }
        return false;
    }

    public function create(User $user)
    {
        // Matriks hak akses: Action Plan dikelola Kepala Divisi (SPI & Super Admin hanya lihat)
        return $user->role === 'kepala_divisi';
    }

    public function update(User $user, ActionPlan $actionPlan)
    {
        if ($user->role !== 'kepala_divisi') {
            return false;
        }
        if ($user->division_id !== $actionPlan->finding->auditPlan->division_id) {
            return false;
        }
        // Bisa diubah selama belum dikirim/diverifikasi
        return in_array($actionPlan->status, ['pending', 'in_progress', 'rejected']);
    }

    public function delete(User $user, ActionPlan $actionPlan)
    {
        if ($user->role === 'super_admin') {
            return $actionPlan->status === 'pending';
        }
        if ($user->role === 'kepala_divisi') {
            return $user->division_id === $actionPlan->finding->auditPlan->division_id
                && in_array($actionPlan->status, ['pending', 'in_progress', 'rejected']);
        }
        return false;
    }

    public function submitForVerification(User $user, ActionPlan $actionPlan)
    {
        if ($actionPlan->status !== 'in_progress') {
            return false;
        }
        // Kepala Divisi atau PIC yang mengirimkan hasil perbaikan
        if ($user->role === 'kepala_divisi') {
            return $user->division_id === $actionPlan->finding->auditPlan->division_id;
        }
        return $user->id === $actionPlan->pic_user_id;
    }

    public function verify(User $user, ActionPlan $actionPlan)
    {
        // SISTEM.md §4: Super Admin tidak boleh melakukan verifikasi temuan.
        // Verifikasi hanya dilakukan SPI/Auditor pada tindak lanjut yang sudah disubmit.
        return $user->role === 'spi' && $actionPlan->status === 'submitted';
    }
}

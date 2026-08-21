<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ActionPlan;

class ActionPlanPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function view(User $user, ActionPlan $actionPlan)
    {
        if (in_array($user->role, ['super_admin', 'spi', 'management'])) {
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
        // Hanya kepala divisi atau SPI yang bisa membuat action plan
        return in_array($user->role, ['super_admin', 'spi', 'kepala_divisi']);
    }

    public function update(User $user, ActionPlan $actionPlan)
    {
        // Hanya bisa update oleh pembuatnya atau SPI, dan status belum verified/completed
        if ($user->role === 'kepala_divisi') {
            // Cek apakah action plan milik divisinya
            if ($user->division_id !== $actionPlan->finding->auditPlan->division_id) {
                return false;
            }
            // Bisa update jika status masih pending atau in_progress
            return in_array($actionPlan->status, ['pending', 'in_progress']);
        }
        if (in_array($user->role, ['super_admin', 'spi'])) {
            // SPI bisa mengupdate status atau memberikan catatan
            return true;
        }
        return false;
    }

    public function delete(User $user, ActionPlan $actionPlan)
    {
        // Hanya super_admin, dan status pending
        return $user->role === 'super_admin' && $actionPlan->status === 'pending';
    }

    public function submitForVerification(User $user, ActionPlan $actionPlan)
    {
        // Kepala divisi bisa submit untuk verifikasi
        if ($user->role === 'kepala_divisi') {
            return $user->division_id === $actionPlan->finding->auditPlan->division_id
                && $actionPlan->status === 'in_progress';
        }
        return false;
    }

    public function verify(User $user, ActionPlan $actionPlan)
    {
        // SPI/Super Admin bisa verifikasi
        return in_array($user->role, ['super_admin', 'spi'])
            && $actionPlan->status === 'submitted';
    }
}
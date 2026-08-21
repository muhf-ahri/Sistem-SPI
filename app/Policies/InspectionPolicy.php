<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Inspection;

class InspectionPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function view(User $user, Inspection $inspection)
    {
        if (in_array($user->role, ['super_admin', 'spi', 'management'])) {
            return true;
        }
        if ($user->role === 'kepala_divisi') {
            return $user->division_id === $inspection->auditPlan->division_id;
        }
        return false;
    }

    public function create(User $user)
    {
        // Hanya SPI/Super Admin yang bisa mencatat pemeriksaan
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function update(User $user, Inspection $inspection)
    {
        // Hanya SPI/Super Admin, dan hanya jika audit plan belum selesai
        if (!in_array($user->role, ['super_admin', 'spi'])) {
            return false;
        }
        return $inspection->auditPlan->status !== 'completed';
    }

    public function delete(User $user, Inspection $inspection)
    {
        // Hanya super_admin, dan hanya jika audit plan draft
        return $user->role === 'super_admin' && $inspection->auditPlan->status === 'draft';
    }

    public function uploadEvidence(User $user, Inspection $inspection)
    {
        // SPI/Super Admin bisa upload bukti
        return in_array($user->role, ['super_admin', 'spi']);
    }
}
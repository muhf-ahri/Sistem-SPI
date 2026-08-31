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
            // Kepala Divisi hanya melihat pemeriksaan divisinya
            return $user->division_id === $inspection->auditPlan->division_id;
        }
        return false;
    }

    public function create(User $user)
    {
        // Matriks: Pemeriksaan dikelola SPI; Super Admin hanya melihat
        return $user->role === 'spi';
    }

    public function update(User $user, Inspection $inspection)
    {
        if ($user->role !== 'spi') {
            return false;
        }
        return $inspection->auditPlan->assignedTo($user)
            && $inspection->auditPlan->status !== 'completed';
    }

    public function delete(User $user, Inspection $inspection)
    {
        // Hanya super_admin, dan hanya jika audit plan draft (pembersihan data administratif)
        return $user->role === 'super_admin' && $inspection->auditPlan->status === 'draft';
    }

    public function uploadEvidence(User $user, Inspection $inspection)
    {
        // Bukti Pemeriksaan dikelola auditor yang ditugaskan
        return $user->role === 'spi'
            && $inspection->auditPlan->assignedTo($user);
    }
}

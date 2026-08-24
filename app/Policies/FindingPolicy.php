<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Finding;

class FindingPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi', 'management', 'kepala_divisi']);
    }

    public function view(User $user, Finding $finding)
    {
        if (in_array($user->role, ['super_admin', 'spi', 'management'])) {
            return true;
        }
        if ($user->role === 'kepala_divisi' && $user->division_id === $finding->auditPlan->division_id) {
            return true;
        }
        return false;
    }

    public function create(User $user)
    {
        // Matriks: Temuan dikelola SPI; Super Admin & role lain hanya melihat
        return $user->role === 'spi';
    }

    public function update(User $user, Finding $finding)
    {
        if ($user->role !== 'spi') {
            return false;
        }
        // Status temuan diubah oleh alur tindak lanjut, bukan lewat edit manual
        return !in_array($finding->status, ['closed']);
    }

    public function delete(User $user, Finding $finding)
    {
        // Hanya super_admin dan status open (pembersihan data administratif)
        return $user->role === 'super_admin' && $finding->status === 'open';
    }

    public function addActionPlan(User $user, Finding $finding)
    {
        // Action Plan dibuat Kepala Divisi pemilik temuan
        return $user->role === 'kepala_divisi'
            && $user->division_id === $finding->auditPlan->division_id;
    }
}

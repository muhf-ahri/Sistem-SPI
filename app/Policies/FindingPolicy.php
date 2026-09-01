<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Finding;

class FindingPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['super_admin', 'spi', 'kepala_divisi']);
    }

    public function view(User $user, Finding $finding)
    {
        if (in_array($user->role, ['super_admin', 'spi'])) {
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
        // Hanya auditor yang ditugaskan pada pengawasan temuan ini
        if (!$finding->auditPlan->assignedTo($user)) {
            return false;
        }
        // Status temuan diubah oleh alur tindak lanjut, bukan lewat edit manual
        return !in_array($finding->status, ['closed']);
    }

    public function delete(User $user, Finding $finding)
    {
        // Super Admin: pembersihan data administratif
        if ($user->role === 'super_admin') {
            return $finding->status === 'open';
        }
        // SPI dapat menghapus temuan buatannya sendiri selama belum ditindaklanjuti divisi
        return $user->role === 'spi'
            && $finding->auditPlan->assignedTo($user)
            && $finding->created_by === $user->id
            && $finding->status === 'open';
    }

    public function addActionPlan(User $user, Finding $finding)
    {
        // Action Plan dibuat Kepala Divisi pemilik temuan
        return $user->role === 'kepala_divisi'
            && $user->division_id === $finding->auditPlan->division_id;
    }
}

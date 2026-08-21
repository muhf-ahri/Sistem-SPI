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
        // Hanya SPI/Super Admin yang bisa membuat temuan
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function update(User $user, Finding $finding)
    {
        if (!in_array($user->role, ['super_admin', 'spi'])) {
            return false;
        }
        // Tidak boleh mengedit temuan yang sudah closed/rejected? Bisa disesuaikan
        return !in_array($finding->status, ['closed', 'rejected']);
    }

    public function delete(User $user, Finding $finding)
    {
        // Hanya super_admin dan status open
        return $user->role === 'super_admin' && $finding->status === 'open';
    }

    public function addActionPlan(User $user, Finding $finding)
    {
        // Kepala divisi dapat menambahkan action plan jika finding milik divisinya
        if ($user->role === 'kepala_divisi' && $user->division_id === $finding->auditPlan->division_id) {
            return true;
        }
        // SPI juga bisa? Mungkin bisa, tapi biasanya divisi yang buat action plan
        return in_array($user->role, ['super_admin', 'spi']);
    }

    public function verify(User $user, Finding $finding)
    {
        // Hanya SPI/Super Admin yang bisa verifikasi
        return in_array($user->role, ['super_admin', 'spi']) && $finding->status === 'waiting_verification';
    }
}
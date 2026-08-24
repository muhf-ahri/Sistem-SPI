<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user)
    {
        // Hanya super_admin yang bisa melihat daftar user
        return $user->role === 'super_admin';
    }

    public function view(User $user, User $model)
    {
        // Matriks: modul Users hanya untuk Super Admin
        return $user->role === 'super_admin';
    }

    public function create(User $user)
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, User $model)
    {
        // Super admin bisa update semua, user biasa hanya bisa update dirinya sendiri
        if ($user->id === $model->id) {
            return true;
        }
        return $user->role === 'super_admin';
    }

    public function delete(User $user, User $model)
    {
        // Hanya super_admin, dan tidak boleh menghapus diri sendiri
        return $user->role === 'super_admin' && $user->id !== $model->id;
    }

    public function assignRole(User $user)
    {
        return $user->role === 'super_admin';
    }
}
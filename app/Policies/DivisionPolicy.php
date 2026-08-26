<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Division;

class DivisionPolicy
{
    public function viewAny(User $user)
    {
        // Matriks: modul Divisi hanya dikelola Super Admin (role lain tidak punya akses)
        return $user->role === 'super_admin';
    }

    public function view(User $user, Division $division)
    {
        return $user->role === 'super_admin';
    }

    public function create(User $user)
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, Division $division)
    {
        return $user->role === 'super_admin';
    }

    public function delete(User $user, Division $division)
    {
        // Guard relasi ditangani di controller agar tombol hapus selalu tampil
        return $user->role === 'super_admin';
    }
}
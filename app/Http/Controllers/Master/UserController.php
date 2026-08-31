<?php

namespace App\Http\Controllers\Master;

use App\Models\User;
use App\Models\Division;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Helpers\AuditLogHelper;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request)
    {
        $query = User::with('division')->orderBy('name');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active == '1');
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = ['super_admin', 'spi', 'kepala_divisi', 'management'];
        return view('master.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $divisions = Division::where('is_active', true)->pluck('name', 'id');
        $roles = ['super_admin', 'spi', 'kepala_divisi', 'management'];
        return view('master.users.create', compact('divisions', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,spi,kepala_divisi,management',
            'division_id' => 'nullable|exists:divisions,id',
            'is_active' => 'boolean',
        ]);

        // Kepala Divisi wajib terikat pada satu divisi (data scoping)
        if ($request->role === 'kepala_divisi' && !$request->division_id) {
            return back()->withErrors(['division_id' => 'Kepala Divisi wajib memilih divisi.'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'division_id' => $request->division_id,
            'is_active' => $request->is_active ?? true,
        ]);

        AuditLogHelper::log('create', 'user', $user->id, null, $user->toArray());
        return redirect()->route('master.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return view('master.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $divisions = Division::where('is_active', true)->pluck('name', 'id');
        $roles = ['super_admin', 'spi', 'kepala_divisi', 'management'];
        return view('master.users.edit', compact('user', 'divisions', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:super_admin,spi,kepala_divisi,management',
            'division_id' => 'nullable|exists:divisions,id',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Kepala Divisi wajib terikat pada satu divisi (data scoping)
        if ($request->role === 'kepala_divisi' && !$request->division_id) {
            return back()->withErrors(['division_id' => 'Kepala Divisi wajib memilih divisi.'])->withInput();
        }

        $old = $user->toArray();
        $data = $request->except(['password', 'password_confirmation']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);

        AuditLogHelper::log('update', 'user', $user->id, $old, $user->toArray());
        return redirect()->route('master.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Jangan izinkan menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Cek apakah user memiliki relasi
        if ($user->createdAuditPlans()->count() > 0 || $user->createdFindings()->count() > 0) {
            return back()->with('error', 'User tidak bisa dihapus karena memiliki data terkait.');
        }

        $old = $user->toArray();
        $user->delete();
        AuditLogHelper::log('delete', 'user', $user->id, $old, null);
        return redirect()->route('master.users.index')->with('success', 'User berhasil dihapus.');
    }
}
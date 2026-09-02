<?php

namespace App\Http\Controllers\Master;

use App\Models\Division;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\AuditLogHelper;

class DivisionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Division::class, 'division');
    }

    public function index(Request $request)
    {
        $query = Division::orderBy('name');
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $divisions = $query->paginate(10)->withQueryString();
        return view('master.divisions.index', compact('divisions'));
    }

    public function create()
    {
        return view('master.divisions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:divisions',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $division = Division::create($request->all());
        AuditLogHelper::log('create', 'division', $division->id, null, $division->toArray());
        return redirect()->route('master.divisions.index')->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function show(Division $division)
    {
        return view('master.divisions.show', compact('division'));
    }

    public function edit(Division $division)
    {
        return view('master.divisions.edit', compact('division'));
    }

    public function update(Request $request, Division $division)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:divisions,code,' . $division->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $old = $division->toArray();
        $division->update($request->all());
        AuditLogHelper::log('update', 'division', $division->id, $old, $division->toArray());
        return redirect()->route('master.divisions.index')->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Division $division)
    {
        // Cek apakah ada relasi
        if ($division->auditPlans()->count() > 0) {
            return back()->with('error', 'Divisi tidak bisa dihapus karena memiliki data Audit.');
        }
        $division->delete();
        AuditLogHelper::log('delete', 'division', $division->id, $division->toArray(), null);
        return redirect()->route('master.divisions.index')->with('success', 'Divisi berhasil dihapus.');
    }
}
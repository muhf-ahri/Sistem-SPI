<?php

namespace App\Http\Controllers\Master;

use App\Models\AuditType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\AuditLogHelper;

class AuditTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AuditType::class, 'audit_type');
    }

    public function index(Request $request)
    {
        $query = AuditType::orderBy('name');
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('name', 'like', "%{$search}%");
        }
        $auditTypes = $query->paginate(10)->withQueryString();
        return view('master.audit-types.index', compact('auditTypes'));
    }

    public function create()
    {
        return view('master.audit-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $auditType = AuditType::create($request->all());
        AuditLogHelper::log('create', 'audit_type', $auditType->id, null, $auditType->toArray());
        return redirect()->route('master.audit-types.index')->with('success', 'Jenis pengawasan berhasil ditambahkan.');
    }

    public function show(AuditType $auditType)
    {
        return view('master.audit-types.show', compact('auditType'));
    }

    public function edit(AuditType $auditType)
    {
        return view('master.audit-types.edit', compact('auditType'));
    }

    public function update(Request $request, AuditType $auditType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $old = $auditType->toArray();
        $auditType->update($request->all());
        AuditLogHelper::log('update', 'audit_type', $auditType->id, $old, $auditType->toArray());
        return redirect()->route('master.audit-types.index')->with('success', 'Jenis pengawasan berhasil diperbarui.');
    }

    public function destroy(AuditType $auditType)
    {
        // Cek apakah ada relasi
        if ($auditType->auditPlans()->count() > 0) {
            return back()->with('error', 'Jenis pengawasan tidak bisa dihapus karena memiliki data pengawasan.');
        }
        $auditType->delete();
        AuditLogHelper::log('delete', 'audit_type', $auditType->id, $auditType->toArray(), null);
        return redirect()->route('master.audit-types.index')->with('success', 'Jenis pengawasan berhasil dihapus.');
    }
}
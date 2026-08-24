<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\AuditPlan;
use App\Models\Finding;
use App\Http\Requests\StoreInspectionRequest;
use App\Http\Requests\UpdateInspectionRequest;
use App\Helpers\AuditLogHelper;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Inspection::class, 'inspection');
    }

    public function index()
    {
        $query = Inspection::with(['auditPlan', 'auditor']);

        // Kepala Divisi hanya melihat pemeriksaan divisinya
        if (auth()->user()->role === 'kepala_divisi') {
            $query->whereHas('auditPlan', function ($q) {
                $q->where('division_id', auth()->user()->division_id);
            });
        }

        $inspections = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('inspections.index', compact('inspections'));
    }

    public function create()
    {
        // Get active audit plans for dropdown
        $auditPlans = AuditPlan::whereIn('status', ['in_progress', 'scheduled'])
            ->pluck('title', 'id');
        
        // Get SPI auditors
        $auditors = \App\Models\User::where('role', 'spi')
            ->where('is_active', true)
            ->pluck('name', 'id');
        
        return view('inspections.create', compact('auditPlans', 'auditors'));
    }

    public function store(StoreInspectionRequest $request)
    {
        $validated = $request->validated();
        $validated['auditor_id'] = auth()->id();
        
        $inspection = Inspection::create($validated);
        
        AuditLogHelper::log('create', 'inspection', $inspection->id, null, $inspection->toArray());
        
        return redirect()->route('inspections.index')
            ->with('success', 'Pemeriksaan berhasil dibuat.');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load(['auditPlan', 'auditor', 'findings', 'evidences']);
        return view('inspections.show', compact('inspection'));
    }

    public function edit(Inspection $inspection)
    {
        $auditPlans = AuditPlan::whereIn('status', ['in_progress', 'scheduled'])
            ->pluck('title', 'id');
        
        $auditors = \App\Models\User::where('role', 'spi')
            ->where('is_active', true)
            ->pluck('name', 'id');
        
        return view('inspections.edit', compact('inspection', 'auditPlans', 'auditors'));
    }

    public function update(UpdateInspectionRequest $request, Inspection $inspection)
    {
        $old = $inspection->toArray();
        $inspection->update($request->validated());
        
        AuditLogHelper::log('update', 'inspection', $inspection->id, $old, $inspection->toArray());
        
        return redirect()->route('inspections.index')
            ->with('success', 'Pemeriksaan berhasil diperbarui.');
    }

    public function destroy(Inspection $inspection)
    {
        $inspection->delete();
        AuditLogHelper::log('delete', 'inspection', $inspection->id, $inspection->toArray(), null);
        return redirect()->route('inspections.index')
            ->with('success', 'Pemeriksaan berhasil dihapus.');
    }

    public function uploadEvidence(Request $request, Inspection $inspection)
    {
        $this->authorize('update', $inspection);
        
        $request->validate([
            'evidence_file' => 'required|file|max:10240', // max 10MB
        ]);

        if ($request->hasFile('evidence_file')) {
            $file = $request->file('evidence_file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('evidences/inspections', 'public');
            
            \App\Models\InspectionEvidence::create([
                'inspection_id' => $inspection->id,
                'uploaded_by' => auth()->id(),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);

            AuditLogHelper::logUpload('inspection', $inspection->id, $filePath);

            return back()->with('success', 'Bukti pemeriksaan berhasil diupload.');
        }

        return back()->with('error', 'Gagal mengupload file.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\AuditPlan;
use App\Models\Finding;
use App\Http\Requests\StoreInspectionRequest;
use App\Http\Requests\UpdateInspectionRequest;
use App\Helpers\AuditLogHelper;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Inspection::class, 'inspection');
    }

    public function index(Request $request)
    {
        $query = Inspection::with(['auditPlan', 'auditor']);

        // Kepala Divisi hanya melihat pemeriksaan divisinya
        if (auth()->user()->role === 'kepala_divisi') {
            $query->whereHas('auditPlan', function ($q) {
                $q->where('division_id', auth()->user()->division_id);
            });
        }

        // Filter: Pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('summary', 'like', "%{$search}%")
                    ->orWhereHas('auditPlan', fn ($sq) => $sq->where('audit_number', 'like', "%{$search}%"))
                    ->orWhereHas('auditor', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter: Auditor
        if ($request->filled('auditor')) {
            $query->where('auditor_id', $request->auditor);
        }

        // Filter: Hasil
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        // Filter: Tahun (tanggal pemeriksaan)
        if ($request->filled('year')) {
            $query->whereYear('inspection_date', $request->year);
        }

        $inspections = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $auditors = \App\Models\User::where('role', 'spi')->where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $years = \App\Models\Inspection::selectRaw('YEAR(inspection_date) as y')->distinct()->orderByDesc('y')->pluck('y');
        return view('inspections.index', compact('inspections', 'auditors', 'years'));
    }

    public function create()
    {
        // Get active audit plans for dropdown (hanya yang ditugaskan ke auditor ini)
        $plans = AuditPlan::with('assignments')->whereIn('status', ['in_progress', 'scheduled'])->get();
        $auditPlans = $plans->filter(fn ($p) => $p->assignedTo(auth()->user()))->pluck('title', 'id');

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

        // Hanya auditor yang ditugaskan pada pengawasan ini yang boleh menginput hasil pemeriksaan
        $plan = AuditPlan::findOrFail($validated['audit_plan_id']);
        abort_unless($plan->assignedTo(auth()->user()), 403, 'Anda tidak ditugaskan pada pengawasan ini.');

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

            // Notifikasi ke Divisi terkait (Kepala Divisi) bahwa bukti pemeriksaan baru diupload SPI
            NotificationService::sendToDivision(
                $inspection->auditPlan->division_id,
                'Bukti Pemeriksaan Baru',
                'Bukti pemeriksaan baru (' . $fileName . ') diupload untuk pengawasan ' . $inspection->auditPlan->audit_number . '.',
                route('inspections.show', $inspection->id),
                'info',
                'kepala_divisi'
            );

            return back()->with('success', 'Bukti pemeriksaan berhasil diupload.');
        }

        return back()->with('error', 'Gagal mengupload file.');
    }

    public function downloadEvidence(\App\Models\InspectionEvidence $evidence)
    {
        $path = storage_path('app/public/' . $evidence->file_path);

        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($path, $evidence->file_name);
    }

    public function deleteEvidence($ids)
    {
        $idList = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idList) {
            return back()->with('error', 'Tidak ada bukti yang dipilih.');
        }

        $deleted = 0;
        foreach ($idList as $id) {
            $evidence = \App\Models\InspectionEvidence::find($id);
            if (!$evidence) continue;
            // Hanya yang bisa memperbarui pemeriksaan yang boleh menghapus bukti
            $this->authorize('update', $evidence->inspection);

            $path = storage_path('app/public/' . $evidence->file_path);
            if (file_exists($path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($evidence->file_path);
            }
            $evidence->delete();
            AuditLogHelper::log('delete', 'inspection_evidence', $evidence->id, $evidence->toArray(), null);
            $deleted++;
        }

        return back()->with($deleted ? 'success' : 'error', $deleted
            ? $deleted . ' bukti pemeriksaan dihapus.'
            : 'Tidak ada bukti yang dapat dihapus.');
    }
}
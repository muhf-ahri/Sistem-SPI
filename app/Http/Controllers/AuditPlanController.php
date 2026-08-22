<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\Division;
use App\Models\AuditType;
use App\Models\User;
use App\Models\AuditAssignment;
use App\Http\Requests\StoreAuditPlanRequest;
use App\Http\Requests\UpdateAuditPlanRequest;
use App\Helpers\AuditLogHelper;
use Illuminate\Http\Request;

class AuditPlanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AuditPlan::class, 'audit_plan');
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $query = AuditPlan::with(['division', 'auditType', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('division')) {
            $query->where('division_id', $request->division);
        }
        if ($request->filled('type')) {
            $query->where('audit_type_id', $request->type);
        }

        // Jika kepala divisi, hanya lihat divisinya sendiri
        if (auth()->user()->role === 'kepala_divisi') {
            $query->where('division_id', auth()->user()->division_id);
        }

        $auditPlans = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('audits.index', compact('auditPlans'));
    }

    public function create()
    {
        $divisions = Division::where('is_active', true)->pluck('name', 'id');
        $auditTypes = AuditType::where('is_active', true)->pluck('name', 'id');
        $auditors = User::where('role', 'spi')->where('is_active', true)->pluck('name', 'id');
        return view('audits.create', compact('divisions', 'auditTypes', 'auditors'));
    }

    public function store(StoreAuditPlanRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'draft';

        $auditPlan = AuditPlan::create($validated);

        // Jika ada auditor yang dipilih, buat assignment
        if ($request->has('auditor_ids')) {
            foreach ($request->auditor_ids as $userId) {
                AuditAssignment::create([
                    'audit_plan_id' => $auditPlan->id,
                    'user_id' => $userId,
                    'role' => 'auditor',
                    'assigned_at' => now(),
                ]);
            }
        }

        AuditLogHelper::log('create', 'audit_plan', $auditPlan->id, null, $auditPlan->toArray());

        return redirect()->route('audit-plans.index')
            ->with('success', 'Pengawasan berhasil dibuat.');
    }

    public function show(AuditPlan $auditPlan)
    {
        $auditPlan->load(['division', 'auditType', 'createdBy', 'assignments.user', 'inspections', 'findings']);
        return view('audits.show', compact('auditPlan'));
    }

    public function edit(AuditPlan $auditPlan)
    {
        $divisions = Division::where('is_active', true)->pluck('name', 'id');
        $auditTypes = AuditType::where('is_active', true)->pluck('name', 'id');
        $auditors = User::where('role', 'spi')->where('is_active', true)->pluck('name', 'id');
        $selectedAuditors = $auditPlan->assignments->pluck('user_id')->toArray();
        return view('audits.edit', compact('auditPlan', 'divisions', 'auditTypes', 'auditors', 'selectedAuditors'));
    }

    public function update(UpdateAuditPlanRequest $request, AuditPlan $auditPlan)
    {
        $old = $auditPlan->toArray();
        $validated = $request->validated();
        $auditPlan->update($validated);

        // Update assignments jika ada
        if ($request->has('auditor_ids')) {
            // Hapus assignment lama
            $auditPlan->assignments()->delete();
            foreach ($request->auditor_ids as $userId) {
                AuditAssignment::create([
                    'audit_plan_id' => $auditPlan->id,
                    'user_id' => $userId,
                    'role' => 'auditor',
                    'assigned_at' => now(),
                ]);
            }
        }

        AuditLogHelper::log('update', 'audit_plan', $auditPlan->id, $old, $auditPlan->toArray());

        return redirect()->route('audit-plans.index')
            ->with('success', 'Pengawasan berhasil diperbarui.');
    }

    public function destroy(AuditPlan $auditPlan)
    {
        $auditPlan->delete();
        AuditLogHelper::log('delete', 'audit_plan', $auditPlan->id, $auditPlan->toArray(), null);
        return redirect()->route('audit-plans.index')
            ->with('success', 'Pengawasan berhasil dihapus.');
    }

    // Custom method: mulai pemeriksaan (ubah status ke in_progress)
    public function startInspection(AuditPlan $auditPlan)
    {
        $this->authorize('startInspection', $auditPlan);
        $auditPlan->status = 'in_progress';
        $auditPlan->save();
        AuditLogHelper::log('start_inspection', 'audit_plan', $auditPlan->id, ['status' => 'scheduled'], ['status' => 'in_progress']);
        return redirect()->route('audit-plans.show', $auditPlan)
            ->with('success', 'Pemeriksaan dimulai.');
    }
}
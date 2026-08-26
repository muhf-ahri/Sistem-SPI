<?php

namespace App\Http\Controllers;

use App\Models\Finding;
use App\Models\AuditPlan;
use App\Models\Inspection;
use App\Models\FindingCategory;
use App\Models\RiskCategory;
use App\Models\User;
use App\Http\Requests\StoreFindingRequest;
use App\Http\Requests\UpdateFindingRequest;
use App\Helpers\AuditLogHelper;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class FindingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Finding::class, 'finding');
    }

    public function index(Request $request)
    {
        $query = Finding::with(['auditPlan.division', 'category', 'riskCategory', 'createdBy']);

        // Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('risk')) {
            $query->whereHas('riskCategory', function ($q) use ($request) {
                $q->where('level', $request->risk);
            });
        }
        if ($request->filled('division')) {
            $query->whereHas('auditPlan', function ($q) use ($request) {
                $q->where('division_id', $request->division);
            });
        }
        if ($request->filled('overdue')) {
            $query->where('deadline', '<', now())->where('status', '!=', 'closed');
        }

        // Jika kepala divisi, hanya temuan divisinya
        if (auth()->user()->role === 'kepala_divisi') {
            $query->whereHas('auditPlan', function ($q) {
                $q->where('division_id', auth()->user()->division_id);
            });
        }

        $findings = $query->orderBy('created_at', 'desc')->paginate(10);
        $statuses = ['open', 'in_progress', 'waiting_verification', 'closed', 'rejected'];
        $risks = ['low', 'medium', 'high', 'critical'];
        $divisions = \App\Models\Division::where('is_active', true)->pluck('name', 'id');

        return view('findings.index', compact('findings', 'statuses', 'risks', 'divisions'));
    }

    public function create(Request $request)
    {
        $auditPlanId = $request->query('audit_plan_id');
        $auditPlan = AuditPlan::findOrFail($auditPlanId);

        // Temuan dapat dikaitkan langsung ke satu pemeriksaan (dari card
        // "Temuan dari Kunjungan Ini"). Pastikan pemeriksaan berasal dari
        // pengawasan yang sama agar datanya konsisten.
        $inspection = null;
        if ($request->filled('inspection_id')) {
            $inspection = Inspection::findOrFail($request->query('inspection_id'));
            abort_unless($inspection->audit_plan_id === (int) $auditPlan->id, 404);
        }

        $categories = FindingCategory::where('is_active', true)->pluck('name', 'id');
        $riskCategories = RiskCategory::where('is_active', true)->pluck('name', 'id');
        return view('findings.create', compact('auditPlan', 'inspection', 'categories', 'riskCategories'));
    }

    public function store(StoreFindingRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['finding_number'] = $this->generateFindingNumber();
        // Alur §14: temuan baru selalu berstatus OPEN
        $validated['status'] = 'open';

        $finding = Finding::create($validated);

        AuditLogHelper::log('create', 'finding', $finding->id, null, $finding->toArray());

        // Notify SPI/Auditor of new finding
        NotificationService::sendToRoles(
            ['spi'],
            'Temuan Baru Dibuat',
            'Temuan baru telah dibuat untuk pengawasan: ' . $finding->auditPlan->auditType->name . ' di Divisi ' . $finding->auditPlan->division->name,
            route('findings.show', $finding),
            'warning'
        );

        return redirect()->route('findings.show', $finding)
            ->with('success', 'Temuan berhasil dibuat.');
    }

    public function show(Finding $finding)
    {
        $finding->load(['auditPlan.division', 'category', 'riskCategory', 'createdBy', 'actionPlans.pic', 'actionPlans.followUpEvidences', 'actionPlans.verifications']);
        return view('findings.show', compact('finding'));
    }

    public function edit(Finding $finding)
    {
        $categories = FindingCategory::where('is_active', true)->pluck('name', 'id');
        $riskCategories = RiskCategory::where('is_active', true)->pluck('name', 'id');
        return view('findings.edit', compact('finding', 'categories', 'riskCategories'));
    }

    public function update(UpdateFindingRequest $request, Finding $finding)
    {
        $old = $finding->toArray();
        // Status tidak boleh diubah manual; hanya melalui alur tindak lanjut & verifikasi
        $data = collect($request->validated())->except(['status'])->all();
        $finding->update($data);
        AuditLogHelper::log('update', 'finding', $finding->id, $old, $finding->toArray());
        return redirect()->route('findings.show', $finding)
            ->with('success', 'Temuan berhasil diperbarui.');
    }

    public function destroy(Finding $finding)
    {
        $finding->delete();
        AuditLogHelper::log('delete', 'finding', $finding->id, $finding->toArray(), null);
        return redirect()->route('findings.index')
            ->with('success', 'Temuan dihapus.');
    }

    private function generateFindingNumber()
    {
        $last = Finding::orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->finding_number, -4)) + 1 : 1;
        return 'FIND-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
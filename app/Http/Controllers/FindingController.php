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

        // Pencarian: nomor, judul, deskripsi, no. pengawasan, divisi
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('finding_number', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhereHas('auditPlan', fn ($p) => $p->where('audit_number', 'like', "%{$s}%"))
                    ->orWhereHas('auditPlan.division', fn ($d) => $d->where('name', 'like', "%{$s}%"));
            });
        }

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
        if ($request->filled('year')) {
            // Tahun temuan dicatat (tanggal pembuatan)
            $query->whereYear('findings.created_at', $request->year);
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

        // Sortir kolom (whitelist). Join hanya saat perlu untuk sortir relasi.
        $sort = $request->get('sort', 'created_at');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sort === 'plan') {
            $query->leftJoin('audit_plans as ap_sort', 'findings.audit_plan_id', '=', 'ap_sort.id')
                ->orderBy('ap_sort.audit_number', $direction)
                ->select('findings.*');
        } elseif ($sort === 'division') {
            $query->leftJoin('audit_plans as ap_sort', 'findings.audit_plan_id', '=', 'ap_sort.id')
                ->leftJoin('divisions as div_sort', 'ap_sort.division_id', '=', 'div_sort.id')
                ->orderBy('div_sort.name', $direction)
                ->select('findings.*');
        } elseif ($sort === 'risk') {
            // id kategori risiko mengikuti urutan seed: low, medium, high, critical
            $query->leftJoin('risk_categories as rc_sort', 'findings.risk_category_id', '=', 'rc_sort.id')
                ->orderBy('rc_sort.id', $direction)
                ->select('findings.*');
        } elseif (in_array($sort, ['finding_number', 'title', 'deadline', 'status'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $sort = 'created_at';
            $direction = 'desc';
            $query->orderBy('created_at', 'desc');
        }

        // withQueryString() agar filter & sortir tetap terbawa saat pindah halaman
        $findings = $query->paginate(10)->withQueryString();

        $statuses = ['open', 'in_progress', 'waiting_verification', 'closed', 'rejected'];
        $risks = ['low', 'medium', 'high', 'critical'];

        $yearsQuery = Finding::query();
        if (auth()->user()->role === 'kepala_divisi') {
            $yearsQuery->whereHas('auditPlan', fn ($q) => $q->where('division_id', auth()->user()->division_id));
        }
        $years = $yearsQuery
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $divisions = \App\Models\Division::when(auth()->user()->role === 'kepala_divisi',
                fn ($q) => $q->where('id', auth()->user()->division_id))
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('findings.index', compact('findings', 'statuses', 'risks', 'divisions', 'years'));
    }

    public function create(Request $request)
    {
        $auditPlanId = $request->query('audit_plan_id');
        $auditPlan = AuditPlan::findOrFail($auditPlanId);

        // Hanya auditor yang ditugaskan yang boleh membuat temuan untuk pengawasan ini
        abort_unless($auditPlan->assignedTo(auth()->user()), 403, 'Anda tidak ditugaskan pada pengawasan ini.');

        // Semua pemeriksaan dari pengawasan ini untuk dropdown "Berdasarkan Pemeriksaan"
        $inspections = Inspection::where('audit_plan_id', $auditPlan->id)
            ->orderBy('inspection_date', 'desc')
            ->get();

        // Default inspection_id dari query (jika navigasi dari card pemeriksaan)
        $selectedInspectionId = $request->query('inspection_id');

        $categories = FindingCategory::where('is_active', true)->pluck('name', 'id');
        $riskCategories = RiskCategory::where('is_active', true)->pluck('name', 'id');
        return view('findings.create', compact('auditPlan', 'inspections', 'selectedInspectionId', 'categories', 'riskCategories'));
    }

    public function store(StoreFindingRequest $request)
    {
        $validated = $request->validated();

        // Hanya auditor yang ditugaskan yang boleh membuat temuan untuk pengawasan ini
        $plan = AuditPlan::findOrFail($validated['audit_plan_id']);
        abort_unless($plan->assignedTo(auth()->user()), 403, 'Anda tidak ditugaskan pada pengawasan ini.');

        $validated['created_by'] = auth()->id();
        // Nomor otomatis: FND_{kode divisi}_{no urut}_{tahun}
        $validated['finding_number'] = $this->generateFindingNumber($validated);
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

    // Nomor otomatis: FND_{kode divisi}_{no urut 3 digit}_{tahun} — contoh: FND_PRO_001_2026
    private function generateFindingNumber(array $data): string
    {
        $plan = AuditPlan::with('division')->findOrFail($data['audit_plan_id']);
        $code = $plan->division->code;
        $year = now()->format('Y');
        $prefix = "FND_{$code}_";
        $suffix = "_{$year}";

        $max = Finding::where('finding_number', 'like', $prefix.'%'.$suffix)
            ->get('finding_number')
            ->map(fn ($f) => (int) substr($f->finding_number, strlen($prefix), -strlen($suffix)))
            ->max();

        return $prefix.str_pad(($max ?? 0) + 1, 3, '0', STR_PAD_LEFT).$suffix;
    }
}
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
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AuditPlanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AuditPlan::class, 'audit_plan');
    }

    public function index(Request $request)
    {
        $query = AuditPlan::with(['division', 'auditType', 'createdBy']);

        // Pencarian: nomor, judul, deskripsi, nama divisi, jenis pengawasan
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('audit_number', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhereHas('division', fn ($d) => $d->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('auditType', fn ($t) => $t->where('name', 'like', "%{$s}%"));
            });
        }

        // Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('division')) {
            $query->where('division_id', $request->division);
        }
        if ($request->filled('type')) {
            $query->where('audit_type_id', $request->type);
        }
        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

        // Jika kepala divisi, hanya lihat divisinya sendiri
        $isKadiv = auth()->user()->role === 'kepala_divisi';
        if ($isKadiv) {
            $query->where('division_id', auth()->user()->division_id);
        }

        // Sortir kolom (whitelist agar aman dari manipulasi query)
        $sort = $request->get('sort', 'created_at');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sort === 'division') {
            $query->orderBy(Division::select('name')->whereColumn('divisions.id', 'audit_plans.division_id'), $direction);
        } elseif ($sort === 'type') {
            $query->orderBy(AuditType::select('name')->whereColumn('audit_types.id', 'audit_plans.audit_type_id'), $direction);
        } elseif (in_array($sort, ['audit_number', 'title', 'start_date', 'end_date', 'status'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $sort = 'created_at';
            $direction = 'desc';
            $query->orderBy('created_at', 'desc');
        }

        // withQueryString() agar filter & sortir tetap terbawa saat pindah halaman
        $auditPlans = $query->paginate(10)->withQueryString();

        // Opsi dropdown filter
        $yearsQuery = AuditPlan::query();
        if ($isKadiv) {
            $yearsQuery->where('division_id', auth()->user()->division_id);
        }
        $years = $yearsQuery
            ->selectRaw('YEAR(start_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $divisions = Division::when($isKadiv, fn ($q) => $q->where('id', auth()->user()->division_id))
            ->orderBy('name')
            ->pluck('name', 'id');

        $statuses = [
            'draft'       => 'Draft',
            'scheduled'   => 'Terjadwal',
            'in_progress' => 'Sedang Berjalan',
            'completed'   => 'Selesai',
            'cancelled'   => 'Dibatalkan',
        ];

        return view('audits.index', compact('auditPlans', 'divisions', 'statuses', 'years'));
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
        // Alur §9: rencana baru langsung terjadwal; status berikutnya diubah
        // melalui tombol Mulai Pemeriksaan / Selesaikan Pengawasan (bukan edit manual).
        $validated['status'] = 'scheduled';
        // Nomor otomatis: PEN_{kode divisi}_{no urut}_{tahun}
        $validated['audit_number'] = $this->generateAuditNumber($validated);

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

        // Notify auditors
        if ($request->has('auditor_ids')) {
            NotificationService::sendToUsers(
                $request->auditor_ids,
                'Penugasan Pengawasan Baru',
                'Anda telah ditugaskan untuk pengawasan: ' . $auditPlan->auditType->name . ' - ' . $auditPlan->division->name,
                route('audit-plans.show', $auditPlan),
                'info'
            );
        }

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

    // Nomor otomatis: PEN_{kode divisi}_{no urut 3 digit}_{tahun} — contoh: PEN_PRO_001_2026
    private function generateAuditNumber(array $data): string
    {
        $division = Division::findOrFail($data['division_id']);
        $year = \Carbon\Carbon::parse($data['start_date'])->format('Y');
        $prefix = "PEN_{$division->code}_";
        $suffix = "_{$year}";

        $max = AuditPlan::where('audit_number', 'like', $prefix.'%'.$suffix)
            ->get('audit_number')
            ->map(fn ($p) => (int) substr($p->audit_number, strlen($prefix), -strlen($suffix)))
            ->max();

        return $prefix.str_pad(($max ?? 0) + 1, 3, '0', STR_PAD_LEFT).$suffix;
    }

    // Custom method: mulai pemeriksaan (ubah status ke in_progress)
    public function startInspection(AuditPlan $auditPlan)
    {
        $this->authorize('startInspection', $auditPlan);
        $oldStatus = $auditPlan->status;
        $auditPlan->status = 'in_progress';
        $auditPlan->save();
        AuditLogHelper::logStatusChange('audit_plan', $auditPlan->id, $oldStatus, 'in_progress');
        return redirect()->route('audit-plans.show', $auditPlan)
            ->with('success', 'Pemeriksaan dimulai.');
    }

    // Custom method: selesaikan pengawasan (alur §9: pengawasan selesai)
    public function complete(AuditPlan $auditPlan)
    {
        $this->authorize('complete', $auditPlan);
        $oldStatus = $auditPlan->status;
        $auditPlan->status = 'completed';
        $auditPlan->save();
        AuditLogHelper::logStatusChange('audit_plan', $auditPlan->id, $oldStatus, 'completed');
        return redirect()->route('audit-plans.show', $auditPlan)
            ->with('success', 'Pengawasan diselesaikan.');
    }
}
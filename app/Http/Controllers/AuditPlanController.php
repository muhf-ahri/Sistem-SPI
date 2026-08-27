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
        $auditPlan->load(['division', 'auditType', 'createdBy', 'assignments.user', 'inspections', 'findings', 'finalReports.createdBy']);
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

    // Reaktivasi: buka kembali pengawasan yang sudah selesai tanpa menghapus data
    public function reactivate(AuditPlan $auditPlan)
    {
        $this->authorize('reactivate', $auditPlan);
        $oldStatus = $auditPlan->status;
        $auditPlan->status = 'in_progress';
        $auditPlan->save();
        AuditLogHelper::logStatusChange('audit_plan', $auditPlan->id, $oldStatus, 'in_progress');
        return redirect()->route('audit-plans.show', $auditPlan)
            ->with('success', 'Pengawasan diaktifkan kembali. Anda dapat mengedit/menambahkan data.');
    }

    // Simpan laporan hasil akhir (khusus SPI, setelah pengawasan selesai)
    public function storeReport(Request $request, AuditPlan $auditPlan)
    {
        abort_unless(auth()->user()->role === 'spi', 403, 'Unauthorized action.');
        abort_unless($auditPlan->status === 'completed', 403, 'Laporan hanya dapat dibuat setelah pengawasan selesai.');

        $request->validate([
            'title' => 'required|string|max:255',
            'report_file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx',
            'description' => 'required|string',
        ], [
            'title.required' => 'Judul laporan wajib diisi.',
            'report_file.required' => 'File laporan wajib diupload.',
            'report_file.mimes' => 'Jenis file harus PDF, Word (doc/docx), atau Excel (xls/xlsx).',
            'description.required' => 'Deskripsi laporan wajib diisi.',
        ]);

        if (!$request->hasFile('report_file')) {
            return back()->with('error', 'File laporan tidak ditemukan.');
        }

        $file = $request->file('report_file');
        $filePath = $file->store('reports/' . $auditPlan->id, 'public');

        $reportNumber = $this->generateReportNumber($auditPlan);

        \App\Models\FinalReport::create([
            'audit_plan_id' => $auditPlan->id,
            'report_number' => $reportNumber,
            'title'         => $request->title,
            'file_path'     => $filePath,
            'file_name'     => $file->getClientOriginalName(),
            'file_type'     => $file->getClientOriginalExtension(),
            'file_size'     => $file->getSize(),
            'description'   => $request->description,
            'created_by'    => auth()->id(),
        ]);

        AuditLogHelper::log('create', 'final_report', $auditPlan->id, null, ['report_number' => $reportNumber]);

        return back()->with('success', 'Laporan hasil akhir berhasil disimpan ('.$reportNumber.').');
    }

    public function downloadReport(\App\Models\FinalReport $report)
    {
        $path = storage_path('app/public/' . $report->file_path);
        if (!file_exists($path)) {
            abort(404, 'File laporan tidak ditemukan.');
        }
        return response()->download($path, $report->file_name);
    }

    // Nomor laporan: LHA_{kode divisi}_{no urut 3 digit}_{tahun} — contoh: LHA_PRO_001_2026
    private function generateReportNumber(AuditPlan $auditPlan): string
    {
        $code = $auditPlan->division->code;
        $year = now()->format('Y');
        $prefix = "LHA_{$code}_";
        $suffix = "_{$year}";

        $max = \App\Models\FinalReport::where('report_number', 'like', $prefix.'%'.$suffix)
            ->get('report_number')
            ->map(fn ($r) => (int) substr($r->report_number, strlen($prefix), -strlen($suffix)))
            ->max();

        return $prefix.str_pad(($max ?? 0) + 1, 3, '0', STR_PAD_LEFT).$suffix;
    }
}
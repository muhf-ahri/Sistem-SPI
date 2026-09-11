<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\Division;
use App\Models\Finding;
use App\Models\ActionPlan;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function auditSummary(Request $request)
    {
        $query = AuditPlan::with(['division', 'auditType', 'createdBy']);

        if ($request->filled('division')) {
            $query->where('division_id', $request->division);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        // Filter: Pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('audit_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        // Kepala Divisi hanya melihat laporan divisinya
        if (auth()->user()->role === 'kepala_divisi') {
            $query->where('division_id', auth()->user()->division_id);
        }

        $audits = $query->get();
        return view('reports.audit-summary', compact('audits'));
    }

    public function findingAnalysis(Request $request)
    {
        $query = Finding::with(['auditPlan.division', 'category', 'riskCategory']);

        if ($request->filled('division')) {
            $query->whereHas('auditPlan', function ($q) use ($request) {
                $q->where('division_id', $request->division);
            });
        }
        if ($request->filled('risk')) {
            $query->whereHas('riskCategory', function ($q) use ($request) {
                $q->where('level', $request->risk);
            });
        }

        // Filter: Pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('finding_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        // Filter: Tahun (deadline temuan)
        if ($request->filled('year')) {
            $query->whereYear('deadline', $request->year);
        }

        // Kepala Divisi hanya melihat laporan divisinya
        if (auth()->user()->role === 'kepala_divisi') {
            $divisionId = auth()->user()->division_id;
            $query->whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }

        $findings = $query->get();
        $years = \App\Models\Finding::selectRaw('YEAR(deadline) as y')->distinct()->orderByDesc('y')->pluck('y');
        return view('reports.finding-analysis', compact('findings', 'years'));
    }

    public function actionPlanStatus(Request $request)
    {
        $query = ActionPlan::with(['finding.auditPlan.division', 'pic']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhereHas('finding', fn ($sq) => $sq->where('finding_number', 'like', "%{$search}%"));
            });
        }

        // Filter: Divisi
        if ($request->filled('division')) {
            $query->whereHas('finding.auditPlan', fn ($q) => $q->where('division_id', $request->division));
        }

        // Filter: Tahun (target selesai)
        if ($request->filled('year')) {
            $query->whereYear('target_date', $request->year);
        }

        // Kepala Divisi hanya melihat laporan divisinya
        if (auth()->user()->role === 'kepala_divisi') {
            $divisionId = auth()->user()->division_id;
            $query->whereHas('finding.auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }

        $actionPlans = $query->get();
        $divisions = \App\Models\Division::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $years = \App\Models\ActionPlan::selectRaw('YEAR(target_date) as y')->distinct()->orderByDesc('y')->pluck('y');
        return view('reports.action-plan-status', compact('actionPlans', 'divisions', 'years'));
    }

    /**
     * Analisis & Perbandingan Temuan.
     * Seluruh role dapat mengakses; Kepala Divisi hanya dibatasi pada divisinya sendiri.
     * Pembanding: banyak temuan antara dua tahun yang dipilih (total, per status, per risiko).
     */
    public function comparison(Request $request)
    {
        $user = auth()->user();
        $isKepalaDivisi = $user->role === 'kepala_divisi';
        $forcedDivisionId = $isKepalaDivisi ? $user->division_id : null;

        $divisions = Division::where('is_active', true)
            ->when($isKepalaDivisi, fn ($q) => $q->where('id', $forcedDivisionId))
            ->orderBy('name')->pluck('name', 'id');

        $yearQuery = Finding::query();
        if ($forcedDivisionId) {
            $yearQuery->whereHas('auditPlan', fn ($q) => $q->where('division_id', $forcedDivisionId));
        }
        $yearOptions = $yearQuery->selectRaw('YEAR(created_at) as y')->distinct()
            ->orderByDesc('y')->pluck('y');

        $divisionId = $request->filled('division') && !$isKepalaDivisi
            ? $request->integer('division') : $forcedDivisionId;

        // Baseline query per periode & divisi
        $scoped = fn ($yr) => Finding::query()
            ->when($divisionId, fn ($q) => $q->whereHas('auditPlan', fn ($p) => $p->where('division_id', $divisionId)))
            ->whereYear('findings.created_at', $yr);

        // Tren pertumbuhan temuan per tahun (semua tahun yang ada)
        $trendYears = $yearOptions->sort()->values();
        $trend = [];
        foreach ($trendYears as $y) {
            $trend[$y] = $scoped($y)->count();
        }

        // Dua tahun pembanding (bebas dipilih). Default: dua tahun terakhir yang punya data.
        $choiceList = $trendYears->values();
        $yearA = $request->filled('year_a') ? $request->integer('year_a')
            : (int) ($choiceList->get($choiceList->count() - 2) ?? $choiceList->last() ?? now()->year);
        $yearB = $request->filled('year_b') ? $request->integer('year_b')
            : (int) ($choiceList->last() ?? $choiceList->first() ?? now()->year);

        $yearATotal = $scoped($yearA)->count();
        $yearBTotal = $scoped($yearB)->count();

        $statuses = ['open', 'in_progress', 'waiting_verification', 'closed', 'rejected'];
        $statusData = [];
        foreach ($statuses as $st) {
            $statusData[$st] = [
                'name' => str_replace('_', ' ', $st),
                'a'    => $scoped($yearA)->where('status', $st)->count(),
                'b'    => $scoped($yearB)->where('status', $st)->count(),
            ];
        }

        $risks = ['low', 'medium', 'high', 'critical'];
        $riskData = [];
        foreach ($risks as $rk) {
            $byRisk = fn ($yr) => $scoped($yr)->whereHas('riskCategory', fn ($q) => $q->where('level', $rk));
            $riskData[$rk] = [
                'name' => ucfirst($rk),
                'a'    => $byRisk($yearA)->count(),
                'b'    => $byRisk($yearB)->count(),
            ];
        }

        $divisionLabel = $divisionId ? ($divisions[$divisionId] ?? 'Divisi') : 'Semua Divisi';

        return view('reports.comparison', compact(
            'divisions', 'divisionId', 'divisionLabel',
            'yearOptions', 'yearA', 'yearB',
            'trendYears', 'trend',
            'yearATotal', 'yearBTotal',
            'statusData', 'riskData'
        ));
    }
}
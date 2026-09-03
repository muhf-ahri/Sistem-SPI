<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\AuditType;
use App\Models\Finding;
use App\Models\ActionPlan;
use App\Models\FindingCategory;
use App\Models\Division;
use App\Models\User;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $data = [];

        // Scope divisi opsional hanya untuk Admin dan SPI
        $filterDivisionId = null;
        if (in_array($user->role, ['super_admin', 'spi']) && $request->filled('divisi_id')) {
            $filterDivisionId = $request->divisi_id;
        }

        // Scope otomatis untuk Kepala Divisi
        if ($user->role === 'kepala_divisi') {
            $filterDivisionId = $user->division_id;
        }

        $data['selected_divisi_id'] = $filterDivisionId;
        $data['divisions'] = Division::orderBy('name')->get();
        $data['years'] = AuditPlan::selectRaw('YEAR(start_date) as y')
            ->distinct()->orderByDesc('y')->pluck('y');

        $year = $request->filled('year') ? $request->year : null;

        // Helper query
        $auditQuery = function ($status = null) use ($filterDivisionId, $year) {
            $q = AuditPlan::query();
            if ($filterDivisionId) {
                $q->where('division_id', $filterDivisionId);
            }
            if ($year) {
                $q->whereYear('start_date', $year);
            }
            if ($status) {
                if (is_array($status)) {
                    $q->whereIn('status', $status);
                } else {
                    $q->where('status', $status);
                }
            }
            return $q;
        };

        $findingQuery = function ($status = null) use ($filterDivisionId, $year) {
            $q = Finding::query();
            if ($filterDivisionId) {
                $q->whereHas('auditPlan', function ($qq) use ($filterDivisionId) {
                    $qq->where('division_id', $filterDivisionId);
                });
            }
            if ($year) {
                $q->whereYear('created_at', $year);
            }
            if ($status) {
                if (is_array($status)) {
                    $q->whereIn('status', $status);
                } else {
                    $q->where('status', $status);
                }
            }
            return $q;
        };

        // Common Counts untuk semua role berdasarkan scope
        $data['total_audits'] = $auditQuery()->count();
        $data['reported_audits'] = $auditQuery()->whereHas('finalReports')->count();
        $data['active_audits'] = $auditQuery(['scheduled', 'in_progress'])->count();
        $data['completed_audits'] = $auditQuery('completed')->count();
        $data['in_progress_audits'] = $auditQuery('in_progress')->count();
        $data['scheduled_audits'] = $auditQuery('scheduled')->count();

        $data['total_findings'] = $findingQuery()->count();
        $data['open_findings'] = $findingQuery('open')->count();
        $data['in_progress_findings'] = $findingQuery('in_progress')->count();
        $data['closed_findings'] = $findingQuery('closed')->count();
        
        $data['high_risk_findings'] = $findingQuery()
            ->whereHas('riskCategory', fn ($q) => $q->where('level', 'high'))->count();
            
        $data['overdue_findings'] = $findingQuery()
            ->where('deadline', '<', now())
            ->where('status', '!=', 'closed')->count();

        // Temuan belum diproses (belum closed)
        $data['unprocessed_findings'] = $findingQuery()
            ->whereIn('status', ['open', 'in_progress', 'waiting_verification', 'rejected'])->count();

        // Menunggu verifikasi
        $data['pending_verification_count'] = $findingQuery('waiting_verification')->count();

        // ===== SUPER ADMIN Specifics =====
        if ($user->role === 'super_admin') {
            $data['total_users'] = User::count();
            $data['active_users'] = User::where('is_active', true)->count();
            $data['total_divisions'] = Division::count();
            $data['total_audit_types'] = AuditType::where('is_active', true)->count();
            $data['total_finding_categories'] = FindingCategory::where('is_active', true)->count();

            $divisionStats = Division::withCount(['auditPlans'])
                ->orderBy('name')
                ->paginate(6, ['*'], 'div_page')
                ->withQueryString();

            $divisionStats->getCollection()->transform(function ($division) {
                $division->active_findings_count = Finding::whereHas('auditPlan', function ($q) use ($division) {
                    $q->where('division_id', $division->id);
                })->whereIn('status', ['open', 'in_progress', 'waiting_verification', 'rejected'])->count();
                return $division;
            });

            $data['division_stats'] = $divisionStats;

            $data['recent_activities'] = \App\Models\AuditLog::with('user')
                ->orderBy('created_at', 'desc')->limit(8)->get();
        }

        // ===== SPI Specifics =====
        if ($user->role === 'spi') {
            $data['pending_verifications'] = ActionPlan::where('status', 'submitted')->count();
            $data['waiting_verification_findings'] = Finding::where('status', 'waiting_verification')->count();
        }

        // ===== KEPALA DIVISI Specifics =====
        if ($user->role === 'kepala_divisi') {
            $divisionId = $user->division_id;
            $data['followup_in_progress'] = ActionPlan::whereHas('finding.auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->where('status', 'in_progress')->count();

            $data['followup_submitted'] = ActionPlan::whereHas('finding.auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->where('status', 'submitted')->count();

            $data['followup_rejected'] = ActionPlan::whereHas('finding.auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->where('status', 'rejected')->count();
        }

        // ===== LIST KEGIATAN =====

        // List Findings & Deadlines (Scoped)
        $recentFindingsQuery = Finding::with(['auditPlan.division', 'riskCategory']);
        $upcomingDeadlinesQuery = Finding::where('deadline', '>=', now())
            ->where('status', '!=', 'closed');

        if ($filterDivisionId) {
            $recentFindingsQuery->whereHas('auditPlan', function ($q) use ($filterDivisionId) {
                $q->where('division_id', $filterDivisionId);
            });
            $upcomingDeadlinesQuery->whereHas('auditPlan', function ($q) use ($filterDivisionId) {
                $q->where('division_id', $filterDivisionId);
            });
        }
        if ($year) {
            $recentFindingsQuery->whereYear('created_at', $year);
            $upcomingDeadlinesQuery->whereYear('created_at', $year);
        }

        $data['recent_findings'] = $recentFindingsQuery->orderBy('created_at', 'desc')->limit(5)->get();
        $data['upcoming_deadlines'] = $upcomingDeadlinesQuery->orderBy('deadline')->limit(5)->get();

        // Chart Data (Scoped)
        $findingsByStatusQuery = Finding::selectRaw('status, count(*) as count')->groupBy('status');
        $findingsByRiskQuery = Finding::join('risk_categories', 'findings.risk_category_id', '=', 'risk_categories.id')
            ->selectRaw('risk_categories.level as risk_level, risk_categories.name as risk_name, count(*) as count')
            ->orderByRaw("FIELD(risk_categories.level, 'critical', 'high', 'medium', 'low')")
            ->groupBy('risk_categories.level', 'risk_categories.name');

        if ($filterDivisionId) {
            $findingsByStatusQuery->whereHas('auditPlan', function ($q) use ($filterDivisionId) {
                $q->where('division_id', $filterDivisionId);
            });
            $findingsByRiskQuery->whereHas('auditPlan', function ($q) use ($filterDivisionId) {
                $q->where('division_id', $filterDivisionId);
            });
        }
        if ($year) {
            $findingsByStatusQuery->whereYear('created_at', $year);
            $findingsByRiskQuery->whereYear('findings.created_at', $year);
        }

        $data['status_chart_data'] = $findingsByStatusQuery->pluck('count', 'status')->toArray();
        $data['risk_chart_data'] = $findingsByRiskQuery->pluck('count', 'risk_level')->toArray();

        // ===== KPI DETAIL TABLE (interaktif saat card diklik) =====
        $kpi = $request->filled('kpi') ? $request->kpi : null;
        $data['selected_kpi'] = $kpi;
        $data['kpi_audits'] = collect();
        $data['kpi_findings'] = collect();
        $data['kpi_type'] = null;
        $data['kpi_title'] = null;

        if ($kpi) {
            $auditKpis = [
                'audit_done'    => ['title' => 'Audit Selesai (Laporan)',   'q' => fn () => $auditQuery()->whereHas('finalReports')],
                'audit_ongoing' => ['title' => 'Audit Berlangsung',          'q' => fn () => $auditQuery('in_progress')],
                'audit_pending' => ['title' => 'Belum Diaudit',              'q' => fn () => $auditQuery('scheduled')],
            ];
            $findingKpis = [
                'finding_total'    => ['title' => 'Total Temuan',            'q' => fn () => $findingQuery()],
                'finding_open'     => ['title' => 'Belum Ditindaklanjuti',   'q' => fn () => $findingQuery('open')],
                'finding_progress' => ['title' => 'Ditindaklanjuti Sebagian','q' => fn () => $findingQuery('in_progress')],
                'finding_closed'   => ['title' => 'Selesai Ditindaklanjuti', 'q' => fn () => $findingQuery('closed')],
            ];

            if (isset($auditKpis[$kpi])) {
                $data['kpi_type'] = 'audit';
                $data['kpi_title'] = $auditKpis[$kpi]['title'];
                $data['kpi_audits'] = $auditKpis[$kpi]['q']()
                    ->with('division')
                    ->orderBy('start_date', 'desc')
                    ->paginate(8, ['*'], 'kpi_page')
                    ->withQueryString();
            } elseif (isset($findingKpis[$kpi])) {
                $data['kpi_type'] = 'finding';
                $data['kpi_title'] = $findingKpis[$kpi]['title'];
                $data['kpi_findings'] = $findingKpis[$kpi]['q']()
                    ->with(['auditPlan.division', 'riskCategory'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(8, ['*'], 'kpi_page')
                    ->withQueryString();
            }
        }

        // ===== KALENDER MINI (dashboard) =====
        $data['mini_calendar'] = $this->buildMiniCalendar($filterDivisionId);

        return view('dashboard.index', $data);
    }

    /**
     * Kalender mini bulan berjalan untuk kartu dashboard. Menandai hari yang
     * memiliki jadwal Audit / deadline Temuan (dibatasi data scope divisi).
     */
    protected function buildMiniCalendar(?int $filterDivisionId): array
    {
        $now = Carbon::now();
        $first = $now->copy()->startOfMonth();
        $start = $first->copy()->startOfMonth();
        $end = $first->copy()->endOfMonth();

        $auditQuery = AuditPlan::query();
        $findingQuery = Finding::query();

        if ($filterDivisionId) {
            $auditQuery->where('division_id', $filterDivisionId);
            $findingQuery->whereHas('auditPlan', fn ($q) => $q->where('division_id', $filterDivisionId));
        }

        $auditQuery->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end);
                });
        });

        $audits = $auditQuery->with(['division'])->get();
        $findings = $findingQuery->with(['auditPlan.division'])->whereBetween('deadline', [$start, $end])->get();

        $markers = [];
        $eventsByDate = [];
        foreach ($audits as $a) {
            $color = match ($a->status) {
                'completed' => 'hijau',
                'in_progress' => 'kuning',
                'scheduled' => 'biru',
                default => 'abu',
            };
            $markers[$a->start_date->format('Y-m-d')] = ($markers[$a->start_date->format('Y-m-d')] ?? 0) + 1;
            $eventsByDate[$a->start_date->format('Y-m-d')][] = [
                'type' => 'audit', 'color' => $color, 'label' => $a->audit_number,
                'title' => $a->title, 'url' => route('audit-plans.show', $a),
                'division' => $a->division->name ?? '-', 'date' => $a->start_date->format('Y-m-d'),
            ];
            if ($a->end_date && $a->end_date->format('Y-m-d') !== $a->start_date->format('Y-m-d')) {
                $markers[$a->end_date->format('Y-m-d')] = ($markers[$a->end_date->format('Y-m-d')] ?? 0) + 1;
                $eventsByDate[$a->end_date->format('Y-m-d')][] = [
                    'type' => 'audit_end', 'color' => $color, 'label' => 'Selesai',
                    'title' => $a->title, 'url' => route('audit-plans.show', $a),
                    'division' => $a->division->name ?? '-', 'date' => $a->end_date->format('Y-m-d'),
                ];
            }
        }
        foreach ($findings as $f) {
            $markers[$f->deadline->format('Y-m-d')] = ($markers[$f->deadline->format('Y-m-d')] ?? 0) + 1;
            $eventsByDate[$f->deadline->format('Y-m-d')][] = [
                'type' => 'finding', 'color' => 'merah', 'label' => 'Temuan',
                'title' => $f->finding_number, 'url' => route('findings.show', $f),
                'division' => $f->auditPlan->division->name ?? '-', 'date' => $f->deadline->format('Y-m-d'),
            ];
        }

        // Daftar penjadwalan per tanggal (untuk panel detail di kartu)
        ksort($eventsByDate);
        $schedule = [];
        foreach ($eventsByDate as $date => $evts) {
            $schedule[] = ['date' => $date, 'events' => $evts];
        }

        $kpiStats = [
            'audits_total' => $audits->count(),
            'audits_scheduled' => $audits->where('status', 'scheduled')->count(),
            'audits_ongoing' => $audits->where('status', 'in_progress')->count(),
            'audits_done' => $audits->where('status', 'completed')->count(),
            'findings_total' => $findings->count(),
            'findings_active' => $findings->whereIn('status', ['open', 'in_progress', 'waiting_verification', 'rejected'])->count(),
            'findings_closed' => $findings->where('status', 'closed')->count(),
            'days_with_schedule' => count($eventsByDate),
        ];

        $holidays = Holiday::whereBetween('date', [$start, $end])->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->all();

        $gridStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $end->copy()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor->lte($gridEnd)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $d = $cursor->copy();
                $key = $d->format('Y-m-d');
                $week[] = [
                    'date' => $key,
                    'day' => $d->day,
                    'inMonth' => $d->month === $now->month,
                    'isToday' => $d->isToday(),
                    'isHoliday' => in_array($key, $holidays, true) || $d->isWeekend(),
                    'marker' => $markers[$key] ?? 0,
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return [
            'month' => $now->month,
            'year' => $now->year,
            'monthLabel' => $now->translatedFormat('F Y'),
            'todayMarker' => $markers[$now->format('Y-m-d')] ?? 0,
            'weeks' => $weeks,
            'schedule' => $schedule,
            'kpiStats' => $kpiStats,
        ];
    }
}

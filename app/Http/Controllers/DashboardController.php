<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\AuditType;
use App\Models\Finding;
use App\Models\ActionPlan;
use App\Models\FindingCategory;
use App\Models\Division;
use App\Models\User;
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

        // Helper query
        $auditQuery = function ($status = null) use ($filterDivisionId) {
            $q = AuditPlan::query();
            if ($filterDivisionId) {
                $q->where('division_id', $filterDivisionId);
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

        $findingQuery = function ($status = null) use ($filterDivisionId) {
            $q = Finding::query();
            if ($filterDivisionId) {
                $q->whereHas('auditPlan', function ($qq) use ($filterDivisionId) {
                    $qq->where('division_id', $filterDivisionId);
                });
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
        $data['active_audits'] = $auditQuery(['scheduled', 'in_progress'])->count();
        $data['completed_audits'] = $auditQuery('completed')->count();

        $data['total_findings'] = $findingQuery()->count();
        $data['open_findings'] = $findingQuery('open')->count();
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

            $data['my_active_audits'] = AuditPlan::whereHas('assignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereIn('status', ['scheduled', 'in_progress'])
                ->orderBy('start_date')
                ->limit(5)
                ->get();
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

        // ===== MANAGEMENT Specifics =====
        if ($user->role === 'management') {
            $data['risk_levels'] = Finding::join('risk_categories', 'findings.risk_category_id', '=', 'risk_categories.id')
                ->selectRaw('risk_categories.level as level, count(*) as total')
                ->groupBy('risk_categories.level')
                ->pluck('total', 'level');
        }

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

        $data['status_chart_data'] = $findingsByStatusQuery->pluck('count', 'status')->toArray();
        $data['risk_chart_data'] = $findingsByRiskQuery->pluck('count', 'risk_level')->toArray();

        return view('dashboard.index', $data);
    }
}

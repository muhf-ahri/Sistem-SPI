<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\Finding;
use App\Models\ActionPlan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = [];

        // Data berdasarkan role
        if (in_array($user->role, ['super_admin', 'spi', 'management'])) {
            $data['total_audits'] = AuditPlan::count();
            $data['active_audits'] = AuditPlan::whereIn('status', ['scheduled', 'in_progress'])->count();
            $data['completed_audits'] = AuditPlan::where('status', 'completed')->count();
            $data['total_findings'] = Finding::count();
            $data['open_findings'] = Finding::where('status', 'open')->count();
            $data['high_risk_findings'] = Finding::whereHas('riskCategory', function ($q) {
                $q->where('level', 'high');
            })->count();
            $data['overdue_findings'] = Finding::where('deadline', '<', now())
                ->where('status', '!=', 'closed')
                ->count();
            $data['closed_findings'] = Finding::where('status', 'closed')->count();
        } elseif ($user->role === 'kepala_divisi') {
            $divisionId = $user->division_id;
            $data['total_audits'] = AuditPlan::where('division_id', $divisionId)->count();
            $data['active_audits'] = AuditPlan::where('division_id', $divisionId)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->count();
            $data['completed_audits'] = AuditPlan::where('division_id', $divisionId)
                ->where('status', 'completed')
                ->count();
            $data['total_findings'] = Finding::whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->count();
            $data['open_findings'] = Finding::whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->where('status', 'open')->count();
            $data['high_risk_findings'] = Finding::whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->whereHas('riskCategory', function ($q) {
                $q->where('level', 'high');
            })->count();
            $data['overdue_findings'] = Finding::whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->where('deadline', '<', now())
                ->where('status', '!=', 'closed')
                ->count();
            $data['closed_findings'] = Finding::whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->where('status', 'closed')->count();
        }

        // Recent findings (5 terbaru) & Upcoming deadlines (5 terdekat)
        $recentFindingsQuery = Finding::with(['auditPlan.division', 'riskCategory']);
        $upcomingDeadlinesQuery = Finding::where('deadline', '>=', now())
            ->where('status', '!=', 'closed');

        if ($user->role === 'kepala_divisi') {
            $divisionId = $user->division_id;
            $recentFindingsQuery->whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
            $upcomingDeadlinesQuery->whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }

        $data['recent_findings'] = $recentFindingsQuery->orderBy('created_at', 'desc')->limit(5)->get();
        $data['upcoming_deadlines'] = $upcomingDeadlinesQuery->orderBy('deadline')->limit(5)->get();

        // Recent activities (dari audit_logs)
        $data['recent_activities'] = \App\Models\AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Data Breakdown untuk Grafik Statistik Dashboard
        $findingsByStatusQuery = Finding::selectRaw('status, count(*) as count')->groupBy('status');
        $findingsByRiskQuery = Finding::join('risk_categories', 'findings.risk_category_id', '=', 'risk_categories.id')
            ->selectRaw('risk_categories.name as risk_name, count(*) as count')
            ->groupBy('risk_categories.name');

        if ($user->role === 'kepala_divisi') {
            $divisionId = $user->division_id;
            $findingsByStatusQuery->whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
            $findingsByRiskQuery->whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }

        $data['status_chart_data'] = $findingsByStatusQuery->pluck('count', 'status')->toArray();
        $data['risk_chart_data'] = $findingsByRiskQuery->pluck('count', 'risk_name')->toArray();

        return view('dashboard.index', $data);
    }
}
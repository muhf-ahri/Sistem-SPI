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
    public function index()
    {
        $user = auth()->user();
        $data = [];

        // ===== SUPER ADMIN: data keseluruhan + statistik sistem =====
        if ($user->role === 'super_admin') {
            $data['total_audits'] = AuditPlan::count();
            $data['active_audits'] = AuditPlan::whereIn('status', ['scheduled', 'in_progress'])->count();
            $data['completed_audits'] = AuditPlan::where('status', 'completed')->count();
            $data['total_findings'] = Finding::count();
            $data['open_findings'] = Finding::where('status', 'open')->count();
            $data['high_risk_findings'] = Finding::whereHas('riskCategory', function ($q) {
                $q->where('level', 'high');
            })->count();
            $data['overdue_findings'] = Finding::where('deadline', '<', now())
                ->where('status', '!=', 'closed')->count();
            $data['closed_findings'] = Finding::where('status', 'closed')->count();

            // Statistik sistem & master data
            $data['total_users'] = User::count();
            $data['active_users'] = User::where('is_active', true)->count();
            $data['total_divisions'] = Division::count();
            $data['total_audit_types'] = AuditType::where('is_active', true)->count();
            $data['total_finding_categories'] = FindingCategory::where('is_active', true)->count();

            // Distribusi pengawasan & temuan aktif per divisi
            $data['division_stats'] = Division::withCount(['auditPlans'])
                ->get()
                ->map(function ($division) {
                    $division->active_findings_count = Finding::whereHas('auditPlan', function ($q) use ($division) {
                        $q->where('division_id', $division->id);
                    })->whereIn('status', ['open', 'in_progress', 'waiting_verification', 'rejected'])->count();
                    return $division;
                });

            // Aktivitas terbaru dari audit log
            $data['recent_activities'] = \App\Models\AuditLog::with('user')
                ->orderBy('created_at', 'desc')->limit(8)->get();
        }

        // ===== SPI: fokus pekerjaan & antrean verifikasi =====
        elseif ($user->role === 'spi') {
            $data['total_audits'] = AuditPlan::count();
            $data['active_audits'] = AuditPlan::whereIn('status', ['scheduled', 'in_progress'])->count();
            $data['completed_audits'] = AuditPlan::where('status', 'completed')->count();
            $data['total_findings'] = Finding::count();
            $data['open_findings'] = Finding::where('status', 'open')->count();
            $data['high_risk_findings'] = Finding::whereHas('riskCategory', function ($q) {
                $q->where('level', 'high');
            })->count();
            $data['overdue_findings'] = Finding::where('deadline', '<', now())
                ->where('status', '!=', 'closed')->count();
            $data['closed_findings'] = Finding::where('status', 'closed')->count();

            // Tindak lanjut yang menunggu diverifikasi SPI
            $data['pending_verifications'] = ActionPlan::where('status', 'submitted')->count();
            $data['waiting_verification_findings'] = Finding::where('status', 'waiting_verification')->count();

            // Pengawasan yang menugaskan SPI ini
            $data['my_active_audits'] = AuditPlan::whereHas('assignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereIn('status', ['scheduled', 'in_progress'])
                ->orderBy('start_date')
                ->limit(5)
                ->get();
        }

        // ===== KEPALA DIVISI: hanya data divisinya =====
        elseif ($user->role === 'kepala_divisi') {
            $divisionId = $user->division_id;
            $scopeFindings = function ($q) use ($divisionId) {
                $q->whereHas('auditPlan', function ($qq) use ($divisionId) {
                    $qq->where('division_id', $divisionId);
                });
            };

            $data['total_audits'] = AuditPlan::where('division_id', $divisionId)->count();
            $data['active_audits'] = AuditPlan::where('division_id', $divisionId)
                ->whereIn('status', ['scheduled', 'in_progress'])->count();
            $data['completed_audits'] = AuditPlan::where('division_id', $divisionId)
                ->where('status', 'completed')->count();
            $data['total_findings'] = Finding::where($scopeFindings)->count();
            $data['open_findings'] = Finding::where($scopeFindings)->where('status', 'open')->count();
            $data['high_risk_findings'] = Finding::where($scopeFindings)
                ->whereHas('riskCategory', fn ($q) => $q->where('level', 'high'))->count();
            $data['overdue_findings'] = Finding::where($scopeFindings)
                ->where('deadline', '<', now())->where('status', '!=', 'closed')->count();
            $data['closed_findings'] = Finding::where($scopeFindings)->where('status', 'closed')->count();

            // Progres tindak lanjut divisinya
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

        // ===== MANAGEMENT: monitoring keseluruhan + sebaran risiko =====
        else {
            $data['total_audits'] = AuditPlan::count();
            $data['active_audits'] = AuditPlan::whereIn('status', ['scheduled', 'in_progress'])->count();
            $data['completed_audits'] = AuditPlan::where('status', 'completed')->count();
            $data['total_findings'] = Finding::count();
            $data['open_findings'] = Finding::whereIn('status', ['open', 'in_progress', 'rejected'])->count();
            $data['high_risk_findings'] = Finding::whereHas('riskCategory', function ($q) {
                $q->where('level', 'high');
            })->count();
            $data['overdue_findings'] = Finding::where('deadline', '<', now())
                ->where('status', '!=', 'closed')->count();
            $data['closed_findings'] = Finding::where('status', 'closed')->count();

            // Sebaran tingkat risiko temuan (SISTEM.md §13)
            $data['risk_levels'] = Finding::join('risk_categories', 'findings.risk_category_id', '=', 'risk_categories.id')
                ->selectRaw('risk_categories.level as level, count(*) as total')
                ->groupBy('risk_categories.level')
                ->pluck('total', 'level');
        }

        // Temuan terbaru & tenggat terdekat (scoped untuk kepala divisi)
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

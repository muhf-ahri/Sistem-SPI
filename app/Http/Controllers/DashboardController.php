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
            $data['total_findings'] = Finding::whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->count();
            $data['open_findings'] = Finding::whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            })->where('status', 'open')->count();
            // dsb
        }

        // Recent findings (5 terbaru)
        $data['recent_findings'] = Finding::with(['auditPlan.division', 'riskCategory'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Upcoming deadlines (5 terdekat)
        $data['upcoming_deadlines'] = Finding::where('deadline', '>=', now())
            ->where('status', '!=', 'closed')
            ->orderBy('deadline')
            ->limit(5)
            ->get();

        // Recent activities (dari audit_logs)
        $data['recent_activities'] = \App\Models\AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', $data);
    }
}
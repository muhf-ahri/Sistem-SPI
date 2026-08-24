<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
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

        // Kepala Divisi hanya melihat laporan divisinya
        if (auth()->user()->role === 'kepala_divisi') {
            $divisionId = auth()->user()->division_id;
            $query->whereHas('auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }

        $findings = $query->get();
        return view('reports.finding-analysis', compact('findings'));
    }

    public function actionPlanStatus(Request $request)
    {
        $query = ActionPlan::with(['finding.auditPlan.division', 'pic']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Kepala Divisi hanya melihat laporan divisinya
        if (auth()->user()->role === 'kepala_divisi') {
            $divisionId = auth()->user()->division_id;
            $query->whereHas('finding.auditPlan', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }

        $actionPlans = $query->get();
        return view('reports.action-plan-status', compact('actionPlans'));
    }
}
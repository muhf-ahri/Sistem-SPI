<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\Finding;
use App\Models\ActionPlan;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function audits(Request $request)
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

        $audits = $query->get();
        return view('reports.audits', compact('audits'));
    }

    public function findings(Request $request)
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

        $findings = $query->get();
        return view('reports.findings', compact('findings'));
    }

    public function actionPlans(Request $request)
    {
        $query = ActionPlan::with(['finding.auditPlan.division', 'pic']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $actionPlans = $query->get();
        return view('reports.action-plans', compact('actionPlans'));
    }

    public function risks(Request $request)
    {
        $risks = Finding::with('riskCategory')
            ->selectRaw('risk_category_id, count(*) as total')
            ->groupBy('risk_category_id')
            ->get();
        return view('reports.risks', compact('risks'));
    }
}
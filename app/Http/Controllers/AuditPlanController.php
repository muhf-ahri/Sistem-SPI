<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class AuditPlanController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AuditPlan::class);
        $auditPlans = AuditPlan::with(['division', 'auditType', 'createdBy'])->paginate(10);
        return view('audits.index', compact('auditPlans'));
    }

    public function show(AuditPlan $auditPlan)
    {
        $this->authorize('view', $auditPlan);
        return view('audits.show', compact('auditPlan'));
    }

    public function create()
    {
        $this->authorize('create', AuditPlan::class);
        return view('audits.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', AuditPlan::class);
        // validation dan store...
    }

    public function edit(AuditPlan $auditPlan)
    {
        $this->authorize('update', $auditPlan);
        return view('audits.edit', compact('auditPlan'));
    }

    public function update(Request $request, AuditPlan $auditPlan)
    {
        $this->authorize('update', $auditPlan);
        // update...
    }

    public function destroy(AuditPlan $auditPlan)
    {
        $this->authorize('delete', $auditPlan);
        $auditPlan->delete();
        return redirect()->route('audits.index')->with('success', 'Audit plan deleted.');
    }

    public function assignAuditor(AuditPlan $auditPlan)
    {
        $this->authorize('assignAuditor', $auditPlan);
        // ...
    }
}
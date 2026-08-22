<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        $logs = $query->paginate(20);
        $users = \App\Models\User::pluck('name', 'id');
        $actions = AuditLog::distinct()->pluck('action');
        $entities = AuditLog::distinct()->pluck('entity_type');

        return view('audit-logs.index', compact('logs', 'users', 'actions', 'entities'));
    }

    public function create()
    {
        // Audit logs are typically not created manually
        abort(404);
    }

    public function store(Request $request)
    {
        // Audit logs are typically not created manually
        abort(404);
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('audit-logs.show', compact('auditLog'));
    }

    public function edit(AuditLog $auditLog)
    {
        // Audit logs are typically not editable
        abort(404);
    }

    public function update(Request $request, AuditLog $auditLog)
    {
        // Audit logs are typically not editable
        abort(404);
    }

    public function destroy(AuditLog $auditLog)
    {
        // Audit logs are typically not deletable
        abort(404);
    }
}
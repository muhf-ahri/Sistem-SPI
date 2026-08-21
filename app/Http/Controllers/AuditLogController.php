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
}
<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user.division'])->orderBy('created_at', 'desc');

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        // Pencarian bebas (nama pengguna, aksi, entitas, atau perubahan data)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('entity_type', 'like', "%{$search}%")
                    ->orWhere('new_values', 'like', "%{$search}%")
                    ->orWhere('old_values', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }
        // Filter tahun
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        $logs = $query->paginate(20)->withQueryString();
        $users = \App\Models\User::pluck('name', 'id');
        $actions = AuditLog::distinct()->pluck('action');
        $entities = AuditLog::distinct()->pluck('entity_type');
        $years = AuditLog::selectRaw('YEAR(created_at) as y')->distinct()->orderByDesc('y')->pluck('y');

        return view('audit-logs.index', compact('logs', 'users', 'actions', 'entities', 'years'));
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
        $auditLog->load('user.division');
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
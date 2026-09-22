<?php

namespace App\Http\Controllers;

use App\Models\MonitoringReport;
use App\Helpers\AuditLogHelper;
use Illuminate\Http\Request;

class MonitoringReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = MonitoringReport::with(['auditPlan.division', 'createdBy']);

        // Kepala Divisi hanya melihat laporan dari divisinya sendiri
        if ($user->role === 'kepala_divisi') {
            $query->whereHas('auditPlan', function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            });
        }

        // Filter: Divisi
        if ($request->filled('division')) {
            $query->whereHas('auditPlan', fn ($q) => $q->where('division_id', $request->division));
        }

        // Filter: Tahun (berdasarkan tanggal laporan)
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // Filter: Pencarian (no. laporan / judul / no. Audit)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('auditPlan', fn ($sq) => $sq->where('audit_number', 'like', "%{$search}%"));
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->get();
        $divisions = \App\Models\Division::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $years = \App\Models\MonitoringReport::selectRaw('YEAR(created_at) as y')->distinct()->orderByDesc('y')->pluck('y');

        return view('reports.monitoring', compact('reports', 'divisions', 'years'));
    }

    public function destroy(MonitoringReport $report)
    {
        // Hapus laporan hanya untuk SPI / Super Admin
        abort_unless(in_array(auth()->user()->role, ['spi', 'super_admin']), 403, 'Unauthorized action.');

        $path = storage_path('app/public/' . $report->file_path);
        if (file_exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($report->file_path);
        }
        $report->delete();
        AuditLogHelper::log('delete', 'monitoring_report', $report->id, $report->toArray(), null);

        return back()->with('success', 'Laporan monitoring berhasil dihapus.');
    }
}
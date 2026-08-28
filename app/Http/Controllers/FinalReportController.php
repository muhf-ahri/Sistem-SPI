<?php

namespace App\Http\Controllers;

use App\Models\FinalReport;
use App\Helpers\AuditLogHelper;
use Illuminate\Http\Request;

class FinalReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = FinalReport::with(['auditPlan.division', 'createdBy']);

        // Kepala Divisi / Management hanya melihat laporan dari divisinya sendiri
        if (in_array($user->role, ['kepala_divisi', 'management'])) {
            $query->whereHas('auditPlan', function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        return view('reports.lha', compact('reports'));
    }

    public function destroy(FinalReport $report)
    {
        // Hapus laporan hanya untuk SPI / Super Admin
        abort_unless(in_array(auth()->user()->role, ['spi', 'super_admin']), 403, 'Unauthorized action.');

        $path = storage_path('app/public/' . $report->file_path);
        if (file_exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($report->file_path);
        }
        $report->delete();
        AuditLogHelper::log('delete', 'final_report', $report->id, $report->toArray(), null);

        return back()->with('success', 'Laporan LHA berhasil dihapus.');
    }
}

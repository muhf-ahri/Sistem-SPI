<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\Finding;
use App\Models\ActionPlan;
use App\Models\FinalReport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportExportController extends Controller
{
    public function export(Request $request, $type, $format)
    {
        abort_unless(in_array($type, ['lha', 'audit-summary', 'finding-analysis', 'action-plan-status']), 404);
        abort_unless(in_array($format, ['excel', 'pdf']), 404);

        [$title, $headers, $rows] = $this->buildData($request, $type);
        $meta = [
            'type' => $type,
            'title' => $title,
            'generated_at' => now()->format('d M Y H:i'),
            'generated_by' => auth()->user()->name ?? '-',
        ];

        $blade = view('reports.export', compact('headers', 'rows', 'meta'));

        $filename = 'Laporan_' . ucfirst($type) . '_' . now()->format('Ymd_His');

        if ($format === 'excel') {
            $response = response($blade->render(), 200, [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.xls"',
            ]);
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            return $response;
        }

        // PDF
        return Pdf::loadHTML($blade->render())
            ->setPaper('a4', 'landscape')
            ->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'sans-serif'])
            ->download($filename . '.pdf');
    }

    private function buildData(Request $request, string $type): array
    {
        $user = auth()->user();

        switch ($type) {
            case 'lha':
                $query = FinalReport::with(['auditPlan.division', 'createdBy']);
                if (in_array($user->role, ['kepala_divisi', 'management'])) {
                    $query->whereHas('auditPlan', fn ($q) => $q->where('division_id', $user->division_id));
                }
                if ($request->filled('division')) {
                    $query->whereHas('auditPlan', fn ($q) => $q->where('division_id', $request->division));
                }
                if ($request->filled('year')) {
                    $query->whereYear('created_at', $request->year);
                }
                if ($request->filled('search')) {
                    $search = trim($request->search);
                    $query->where(function ($q) use ($search) {
                        $q->where('report_number', 'like', "%{$search}%")
                            ->orWhere('title', 'like', "%{$search}%")
                            ->orWhereHas('auditPlan', fn ($sq) => $sq->where('audit_number', 'like', "%{$search}%"));
                    });
                }
                $reports = $query->orderBy('created_at', 'desc')->get();
                return [
                    'Laporan Hasil Audit (LHA)',
                    ['No. Laporan', 'Judul', 'Pengawasan', 'Divisi', 'Dibuat Oleh', 'Tanggal', 'Ukuran (KB)'],
                    $reports->map(fn ($r) => [
                        $r->report_number,
                        $r->title,
                        $r->auditPlan->audit_number ?? '-',
                        $r->auditPlan->division->name ?? '-',
                        $r->createdBy->name ?? '-',
                        optional($r->created_at)->format('d M Y') ?? '-',
                        $r->file_size ? number_format($r->file_size / 1024, 1) : '-',
                    ])->toArray(),
                ];

            case 'audit-summary':
                $query = AuditPlan::with(['division', 'auditType', 'createdBy']);
                if ($request->filled('division')) $query->where('division_id', $request->division);
                if ($request->filled('date_from')) $query->whereDate('start_date', '>=', $request->date_from);
                if ($request->filled('date_to')) $query->whereDate('end_date', '<=', $request->date_to);
                if ($request->filled('search')) {
                    $search = trim($request->search);
                    $query->where(fn ($q) => $q->where('audit_number', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%"));
                }
                if ($user->role === 'kepala_divisi') $query->where('division_id', $user->division_id);
                $audits = $query->get();
                return [
                    'Laporan Ringkasan Pengawasan',
                    ['No. Pengawasan', 'Judul', 'Divisi', 'Jenis', 'Periode', 'Pembuat', 'Status'],
                    $audits->map(fn ($a) => [
                        $a->audit_number,
                        $a->title,
                        $a->division->name ?? '-',
                        $a->auditType->name ?? '-',
                        optional($a->start_date)->format('d M Y') . ' - ' . optional($a->end_date)->format('d M Y'),
                        $a->createdBy->name ?? '-',
                        ucwords(str_replace('_', ' ', $a->status)),
                    ])->toArray(),
                ];

            case 'finding-analysis':
                $query = Finding::with(['auditPlan.division', 'category', 'riskCategory']);
                if ($request->filled('division')) $query->whereHas('auditPlan', fn ($q) => $q->where('division_id', $request->division));
                if ($request->filled('risk')) $query->whereHas('riskCategory', fn ($q) => $q->where('level', $request->risk));
                if ($request->filled('year')) $query->whereYear('deadline', $request->year);
                if ($request->filled('search')) {
                    $search = trim($request->search);
                    $query->where(fn ($q) => $q->where('finding_number', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%"));
                }
                if ($user->role === 'kepala_divisi') {
                    $query->whereHas('auditPlan', fn ($q) => $q->where('division_id', $user->division_id));
                }
                $findings = $query->get();
                return [
                    'Laporan Analisis Temuan',
                    ['No. Temuan', 'Judul', 'Divisi', 'Kategori', 'Risiko', 'Deadline', 'Status'],
                    $findings->map(fn ($f) => [
                        $f->finding_number,
                        $f->title,
                        $f->auditPlan->division->name ?? '-',
                        $f->category->name ?? '-',
                        ucfirst($f->riskCategory->level ?? 'low'),
                        optional($f->deadline)->format('d M Y') ?? '-',
                        ucwords(str_replace('_', ' ', $f->status)),
                    ])->toArray(),
                ];

            case 'action-plan-status':
                $query = ActionPlan::with(['finding.auditPlan.division', 'pic']);
                if ($request->filled('status')) $query->where('status', $request->status);
                if ($request->filled('division')) $query->whereHas('finding.auditPlan', fn ($q) => $q->where('division_id', $request->division));
                if ($request->filled('year')) $query->whereYear('target_date', $request->year);
                if ($request->filled('search')) {
                    $search = trim($request->search);
                    $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhereHas('finding', fn ($sq) => $sq->where('finding_number', 'like', "%{$search}%")));
                }
                if ($user->role === 'kepala_divisi') {
                    $query->whereHas('finding.auditPlan', fn ($q) => $q->where('division_id', $user->division_id));
                }
                $actionPlans = $query->get();
                return [
                    'Laporan Status Tindak Lanjut',
                    ['No. Temuan', 'Rencana Aksi', 'PIC', 'Divisi', 'Target Date', 'Status'],
                    $actionPlans->map(fn ($p) => [
                        $p->finding->finding_number ?? '-',
                        $p->action,
                        $p->pic->name ?? '-',
                        $p->finding->auditPlan->division->name ?? '-',
                        optional($p->target_date)->format('d M Y') ?? '-',
                        ucwords(str_replace('_', ' ', $p->status)),
                    ])->toArray(),
                ];
        }
    }
}

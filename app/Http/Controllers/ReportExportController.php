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
            $xlsx = $this->makeXlsx($title, $headers, $rows);
            return response($xlsx, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
                'Content-Length' => strlen($xlsx),
            ]);
        }

        // PDF
        return Pdf::loadHTML($blade->render())
            ->setPaper('a4', 'landscape')
            ->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'sans-serif'])
            ->download($filename . '.pdf');
    }

    // Bangun file .xlsx (Open XML) secara langsung tanpa library eksternal.
    private function makeXlsx(string $title, array $headers, array $rows): string
    {
        $escape = fn ($s) => htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $sheetRows = '';

        foreach ($rows as $row) {
            $cells = '';
            foreach ($headers as $i => $h) {
                $v = $row[$i] ?? '';
                if (is_numeric($v)) {
                    $cells .= '<c t="n"><v>' . $v . '</v></c>';
                } else {
                    $cells .= '<c t="inlineStr"><is><t xml:space="preserve">' . $escape($v) . '</t></is></c>';
                }
            }
            $sheetRows .= '<row>' . $cells . '</row>';
        }

        // Header title (2 baris atas) + baris kolom
        $titleCells = '<c t="inlineStr" s="1"><is><t>' . $escape($title) . '</t></is></c>';
        $subtitle = 'PT Pindad Enjiniring Indonesia - Satuan Pengawasan Internal';
        $subCells = '<c t="inlineStr" s="2"><is><t>' . $escape($subtitle) . '</t></is></c>';
        $headerRow = '';
        foreach ($headers as $h) {
            $headerRow .= '<c t="inlineStr" s="3"><is><t>' . $escape($h) . '</t></is></c>';
        }

        $cols = count($headers);
        static $cellStyles = [
            1 => '<xf numFmtId="0" fontId="1" fillId="1" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>',
            2 => '<xf numFmtId="0" fontId="0" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>',
            3 => '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center"/></xf>',
        ];
        $styles = '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';
        $styles .= '<cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>{'
            . $cellStyles[1] . $cellStyles[2] . $cellStyles[3] . '}</cellXfs>';
        $styles .= '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>';

        $zip = new \ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip->open($tmp, \ZipArchive::OVERWRITE);

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);

        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<sheetData>'
            . '<row>' . $titleCells . '</row>'
            . '<row>' . $subCells . '</row>'
            . '<row>' . $headerRow . '</row>'
            . $sheetRows
            . '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet);

        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="14"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>
<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF2F2F2"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDDEBF7"/></patternFill></fill></fills>
<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/><diagonal/></border></borders>
' . $styles . '
</styleSheet>');

        $zip->close();
        $data = file_get_contents($tmp);
        @unlink($tmp);
        return $data;
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

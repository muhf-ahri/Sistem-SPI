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
        $colsMeta = $this->colMeta($type);
        $meta = [
            'type' => $type,
            'title' => $title,
            'generated_at' => now()->format('d M Y H:i'),
            'generated_by' => auth()->user()->name ?? '-',
        ];

        $blade = view('reports.export', compact('headers', 'rows', 'meta'));

        $filename = 'Laporan_' . ucfirst($type) . '_' . now()->format('Ymd_His');

        if ($format === 'excel') {
            $xlsx = $this->makeXlsx($title, $colsMeta, $headers, $rows);
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

    // Deskripsi styling tiap kolom per jenis laporan (urutan = urutan kolom di $headers).
    private function colMeta(string $type): array
    {
        $left   = ['align' => 'left',  'status' => false, 'width' => null, 'wrap' => true];
        $center = ['align' => 'center', 'status' => false, 'width' => null, 'wrap' => true];
        $status = ['align' => 'center', 'status' => true,  'width' => null, 'wrap' => true];

        switch ($type) {
            case 'audit-summary':
                return [
                    array_merge($center, ['width' => 20]),   // No. Audit
                    array_merge($left,  ['width' => 38]),    // Judul
                    array_merge($left,  ['width' => 26]),    // Divisi
                    array_merge($left,  ['width' => 24]),    // Jenis
                    array_merge($center, ['width' => 32]),   // Periode
                    array_merge($center, ['width' => 22]),   // Pembuat
                    $status,                                  // Status
                ];
            case 'finding-analysis':
                return [
                    array_merge($center, ['width' => 18]),
                    array_merge($left,  ['width' => 38]),
                    array_merge($left,  ['width' => 26]),
                    array_merge($left,  ['width' => 24]),
                    array_merge($center, ['width' => 14]),
                    array_merge($center, ['width' => 18]),
                    $status,
                ];
            case 'action-plan-status':
                return [
                    array_merge($center, ['width' => 18]),
                    array_merge($left,  ['width' => 42]),
                    array_merge($center, ['width' => 22]),
                    array_merge($left,  ['width' => 26]),
                    array_merge($center, ['width' => 18]),
                    $status,
                ];
            default: // lha
                return [
                    array_merge($center, ['width' => 20]),
                    array_merge($left,  ['width' => 36]),
                    array_merge($center, ['width' => 16]),
                    array_merge($left,  ['width' => 26]),
                    array_merge($center, ['width' => 22]),
                    array_merge($center, ['width' => 18]),
                    array_merge($center, ['width' => 14]),
                ];
        }
    }

    // Bangun file .xlsx (Open XML) secara langsung tanpa library eksternal.
    private function makeXlsx(string $title, array $colsMeta, array $headers, array $rows): string
    {
        $escape = fn ($s) => htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        // ===================== SEL =====================
        $cellXml = function ($v, int $style) use ($escape) {
            if (is_numeric($v)) {
                return '<c s="' . $style . '" t="n"><v>' . $v . '</v></c>';
            }
            return '<c s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $escape($v) . '</t></is></c>';
        };

        // Baris data
        $sheetRows = '';
        foreach ($rows as $row) {
            $cells = '';
            foreach ($colsMeta as $i => $meta) {
                $v = $row[$i] ?? '';
                // Pembulatan angka untuk tampilan
                if (is_numeric($v) && floor($v) != $v) {
                    $v = round((float) $v, 2);
                }
                if (!empty($meta['status']) && strtolower((string) $v) === 'completed') {
                    $style = 6; // hijau
                } else {
                    $style = $meta['align'] === 'center' ? 5 : 4;
                }
                $cells .= $cellXml($v, $style);
            }
            $sheetRows .= '<row>' . $cells . '</row>';
        }

        // Baris header dokumen
        $titleCells = $cellXml($title, 1);
        $subtitle = 'PT Pindad Enjiniring Indonesia - Satuan Audit Internal';
        $subCells = $cellXml($subtitle, 2);
        $headerRow = '';
        foreach ($headers as $h) {
            $headerRow .= $cellXml($h, 3);
        }

        // ===================== LEBAR KOLOM =====================
        $colWidths = [];
        foreach ($colsMeta as $i => $meta) {
            if (!empty($meta['width'])) {
                $colWidths[$i] = $meta['width'];
                continue;
            }
            $len = mb_strlen((string) $headers[$i]);
            foreach ($rows as $row) {
                $cellLen = mb_strlen((string) ($row[$i] ?? ''));
                if ($cellLen > $len) $len = $cellLen;
            }
            $colWidths[$i] = max(10, min(40, round($len * 1.15) + 3));
        }
        $colsXml = '<cols>';
        $colIndex = 0;
        // Kolom pertama agak lebih lebar untuk nomor
        foreach ($colWidths as $w) {
            $colIndex++;
            $colsXml .= '<col min="' . $colIndex . '" max="' . $colIndex . '" width="' . $w . '" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        // ===================== STYLE =====================
        // xs: 0 default, 1 judul, 2 sub-judul, 3 header, 4 data-kiri(wrap), 5 data-center(wrap), 6 status-hijau
        $cellStyles = [
            1 => '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>',
            2 => '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>',
            3 => '<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
            4 => '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>',
            5 => '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
            6 => '<xf numFmtId="0" fontId="4" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>',
        ];
        $styles = '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';
        $styles .= '<cellXfs count="7"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . $cellStyles[1] . $cellStyles[2] . $cellStyles[3]
            . $cellStyles[4] . $cellStyles[5] . $cellStyles[6]
            . '</cellXfs>';
        $styles .= '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>';

        // ===================== ZIP =====================
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

        $footerRow = '<row>'
            . $cellXml('Dokumen dihasilkan otomatis oleh Sistem SPI - PT Pindad Enjiniring Indonesia', 5)
            . '</row>';

        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
' . $colsXml . '
<sheetData>'
            . '<row ht="24">' . $titleCells . '</row>'
            . '<row ht="16">' . $subCells . '</row>'
            . '<row ht="28">' . $headerRow . '</row>'
            . $sheetRows
            . '<row ht="20">' . $footerRow . '</row>'
            . '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet);

        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="5">
<font><sz val="11"/><name val="Calibri"/></font>
<font><b/><sz val="15"/><color rgb="FF1B365D"/><name val="Calibri"/></font>
<font><i/><sz val="10"/><color rgb="FF606060"/><name val="Calibri"/></font>
<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
<font><b/><sz val="11"/><color rgb="FF155724"/><name val="Calibri"/></font>
</fonts>
<fills count="4">
<fill><patternFill patternType="none"/></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FF1B365D"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFD4EDDA"/></patternFill></fill>
</fills>
<borders count="2">
<border><left/><right/><top/><bottom/><diagonal/></border>
<border><left style="thin"><color rgb="FFA6A6A6"/></left><right style="thin"><color rgb="FFA6A6A6"/></right><top style="thin"><color rgb="FFA6A6A6"/></top><bottom style="thin"><color rgb="FFA6A6A6"/></bottom><diagonal/></border>
</borders>
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
                if (in_array($user->role, ['kepala_divisi'])) {
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
                    ['No. Laporan', 'Judul', 'Audit', 'Divisi', 'Dibuat Oleh', 'Tanggal', 'Ukuran (KB)'],
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
                    'Laporan Ringkasan Audit',
                    ['No. Audit', 'Judul', 'Divisi', 'Jenis', 'Periode', 'Pembuat', 'Status'],
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

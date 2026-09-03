<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\Finding;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        // Halaman kalender khusus Admin (Super Admin)
        abort_unless(auth()->user()->role === 'super_admin', 403, 'Halaman kalender hanya untuk Admin.');

        $month = $request->filled('month') ? (int) $request->month : now()->month;
        $year = $request->filled('year') ? (int) $request->year : now()->year;

        $month = min(12, max(1, $month));
        $year = min(2100, max(2000, $year));

        $first = Carbon::create($year, $month, 1);
        $start = $first->copy()->startOfMonth();
        $end = $first->copy()->endOfMonth();

        $auditQuery = AuditPlan::with(['division', 'auditType'])->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end);
                });
        });
        $findingQuery = Finding::with(['auditPlan.division'])->whereBetween('deadline', [$start, $end]);

        $audits = $auditQuery->orderBy('start_date')->get();
        $findings = $findingQuery->orderBy('deadline')->get();

        $holidays = Holiday::whereBetween('date', [$start, $end])->orderBy('date')->get()->keyBy(fn ($h) => $h->date->format('Y-m-d'));

        // Event per tanggal (untuk grid + panel detail kanan)
        $eventsByDate = [];
        foreach ($audits as $a) {
            $color = match ($a->status) {
                'completed' => 'hijau',
                'in_progress' => 'kuning',
                'scheduled' => 'biru',
                default => 'abu',
            };
            $eventsByDate[$a->start_date->format('Y-m-d')][] = [
                'type' => 'audit', 'color' => $color, 'label' => $a->audit_number,
                'title' => $a->title, 'url' => route('audit-plans.show', $a),
                'division' => $a->division->name ?? '-', 'date' => $a->start_date->format('Y-m-d'),
            ];
            if ($a->end_date && $a->end_date->format('Y-m-d') !== $a->start_date->format('Y-m-d')) {
                $eventsByDate[$a->end_date->format('Y-m-d')][] = [
                    'type' => 'audit_end', 'color' => $color, 'label' => 'Selesai',
                    'title' => $a->title, 'url' => route('audit-plans.show', $a),
                    'division' => $a->division->name ?? '-', 'date' => $a->end_date->format('Y-m-d'),
                ];
            }
        }
        foreach ($findings as $f) {
            $eventsByDate[$f->deadline->format('Y-m-d')][] = [
                'type' => 'finding', 'color' => 'merah', 'label' => 'Temuan',
                'title' => $f->finding_number, 'url' => route('findings.show', $f),
                'division' => $f->auditPlan->division->name ?? '-', 'date' => $f->deadline->format('Y-m-d'),
            ];
        }

        // Daftar penjadwalan bulan berjalan (untuk panel kiri/tabel KPI atas)
        ksort($eventsByDate);
        $schedule = [];
        foreach ($eventsByDate as $date => $evts) {
            $schedule[] = ['date' => $date, 'events' => $evts];
        }

        // KPI bulan berjalan (untuk tabel atas)
        $kpiStats = [
            'audits_total' => $audits->count(),
            'audits_scheduled' => $audits->where('status', 'scheduled')->count(),
            'audits_ongoing' => $audits->where('status', 'in_progress')->count(),
            'audits_done' => $audits->where('status', 'completed')->count(),
            'findings_total' => $findings->count(),
            'findings_open' => $findings->whereIn('status', ['open', 'in_progress', 'waiting_verification', 'rejected'])->count(),
            'findings_closed' => $findings->where('status', 'closed')->count(),
            'holidays_count' => $holidays->count(),
            'days_with_schedule' => count($eventsByDate),
        ];

        // Build kalender grid
        $gridStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $end->copy()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor->lte($gridEnd)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $d = $cursor->copy();
                $key = $d->format('Y-m-d');
                $week[] = [
                    'date' => $d,
                    'inMonth' => $d->month === $month,
                    'isToday' => $d->isToday(),
                    'isWeekend' => $d->isWeekend(),
                    'isHoliday' => isset($holidays[$key]) || $d->isWeekend(),
                    'holidayName' => isset($holidays[$key]) ? $holidays[$key]->name : null,
                    'events' => $eventsByDate[$key] ?? [],
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        $prev = Carbon::create($year, $month, 1)->subMonth();
        $next = Carbon::create($year, $month, 1)->addMonth();
        $years = range(now()->year - 2, now()->year + 2);

        return view('calendar.index', compact('month', 'year', 'weeks', 'prev', 'next', 'years', 'holidays', 'schedule', 'kpiStats'));
    }
}

<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Support\WorkingDayCalculator;
use App\Helpers\AuditLogHelper;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim($request->search) . '%');
        }

        $holidays = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        $years = Holiday::selectRaw('YEAR(year) as y')->distinct()->orderByDesc('y')->pluck('y');
        $types = Holiday::TYPES;
        $totalNational = Holiday::where('type', 'national')->count();
        $totalCustom = Holiday::where('type', 'custom')->count();

        return view('master.holidays.index', compact('holidays', 'years', 'types', 'totalNational', 'totalCustom'));
    }

    public function create()
    {
        return view('master.holidays.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'type' => 'required|in:national,international,custom',
            'note' => 'nullable|string|max:255',
        ]);

        $date = \Carbon\Carbon::parse($request->date);

        Holiday::updateOrCreate(
            ['date' => $date->format('Y-m-d')],
            [
                'name' => $request->name,
                'type' => $request->type,
                'year' => $date->year,
                'note' => $request->note,
            ]
        );

        WorkingDayCalculator::clearHolidayCache();
        AuditLogHelper::log('create', 'holiday', $date->year, null, $request->only(['date', 'name', 'type']));

        return redirect()->route('master.holidays.index')->with('success', 'Hari libur berhasil disimpan.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        WorkingDayCalculator::clearHolidayCache();
        AuditLogHelper::log('delete', 'holiday', $holiday->id, $holiday->toArray(), null);
        return redirect()->route('master.holidays.index')->with('success', 'Hari libur dihapus.');
    }

    public function sync(Request $request)
    {
        $year = $request->filled('year') ? $request->year : now()->year;

        $exitCode = \Illuminate\Support\Facades\Artisan::call('holidays:sync', ['--year' => $year]);
        $output = \Illuminate\Support\Facades\Artisan::output();

        if ($exitCode === 0) {
            return back()->with('success', trim($output));
        }
        return back()->with('error', trim($output) ?: 'Gagal sinkronisasi hari libur.');
    }
}

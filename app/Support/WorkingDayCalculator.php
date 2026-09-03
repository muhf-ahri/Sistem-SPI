<?php

namespace App\Support;

use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class WorkingDayCalculator
{
    /**
     * Cek apakah satu tanggal merupakan hari kerja: bukan Sabtu/Minggu
     * dan bukan hari libur (nasional/internasional/custom).
     *
     * @param  CarbonInterface|string|int  $date
     */
    public static function isWorkingDay($date): bool
    {
        $date = Carbon::parse($date);
        if ($date->isWeekend()) {
            return false;
        }
        return !self::isStoredHoliday($date);
    }

    /**
     * Hari kerja dihitung inklusif: menghitung start & end apabila keduanya
     * hari kerja. Jumlah hari kerja antara $start dan $end (inklusif).
     *
     * @return int>0  minimal 1 bila rentang valid
     */
    public static function countWorkingDays($start, $end = null): int
    {
        $start = Carbon::parse($start)->startOfDay();
        $end = $end === null ? $start->copy() : Carbon::parse($end)->startOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $count = 0;
        $cursor = $start->copy();
        $limit = 366 * 6; // pengaman agar tidak infinite loop

        while ($cursor->lte($end) && $limit-- > 0) {
            if (self::isWorkingDay($cursor)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    /**
     * Hari kerja yang tersisa dari tanggal awal sampai ke $end,
     * mengambil konteks "sekarang" untuk pengerjaan temuan (start = hari ini).
     *
     * @return int>0
     */
    public static function remainingWorkingDays(string $end): int
    {
        return self::countWorkingDays(now()->toDateString(), $end);
    }

    /**
     * Cek apakah tanggal tercatat sebagai hari libur di tabel holidays.
     * Dibatasi per tahun dan di-cache untuk performa realtime.
     */
    protected static function isStoredHoliday(CarbonInterface $date): bool
    {
        $year = $date->year;
        $key = "holiday_dates_{$year}";

        $dates = Cache::remember($key, 3600 * 6, function () use ($year) {
            return Holiday::where('year', $year)->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->all();
        });

        return in_array($date->format('Y-m-d'), $dates, true);
    }

    public static function clearHolidayCache(): void
    {
        // Bersihkan tahun di tabel + jendela tahun sekitar sekarang, karena
        // cache bertahan antar-proses (store database) dan sebuah tahun bisa
        // dibersihkan semua barisnya sehingga tak terwakili lagi di query pluck.
        $years = Holiday::query()->distinct()->pluck('year')->push(now()->year)->unique()->all();
        foreach ($years as $y) {
            for ($off = -1; $off <= 1; $off++) {
                Cache::forget("holiday_dates_" . ($y + $off));
            }
        }
    }
}

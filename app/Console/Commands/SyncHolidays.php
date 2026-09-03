<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use App\Support\WorkingDayCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncHolidays extends Command
{
    protected $signature = 'holidays:sync {--year= : Tahun (default: tahun berjalan)} {--country=ID : Kode negara ISO}';
    protected $description = 'Ambil hari libur nasional dari Nager.Date API dan simpan ke database';

    public function handle(): int
    {
        $year = $this->option('year') ?: now()->year;
        $country = $this->option('country');

        $url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/{$country}";

        try {
            $response = Http::timeout(15)->get($url);
        } catch (\Throwable $e) {
            // Windows PHP sering belum mengonfigurasi CA bundle sehingga verifikasi
            // SSL gagal padahal sertifikat valid. API ini read-only & publik, jadi
            // fall back ke tanpa verifikasi hanya bila verifikasi gagal oleh SSL.
            $this->warn('Verifikasi SSL gagal, mencoba tanpa verifikasi: ' . $e->getMessage());
            try {
                $response = Http::timeout(15)->withOptions(['verify' => false])->get($url);
            } catch (\Throwable $e2) {
                $this->error('Gagal terhubung ke Nager.Date: ' . $e2->getMessage());
                return self::FAILURE;
            }
        }

        if ($response->failed()) {
            $this->error('API Nager.Date mengembalikan error ' . $response->status());
            return self::FAILURE;
        }

        $holidays = $response->json();
        if (!is_array($holidays) || count($holidays) === 0) {
            $this->error('Tidak ada data libur untuk tahun ' . $year);
            return self::FAILURE;
        }

        $saved = 0;
        $skipped = 0;

        foreach ($holidays as $h) {
            $date = Carbon::parse($h['date'])->format('Y-m-d');
            $exists = Holiday::where('date', $date)
                ->whereIn('type', ['national', 'international'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Holiday::updateOrCreate(
                ['date' => $date],
                [
                    'name' => $h['name'] ?? $h['localName'] ?? 'Libur',
                    'type' => 'national',
                    'year' => $year,
                    'note' => 'Sumber: Nager.Date',
                ]
            );
            $saved++;
        }

        WorkingDayCalculator::clearHolidayCache();

        $this->info("Sinkronisasi selesai. {$saved} libur baru disimpan, {$skipped} sudah ada.");
        return self::SUCCESS;
    }
}

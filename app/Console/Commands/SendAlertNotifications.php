<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AlertNotificationService;
use Illuminate\Console\Command;

class SendAlertNotifications extends Command
{
    protected $signature = 'notifications:alerts';
    protected $description = 'Kirim notifikasi peringatan otomatis (opsional — default dijalankan on-request via php artisan serve)';

    public function handle(AlertNotificationService $service): int
    {
        $users = User::where('is_active', true)->get();

        foreach ($users as $user) {
            $service->checkFor($user);
        }

        $this->info('Pemeriksaan peringatan selesai.');
        return self::SUCCESS;
    }
}

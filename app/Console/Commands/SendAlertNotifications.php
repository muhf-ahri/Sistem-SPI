<?php

namespace App\Console\Commands;

use App\Models\ActionPlan;
use App\Models\AuditPlan;
use App\Models\Finding;
use App\Models\Division;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAlertNotifications extends Command
{
    protected $signature = 'notifications:alerts';
    protected $description = 'Kirim notifikasi peringatan otomatis (jadwal audit, temuan, verifikasi, close audit)';

    public function handle(): int
    {
        $today = Carbon::today();

        $this->warnMissedAudits($today);
        $this->warnAuditCountdown($today);
        $this->warnUnfixedFindings($today);
        $this->warnPendingVerification($today);
        $this->warnUnclosedAudits($today);

        $this->info('Pemeriksaan peringatan selesai.');
        return self::SUCCESS;
    }

    protected function spiUsers()
    {
        return User::where('role', 'spi')->where('is_active', true)->get();
    }

    protected function alreadyNotified(User $user, string $alertKey): bool
    {
        return $user->notifications()->where('data->alert_key', $alertKey)->exists();
    }

    protected function divisionHeads(int $divisionId)
    {
        return User::where('division_id', $divisionId)
            ->where('role', 'kepala_divisi')
            ->where('is_active', true)
            ->get();
    }

    // 1) Peringatan jadwal Audit yang terlewat
    protected function warnMissedAudits(Carbon $today): void
    {
        $audits = AuditPlan::with(['division'])
            ->where('status', 'scheduled')
            ->where('start_date', '<', $today)
            ->get();

        foreach ($this->spiUsers() as $user) {
            foreach ($audits as $a) {
                $key = "audit-missed-A{$a->id}-" . $today->format('Y-m-d');
                if ($this->alreadyNotified($user, $key)) {
                    continue;
                }
                $days = (int) $today->diffInDays($a->start_date);
                NotificationService::sendToUsers(
                    $user->id,
                    'Jadwal Audit Terlewat',
                    "Audit {$a->audit_number} ({$a->division->name}) telah terlewat {$days} hari dan belum dimulai. Segera jadwalkan ulang.",
                    route('audit-plans.show', $a),
                    'danger',
                    $key
                );
            }
        }
    }

    // 2) Peringatan hitung mundur hari (mulai H-3) sebelum Audit berlangsung
    protected function warnAuditCountdown(Carbon $today): void
    {
        $start = $today->copy()->addDay();
        $end = $today->copy()->addDays(3);

        $audits = AuditPlan::with(['division'])
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->whereBetween('start_date', [$start, $end])
            ->get();

        foreach ($this->spiUsers() as $user) {
            foreach ($audits as $a) {
                $daysLeft = (int) $today->diffInDays($a->start_date);
                if ($daysLeft < 1 || $daysLeft > 3) {
                    continue;
                }
                $key = "audit-countdown-A{$a->id}-D{$daysLeft}";
                if ($this->alreadyNotified($user, $key)) {
                    continue;
                }
                NotificationService::sendToUsers(
                    $user->id,
                    "H-{$daysLeft}: Audit Segera Berlangsung",
                    "Audit {$a->audit_number} ({$a->division->name}) akan berlangsung {$daysLeft} hari lagi pada " . $a->start_date->format('d M Y') . ".",
                    route('audit-plans.show', $a),
                    'danger',
                    $key
                );
            }
        }
    }

    // 3) Peringatan Temuan belum diperbaiki (untuk Divisi / Kepala Divisi)
    protected function warnUnfixedFindings(Carbon $today): void
    {
        $divisions = Division::where('is_active', true)->get();

        foreach ($divisions as $division) {
            $findings = Finding::with(['auditPlan.division'])
                ->whereHas('auditPlan', fn ($q) => $q->where('division_id', $division->id))
                ->where('deadline', '<=', $today)
                ->where('status', '!=', 'closed')
                ->get();

            foreach ($this->divisionHeads($division->id) as $user) {
                foreach ($findings as $f) {
                    $days = (int) $today->diffInDays($f->deadline);
                    $key = "finding-unfixed-F{$f->id}-" . $today->format('Y-m-d');
                    if ($this->alreadyNotified($user, $key)) {
                        continue;
                    }
                    NotificationService::sendToUsers(
                        $user->id,
                        'Temuan Belum Diperbaiki',
                        "Temuan {$f->finding_number} telah lewat batas {$days} hari dan belum diperbaiki. Segera tindak lanjuti.",
                        route('findings.show', $f),
                        'danger',
                        $key
                    );
                }
            }
        }
    }

    // 4) Peringatan verifikasi tindak lanjut (untuk SPI)
    protected function warnPendingVerification(Carbon $today): void
    {
        $plans = ActionPlan::with(['finding'])
            ->where('status', 'submitted')
            ->get();

        foreach ($this->spiUsers() as $user) {
            foreach ($plans as $p) {
                $key = "verif-pending-AP{$p->id}-" . $today->format('Y-m-d');
                if ($this->alreadyNotified($user, $key)) {
                    continue;
                }
                NotificationService::sendToUsers(
                    $user->id,
                    'Tindak Lanjut Menunggu Verifikasi',
                    "Rencana tindak lanjut untuk temuan {$p->finding->finding_number} menunggu verifikasi Anda.",
                    route('action-plans.show', $p),
                    'danger',
                    $key
                );
            }
        }
    }

    // 5) Peringatan Audit belum diselesaikan / di-close (untuk SPI)
    protected function warnUnclosedAudits(Carbon $today): void
    {
        $audits = AuditPlan::with(['division'])
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where('end_date', '<', $today)
            ->get();

        foreach ($this->spiUsers() as $user) {
            foreach ($audits as $a) {
                $days = (int) $today->diffInDays($a->end_date);
                $key = "audit-unclosed-A{$a->id}-" . $today->format('Y-m-d');
                if ($this->alreadyNotified($user, $key)) {
                    continue;
                }
                NotificationService::sendToUsers(
                    $user->id,
                    'Audit Belum Diselesaikan',
                    "Audit {$a->audit_number} ({$a->division->name}) telah melewati tanggal selesai {$days} hari namun belum ditutup.",
                    route('audit-plans.show', $a),
                    'danger',
                    $key
                );
            }
        }
    }
}

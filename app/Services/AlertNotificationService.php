<?php

namespace App\Services;

use App\Models\ActionPlan;
use App\Models\AuditPlan;
use App\Models\Finding;
use App\Models\User;
use Carbon\Carbon;

class AlertNotificationService
{
    /**
     * Jalankan pemeriksaan peringatan untuk satu pengguna (berdasarkan peran & divisi).
     * Disebut tiap request (on-request) atau dari command notifications:alerts.
     * Dedup memakai alert_key agar tidak spam.
     */
    public function checkFor(User $user): void
    {
        if (!$user->is_active) {
            return;
        }

        $today = Carbon::today();

        if ($user->role === 'spi') {
            $this->warnMissedAudits($user, $today);
            $this->warnAuditCountdown($user, $today);
            $this->warnUnclosedAudits($user, $today);
            $this->warnPendingVerification($user, $today);
        }

        if ($user->role === 'kepala_divisi') {
            $this->warnUnfixedFindings($user, $today);
        }
    }

    protected function alreadyNotified(User $user, string $alertKey): bool
    {
        return $user->notifications()->where('data->alert_key', $alertKey)->exists();
    }

    // 1) Peringatan jadwal Audit yang terlewat
    protected function warnMissedAudits(User $user, Carbon $today): void
    {
        $audits = AuditPlan::with(['division'])
            ->where('status', 'scheduled')
            ->where('start_date', '<', $today)
            ->get();

        foreach ($audits as $a) {
            $key = "audit-missed-A{$a->id}-" . $today->format('Y-m-d');
            if ($this->alreadyNotified($user, $key)) {
                continue;
            }
            $days = abs((int) $today->diffInDays($a->start_date));
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

    // 2) Peringatan hitung mundur hari (mulai H-3) sebelum Audit berlangsung
    protected function warnAuditCountdown(User $user, Carbon $today): void
    {
        $start = $today->copy()->addDay();
        $end = $today->copy()->addDays(3);

        $audits = AuditPlan::with(['division'])
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->whereBetween('start_date', [$start, $end])
            ->get();

        foreach ($audits as $a) {
            $daysLeft = abs((int) $today->diffInDays($a->start_date));
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

    // 3) Peringatan Temuan belum diperbaiki (untuk Divisi / Kepala Divisi)
    protected function warnUnfixedFindings(User $user, Carbon $today): void
    {
        $divisionId = $user->division_id;
        if (!$divisionId) {
            return;
        }

        $findings = Finding::with(['auditPlan.division'])
            ->whereHas('auditPlan', fn ($q) => $q->where('division_id', $divisionId))
            ->where('deadline', '<=', $today)
            ->where('status', '!=', 'closed')
            ->get();

        foreach ($findings as $f) {
            $days = abs((int) $today->diffInDays($f->deadline));
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

    // 4) Peringatan verifikasi tindak lanjut (untuk SPI)
    protected function warnPendingVerification(User $user, Carbon $today): void
    {
        $plans = ActionPlan::with(['finding'])
            ->where('status', 'submitted')
            ->get();

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

    // 5) Peringatan Audit belum diselesaikan / di-close (untuk SPI)
    protected function warnUnclosedAudits(User $user, Carbon $today): void
    {
        $audits = AuditPlan::with(['division'])
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where('end_date', '<', $today)
            ->get();

        foreach ($audits as $a) {
            $days = abs((int) $today->diffInDays($a->end_date));
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

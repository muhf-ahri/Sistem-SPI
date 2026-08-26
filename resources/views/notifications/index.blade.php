@extends('layouts.app')

@section('title', 'Notifikasi')

@section('breadcrumb', 'Notifikasi')

@section('styles')
    <style>
        /* Daftar notifikasi halaman penuh: lembar pemberitahuan */
        .notif-row {
            display: flex; align-items: flex-start; gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--garis-halus);
            border-left: 3px solid transparent;
            color: inherit; text-decoration: none;
            transition: background .15s ease;
        }
        .notif-row:last-child { border-bottom: none; }
        .notif-row:hover { background: var(--kertas); color: inherit; }
        .notif-row.is-unread { border-left-color: var(--kuning); background: #fffdf4; }
        .notif-row.is-unread:hover { background: #fdf8e3; }

        .notif-icon {
            flex: 0 0 auto;
            width: 38px; height: 38px;
            display: grid; place-items: center;
            border-radius: 2px;
            border: 1px solid transparent;
            font-size: 1.05rem;
        }
        .notif-body { min-width: 0; flex: 1; }
        .notif-name {
            font-size: .9rem; font-weight: 500; line-height: 1.35;
            color: #24384e; margin: 0 0 .15rem;
        }
        .is-unread .notif-name { font-weight: 700; color: var(--tinta); }
        .notif-msg { font-size: .84rem; line-height: 1.5; color: #64788d; margin: 0 0 .3rem; }
        .notif-time {
            font-family: var(--font-mono);
            font-size: .66rem; letter-spacing: .06em;
            color: #93a5b6; text-transform: uppercase;
        }
        .notif-dot {
            display: inline-block;
            width: 8px; height: 8px; border-radius: 1px;
            background: var(--kuning);
            flex: 0 0 auto; margin-top: .7rem;
        }

        .notif-count-chip {
            font-family: var(--font-mono);
            font-size: .64rem; font-weight: 600;
            letter-spacing: .06em;
            color: var(--tinta);
            background: var(--kuning);
            border-radius: 2px;
            padding: .2em .55em;
        }
    </style>
@endsection

@section('content')
    <div class="sdx-page-head">
        <div>
            <div class="sdx-eyebrow">Rekam Pemberitahuan</div>
            <h1>Notifikasi</h1>
            <p class="sdx-page-desc">
                Semua pemberitahuan untuk Anda: penugasan pengawasan, temuan, tindak lanjut,
                dan hasil verifikasi sesuai peran serta divisi Anda.
            </p>
        </div>
        <div class="sdx-page-actions">
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.markAsRead') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-check2-all me-1"></i>Tandai semua dibaca
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>Daftar Pemberitahuan</span>
            <span class="text-muted" style="letter-spacing:.08em;">
                {{ $unreadCount }} belum dibaca &middot; {{ $notifications->total() }} total
            </span>
        </div>

        @forelse($notifications as $notification)
            @php
                $toneMap = [
                    'info'    => ['icon' => 'bi-info-circle',          'tone' => 'sdx-notif-tone--blue'],
                    'success' => ['icon' => 'bi-check2-circle',        'tone' => 'sdx-notif-tone--green'],
                    'warning' => ['icon' => 'bi-exclamation-triangle', 'tone' => 'sdx-notif-tone--gold'],
                    'danger'  => ['icon' => 'bi-x-octagon',            'tone' => 'sdx-notif-tone--red'],
                ];
                $tone = $toneMap[$notification->data['type'] ?? 'info'] ?? $toneMap['info'];
            @endphp
            <a class="notif-row {{ $notification->unread() ? 'is-unread' : '' }}"
               href="{{ $notification->data['url'] ?? '#' }}"
               data-notif-id="{{ $notification->id }}">
                <span class="notif-icon sdx-notif-tone {{ $tone['tone'] }}"><i class="bi {{ $tone['icon'] }}"></i></span>
                <span class="notif-body">
                    <p class="notif-name">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                    <p class="notif-msg">{{ $notification->data['message'] ?? '' }}</p>
                    <span class="notif-time">{{ $notification->created_at->translatedFormat('d F Y, H:i') }} &middot; {{ $notification->created_at->diffForHumans() }}</span>
                </span>
                @if($notification->unread())
                    <span class="notif-dot"></span>
                @endif
            </a>
        @empty
            <div class="card-body text-center py-5">
                <div class="sdx-empty-icon mb-3"><i class="bi bi-bell-slash"></i></div>
                <strong class="d-block mb-1">Belum ada notifikasi</strong>
                <span style="font-family:var(--font-mono);font-size:.66rem;letter-spacing:.08em;text-transform:uppercase;color:#93a5b6;">
                    Pemberitahuan akan muncul di sini
                </span>
            </div>
        @endforelse

        @if($notifications->hasPages())
            <div class="card-footer d-flex justify-content-center py-3">
                {{ $notifications->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (!csrf) return;

            document.querySelectorAll('.notif-row[data-notif-id]').forEach(function (row) {
                row.addEventListener('click', function () {
                    try {
                        fetch('{{ route("notifications.markAsRead") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf.content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ id: row.dataset.notifId }),
                            keepalive: true
                        });
                    } catch (e) { /* navigasi tetap dilanjutkan */ }
                });
            });
        });
    </script>
@endsection

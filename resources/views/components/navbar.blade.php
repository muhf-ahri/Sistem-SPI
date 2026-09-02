<!-- Navbar Component -->
@php
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'spi' => 'Auditor SPI',
        'kepala_divisi' => 'Kepala Divisi',
        'staff' => 'Staff',
    ];

    $unreadCount = auth()->user()->unreadNotifications()->count();
    $recentNotifications = auth()->user()->notifications()->latest()->take(5)->get();

    $notifTones = [
        'info'    => ['icon' => 'bi-info-circle',          'tone' => 'sdx-notif-tone--blue'],
        'success' => ['icon' => 'bi-check2-circle',        'tone' => 'sdx-notif-tone--green'],
        'warning' => ['icon' => 'bi-exclamation-triangle', 'tone' => 'sdx-notif-tone--gold'],
        'danger'  => ['icon' => 'bi-x-octagon',            'tone' => 'sdx-notif-tone--red'],
    ];
@endphp
<nav class="sdx-topbar">
    <style>
        /* Lonceng notifikasi: stempel kontrol yang bisa ditekan */
        .sdx-bell.sdx-bell-btn {
            position: relative;
            width: 36px; height: 36px;
            display: grid; place-items: center;
            color: var(--baja);
            background: transparent;
            border: 1px solid var(--garis-halus);
            border-radius: 2px;
            font-size: 1.05rem; line-height: 1;
            opacity: 1; cursor: pointer;
            transition: background .18s ease, color .18s ease, border-color .18s ease;
        }
        .sdx-bell.sdx-bell-btn:hover,
        .sdx-bell.sdx-bell-btn[aria-expanded="true"] {
            color: var(--tinta);
            background: var(--kertas);
            border-color: var(--garis);
        }
        .sdx-bell-badge {
            position: absolute; top: -6px; right: -6px;
            min-width: 16px; height: 16px;
            display: grid; place-items: center;
            padding: 0 4px;
            background: var(--merah);
            color: #fff;
            font-family: var(--font-mono);
            font-size: .58rem; font-weight: 600;
            font-variant-numeric: tabular-nums;
            border-radius: 2px;
            border: 1.5px solid #fff;
            line-height: 1;
        }

        /* Menu notifikasi: lembar pemberitahuan berkontrol */
        .sdx-notif-menu {
            width: min(360px, calc(100vw - 2rem));
            padding: 0;
            overflow: hidden;
        }
        .sdx-notif-head {
            display: flex; align-items: center; justify-content: space-between; gap: .5rem;
            padding: .65rem .9rem;
            background: #f6f9fb;
            border-bottom: 1px solid var(--garis-halus);
        }
        .sdx-notif-title {
            font-family: var(--font-mono);
            font-size: .7rem; font-weight: 600;
            letter-spacing: .14em; text-transform: uppercase;
            color: var(--tinta);
        }
        .sdx-notif-count {
            font-family: var(--font-mono);
            font-size: .62rem; font-weight: 600;
            letter-spacing: .06em;
            color: var(--tinta);
            background: var(--kuning);
            border-radius: 2px;
            padding: .15em .5em;
            margin-left: .45rem;
            vertical-align: middle;
        }
        .sdx-notif-markall {
            border: none; background: transparent; cursor: pointer;
            font-family: var(--font-mono);
            font-size: .62rem; font-weight: 500;
            letter-spacing: .08em; text-transform: uppercase;
            color: var(--baja);
            padding: .25rem .4rem; border-radius: 2px;
        }
        .sdx-notif-markall:hover { color: var(--tinta); background: #e9eef5; }

        .sdx-notif-list { max-height: 330px; overflow-y: auto; }

        .sdx-notif-item {
            display: flex; align-items: flex-start; gap: .75rem;
            padding: .8rem 1rem .8rem .85rem;
            border-bottom: 1px solid var(--garis-halus);
            border-left: 3px solid transparent;
            color: inherit; text-decoration: none;
            transition: background .15s ease;
        }
        .sdx-notif-list .sdx-notif-item:last-child { border-bottom: none; }
        .sdx-notif-item:hover { background: var(--kertas); color: inherit; }
        .sdx-notif-item.is-unread { border-left-color: var(--kuning); background: #fffdf4; }
        .sdx-notif-item.is-unread:hover { background: #fdf8e3; }

        .sdx-notif-icon {
            flex: 0 0 auto;
            width: 30px; height: 30px;
            display: grid; place-items: center;
            border-radius: 2px;
            border: 1px solid transparent;
            font-size: .9rem;
        }
        .sdx-notif-tone--blue  { color: #2c62b8; background: #e9f1fb; border-color: #c4d8f0; }
        .sdx-notif-tone--green { color: #177244; background: #e5f4ec; border-color: #bfe3cf; }
        .sdx-notif-tone--gold  { color: #8a6d00; background: #fcf4d8; border-color: #ecd98a; }
        .sdx-notif-tone--red   { color: #b02a25; background: #fceeee; border-color: #efc4c1; }

        .sdx-notif-body { min-width: 0; flex: 1; }
        .sdx-notif-name {
            font-size: .83rem; font-weight: 500; line-height: 1.35;
            color: #24384e; margin: 0 0 .12rem;
        }
        .is-unread .sdx-notif-name { font-weight: 700; color: var(--tinta); }
        .sdx-notif-msg {
            font-size: .77rem; line-height: 1.45;
            color: #64788d;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden;
            margin: 0 0 .3rem;
        }
        .sdx-notif-time {
            font-family: var(--font-mono);
            font-size: .62rem; letter-spacing: .06em;
            color: #93a5b6; text-transform: uppercase;
        }
        .sdx-notif-dot {
            display: inline-block;
            width: 7px; height: 7px; border-radius: 1px;
            background: var(--kuning);
            flex: 0 0 auto; margin-top: .55rem;
        }

        .sdx-notif-foot > a,
        .sdx-notif-foot > button {
            display: block; width: 100%; text-align: center;
            padding: .62rem;
            font-family: var(--font-mono);
            font-size: .66rem; font-weight: 600;
            letter-spacing: .14em; text-transform: uppercase;
            color: var(--tinta); text-decoration: none;
            background: transparent; border: none; cursor: pointer;
            border-top: 1px solid var(--garis-halus);
        }
        .sdx-notif-foot > a:hover,
        .sdx-notif-foot > button:hover { background: var(--kertas); }

        .sdx-notif-empty { padding: 1.7rem 1rem; text-align: center; }
        .sdx-notif-empty-icon {
            width: 52px; height: 52px;
            display: grid; place-items: center;
            margin-inline: auto; margin-bottom: .7rem;
            border: 1.5px dashed var(--garis);
            border-radius: 2px;
            color: var(--baja);
            font-size: 1.3rem;
            background: #f6f9fb;
        }
        .sdx-notif-empty strong {
            display: block;
            font-size: .85rem; font-weight: 700; color: var(--tinta);
            margin-bottom: .2rem;
        }
        .sdx-notif-empty span {
            font-family: var(--font-mono);
            font-size: .64rem; letter-spacing: .08em; text-transform: uppercase;
            color: #93a5b6;
        }
    </style>
    <div class="sdx-topbar-inner">
        <button class="sdx-burger" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-label="Buka menu navigasi">
            <i class="bi bi-list"></i>
        </button>

        <span class="sdx-page-title">@yield('breadcrumb', 'Sistem Audit Intern')</span>

        <div class="sdx-topbar-right">
            <div class="dropdown">
                <button type="button" class="sdx-bell sdx-bell-btn" id="notificationDropdown"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                        aria-label="{{ $unreadCount > 0 ? $unreadCount . ' notifikasi belum dibaca' : 'Notifikasi' }}">
                    <i class="bi bi-bell{{ $unreadCount > 0 ? '-fill' : '' }}"></i>
                    @if($unreadCount > 0)
                        <span class="sdx-bell-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end sdx-notif-menu" aria-labelledby="notificationDropdown">
                    <div class="sdx-notif-head">
                        <span class="sdx-notif-title">Notifikasi</span>
                        @if($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.markAsRead') }}">
                                @csrf
                                <button type="submit" class="sdx-notif-markall">Tandai semua dibaca</button>
                            </form>
                        @endif
                    </div>

                    <div class="sdx-notif-list">
                        @forelse($recentNotifications as $notification)
                            @php $tone = $notifTones[$notification->data['type'] ?? 'info'] ?? $notifTones['info']; @endphp
                            <a class="sdx-notif-item {{ $notification->unread() ? 'is-unread' : '' }}"
                               href="{{ $notification->data['url'] ?? '#' }}"
                               data-notif-id="{{ $notification->id }}">
                                <span class="sdx-notif-icon {{ $tone['tone'] }}"><i class="bi {{ $tone['icon'] }}"></i></span>
                                <span class="sdx-notif-body">
                                    <p class="sdx-notif-name">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                                    <p class="sdx-notif-msg">{{ $notification->data['message'] ?? '' }}</p>
                                    <span class="sdx-notif-time">{{ $notification->created_at->diffForHumans() }}</span>
                                </span>
                                @if($notification->unread())
                                    <span class="sdx-notif-dot" title="Belum dibaca"></span>
                                @endif
                            </a>
                        @empty
                            <div class="sdx-notif-empty">
                                <span class="sdx-notif-empty-icon"><i class="bi bi-bell-slash"></i></span>
                                <strong>Belum ada notifikasi</strong>
                                <span>Pemberitahuan akan muncul di sini</span>
                            </div>
                        @endforelse
                    </div>

                    <div class="sdx-notif-foot">
                        <a href="{{ route('notifications.index') }}">Lihat semua notifikasi</a>
                    </div>
                </div>
            </div>

            <span class="sdx-divider-v"></span>

            <div class="dropdown">
                <a class="sdx-user-btn dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="sdx-avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="text-start">
                        <span class="sdx-user-name d-block">{{ auth()->user()->name }}</span>
                        <span class="sdx-user-role">{{ $roleLabels[auth()->user()->role] ?? ucwords(str_replace('_', ' ', auth()->user()->role)) }}</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var csrf = document.querySelector('meta[name="csrf-token"]');
        if (!csrf) return;

        document.querySelectorAll('.sdx-notif-item[data-notif-id]').forEach(function (item) {
            item.addEventListener('click', function () {
                try {
                    fetch('{{ route("notifications.markAsRead") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf.content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ id: item.dataset.notifId }),
                        keepalive: true
                    });
                } catch (e) { /* navigasi tetap dilanjutkan */ }
            });
        });
    });
</script>

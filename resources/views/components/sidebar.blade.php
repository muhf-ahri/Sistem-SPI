<!-- Sidebar Component (Teknikal / Blueprint Engineering Theme) -->
<nav class="sidebar" aria-label="Navigasi utama">
    <div class="sdx-brand">
        <span class="sdx-brand-mark"><img src="{{ asset('images/PEI.png') }}" alt="Logo PT Pindad Enjiniring Indonesia"></span>
        <div class="sdx-brand-info">
            <span class="sdx-brand-tag">DOK. UTAMA</span>
            <div class="sdx-brand-name">SPI PINDAD</div>
            <div class="sdx-brand-sub">PEI / ENG-SPI</div>
        </div>
    </div>

    <ul class="sdx-nav">
        <li class="sdx-section"><span class="sdx-sec-code">01</span> KONTROL UTAMA</li>
        <li class="sdx-item">
            <a class="sdx-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" {{ request()->routeIs('dashboard') ? 'aria-current="page"' : '' }}>
                <i class="bi bi-speedometer2"></i>
                <span class="sdx-link-text">Dashboard</span>
            </a>
        </li>

        <!-- Pengawasan -->
        <li class="sdx-item">
            <a class="sdx-link {{ request()->routeIs('audit-plans.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuPengawasan" role="button" aria-expanded="{{ request()->routeIs('audit-plans.*') ? 'true' : 'false' }}" aria-controls="menuPengawasan">
                <i class="bi bi-clipboard-check"></i>
                <span class="sdx-link-text">Pengawasan</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse {{ request()->routeIs('audit-plans.*') ? 'show' : '' }}" id="menuPengawasan">
                <ul class="sdx-sub">
                    <li><a class="sdx-link {{ request()->routeIs('audit-plans.index') && !request('status') ? 'active' : '' }}" href="{{ route('audit-plans.index') }}"><span class="sub-dot"></span>Semua Pengawasan</a></li>
                    <li><a class="sdx-link {{ request('status') == 'draft' ? 'active' : '' }}" href="{{ route('audit-plans.index', ['status' => 'draft']) }}"><span class="sub-dot"></span>Rencana Pengawasan</a></li>
                    <li><a class="sdx-link {{ request('status') == 'in_progress' ? 'active' : '' }}" href="{{ route('audit-plans.index', ['status' => 'in_progress']) }}"><span class="sub-dot"></span>Sedang Berlangsung</a></li>
                    <li><a class="sdx-link {{ request('status') == 'completed' ? 'active' : '' }}" href="{{ route('audit-plans.index', ['status' => 'completed']) }}"><span class="sub-dot"></span>Riwayat Pengawasan</a></li>
                </ul>
            </div>
        </li>

        <!-- Temuan -->
        <li class="sdx-item">
            <a class="sdx-link {{ request()->routeIs('findings.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuTemuan" role="button" aria-expanded="{{ request()->routeIs('findings.*') ? 'true' : 'false' }}" aria-controls="menuTemuan">
                <i class="bi bi-exclamation-triangle"></i>
                <span class="sdx-link-text">Temuan</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse {{ request()->routeIs('findings.*') ? 'show' : '' }}" id="menuTemuan">
                <ul class="sdx-sub">
                    <li><a class="sdx-link {{ request()->routeIs('findings.index') && !request('status') && !request('overdue') ? 'active' : '' }}" href="{{ route('findings.index') }}"><span class="sub-dot"></span>Semua Temuan</a></li>
                    <li><a class="sdx-link {{ request('status') == 'open' ? 'active' : '' }}" href="{{ route('findings.index', ['status' => 'open']) }}"><span class="sub-dot"></span>Open</a></li>
                    <li><a class="sdx-link {{ request('status') == 'in_progress' ? 'active' : '' }}" href="{{ route('findings.index', ['status' => 'in_progress']) }}"><span class="sub-dot"></span>In Progress</a></li>
                    <li><a class="sdx-link {{ request('status') == 'waiting_verification' ? 'active' : '' }}" href="{{ route('findings.index', ['status' => 'waiting_verification']) }}"><span class="sub-dot"></span>Waiting Verification</a></li>
                    <li><a class="sdx-link {{ request('status') == 'closed' ? 'active' : '' }}" href="{{ route('findings.index', ['status' => 'closed']) }}"><span class="sub-dot"></span>Closed</a></li>
                    <li><a class="sdx-link {{ request('overdue') ? 'active' : '' }}" href="{{ route('findings.index', ['overdue' => 1]) }}"><span class="sub-dot"></span>Overdue</a></li>
                </ul>
            </div>
        </li>

        <!-- Tindak Lanjut -->
        <li class="sdx-item">
            <a class="sdx-link {{ request()->routeIs('action-plans.*') ? 'active' : '' }}" href="{{ route('action-plans.index') }}">
                <i class="bi bi-arrow-repeat"></i>
                <span class="sdx-link-text">Tindak Lanjut</span>
            </a>
        </li>

        <!-- Pemeriksaan -->
        <li class="sdx-item">
            <a class="sdx-link {{ request()->routeIs('inspections.*') ? 'active' : '' }}" href="{{ route('inspections.index') }}">
                <i class="bi bi-search"></i>
                <span class="sdx-link-text">Pemeriksaan</span>
            </a>
        </li>

        <!-- Laporan -->
        <li class="sdx-item">
            <a class="sdx-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuLaporan" role="button" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}" aria-controls="menuLaporan">
                <i class="bi bi-file-earmark-text"></i>
                <span class="sdx-link-text">Laporan</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="menuLaporan">
                <ul class="sdx-sub">
                    <li><a class="sdx-link {{ request()->routeIs('reports.audit-summary') ? 'active' : '' }}" href="{{ route('reports.audit-summary') }}"><span class="sub-dot"></span>Laporan Pengawasan</a></li>
                    <li><a class="sdx-link {{ request()->routeIs('reports.finding-analysis') ? 'active' : '' }}" href="{{ route('reports.finding-analysis') }}"><span class="sub-dot"></span>Laporan Temuan &amp; Risiko</a></li>
                    <li><a class="sdx-link {{ request()->routeIs('reports.action-plan-status') ? 'active' : '' }}" href="{{ route('reports.action-plan-status') }}"><span class="sub-dot"></span>Laporan Tindak Lanjut</a></li>
                </ul>
            </div>
        </li>

        @can('manage-master')
        <li class="sdx-section"><span class="sdx-sec-code">02</span> ADMINISTRASI</li>
        <!-- Master Data (Super Admin & SPI saja) -->
        <li class="sdx-item">
            <a class="sdx-link {{ request()->routeIs('master.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuMaster" role="button" aria-expanded="{{ request()->routeIs('master.*') ? 'true' : 'false' }}" aria-controls="menuMaster">
                <i class="bi bi-gear"></i>
                <span class="sdx-link-text">Master Data</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse {{ request()->routeIs('master.*') ? 'show' : '' }}" id="menuMaster">
                <ul class="sdx-sub">
                    @can('viewAny', App\Models\Division::class)
                        <li><a class="sdx-link {{ request()->routeIs('master.divisions.*') ? 'active' : '' }}" href="{{ route('master.divisions.index') }}"><span class="sub-dot"></span>Divisi</a></li>
                    @endcan
                    <li><a class="sdx-link {{ request()->routeIs('master.audit-types.*') ? 'active' : '' }}" href="{{ route('master.audit-types.index') }}"><span class="sub-dot"></span>Jenis Pengawasan</a></li>
                    <li><a class="sdx-link {{ request()->routeIs('master.finding-categories.*') ? 'active' : '' }}" href="{{ route('master.finding-categories.index') }}"><span class="sub-dot"></span>Kategori Temuan</a></li>
                    <li><a class="sdx-link {{ request()->routeIs('master.risk-categories.*') ? 'active' : '' }}" href="{{ route('master.risk-categories.index') }}"><span class="sub-dot"></span>Kategori Risiko</a></li>
                    @can('viewAny', App\Models\User::class)
                        <li><a class="sdx-link {{ request()->routeIs('master.users.*') ? 'active' : '' }}" href="{{ route('master.users.index') }}"><span class="sub-dot"></span>Users</a></li>
                    @endcan
                </ul>
            </div>
        </li>

        <!-- Audit Log (Super Admin & SPI saja) -->
        <li class="sdx-item">
            <a class="sdx-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">
                <i class="bi bi-clock-history"></i>
                <span class="sdx-link-text">Audit Log</span>
            </a>
        </li>
        @endcan
    </ul>

    <div class="sdx-foot">
        <a class="sdx-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
            <i class="bi bi-person"></i>
            <span class="sdx-link-text">Profil Pengguna</span>
        </a>
        <div class="sdx-sys-status">
            <span class="sys-dot"></span> SISTEM ONLINE
        </div>
    </div>
</nav>

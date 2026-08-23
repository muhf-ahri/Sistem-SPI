<!-- Navbar Component -->
@php
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'spi' => 'Auditor SPI',
        'kepala_divisi' => 'Kepala Divisi',
        'management' => 'Management',
        'staff' => 'Staff',
    ];
@endphp
<nav class="sdx-topbar">
    <div class="sdx-topbar-inner">
        <button class="sdx-burger" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-label="Buka menu navigasi">
            <i class="bi bi-list"></i>
        </button>

        <span class="sdx-page-title">@yield('breadcrumb', 'Sistem Pengawasan Intern')</span>

        <div class="sdx-topbar-right">
            <span class="sdx-bell" aria-disabled="true" title="Notifikasi belum tersedia">
                <i class="bi bi-bell"></i>
            </span>

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

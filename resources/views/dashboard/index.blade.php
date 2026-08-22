@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold">Dashboard</h1>
        <span class="text-muted">Selamat datang, {{ auth()->user()->name }}</span>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <x-stat-card icon="clipboard-check" label="Total Pengawasan" value="{{ $total_audits ?? 0 }}" color="primary" />
        </div>
        <div class="col-md-3 col-sm-6">
            <x-stat-card icon="play-circle" label="Aktif" value="{{ $active_audits ?? 0 }}" color="success" />
        </div>
        <div class="col-md-3 col-sm-6">
            <x-stat-card icon="exclamation-triangle" label="Total Temuan" value="{{ $total_findings ?? 0 }}" color="warning" />
        </div>
        <div class="col-md-3 col-sm-6">
            <x-stat-card icon="check-circle" label="Selesai" value="{{ $closed_findings ?? 0 }}" color="info" />
        </div>
    </div>

    <!-- Charts dan Tabel -->
    <div class="row g-4">
        <div class="col-lg-8">
            <x-card header="Temuan Terbaru">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Judul</th>
                                <th>Divisi</th>
                                <th>Risiko</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_findings ?? [] as $finding)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><a href="{{ route('findings.show', $finding) }}">{{ $finding->title }}</a></td>
                                    <td>{{ $finding->auditPlan->division->name ?? '-' }}</td>
                                    <td><x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" /></td>
                                    <td><x-status-badge status="{{ $finding->status }}" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Belum ada temuan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
        <div class="col-lg-4">
            <x-card header="Deadline Mendatang">
                @forelse($upcoming_deadlines ?? [] as $finding)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <span>{{ $finding->title }}</span>
                        <span class="badge bg-{{ $finding->deadline <= now()->addDays(3) ? 'danger' : 'warning' }}">
                            {{ \Carbon\Carbon::parse($finding->deadline)->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p class="text-muted text-center my-3">Tidak ada deadline mendatang.</p>
                @endforelse
            </x-card>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row mt-4">
        <div class="col-12">
            <x-card header="Aktivitas Terbaru">
                <ul class="list-group list-group-flush">
                    @forelse($recent_activities ?? [] as $activity)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-circle-fill text-{{ $activity->action == 'create' ? 'success' : ($activity->action == 'update' ? 'warning' : 'danger') }}" style="font-size: 0.5rem;"></i>
                                {{ $activity->user->name }} <strong>{{ $activity->action }}</strong> {{ str_replace('_', ' ', $activity->entity_type) }}
                            </span>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>
    </div>
@endsection
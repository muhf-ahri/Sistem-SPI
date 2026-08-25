@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <x-page-header
        eyebrow="AKUN PENGGUNA"
        title="Profile"
        description="Kelola informasi akun, password, dan hapus akun jika diperlukan."
    />

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Informasi Profil</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Update Password</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-danger">
                <div class="card-header">
                    <h5 class="text-danger">Zona Berbahaya</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection

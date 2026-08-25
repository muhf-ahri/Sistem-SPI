@extends('layouts.guest')

@section('title', 'Verifikasi email')

@section('content')
    <h2 class="agx-heading">Verifikasi email Anda</h2>
    <p class="agx-sub">Tautan verifikasi telah dikirim ke email yang Anda daftarkan. Buka tautan tersebut untuk mengaktifkan akun. Jika belum diterima, kirim ulang tautan di bawah.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert agx-alert agx-alert-success" role="alert">
            Tautan verifikasi baru telah dikirim ke email Anda.
        </div>
    @endif

    <div class="d-grid gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="agx-submit">Kirim ulang tautan verifikasi</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="agx-link btn btn-link w-100 p-0 mt-1">Keluar dari akun</button>
        </form>
    </div>
@endsection

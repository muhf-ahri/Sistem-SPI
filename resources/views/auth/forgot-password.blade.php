@extends('layouts.guest')

@section('title', 'Lupa password')

@section('content')
    <h2 class="agx-heading">Lupa password?</h2>
    <p class="agx-sub">Masukkan email kantor Anda. Tautan atur ulang password akan dikirim jika email terdaftar.</p>

    @if (session('status'))
        <div class="alert agx-alert agx-alert-success" role="alert">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert agx-alert agx-alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="agx-label">Email</label>
            <input type="email" id="email" name="email"
                   class="agx-input @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
                   placeholder="nama@pindad.co.id"
                   autocomplete="email" required autofocus>
            @error('email')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="agx-submit">Kirim tautan atur ulang</button>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="agx-link">Kembali ke halaman masuk</a>
        </div>
    </form>
@endsection

@extends('layouts.guest')

@section('title', 'Atur ulang password')

@section('content')
    <h2 class="agx-heading">Atur ulang password</h2>
    <p class="agx-sub">Buat password baru untuk akun Anda. Gunakan kombinasi yang belum pernah dipakai sebelumnya.</p>

    @if ($errors->any())
        <div class="alert agx-alert agx-alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="agx-label">Email</label>
            <input type="email" id="email" name="email"
                   class="agx-input @error('email') is-invalid @enderror"
                   value="{{ old('email', $request->email) }}"
                   autocomplete="username" required autofocus>
            @error('email')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="agx-label">Password baru</label>
            <input type="password" id="password" name="password"
                   class="agx-input @error('password') is-invalid @enderror"
                   autocomplete="new-password" required>
            @error('password')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="agx-label">Ulangi password baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="agx-input @error('password_confirmation') is-invalid @enderror"
                   autocomplete="new-password" required>
            @error('password_confirmation')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="agx-submit">Simpan password baru</button>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="agx-link">Kembali ke halaman masuk</a>
        </div>
    </form>
@endsection

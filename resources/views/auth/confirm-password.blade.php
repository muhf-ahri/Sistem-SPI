<x-guest-layout>
    <x-slot name="title">Konfirmasi password</x-slot>

    <h2 class="agx-heading">Konfirmasi password</h2>
    <p class="agx-sub">Ini area terlindungi. Masukkan password Anda satu kali untuk melanjutkan.</p>

    @if ($errors->any())
        <div class="alert agx-alert agx-alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="password" class="agx-label">Password</label>
            <input type="password" id="password" name="password"
                   class="agx-input @error('password') is-invalid @enderror"
                   autocomplete="current-password" required autofocus>
            @error('password')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="agx-submit">Konfirmasi</button>
    </form>
</x-guest-layout>

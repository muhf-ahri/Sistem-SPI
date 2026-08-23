<x-guest-layout>
    <x-slot name="title">Daftar akun</x-slot>

    <h2 class="agx-heading">Buat akun baru</h2>
    <p class="agx-sub">Daftarkan akun untuk mengakses Sistem Pengawasan Internal. Akun akan aktif setelah email diverifikasi.</p>

    @if ($errors->any())
        <div class="alert agx-alert agx-alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="name" class="agx-label">Nama lengkap</label>
            <input type="text" id="name" name="name"
                   class="agx-input @error('name') is-invalid @enderror"
                   value="{{ old('name') }}"
                   placeholder="Nama sesuai kepegawaian"
                   autocomplete="name" required autofocus>
            @error('name')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="agx-label">Email</label>
            <input type="email" id="email" name="email"
                   class="agx-input @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
                   placeholder="nama@pindad.co.id"
                   autocomplete="email" required>
            @error('email')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="division_id" class="agx-label">Divisi <span class="text-muted fw-normal">(opsional)</span></label>
            <select id="division_id" name="division_id" class="agx-input form-select @error('division_id') is-invalid @enderror">
                <option value="">Pilih divisi</option>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                        {{ $division->name }} ({{ $division->code }})
                    </option>
                @endforeach
            </select>
            @error('division_id')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="agx-label">Password</label>
            <input type="password" id="password" name="password"
                   class="agx-input @error('password') is-invalid @enderror"
                   placeholder="Minimal 8 karakter"
                   autocomplete="new-password" required>
            @error('password')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="agx-label">Ulangi password</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="agx-input @error('password_confirmation') is-invalid @enderror"
                   autocomplete="new-password" required>
            @error('password_confirmation')
                <div class="agx-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="agx-submit">Daftar</button>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="agx-link">Sudah punya akun? Masuk</a>
        </div>
    </form>
</x-guest-layout>

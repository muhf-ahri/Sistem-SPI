<form method="post" action="{{ route('profile.update') }}" novalidate>
    @csrf
    @method('patch')

    <div class="mb-3">
        <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $user->email) }}" required autocomplete="username"
               pattern=".+@spi\.com$" title="Email harus menggunakan domain @spi.com">
        <div class="form-text">Email hanya dapat menggunakan domain resmi <code>@spi.com</code>.</div>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>

        @if (session('status') === 'profile-updated')
            <span class="small text-success">Tersimpan.</span>
        @endif
    </div>
</form>

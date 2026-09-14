<form method="post" action="{{ route('profile.password.update') }}" novalidate>
    @csrf
    @method('put')

    <div class="mb-3">
        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
        <input type="password" id="current_password" name="current_password"
               class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
               autocomplete="current-password" required>
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
        <input type="password" id="password" name="password"
               class="form-control @error('password', 'updatePassword') is-invalid @enderror"
               autocomplete="new-password" required>
        @error('password', 'updatePassword')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Ulangi Password Baru <span class="text-danger">*</span></label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
               autocomplete="new-password" required>
        @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">Simpan Password</button>

        @if (session('status') === 'password-updated')
            <span class="small text-success">Password diperbarui.</span>
        @endif
    </div>

    <div class="alert alert-light border mt-3 mb-0 d-flex align-items-center gap-2 py-2 px-3">
        <i class="bi bi-info-circle text-muted"></i>
        <span class="small text-muted">Lupa password? Hubungi Tim SPI:</span>
        <a href="https://wa.me/6282130641298?text=Halo%20Tim%20SPI%2C%20saya%20lupa%20password%20akun%20Sistem%20Audit%20Internal.%20Mohon%20bantuan%20reset%20akun%20saya.%20Terima%20kasih."
           target="_blank" rel="noopener" class="btn btn-sm btn-outline-success ms-auto">
            <i class="bi bi-whatsapp me-1"></i>Chat Tim SPI
        </a>
    </div>
</form>

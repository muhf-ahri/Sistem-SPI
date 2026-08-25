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
</form>

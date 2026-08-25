<p class="text-muted small">
    Setelah akun dihapus, seluruh data terkait akan hilang permanen. Unduh dahulu data yang masih diperlukan
    sebelum melanjutkan.
</p>

<button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#hapusAkun">
    <i class="bi bi-trash me-2"></i>Hapus Akun
</button>

<div class="modal fade" id="hapusAkun" tabindex="-1" aria-labelledby="hapusAkunLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('profile.destroy') }}" novalidate>
                @csrf
                @method('delete')
                <div class="modal-body text-center p-4">
                    <div class="sdx-empty-icon" style="width: 56px; height: 56px; font-size: 1.4rem; background: #fbeeed; color: #c6362b; border-color: #efc4c1;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h5 class="mt-3 mb-2" id="hapusAkunLabel" style="font-family: var(--font-display, 'Chakra Petch', sans-serif); font-weight: 700; text-transform: uppercase; letter-spacing: .01em; color: var(--tinta, #10263f);">
                        Hapus akun ini?
                    </h5>
                    <p class="text-muted mb-3" style="font-size: .87rem;">
                        Seluruh data akun akan dihapus permanen. Masukkan password untuk konfirmasi.
                    </p>

                    <div class="text-start">
                        <label for="delete_password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" id="delete_password" name="password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               autocomplete="current-password" required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Akun Permanen</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirm Modal Component: konfirmasi aksi berbahaya
     Usage:
     <x-confirm-modal id="hapusPlan" title="Hapus pengawasan?"
        description="Data pengawasan beserta temuan terkait akan dihapus. Tindakan ini tidak dapat dibatalkan."
        confirm-text="Hapus" form-action="{{ route('audit-plans.destroy', $plan) }}" />
--}}
@props([
    'id',
    'title',
    'description',
    'formAction',
    'confirmText' => 'Hapus',
    'cancelText' => 'Batal',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="sdx-empty-icon" style="width: 56px; height: 56px; font-size: 1.4rem; background: #fcebeb; color: #bf2b2b; border-radius: 16px;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 class="mt-3 mb-2" id="{{ $id }}Label" style="font-weight: 700; color: var(--spi-navy-deep, #122e56);">{{ $title }}</h5>
                <p class="text-muted mb-0" style="font-size: .87rem;">{{ $description }}</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ $cancelText }}</button>
                <form method="POST" action="{{ $formAction }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ $confirmText }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

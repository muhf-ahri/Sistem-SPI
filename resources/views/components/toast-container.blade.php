<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
    <div id="toastContainer" class="toast-container">
        @if(session('success'))
            <div class="toast align-items-center text-white bg-success border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="toast align-items-center text-white bg-danger border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">{{ session('error') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif
        @if(session('warning'))
            <div class="toast align-items-center text-white bg-warning border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">{{ session('warning') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toasts = document.querySelectorAll('.toast');
        toasts.forEach(function(toast) {
            var bsToast = new bootstrap.Toast(toast, { delay: 5000 });
            bsToast.show();
        });
    });
</script>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SPI PT Pindad Enjiniring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .register-card {
            max-width: 500px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            border: none;
        }
        .register-card .card-header {
            background: #fff;
            border-bottom: none;
            padding: 2rem 2rem 0 2rem;
            text-align: center;
        }
        .register-card .card-body {
            padding: 2rem;
        }
        .register-card .card-footer {
            background: #fff;
            border-top: none;
            padding: 0 2rem 2rem 2rem;
            text-align: center;
        }
        .btn-primary {
            background: #2d6ac7;
            border-color: #2d6ac7;
        }
        .btn-primary:hover {
            background: #1e4f9e;
            border-color: #1e4f9e;
        }
        .logo-text {
            color: #2d6ac7;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .logo-sub {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card register-card">
                    <div class="card-header">
                        <div class="logo-text">
                            <i class="bi bi-shield-plus me-2"></i>Register
                        </div>
                        <div class="logo-sub">Buat Akun Sistem Pengawasan Internal</div>
                        <hr class="my-3">
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            
                            <!-- Nama Lengkap -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Masukkan nama lengkap">
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="Masukkan email">
                                </div>
                            </div>

                            <!-- Divisi -->
                            <div class="mb-3">
                                <label for="division_id" class="form-label">Divisi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-building"></i></span>
                                    <select id="division_id" name="division_id" class="form-select">
                                        <option value="">-- Pilih Divisi (Opsional) --</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }} ({{ $division->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Buat password baru">
                                </div>
                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi password">
                                </div>
                            </div>

                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-person-plus me-2"></i>Daftar
                                </button>
                            </div>

                            <div class="text-center">
                                <a href="{{ route('login') }}" class="text-decoration-none">Sudah punya akun? Login di sini</a>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-muted">
                        <small>© {{ date('Y') }} PT Pindad Enjiniring Indonesia</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
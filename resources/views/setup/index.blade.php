<!DOCTYPE html>
<html lang="id" class="h-100">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Setup Awal - {{ config('app.name', 'Sistem Keuangan Keluarga') }}</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    <style>
        body { background: #0f172a; min-height: 100vh; }
        .setup-card { max-width: 560px; width: 100%; }
        .step-indicator .step-item { flex: 1; text-align: center; position: relative; }
        .step-indicator .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 16px; left: 60%;
            width: 80%; height: 2px;
            background: rgba(255,255,255,0.15);
        }
        .step-indicator .step-item.active::after,
        .step-indicator .step-item.done::after { background: #206bc4; }
        .step-circle {
            width: 32px; height: 32px; border-radius: 50%;
            background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.4);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 600; margin-bottom: 6px;
            border: 2px solid rgba(255,255,255,0.15);
        }
        .step-item.active .step-circle {
            background: #206bc4; color: #fff; border-color: #206bc4;
        }
        .step-item.done .step-circle {
            background: #2fb344; color: #fff; border-color: #2fb344;
        }
        .step-label { font-size: 11px; color: rgba(255,255,255,0.4); }
        .step-item.active .step-label { color: #fff; }
        .step-item.done .step-label { color: #2fb344; }
        .brand-icon {
            width: 56px; height: 56px; border-radius: 12px;
            background: linear-gradient(135deg, #206bc4, #4299e1);
            display: flex; align-items: center; justify-content: center;
        }
    </style>
</head>
<body class="d-flex align-items-center py-4">
<div class="container-tight setup-card mx-auto">

    {{-- Brand --}}
    <div class="text-center mb-4">
        <div class="brand-icon mx-auto mb-3">
            <i class="ti ti-wallet text-white" style="font-size:28px"></i>
        </div>
        <h1 class="text-white fw-bold mb-1">Sistem Keuangan Keluarga</h1>
        <p class="text-white-50 mb-0">Setup awal aplikasi — hanya dilakukan sekali</p>
    </div>

    {{-- Step Indicator --}}
    <div class="d-flex step-indicator mb-4 px-2">
        <div class="step-item {{ $currentStep >= 1 ? ($currentStep > 1 ? 'done' : 'active') : '' }}">
            <div>
                <div class="step-circle">
                    @if($currentStep > 1) <i class="ti ti-check" style="font-size:14px"></i>
                    @else 1 @endif
                </div>
            </div>
            <div class="step-label">Admin</div>
        </div>
        <div class="step-item {{ $currentStep >= 2 ? ($currentStep > 2 ? 'done' : 'active') : '' }}">
            <div>
                <div class="step-circle">
                    @if($currentStep > 2) <i class="ti ti-check" style="font-size:14px"></i>
                    @else 2 @endif
                </div>
            </div>
            <div class="step-label">Pengaturan</div>
        </div>
        <div class="step-item {{ $currentStep >= 3 ? 'active' : '' }}">
            <div><div class="step-circle">3</div></div>
            <div class="step-label">Email</div>
        </div>
    </div>

    {{-- Card --}}
    <div class="card" style="background:#1e293b; border:1px solid rgba(255,255,255,0.08)">
        <div class="card-body p-4">

            {{-- Validation Errors --}}
            @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- ========================= STEP 1: Admin Account ========================= --}}
            @if($currentStep === 1)
            <h2 class="card-title text-white mb-1">Buat Akun Admin</h2>
            <p class="text-white-50 mb-4" style="font-size:0.875rem">
                Akun ini akan menjadi pemilik sistem (Admin/Suami) dengan akses penuh.
            </p>
            <form action="{{ route('setup.admin') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-white-50">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           placeholder="Nama Suami / Admin" value="{{ old('name') }}" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           placeholder="admin@keluarga.com" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           placeholder="Minimal 8 karakter" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label text-white-50">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    Lanjut <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </form>
            @endif

            {{-- ========================= STEP 2: App Settings ========================= --}}
            @if($currentStep === 2)
            <h2 class="card-title text-white mb-1">Pengaturan Aplikasi</h2>
            <p class="text-white-50 mb-4" style="font-size:0.875rem">
                Konfigurasi dasar tampilan dan zona waktu sistem.
            </p>
            <form action="{{ route('setup.settings') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-white-50">Simbol Mata Uang</label>
                    <input type="text" name="currency" class="form-control @error('currency') is-invalid @enderror"
                           placeholder="Rp" value="{{ old('currency', 'Rp') }}" required>
                    <div class="form-hint text-white-50">Contoh: Rp, IDR, $</div>
                    @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    Lanjut <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </form>
            @endif

            {{-- ========================= STEP 3: SMTP Mail ========================= --}}
            @if($currentStep === 3)
            <h2 class="card-title text-white mb-1">Konfigurasi Email (SMTP)</h2>
            <p class="text-white-50 mb-4" style="font-size:0.875rem">
                Digunakan untuk mengirim notifikasi email. Bisa diisi nanti via Pengaturan Sistem.
            </p>
            <form action="{{ route('setup.mail') }}" method="POST" id="mail-form">
                @csrf
                <input type="hidden" name="skip" id="skip-input" value="0">

                <div id="mail-fields">
                    <div class="row g-3 mb-3">
                        <div class="col-8">
                            <label class="form-label text-white-50">SMTP Host</label>
                            <input type="text" name="mail_host" class="form-control"
                                   placeholder="smtp.gmail.com" value="{{ old('mail_host') }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-white-50">Port</label>
                            <input type="number" name="mail_port" class="form-control"
                                   placeholder="587" value="{{ old('mail_port', '587') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">Username / Email Pengirim</label>
                        <input type="text" name="mail_username" class="form-control"
                               placeholder="noreply@keluarga.com" value="{{ old('mail_username') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-white-50">App Password SMTP</label>
                        <input type="password" name="mail_password" class="form-control"
                               placeholder="App password atau password SMTP">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label text-white-50">Enkripsi</label>
                            <select name="mail_encryption" class="form-select">
                                <option value="tls" selected>TLS</option>
                                <option value="ssl">SSL</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-white-50">From Email</label>
                            <input type="email" name="mail_from" class="form-control"
                                   placeholder="noreply@keluarga.com" value="{{ old('mail_from') }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="ti ti-check me-1"></i> Selesai & Masuk
                    </button>
                    <button type="button" class="btn btn-ghost-secondary" onclick="skipMail()">
                        Skip <i class="ti ti-skip-forward ms-1"></i>
                    </button>
                </div>
            </form>
            @endif

        </div>
    </div>

    <div class="text-center mt-3 text-white-50" style="font-size:0.75rem">
        &copy; {{ date('Y') }} Sistem Keuangan Keluarga
    </div>
</div>

{{-- Tabler JS bundled via Vite --}}
<script>
    function skipMail() {
        document.getElementById('skip-input').value = '1';
        document.getElementById('mail-form').submit();
    }
</script>
</body>
</html>

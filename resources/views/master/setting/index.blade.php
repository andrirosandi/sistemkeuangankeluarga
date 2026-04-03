@extends('layouts.admin')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="row row-cards">
    <div class="col-md-3">
        <div class="card card-flush">
            <div class="card-body p-0">
                <div class="nav flex-column nav-pills" id="settings-tabs" role="tablist" aria-orientation="vertical">
                    <button class="nav-link text-start active" id="tab-general-link" data-bs-toggle="pill" data-bs-target="#tab-general" type="button" role="tab">
                        <x-icon name="settings" class="me-2" />
                        Umum (General)
                    </button>
                    <button class="nav-link text-start" id="tab-finance-link" data-bs-toggle="pill" data-bs-target="#tab-finance" type="button" role="tab">
                        <x-icon name="coin" class="me-2" />
                        Finansial (Finance)
                    </button>
                    <button class="nav-link text-start" id="tab-mail-link" data-bs-toggle="pill" data-bs-target="#tab-mail" type="button" role="tab">
                        <x-icon name="mail" class="me-2" />
                        Layanan Email (SMTP)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9" x-data="settingsUpload()">
        <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body tab-content" id="settings-tabContent">
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                            <h3 class="card-title mb-4">Pengaturan Umum</h3>
                            <div class="mb-3">
                                <label class="form-label required">Nama Aplikasi</label>
                                <input type="text" name="app_name" class="form-control" value="{{ $settings['app_name'] }}" required>
                                <small class="text-secondary mt-1">Nama ini akan muncul di sidebar, judul halaman, dan footer.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Logo & Favicon</label>
                                <div class="row g-3">
                                    <!-- Full Logo -->
                                    <div class="col-md-6">
                                        <div class="form-label">Logo Utama (Full)</div>
                                        <div class="row align-items-start g-2">
                                            <div class="col-auto">
                                                <template x-if="logoPreview">
                                                    <span class="avatar avatar-md" :style="'background-image: url(' + logoPreview + ')'"></span>
                                                </template>
                                                <template x-if="!logoPreview">
                                                    @php
                                                        $logo = \App\Models\Setting::get('app_logo');
                                                        $media = $logo ? \Spatie\MediaLibrary\MediaCollections\Models\Media::where('file_name', $logo)->first() : null;
                                                    @endphp
                                                    @if($media)
                                                        <span class="avatar avatar-md" style="background-image: url({{ $media->getUrl() }})"></span>
                                                    @else
                                                        <span class="avatar avatar-md"><x-icon name="wallet" /></span>
                                                    @endif
                                                </template>
                                            </div>
                                            <div class="col">
                                                <input type="file" class="form-control" accept="image/*" @change="handleUpload($event, 'logo')">
                                                <input type="hidden" name="logo_media_id" x-model="logoMediaId">
                                                <small class="text-secondary mt-1" x-text="logoStatus || 'Format: PNG, JPG, SVG. Di-resize ke 512px (kecuali SVG).'"></small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Favicon -->
                                    <div class="col-md-6">
                                        <div class="form-label">Favicon (Tab Browser)</div>
                                        <div class="row align-items-start g-2">
                                            <div class="col-auto">
                                                <template x-if="faviconPreview">
                                                    <span class="avatar avatar-md" :style="'background-image: url(' + faviconPreview + ')'"></span>
                                                </template>
                                                <template x-if="!faviconPreview">
                                                    @php
                                                        $favicon = \App\Models\Setting::get('app_favicon');
                                                        $media = $favicon ? \Spatie\MediaLibrary\MediaCollections\Models\Media::where('file_name', $favicon)->first() : null;
                                                    @endphp
                                                    @if($media)
                                                        <span class="avatar avatar-md" style="background-image: url({{ $media->getUrl() }})"></span>
                                                    @else
                                                        <span class="avatar avatar-md"><x-icon name="coin" /></span>
                                                    @endif
                                                </template>
                                            </div>
                                            <div class="col">
                                                <input type="file" class="form-control" accept="image/*" @change="handleUpload($event, 'favicon')">
                                                <input type="hidden" name="favicon_media_id" x-model="faviconMediaId">
                                                <small class="text-secondary mt-1" x-text="faviconStatus || 'Format: PNG, JPG, SVG. Di-resize ke 64x64px.'"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Zona Waktu (Timezone)</label>
                                <select name="timezone" class="form-select" required>
                                    @php
                                        $timezones = \DateTimeZone::listIdentifiers();
                                    @endphp
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}" {{ $tz === $settings['timezone'] ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-finance" role="tabpanel">
                            <h3 class="card-title mb-4">Pengaturan Finansial</h3>
                            <div class="mb-3">
                                <label class="form-label required">Simbol Mata Uang</label>
                                <input type="text" name="currency" class="form-control" value="{{ $settings['currency'] }}" placeholder="Rp" required>
                                <small class="text-secondary mt-1">Pilih simbol yang akan digunakan di seluruh laporan.</small>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-mail" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="card-title m-0">Konfigurasi Email (SMTP)</h3>
                                @if(\App\Models\Setting::get('smtp_verified_at'))
                                    <span class="badge bg-success-lt">
                                        <x-icon name="check" class="me-1" />
                                        Terverifikasi
                                    </span>
                                @else
                                    <span class="badge bg-warning-lt">
                                        <x-icon name="alert-circle" class="me-1" />
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                            <div class="alert alert-info">
                                <x-icon name="info-circle" class="me-2" />
                                Gunakan layanan SMTP untuk mengirim notifikasi penagihan atau reset password.
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" name="mail_host" class="form-control" value="{{ $settings['mail_host'] }}" placeholder="smtp.gmail.com">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Port</label>
                                        <input type="number" name="mail_port" class="form-control" value="{{ $settings['mail_port'] }}" placeholder="587">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="mail_username" class="form-control" value="{{ $settings['mail_username'] }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <div class="input-group input-group-flat">
                                            <input type="password" name="mail_password" id="mail_password" class="form-control" value="{{ $settings['mail_password'] }}">
                                            <span class="input-group-text">
                                                <a href="javascript:void(0)" class="link-secondary" id="toggle-password-btn" title="Show password" data-bs-toggle="tooltip" onclick="toggleSmtpPassword(this)">
                                                    <x-icon name="eye" id="password-hide-icon" />
                                                    <x-icon name="eye-off" id="password-show-icon" class="d-none" />
                                                </a>
                                            </span>
                                        </div>
                                        <small class="text-secondary mt-1">Password akan dienkripsi 2 arah menggunakan <code>APP_KEY</code>.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Encryption</label>
                                        <select name="mail_encryption" class="form-select">
                                            <option value="tls" {{ $settings['mail_encryption'] === 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ $settings['mail_encryption'] === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mail From Address</label>
                                        <input type="email" name="mail_from" class="form-control" value="{{ $settings['mail_from'] }}" placeholder="noreply@domain.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary" :disabled="isUploading">
                            <span x-show="!isUploading">Simpan Pengaturan</span>
                            <span x-show="isUploading" style="display: none;">
                                <span class="spinner-border spinner-border-sm me-2"></span>Mengunggah...
                            </span>
                        </button>
                    </div>
                </div>
        </form>
    </div>
</div>

{{-- Modal Verifikasi OTP --}}
<div class="modal modal-blur fade" id="modal-otp" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-primary"></div>
            <div class="modal-body text-center py-4">
                <x-icon name="mail-opened" class="text-primary icon-lg mb-2" />
                <h3>Verifikasi Email</h3>
                <div class="text-secondary mb-3">Kami telah mengirimkan kode OTP ke <strong>{{ $settings['mail_from'] }}</strong>. Silakan masukkan kode tersebut di bawah ini:</div>
                <form action="{{ route('settings.verify-otp') }}" method="POST" id="form-otp">
                    @csrf
                    <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold" placeholder="000000" maxlength="6" required autofocus>
                </form>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Nanti</a></div>
                        <div class="col"><button type="submit" form="form-otp" class="btn btn-primary w-100">Verifikasi</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function toggleSmtpPassword(el) {
        const input = document.getElementById('mail_password');
        const eyeIcon = document.getElementById('password-hide-icon');
        const eyeOffIcon = document.getElementById('password-show-icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            el.setAttribute('title', 'Hide password');
            eyeIcon.classList.add('d-none');
            eyeOffIcon.classList.status = 'icon'; // Ensure icon class
            eyeOffIcon.classList.remove('d-none');
        } else {
            input.type = 'password';
            el.setAttribute('title', 'Show password');
            eyeOffIcon.classList.add('d-none');
            eyeIcon.classList.remove('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('show_otp_modal'))
            new bootstrap.Modal(document.getElementById('modal-otp')).show();
            // Switch to mail tab
            const mailTab = new bootstrap.Tab(document.getElementById('tab-mail-link'));
            mailTab.show();
        @endif
    });
    function settingsUpload() {
        return {
            isUploading: false,
            logoPreview: null,
            logoMediaId: '',
            logoStatus: '',
            faviconPreview: null,
            faviconMediaId: '',
            faviconStatus: '',

            async handleUpload(event, type) {
                const file = event.target.files[0];
                if (!file) return;

                this.isUploading = true;
                if (type === 'logo') {
                    this.logoStatus = 'Memproses...';
                    this.logoPreview = URL.createObjectURL(file);
                } else {
                    this.faviconStatus = 'Memproses...';
                    this.faviconPreview = URL.createObjectURL(file);
                }

                try {
                    let uploadFile = file;

                    // Skip compression if SVG
                    if (file.type !== 'image/svg+xml') {
                        const targetSize = type === 'logo' ? 512 : 64;
                        uploadFile = await this.compressImage(file, targetSize, type === 'favicon');
                    }

                    const formData = new FormData();
                    formData.append('file', uploadFile);
                    formData.append('folder', 'settings');
                    formData.append('_token', '{{ csrf_token() }}');

                    const response = await fetch('{{ route("api.upload") }}', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (!response.ok) throw new Error(result.error || 'Upload gagal');

                    if (type === 'logo') {
                        this.logoMediaId = result.media_id;
                        this.logoStatus = '✅ Berhasil diunggah';
                    } else {
                        this.faviconMediaId = result.media_id;
                        this.faviconStatus = '✅ Berhasil diunggah';
                    }
                } catch (error) {
                    if (type === 'logo') this.logoStatus = '❌ ' + error.message;
                    else this.faviconStatus = '❌ ' + error.message;
                } finally {
                    this.isUploading = false;
                }
            },

            compressImage(file, maxSize, forceSquare = false) {
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = (e) => {
                        const img = new Image();
                        img.src = e.target.result;
                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            let width = img.width;
                            let height = img.height;

                            if (forceSquare) {
                                // Center crop to square
                                const side = Math.min(width, height);
                                canvas.width = maxSize;
                                canvas.height = maxSize;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, (width - side) / 2, (height - side) / 2, side, side, 0, 0, maxSize, maxSize);
                            } else {
                                // Maintain aspect ratio
                                if (width > height) {
                                    if (width > maxSize) {
                                        height *= maxSize / width;
                                        width = maxSize;
                                    }
                                } else {
                                    if (height > maxSize) {
                                        width *= maxSize / height;
                                        height = maxSize;
                                    }
                                }
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);
                            }

                            canvas.toBlob((blob) => {
                                resolve(new File([blob], file.name, { type: 'image/webp' }));
                            }, 'image/webp', 0.8);
                        };
                    };
                });
            }
        }
    }
</script>
@endpush

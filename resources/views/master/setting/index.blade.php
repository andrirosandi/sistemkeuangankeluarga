@extends('layouts.admin')

@section('title', 'Pengaturan Sistem')

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
<div class="row row-cards">
    <div class="col-md-3">
        <div class="card card-flush">
            <div class="card-body p-0">
                <div class="nav flex-column nav-pills" id="settings-tabs" role="tablist" aria-orientation="vertical">
                    <button class="nav-link text-start active" id="tab-general-link" data-bs-toggle="pill" data-bs-target="#tab-general" type="button" role="tab">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37a1.724 1.724 0 0 0 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                        Umum (General)
                    </button>
                    <button class="nav-link text-start" id="tab-finance-link" data-bs-toggle="pill" data-bs-target="#tab-finance" type="button" role="tab">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M14.8 9a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1" /><path d="M12 7v10" /></svg>
                        Finansial (Finance)
                    </button>
                    <button class="nav-link text-start" id="tab-mail-link" data-bs-toggle="pill" data-bs-target="#tab-mail" type="button" role="tab">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                        Layanan Email (SMTP)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9" x-data="{ logoMediaId: '', faviconMediaId: '', isUploading: false }" @uploading-changed.window="isUploading = $event.detail.uploading">
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
                                <x-shared.file-upload 
                                    name="logo_component" 
                                    label="Logo & Favicon" 
                                    :current-value="$logoMedia?->getUrl()"
                                    mode="settings-logo"
                                    @logo-uploaded="logoMediaId = $event.detail"
                                    @favicon-uploaded="faviconMediaId = $event.detail"
                                />

                                <input type="hidden" name="logo_media_id" :value="logoMediaId">
                                <input type="hidden" name="favicon_media_id" :value="faviconMediaId">
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
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                        Terverifikasi
                                    </span>
                                @else
                                    <span class="badge bg-warning-lt">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                            <div class="alert alert-info">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
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
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" id="password-hide-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon d-none" id="password-show-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.584 10.587a2 2 0 0 0 2.829 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87" /><path d="M3 3l18 18" /></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary icon-lg mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 9l9 6l9 -6l-9 -6l-9 6" /><path d="M21 9v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10" /><path d="M3 19l6 -6" /><path d="M15 13l6 6" /></svg>
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
            const mailTab = new bootstrap.Tab(document.getElementById('tab-mail-link'));
            mailTab.show();
        @endif
    });


</script>
@endpush

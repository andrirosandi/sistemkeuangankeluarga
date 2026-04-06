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
                        <i class="ti ti-settings me-2"></i>
                        Umum
                    </button>
                    <button class="nav-link text-start" id="tab-finance-link" data-bs-toggle="pill" data-bs-target="#tab-finance" type="button" role="tab">
                        <i class="ti ti-coin me-2"></i>
                        Finansial
                    </button>
                    <button class="nav-link text-start" id="tab-mail-link" data-bs-toggle="pill" data-bs-target="#tab-mail" type="button" role="tab">
                        <i class="ti ti-mail me-2"></i>
                        Layanan Email
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
                                    label="Logo dan Favicon" 
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

                            {{-- Danger Zone: Reset Aplikasi --}}
                            <div class="mt-5 pt-4 border-top">
                                <h4 class="text-danger mb-3"><i class="ti ti-trash me-2"></i>Reset Aplikasi (Interview)</h4>
                                <div class="alert alert-danger">
                                    <div class="mb-2">Hapus semua data dalam database dan lakukan setup ulang.</div>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modal-reset">
                                        <i class="ti ti-refresh me-1"></i>Kosongkan DB & Setup Ulang
                                    </button>
                                </div>
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
                                        <i class="ti ti-check me-1" style="font-size: 16px;"></i>
                                        Terverifikasi
                                    </span>
                                @else
                                    <span class="badge bg-warning-lt">
                                        <i class="ti ti-alert-circle me-1" style="font-size: 16px;"></i>
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </div>
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
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
                                                    <i class="ti ti-eye icon" id="password-hide-icon"></i>
                                                    <i class="ti ti-eye-off icon d-none" id="password-show-icon"></i>
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
                <i class="ti ti-mail-opened text-primary icon-lg mb-2"></i>
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

{{-- Modal Reset Aplikasi --}}
<div class="modal modal-blur fade" id="modal-reset" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status bg-danger"></div>
            <div class="modal-body text-center py-4">
                <i class="ti ti-alert-triangle text-danger icon-lg mb-2"></i>
                <h3>Reset Aplikasi?</h3>
                <div class="text-secondary mb-3">
                    Semua tabel database akan dikosongkan dan aplikasi akan mulai lagi dari menu setup awal.
                    <br><br>
                    <strong class="text-danger">Tindakan ini tidak dapat dibatalkan!</strong>
                </div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                        <div class="col">
                            <form action="{{ route('settings.reset') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100" onclick="this.disabled=true;this.closest('form').submit();">
                                    <i class="ti ti-trash me-1"></i>Ya, Reset!
                                </button>
                            </form>
                        </div>
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

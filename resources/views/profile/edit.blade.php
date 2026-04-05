@extends('layouts.admin')

@section('title', 'Pengaturan Profil')

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
<div class="row row-cards" x-data="{ activeTab: 'info', isUploading: false, avatarMediaId: '' }" @uploading-changed.window="isUploading = $event.detail.uploading">
    <div class="col-md-3">
        <div class="card card-flush">
            <div class="card-body p-0">
                <div class="nav flex-column nav-pills" id="profile-tabs" role="tablist" aria-orientation="vertical">
                    <button class="nav-link text-start active" id="tab-info-link" data-bs-toggle="pill" data-bs-target="#tab-info" type="button" role="tab" @click="activeTab = 'info'">
                        <i class="ti ti-user me-2"></i>
                        Data Profil
                    </button>
                    <button class="nav-link text-start" id="tab-password-link" data-bs-toggle="pill" data-bs-target="#tab-password" type="button" role="tab" @click="activeTab = 'password'">
                        <i class="ti ti-lock me-2"></i>
                        Ganti Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-body tab-content pb-0">
                {{-- Content: Data Profil --}}
                <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                    @include('profile.partials.update-profile-information-form')
                </div>

                {{-- Content: Ganti Password --}}
                <div class="tab-pane fade" id="tab-password" role="tabpanel">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" :form="activeTab === 'info' ? 'form-profile' : 'form-password'" class="btn btn-primary" :disabled="isUploading">
                    <span x-show="!isUploading">
                        <i class="ti ti-device-floppy me-2"></i> Simpan Perubahan
                    </span>
                    <span x-show="isUploading" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2"></span>Memproses...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle redirect back with tab state
        @if(session('status') === 'password-updated' || $errors->updatePassword->any())
            const passTab = new bootstrap.Tab(document.getElementById('tab-password-link'));
            passTab.show();
            // Update Alpine state manually if needed, but bootstrap trigger might not bubble to x-on:click
            // So we manually set the alpine property if we can find the scope
            const el = document.querySelector('[x-data]');
            if (el && el.__x && el.__x.$data) {
                el.__x.$data.activeTab = 'password';
            }
        @endif
    });
</script>
@endpush

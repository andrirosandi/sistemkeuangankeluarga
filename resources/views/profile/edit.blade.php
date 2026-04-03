@extends('layouts.admin')

@section('title', 'Pengaturan Profil')

@section('page-header')
<div class="row align-items-center">
    <div class="col">
        <div class="page-pretitle">Akun</div>
        <h2 class="page-title">Pengaturan Profil</h2>
    </div>
</div>
@endsection

@section('content')
<div class="row row-cards">
    <div class="col-md-3">
        <div class="card card-flush">
            <div class="card-body p-0">
                <div class="nav flex-column nav-pills" id="profile-tabs" role="tablist" aria-orientation="vertical">
                    <button class="nav-link text-start active" id="tab-info-link" data-bs-toggle="pill" data-bs-target="#tab-info" type="button" role="tab">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                        Data Profil
                    </button>
                    <button class="nav-link text-start" id="tab-password-link" data-bs-toggle="pill" data-bs-target="#tab-password" type="button" role="tab">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                        Ganti Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9" x-data="{ avatarMediaId: '', isUploading: false }" @uploading-changed.window="isUploading = $event.detail.uploading">
        <div class="card">
            <div class="card-body tab-content">
                {{-- Tab: Data Profil --}}
                <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                    @include('profile.partials.update-profile-information-form')
                </div>

                {{-- Tab: Keamanan (Password) --}}
                <div class="tab-pane fade" id="tab-password" role="tabpanel">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-switch tab if redirected back index (e.g. status success)
        @if(session('status') === 'password-updated')
            const passTab = new bootstrap.Tab(document.getElementById('tab-password-link'));
            passTab.show();
        @endif
    });
</script>
@endpush

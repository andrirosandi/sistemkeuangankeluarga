<section>
    <div class="mb-4">
        <h3 class="card-title m-0">Ganti Password</h3>
        <p class="text-secondary small">Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk tetap aman.</p>
    </div>

    <form id="form-password" method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label class="form-label required">Password Saat Ini</label>
            <input type="password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label required">Password Baru</label>
            <input type="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label required">Konfirmasi Password Baru</label>
            <input type="password" name="password_confirmation" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if (session('status') === 'password-updated')
            <div class="alert alert-success alert-dismissible" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <div class="d-flex">
                    <div><i class="ti ti-check me-2"></i></div>
                    <div>Password berhasil diperbarui.</div>
                </div>
                <a class="btn-close" @click="show = false"></a>
            </div>
        @endif
    </form>
</section>

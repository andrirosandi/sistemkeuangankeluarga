<section>
    <div class="mb-4">
        <h3 class="card-title m-0">Ganti Password</h3>
        <p class="text-secondary small">Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk tetap aman.</p>
    </div>

    <form method="post" action="{{ route('password.update') }}">
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

        <div class="form-footer">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-lock-check me-2"></i> Update Password
            </button>
            @if (session('status') === 'password-updated')
                <span class="text-success ms-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)">
                    Password berhasil diperbarui.
                </span>
            @endif
        </div>
    </form>
</section>

<section>
    <div class="d-flex align-items-center mb-4">
        <div>
            <h3 class="card-title m-0">Informasi Profil</h3>
            <p class="text-secondary small">Perbarui informasi profil dan alamat email akun Anda.</p>
        </div>
    </div>

    <form id="form-profile" method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-4">
            <x-shared.file-upload 
                name="avatar" 
                label="Foto Profil" 
                :current-value="$user->getFirstMediaUrl('avatars', 'thumb')"
                @avatar-uploaded="avatarMediaId = $event.detail"
            />
            <input type="hidden" name="avatar_media_id" :value="avatarMediaId">
        </div>

        <div class="mb-3">
            <label class="form-label required">Nama</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label required">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 alert alert-warning">
                    <p class="text-sm mb-0">
                        {{ __('Alamat email Anda belum diverifikasi.') }}

                        <button form="send-verification" class="btn btn-link p-0 text-sm">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-success">
                            {{ __('Link verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success alert-dismissible" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <div class="d-flex">
                    <div><i class="ti ti-check me-2"></i></div>
                    <div>Profil berhasil diperbarui.</div>
                </div>
                <a class="btn-close" @click="show = false"></a>
            </div>
        @endif
    </form>
</section>

@extends('layouts.admin')

@section('title', 'Semua Notifikasi')

@section('page-header')
<div class="row align-items-center">
    <div class="col">
        <h2 class="page-title">
            <i class="ti ti-bell me-2"></i> Semua Notifikasi
        </h2>
        <div class="text-secondary mt-1">Daftar riwayat pemberitahuan aktivitas Anda.</div>
    </div>
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            @if(auth()->user()->notifications()->where('is_read', false)->count() > 0)
            <form action="{{ route('notification.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary d-none d-sm-inline-block">
                    <i class="ti ti-checks me-2"></i> Tandai Semua Dibaca
                </button>
                <button type="submit" class="btn btn-primary d-sm-none btn-icon" aria-label="Tandai Dibaca">
                    <i class="ti ti-checks"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="list-group list-group-flush list-group-hoverable">
                    @forelse($notifications as $notif)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    @if(!$notif->is_read)
                                        <span class="status-indicator status-blue status-indicator-animated">
                                            <span class="status-indicator-circle"></span>
                                            <span class="status-indicator-circle"></span>
                                            <span class="status-indicator-circle"></span>
                                        </span>
                                    @else
                                        <span class="status-indicator status-green"></span>
                                    @endif
                                </div>
                                <div class="col text-truncate">
                                    <a href="{{ $notif->getRedirectUrl() }}" class="text-decoration-none">
                                        <div class="text-wrap {{ !$notif->is_read ? 'fw-bold text-body' : 'text-muted' }} mb-1" style="font-size: 14px;">
                                            {!! $notif->message !!}
                                        </div>
                                        <div class="text-secondary" style="font-size: 12px;">
                                            <i class="ti ti-clock" style="width: 14px; height: 14px;"></i> {{ $notif->created_at->translatedFormat('d F Y, H:i') }}
                                            ({{ $notif->created_at->diffForHumans() }})
                                        </div>
                                    </a>
                                </div>
                                <div class="col-auto d-flex gap-2">
                                    <form action="{{ route('notification.destroy', $notif->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-outline-danger" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center py-5">
                            <div class="empty">
                                <div class="empty-img text-muted">
                                    <i class="ti ti-bell-off" style="width: 64px; height: 64px;"></i>
                                </div>
                                <p class="empty-title mt-3">Tidak ada Notifikasi</p>
                                <p class="empty-subtitle text-secondary">
                                    Anda sudah membaca semua pesan. Belum ada aktivitas baru.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
            
            @if($notifications->hasPages())
            <div class="card-footer d-flex align-items-center">
                {{ $notifications->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

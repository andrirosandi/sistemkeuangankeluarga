@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('page-header')
<div class="col">
    <h2 class="page-title">Dashboard</h2>
    <div class="text-secondary mt-1">{{ now()->translatedFormat('l, d F Y') }}</div>
</div>
@endsection

@section('content')

{{-- Welcome bar --}}
<div class="card mb-4">
    <div class="card-body d-flex align-items-center gap-3">
        <span class="avatar avatar-lg bg-blue text-white rounded">
            <i class="ti ti-user" style="font-size:24px"></i>
        </span>
        <div>
            <h3 class="mb-0">Hai, {{ auth()->user()->name }}!</h3>
            <div class="text-secondary">Selamat datang di Sistem Keuangan Keluarga.</div>
        </div>
        @can('in.request.create')
        <div class="ms-auto">
            <a href="{{ route('in.request.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Buat Pengajuan Baru
            </a>
        </div>
        @endcan
    </div>
</div>

{{-- Balance Cards --}}
@can('dashboard.system.balance')
    @include('dashboard.balance-cards')
@endcan

{{-- Filterable Widgets Grid --}}
<div class="row row-deck row-cards">

    @can('dashboard.widget.summary')
    @include('dashboard.widget-filterable', [
        'widgetId'          => 'summary',
        'title'             => 'Ringkasan Transaksi',
        'icon'              => 'chart-pie',
        'apiUrl'            => route('api.dashboard.summary'),
        'scopes'            => $availableScopes,
        'defaultScope'      => $defaultScope,
        'showCategoryFilter' => true,
        'categories'        => $categories,
        'fullWidth'         => false,
    ])
    @endcan

    @can('dashboard.widget.activity')
    @include('dashboard.widget-filterable', [
        'widgetId'          => 'activity',
        'title'             => 'Aktivitas 7 Hari',
        'icon'              => 'activity',
        'apiUrl'            => route('api.dashboard.activity'),
        'scopes'            => $availableScopes,
        'defaultScope'      => $defaultScope,
        'showCategoryFilter' => true,
        'categories'        => $categories,
        'fullWidth'         => false,
    ])
    @endcan

    @can('dashboard.widget.alerts')
    @include('dashboard.widget-filterable', [
        'widgetId'          => 'alerts',
        'title'             => 'Pengajuan Pending',
        'icon'              => 'bell-ringing',
        'apiUrl'            => route('api.dashboard.alerts'),
        'scopes'            => $availableScopes,
        'defaultScope'      => $defaultScope,
        'showCategoryFilter' => false,
        'categories'        => $categories,
        'fullWidth'         => false,
    ])
    @endcan

    @can('dashboard.widget.recent')
    @include('dashboard.widget-filterable', [
        'widgetId'          => 'recent',
        'title'             => 'Transaksi Terkini',
        'icon'              => 'history',
        'apiUrl'            => route('api.dashboard.recent'),
        'scopes'            => $availableScopes,
        'defaultScope'      => $defaultScope,
        'showCategoryFilter' => true,
        'categories'        => $categories,
        'fullWidth'         => true,
    ])
    @endcan

</div>

@endsection

@push('scripts')
<script>
function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function dashboardBalance() {
    return {
        data: { begin: 0, totalIn: 0, totalOut: 0, ending: 0 },
        loading: true,
        init() {
            fetch('{{ route('api.dashboard.balance') }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(d => { this.data = d; this.loading = false; })
            .catch(() => { this.loading = false; });
        },
        formatRupiah
    };
}

function dashboardWidget(config) {
    return {
        widgetId: config.widgetId,
        apiUrl: config.apiUrl,
        scope: config.defaultScope,
        categoryId: '',
        scopes: config.scopes,
        showCategoryFilter: config.showCategoryFilter,
        categories: config.categories,
        html: '',
        loading: true,
        init() {
            this.loadContent();
            this.$watch('scope', () => this.loadContent());
            this.$watch('categoryId', () => this.loadContent());
        },
        loadContent() {
            this.loading = true;
            let url = new URL(this.apiUrl);
            url.searchParams.set('scope', this.scope);
            if (this.categoryId) {
                url.searchParams.set('category_id', this.categoryId);
            } else {
                url.searchParams.delete('category_id');
            }
            fetch(url.toString(), {
                headers: { 'Accept': 'text/html' }
            })
            .then(r => r.text())
            .then(html => { this.html = html; this.loading = false; })
            .catch(() => { this.html = ''; this.loading = false; });
        }
    };
}
</script>
@endpush

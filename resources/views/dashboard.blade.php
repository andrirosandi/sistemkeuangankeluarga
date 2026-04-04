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

{{-- ═══ Row 1: Request Summary + Outstanding ═══ --}}
<div class="row row-deck row-cards mb-4">
    @can('dashboard.widget.request-summary')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'request-summary',
        'title'              => 'Ringkasan Pengajuan',
        'icon'               => 'file-text',
        'apiUrl'             => route('api.dashboard.request-summary'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => false,
        'categories'         => $categories,
        'fullWidth'          => false,
    ])
    @endcan

    @can('dashboard.widget.outstanding')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'outstanding',
        'title'              => 'Outstanding Board',
        'icon'               => 'hourglass',
        'apiUrl'             => route('api.dashboard.outstanding'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => false,
        'categories'         => $categories,
        'fullWidth'          => false,
    ])
    @endcan
</div>

{{-- ═══ Row 2: Category Breakdown (Donut) + Month Compare (Bar) ═══ --}}
<div class="row row-deck row-cards mb-4">
    @can('dashboard.widget.category')
    @include('dashboard.widget-chart', [
        'widgetId'     => 'category-breakdown',
        'title'        => 'Breakdown per Kategori',
        'icon'         => 'chart-donut-3',
        'apiUrl'       => route('api.dashboard.category-breakdown'),
        'scopes'       => $availableScopes,
        'defaultScope' => $defaultScope,
        'chartType'    => 'donut',
        'fullWidth'    => false,
    ])
    @endcan

    @can('dashboard.widget.month-compare')
    @include('dashboard.widget-chart', [
        'widgetId'     => 'month-compare',
        'title'        => 'Bulan Ini vs Bulan Lalu',
        'icon'         => 'arrow-left-right',
        'apiUrl'       => route('api.dashboard.month-compare'),
        'scopes'       => $availableScopes,
        'defaultScope' => $defaultScope,
        'chartType'    => 'bar-compare',
        'fullWidth'    => false,
    ])
    @endcan
</div>

{{-- ═══ Row 3: Transaction Summary + Activity 7 Days ═══ --}}
<div class="row row-deck row-cards mb-4">
    @can('dashboard.widget.summary')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'summary',
        'title'              => 'Ringkasan Transaksi',
        'icon'               => 'chart-pie',
        'apiUrl'             => route('api.dashboard.summary'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => true,
        'categories'         => $categories,
        'fullWidth'          => false,
    ])
    @endcan

    @can('dashboard.widget.activity')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'activity',
        'title'              => 'Aktivitas 7 Hari',
        'icon'               => 'activity',
        'apiUrl'             => route('api.dashboard.activity'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => true,
        'categories'         => $categories,
        'fullWidth'          => false,
    ])
    @endcan
</div>

{{-- ═══ Row 4: Group Ranking + User Ranking ═══ --}}
<div class="row row-deck row-cards mb-4">
    @can('dashboard.widget.group-ranking')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'group-ranking',
        'title'              => 'Ranking Grup',
        'icon'               => 'trophy',
        'apiUrl'             => route('api.dashboard.group-ranking'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => false,
        'categories'         => $categories,
        'fullWidth'          => false,
    ])
    @endcan

    @can('dashboard.widget.user-ranking')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'user-ranking',
        'title'              => 'Ranking Pengguna',
        'icon'               => 'users',
        'apiUrl'             => route('api.dashboard.user-ranking'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => false,
        'categories'         => $categories,
        'fullWidth'          => false,
    ])
    @endcan
</div>

{{-- ═══ Row 5: Approval Stats (Approver only) ═══ --}}
@if($isApprover)
<div class="row row-deck row-cards mb-4">
    @can('dashboard.widget.approval-stats')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'approval-stats',
        'title'              => 'Statistik Approval',
        'icon'               => 'shield-check',
        'apiUrl'             => route('api.dashboard.approval-stats'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => false,
        'categories'         => $categories,
        'fullWidth'          => true,
    ])
    @endcan
</div>
@endif

{{-- ═══ Row 6: Alerts + Recent ═══ --}}
<div class="row row-deck row-cards">
    @can('dashboard.widget.alerts')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'alerts',
        'title'              => 'Pengajuan Pending',
        'icon'               => 'bell-ringing',
        'apiUrl'             => route('api.dashboard.alerts'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => false,
        'categories'         => $categories,
        'fullWidth'          => false,
    ])
    @endcan

    @can('dashboard.widget.recent')
    @include('dashboard.widget-filterable', [
        'widgetId'           => 'recent',
        'title'              => 'Transaksi Terkini',
        'icon'               => 'history',
        'apiUrl'             => route('api.dashboard.recent'),
        'scopes'             => $availableScopes,
        'defaultScope'       => $defaultScope,
        'showCategoryFilter' => true,
        'categories'         => $categories,
        'fullWidth'          => true,
    ])
    @endcan
</div>

@endsection

@push('scripts')
{{-- ApexCharts CDN --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// ═══ Balance Cards Component ═══
function dashboardBalance() {
    return {
        data: { begin: 0, totalIn: 0, totalOut: 0, ending: 0 },
        loading: true,
        error: false,
        init() {
            fetch('{{ route('api.dashboard.balance') }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(d => { this.data = d; this.loading = false; })
            .catch(() => { this.loading = false; this.error = true; });
        },
        formatRupiah
    };
}

// ═══ HTML Fragment Widget Component (existing pattern) ═══
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
        error: false,
        init() {
            this.loadContent();
            this.$watch('scope', () => this.loadContent());
            this.$watch('categoryId', () => this.loadContent());
        },
        loadContent() {
            this.loading = true;
            this.error = false;
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
            .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
            .then(html => { this.html = html; this.loading = false; })
            .catch(() => { this.html = ''; this.loading = false; this.error = true; });
        }
    };
}

// ═══ Chart Widget Component (NEW — for ApexCharts) ═══
function dashboardChart(config) {
    return {
        widgetId: config.widgetId,
        apiUrl: config.apiUrl,
        scope: config.defaultScope,
        scopes: config.scopes,
        chartType: config.chartType,
        loading: true,
        error: false,
        chartInstance: null,
        init() {
            this.loadChart();
            this.$watch('scope', () => this.loadChart());
        },
        loadChart() {
            this.loading = true;
            this.error = false;
            let url = new URL(this.apiUrl);
            url.searchParams.set('scope', this.scope);
            fetch(url.toString(), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(data => {
                this.loading = false;
                this.$nextTick(() => {
                    this.renderChart(data);
                });
            })
            .catch(() => { this.loading = false; this.error = true; });
        },
        renderChart(data) {
            if (this.chartInstance) {
                this.chartInstance.destroy();
                this.chartInstance = null;
            }

            const container = this.$refs.chartContainer;
            if (!container) return;

            let options = {};

            if (this.chartType === 'donut') {
                options = this.buildDonutOptions(data);
            } else if (this.chartType === 'bar-compare') {
                options = this.buildBarCompareOptions(data);
            }

            this.chartInstance = new ApexCharts(container, options);
            this.chartInstance.render();
        },

        // ── Donut Chart: Category Breakdown ──
        buildDonutOptions(data) {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const labels = data.map(d => d.category);
            const series = data.map(d => d.totalOut + d.totalIn);
            const colors = data.map(d => d.color || '#6c757d');

            return {
                chart: {
                    type: 'donut',
                    height: 280,
                    background: 'transparent',
                    fontFamily: 'inherit',
                },
                series: series,
                labels: labels,
                colors: colors,
                legend: {
                    position: 'bottom',
                    labels: { colors: isDark ? '#c8d3e1' : '#333' }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    color: isDark ? '#c8d3e1' : '#333',
                                    formatter: function (w) {
                                        return formatRupiah(w.globals.seriesTotals.reduce((a, b) => a + b, 0));
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                tooltip: {
                    y: {
                        formatter: function (val) { return formatRupiah(val); }
                    }
                },
                noData: {
                    text: 'Belum ada data',
                    style: { color: isDark ? '#c8d3e1' : '#666', fontSize: '14px' }
                },
                theme: { mode: isDark ? 'dark' : 'light' }
            };
        },

        // ── Bar Compare: Month vs Month ──
        buildBarCompareOptions(data) {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

            return {
                chart: {
                    type: 'bar',
                    height: 280,
                    background: 'transparent',
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                },
                series: [
                    { name: 'Pemasukan', data: [data.previous.in, data.current.in] },
                    { name: 'Pengeluaran', data: [data.previous.out, data.current.out] },
                ],
                xaxis: {
                    categories: data.labels,
                    labels: { style: { colors: isDark ? '#c8d3e1' : '#333' } }
                },
                yaxis: {
                    labels: {
                        style: { colors: isDark ? '#c8d3e1' : '#333' },
                        formatter: function (val) { return formatRupiah(val); }
                    }
                },
                colors: ['#2fb344', '#d63939'],
                plotOptions: {
                    bar: { borderRadius: 4, columnWidth: '50%' }
                },
                dataLabels: { enabled: false },
                legend: {
                    position: 'top',
                    labels: { colors: isDark ? '#c8d3e1' : '#333' }
                },
                tooltip: {
                    y: {
                        formatter: function (val) { return formatRupiah(val); }
                    }
                },
                grid: {
                    borderColor: isDark ? '#2c3e50' : '#e9ecef',
                },
                theme: { mode: isDark ? 'dark' : 'light' }
            };
        }
    };
}
</script>
@endpush

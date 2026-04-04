{{--
    Chart Widget Wrapper — for widgets that return JSON and render ApexCharts.
    Props: $widgetId, $title, $icon, $apiUrl, $scopes, $defaultScope, $chartType, $fullWidth
--}}
@php
    $colClass = $fullWidth ?? false ? 'col-12' : 'col-lg-6';
@endphp

<div class="{{ $colClass }}" x-data="dashboardChart({
    widgetId: '{{ $widgetId }}',
    apiUrl: '{{ $apiUrl }}',
    defaultScope: '{{ $defaultScope }}',
    scopes: {{ Illuminate\Support\Js::from($scopes) }},
    chartType: '{{ $chartType ?? 'donut' }}'
})">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-{{ $icon }} me-1"></i> {{ $title }}
            </h3>
            <div class="card-actions d-flex gap-2">
                {{-- Scope dropdown --}}
                <select class="form-select form-select-sm" x-model="scope" style="width:auto">
                    <template x-for="s in scopes" :key="s.value">
                        <option :value="s.value" x-text="s.label"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="card-body">
            {{-- Loading skeleton --}}
            <template x-if="loading">
                <div class="d-flex align-items-center justify-content-center" style="min-height:250px">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </template>

            {{-- Chart container --}}
            <template x-if="!loading && !error">
                <div>
                    <div x-ref="chartContainer" style="min-height:250px"></div>
                </div>
            </template>

            {{-- Error state --}}
            <template x-if="!loading && error">
                <div class="text-center text-danger py-4">
                    <i class="ti ti-alert-circle" style="font-size:32px"></i>
                    <div class="mt-2">Gagal memuat grafik</div>
                </div>
            </template>
        </div>
    </div>
</div>

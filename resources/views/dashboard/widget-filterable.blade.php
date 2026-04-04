{{--
    Filterable Widget Wrapper
    Props: $widgetId, $title, $icon, $apiUrl, $scopes, $defaultScope, $showCategoryFilter, $categories, $fullWidth
--}}
@php
    $colClass = $fullWidth ?? false ? 'col-12' : 'col-lg-6';
@endphp

<div class="{{ $colClass }}" x-data="dashboardWidget({
    widgetId: '{{ $widgetId }}',
    apiUrl: '{{ $apiUrl }}',
    defaultScope: '{{ $defaultScope }}',
    scopes: {{ Illuminate\Support\Js::from($scopes) }},
    showCategoryFilter: {{ $showCategoryFilter ? 'true' : 'false' }},
    categories: {{ Illuminate\Support\Js::from($categories) }}
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

                {{-- Category dropdown --}}
                <select class="form-select form-select-sm" x-model="categoryId" x-show="showCategoryFilter" style="width:auto">
                    <option value="">Semua Kategori</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="card-body">
            {{-- Loading skeleton --}}
            <template x-if="loading">
                <div class="placeholder-glow">
                    <div class="mb-2"><span class="placeholder col-8"></span></div>
                    <div class="mb-2"><span class="placeholder col-6"></span></div>
                    <div class="mb-2"><span class="placeholder col-10"></span></div>
                    <div><span class="placeholder col-4"></span></div>
                </div>
            </template>

            {{-- Loaded content --}}
            <template x-if="!loading && html">
                <div x-html="html"></div>
            </template>

            {{-- Empty state --}}
            <template x-if="!loading && !error && !html">
                <div class="text-center text-secondary py-4">
                    <i class="ti ti-database-off" style="font-size:32px"></i>
                    <div class="mt-2">Tidak ada data</div>
                </div>
            </template>

            {{-- Error state --}}
            <template x-if="!loading && error">
                <div class="text-center text-danger py-4">
                    <i class="ti ti-alert-circle" style="font-size:32px"></i>
                    <div class="mt-2">Gagal memuat data</div>
                </div>
            </template>
        </div>
    </div>
</div>

@extends('layouts.admin')

@section('title', $title)

@section('page-header')
<div class="row align-items-center">
    <div class="col">
        <h2 class="page-title">{{ $title }}</h2>
    </div>
</div>
@endsection

@section('content')
@php
    $isEdit = isset($transactionData);
    $actionUrl = $isEdit ? route($type . '.transaction.update', $transactionData->id) : route($type . '.transaction.store');

    // Default items
    $defaultItems = [];
    $isTemplate = isset($templateData);

    if ($isEdit && $transactionData->details) {
        foreach($transactionData->details as $det) {
            $defaultItems[] = [
                'id' => $det->id,
                'description' => $det->description,
                'amount' => (float) $det->amount,
                'request_detail_id' => $det->request_detail_id,
            ];
        }
    } elseif ($isTemplate && $templateData->details) {
        foreach($templateData->details as $det) {
            $defaultItems[] = [
                'id' => null,
                'description' => $det->description,
                'amount' => (float) $det->amount
            ];
        }

        // Mock transactionData to prefill form fields easily
        $transactionData = (object) [
           'category_id' => $templateData->category_id,
           'description' => $templateData->description,
           'notes' => '',
           'transaction_date' => date('Y-m-d')
        ];
    } else {
        $defaultItems[] = ['id' => null, 'description' => '', 'amount' => 0];
    }
    
    $readOnly = $readOnly ?? false;
@endphp

<script>
    // Global data - must be defined early with all methods
    window.transactionCategories = @json($categories->keyBy('id'));
    window.transactionFormData = {
        categories: window.transactionCategories,
        selectedCategoryId: String('{{ old('category_id', $transactionData->category_id ?? '') }}'),
        items: @json($defaultItems),

        get selectedCategoryColor() {
            if (this.selectedCategoryId && this.categories[this.selectedCategoryId]) {
                return this.categories[this.selectedCategoryId].color;
            }
            return null;
        },

        get totalAmount() {
            return this.items.reduce((acc, item) => acc + (parseFloat(item.amount) || 0), 0);
        },

        addItem() {
            this.items.push({ id: null, description: '', amount: 0, request_detail_id: null });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }
    };
</script>

<form action="{{ $actionUrl }}" method="POST" x-data="transactionFormData" id="mainTransactionForm" x-cloak>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row row-cards">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Info Utama</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3" x-data="{ open: false }">
                        <label class="form-label required">Kategori</label>
                        
                        <div class="dropdown position-relative">
                            <!-- Custom Styled Select Button -->
                            <button type="button" class="form-select text-start d-flex align-items-center justify-content-between @error('category_id') is-invalid @enderror" 
                                    @click="open = !open" @click.outside="open = false" 
                                    :class="{'text-muted': !selectedCategoryId}"
                                    {{ $readOnly ? 'disabled' : '' }}
                                    style="min-height: 2.375rem;">
                                <template x-if="selectedCategoryId && categories[selectedCategoryId]">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge" :style="{ backgroundColor: selectedCategoryColor, width: '12px', height: '12px', padding: 0 }"></span>
                                        <span x-text="categories[selectedCategoryId].name"></span>
                                    </div>
                                </template>
                                <template x-if="!selectedCategoryId || !categories[selectedCategoryId]">
                                    <span>-- Pilih Kategori --</span>
                                </template>
                            </button>
                            
                            <!-- Custom Dropdown Menu -->
                            <div class="dropdown-menu w-100 shadow-sm mt-1" :class="{'show': open}" x-show="open" style="max-height: 250px; overflow-y: auto; display: none; z-index: 1050;" x-transition>
                                @foreach($categories as $cat)
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 mb-1" 
                                            :class="{'bg-primary-subtle text-primary fw-bold': selectedCategoryId == '{{ $cat->id }}'}"
                                            @click="selectedCategoryId = '{{ $cat->id }}'; open = false">
                                        <span class="badge" style="background-color: {{ $cat->color }}; width: 12px; height: 12px; padding: 0;"></span>
                                        {{ $cat->name }}
                                    </button>
                                @endforeach
                            </div>

                            <!-- Invisible Native Select for HTML5 Validation -->
                            <select name="category_id" x-model="selectedCategoryId" required 
                                    class="form-select position-absolute top-0 start-0 w-100 h-100 opacity-0" 
                                    style="z-index: -1; pointer-events: none;" tabindex="-1">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Tanggal Realisasi</label>
                        <input type="date" name="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror" 
                               {{ $readOnly ? 'disabled' : '' }}
                               value="{{ old('transaction_date', isset($transactionData) ? \Carbon\Carbon::parse($transactionData->transaction_date)->format('Y-m-d') : date('Y-m-d')) }}" required>
                        @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Deskripsi Singkat</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" 
                               {{ $readOnly ? 'disabled' : '' }}
                               placeholder="Contoh: Belanja Bulanan" value="{{ old('description', $transactionData->description ?? '') }}" required>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" {{ $readOnly ? 'disabled' : '' }}>{{ old('notes', $transactionData->notes ?? '') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Rincian Item</h3>
                    @if(!$readOnly)
                    <div class="card-actions">
                        <button type="button" class="btn btn-outline-primary btn-sm" @click="addItem()">
                            <i class="ti ti-plus me-1"></i> Tambah Item
                        </button>
                    </div>
                    @endif
                </div>
                
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>Keterangan Item</th>
                                <th class="w-25">Nominal (Rp)</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td>
                                        <input type="hidden" :name="`items[${index}][id]`" :value="item.id">
                                        <input type="hidden" :name="`items[${index}][request_detail_id]`" :value="item.request_detail_id">
                                        <input type="text" :name="`items[${index}][description]`" class="form-control" x-model="item.description" placeholder="Contoh: Beras" required {{ $readOnly ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <input type="number" :name="`items[${index}][amount]`" class="form-control text-end" x-model.number="item.amount" min="0" required {{ $readOnly ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        @if(!$readOnly)
                                        <button type="button" class="btn btn-icon btn-outline-danger" @click="removeItem(index)" :disabled="items.length === 1">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="fw-bold text-end">Total Realisasi</td>
                                <td class="fw-bold text-end" x-text="formatRupiah(totalAmount)">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @error('items')
                    <div class="card-body py-2">
                        <div class="text-danger small">{{ $message }}</div>
                    </div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Floating Bottom Bar -->
    <div class="position-sticky bottom-0 pb-3 mt-3" style="z-index: 1020;">
        <div class="card shadow-lg mb-0 border-primary border-opacity-25">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase tracking-wide">Total Realisasi</div>
                    <div class="h2 mb-0 text-primary" x-text="formatRupiah(totalAmount)">Rp 0</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($type . '.transaction.index') }}" class="btn btn-link link-secondary px-3">
                        Kembali
                    </a>
                    @if(!$readOnly)
                    <button type="submit" name="action_type" value="draft" class="btn btn-outline-primary">
                        <i class="ti ti-device-floppy me-2"></i> Simpan Draft
                    </button>
                    <button type="submit" name="action_type" value="completed" class="btn btn-success shadow-sm">
                        <i class="ti ti-check me-2"></i> {{ $isEdit ? 'Update & Realisasikan' : 'Simpan & Realisasikan' }}
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

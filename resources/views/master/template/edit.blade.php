@extends('layouts.admin')

@section('title', 'Edit Template Transaksi')

@section('content')
<script>
    window.templateCategories = {{ Js::from($categories->keyBy('id')) }};

    // Register Alpine component
    Alpine.data('templateForm', (config = {}) => ({
            categories: config.categories || {},
            selectedCategoryId: String('{{ old('category_id', $template->category_id) }}'),
            // Priority: Old input (if any), then Template Details, then Default empty array
            items: {{ Js::from(old('details', $template->details)) }} || [],

            get selectedCategoryColor() {
                if (this.selectedCategoryId && this.categories[this.selectedCategoryId]) {
                    return this.categories[this.selectedCategoryId].color;
                }
                return null;
            },

            init() {
                // Ensure items is an array
                if (!Array.isArray(this.items)) {
                    this.items = [];
                }

                if (this.items.length === 0) {
                    this.addItem();
                }
            },

            addItem() {
                this.items.push({ description: '', amount: 0 });
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },

            get totalAmount() {
                return this.items.reduce((total, item) => total + (parseFloat(item.amount) || 0), 0);
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            }
        }));

    // Re-initialize the form if Alpine is already running
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const form = document.getElementById('mainTemplateForm');
            if (form && window.Alpine) {
                window.Alpine.initTree(form);
            }
        }, 100);
    });
</script>

<form action="{{ route('master.templates.update', $template->id) }}" method="POST" x-data="templateForm({
        categories: window.templateCategories
    })" id="mainTemplateForm" x-cloak>
    @csrf
    @method('PUT')
    <div class="row row-cards">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title">Informasi Header</h3>
                    <div class="card-actions">
                        @if($template->trans_code == 1)
                            <span class="badge bg-green-lt">Pemasukan</span>
                        @else
                            <span class="badge bg-red-lt">Pengeluaran</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Template (Deskripsi)</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description', $template->description) }}" placeholder="Contoh: Belanja Bulanan" required>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Jenis Transaksi</label>
                        <select name="trans_code" class="form-select @error('trans_code') is-invalid @enderror" required>
                            <option value="1" {{ old('trans_code', $template->trans_code) == '1' ? 'selected' : '' }}>Pemasukan (Kas Masuk)</option>
                            <option value="2" {{ old('trans_code', $template->trans_code) == '2' ? 'selected' : '' }}>Pengeluaran (Kas Keluar)</option>
                        </select>
                        @error('trans_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" x-data="{ open: false }">
                        <label class="form-label required">Kategori Template</label>
                        
                        <div class="dropdown position-relative">
                            <!-- Custom Styled Select Button -->
                            <button type="button" class="form-select text-start d-flex align-items-center justify-content-between @error('category_id') is-invalid @enderror" 
                                    @click="open = !open" @click.outside="open = false" 
                                    :class="{'text-muted': !selectedCategoryId}"
                                    style="min-height: 2.375rem;">
                                <template x-if="selectedCategoryId && categories[selectedCategoryId]">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge" :style="{ backgroundColor: selectedCategoryColor, width: '12px', height: '12px', padding: 0 }"></span>
                                        <span x-text="categories[selectedCategoryId].name"></span>
                                    </div>
                                </template>
                                <template x-if="!selectedCategoryId || !categories[selectedCategoryId]">
                                    <span>Pilih Kategori</span>
                                </template>
                            </button>
                            
                            <!-- Custom Dropdown Menu -->
                            <div class="dropdown-menu w-100 shadow-sm mt-1" :class="{'show': open}" x-show="open" style="max-height: 250px; overflow-y: auto; display: none; z-index: 1050;" x-transition>
                                @foreach($categories as $category)
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 mb-1" 
                                            :class="{'bg-primary-subtle text-primary fw-bold': selectedCategoryId == '{{ $category->id }}'}"
                                            @click="selectedCategoryId = '{{ $category->id }}'; open = false">
                                        <span class="badge" style="background-color: {{ $category->color }}; width: 12px; height: 12px; padding: 0;"></span>
                                        {{ $category->name }}
                                    </button>
                                @endforeach
                            </div>

                            <!-- Invisible Native Select for HTML5 Validation -->
                            <select name="category_id" x-model="selectedCategoryId" required 
                                    class="form-select position-absolute top-0 start-0 w-100 h-100 opacity-0" 
                                    style="z-index: -1; pointer-events: none;" tabindex="-1">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        @error('category_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rincian Item (Template Details)</h3>
                    <div class="card-actions">
                        <button type="button" class="btn btn-outline-primary btn-sm" @click="addItem()">
                            <i class="ti ti-plus me-1"></i> Tambah Item
                        </button>
                    </div>
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
                                        <input type="text" :name="'details['+index+'][description]'" x-model="item.description" class="form-control" placeholder="Contoh: Listrik" required>
                                    </td>
                                    <td>
                                        <input type="number" :name="'details['+index+'][amount]'" x-model.number="item.amount" class="form-control text-end" min="0" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-icon btn-outline-danger" @click="removeItem(index)" :disabled="items.length === 1">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="fw-bold text-end">Subtotal</td>
                                <td class="fw-bold text-end" x-text="formatRupiah(totalAmount)">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @error('details')
                    <div class="card-body py-2">
                        <div class="text-danger small">{{ $message }}</div>
                    </div>
                @enderror
            </div>
            
            <div class="alert alert-info mt-3 d-flex align-items-center">
                <i class="ti ti-info-circle fs-2 me-2"></i>
                <div>
                   Mengubah template tidak akan mengubah transaksi yang sudah pernah dibuat menggunakan template ini sebelumnya.
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Bottom Bar -->
    <div class="position-sticky bottom-0 pb-3 mt-3" style="z-index: 1020;">
        <div class="card shadow-lg mb-0 border-primary border-opacity-25">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase tracking-wide">Total Estimasi Baru</div>
                    <div class="h2 mb-0 text-primary" x-text="formatRupiah(totalAmount)">Rp 0</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('master.templates.index') }}" class="btn btn-link link-secondary px-3">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="ti ti-device-floppy me-2"></i> Perbarui Template
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

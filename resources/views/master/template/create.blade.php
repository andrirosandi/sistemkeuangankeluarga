@extends('layouts.admin')

@section('title', 'Tambah Template Baru')

@section('content')
<form action="{{ route('master.templates.store') }}" method="POST">
    @csrf
    <div class="row row-cards" x-data="templateForm({
        categories: @json($categories->keyBy('id'))
    })">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Header</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Template (Deskripsi)</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}" placeholder="Contoh: Belanja Bulanan" required>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Jenis Transaksi</label>
                        <select name="trans_code" class="form-select @error('trans_code') is-invalid @enderror" required>
                            <option value="1" {{ old('trans_code') == '1' ? 'selected' : '' }}>Pemasukan (Kas Masuk)</option>
                            <option value="2" {{ old('trans_code') == '2' ? 'selected' : '' }}>Pengeluaran (Kas Keluar)</option>
                        </select>
                        @error('trans_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required d-flex align-items-center justify-content-between">
                            Kategori Default
                            <span x-show="selectedCategoryColor" class="badge" :style="{ backgroundColor: selectedCategoryColor, width: '12px', height: '12px', padding: 0 }"></span>
                        </label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" x-model="selectedCategoryId" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <div class="p-3 bg-body-tertiary rounded">
                            <div class="text-secondary small mb-1">Total Estimasi</div>
                            <div class="h2 mb-0" x-text="formatRupiah(totalAmount())">Rp 0</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('master.templates.index') }}" class="btn btn-link text-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-2"></i> Simpan Template
                    </button>
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
                                        <input type="text" :name="'details['+index+'][description]'" x-model="item.description" class="form-control" placeholder="Contoh: Beras 10kg" required>
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
                                <td class="fw-bold text-end" x-text="formatRupiah(totalAmount())">Rp 0</td>
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
                   Template ini akan muncul sebagai pilihan saat Anda menginput transaksi Kas Masuk atau Kas Keluar untuk membantu mengisi rincian secara otomatis.
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    function templateForm(config = {}) {
        return {
            categories: config.categories || {},
            selectedCategoryId: String('{{ old('category_id', '') }}'),
            items: {{ Js::from(old('details')) }} || [
                { description: '', amount: 0 }
            ],
            
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

            totalAmount() {
                return this.items.reduce((total, item) => total + (parseFloat(item.amount) || 0), 0);
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            }
        }
    }
</script>
@endpush

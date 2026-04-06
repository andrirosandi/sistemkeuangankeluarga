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
    $isEdit = isset($requestData) && $requestData->exists;
    $actionUrl = $isEdit ? route($type . '.request.update', $requestData->id) : route($type . '.request.store');
    
    // Default items
    $defaultItems = [];
    if (isset($requestData) && $requestData->details && $requestData->details->count() > 0) {
        foreach($requestData->details as $det) {
            $defaultItems[] = [
                'description' => $det->description,
                'amount' => (float) $det->amount
            ];
        }
    } else {
        $defaultItems[] = ['description' => '', 'amount' => 0];
    }
@endphp

<script>
    window.requestCategories = @json($categories->keyBy('id'));

    // Register Alpine component
    Alpine.data('requestForm', (config = {}) => ({
            categories: config.categories || {},
            selectedCategoryId: String('{{ old('category_id', $requestData->category_id ?? '') }}'),
            items: @json($defaultItems),
            uploadedMedia: [],
            isUploading: false,

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
                this.items.push({ description: '', amount: 0 });
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
            },

            async uploadFiles(event) {
                const files = event.target.files;
                if (!files || files.length === 0) return;

                this.isUploading = true;

                try {
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const formData = new FormData();
                        formData.append('file', file);
                        formData.append('folder', 'requests');

                        const response = await fetch("{{ route('api.upload') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            this.uploadedMedia.push({
                                id: result.media_id,
                                name: result.name || file.name,
                                url: result.url
                            });
                        } else {
                            alert('Gagal mengunggah ' + file.name + ': ' + (result.error || 'Server error'));
                        }
                    }
                } catch (error) {
                    alert('Terjadi kesalahan koneksi saat mengunggah file.');
                    console.error(error);
                } finally {
                    this.isUploading = false;
                    event.target.value = '';
                }
            },

            removeMedia(index) {
                this.uploadedMedia.splice(index, 1);
            },

            submitMainForm(event) {
                if (this.isUploading) {
                    event.preventDefault();
                    alert('Harap tunggu hingga proses upload selesai!');
                    return false;
                }
                return true;
            }
        }));

    // Re-initialize the form if Alpine is already running
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const form = document.getElementById('mainRequestForm');
            if (form && window.Alpine) {
                form.removeAttribute('x-ignore');
                window.Alpine.initTree(form);
            }
        }, 100);
    });
</script>

<form action="{{ $actionUrl }}" method="POST" x-data="requestForm({ categories: window.requestCategories })" @submit="submitMainForm($event)" id="mainRequestForm" x-cloak>
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
                        <label class="form-label required">Tanggal Pengajuan</label>
                        <input type="date" name="request_date" class="form-control @error('request_date') is-invalid @enderror" 
                               value="{{ old('request_date', isset($requestData) ? \Carbon\Carbon::parse($requestData->request_date)->format('Y-m-d') : date('Y-m-d')) }}" required>
                        @error('request_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Prioritas / Urgensi</label>
                        <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="low" {{ old('priority', $requestData->priority ?? '') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="normal" {{ old('priority', $requestData->priority ?? 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ old('priority', $requestData->priority ?? '') == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Deskripsi Singkat</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" 
                               placeholder="Contoh: Belanja Bulanan" value="{{ old('description', $requestData->description ?? '') }}" required>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $requestData->notes ?? '') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>


                </div>
            </div>

            <!-- Upload Section -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Lampiran Bukti</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="file" id="fileUploader" class="form-control" multiple @change="uploadFiles($event)" :disabled="isUploading">
                        <div x-show="isUploading" class="mt-2 text-primary small">
                            <i class="ti ti-loader icon-spin"></i> Sedang mengunggah...
                        </div>
                    </div>

                    <!-- Hidden inputs for uploaded files -->
                    <template x-for="media in uploadedMedia" :key="media.id">
                        <input type="hidden" name="media_ids[]" :value="media.id">
                    </template>

                    <!-- File List UI (Thumbnails) -->
                    <div class="row g-2 mt-2" x-show="uploadedMedia.length > 0">
                        <template x-for="(media, index) in uploadedMedia" :key="media.id">
                            <div class="col-4">
                                <div class="position-relative border rounded bg-light" style="padding-bottom: 100%; height: 0; overflow: hidden;">
                                    <a :href="media.url" target="_blank" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white">
                                        <img :src="media.url" class="img-fluid" style="object-fit: contain; width: 100%; height: 100%;" :alt="media.name" x-on:error="$el.outerHTML = '<i class=\'ti ti-file-text text-muted h1\'></i>'">
                                    </a>
                                    <button type="button" class="btn btn-sm btn-icon btn-danger position-absolute top-0 end-0 m-1 rounded-circle" 
                                            @click="removeMedia(index)" title="Hapus Bukti" :disabled="isUploading" 
                                            style="width: 24px; height: 24px; min-height: 24px;">
                                        <i class="ti ti-x" style="font-size: 12px;"></i>
                                    </button>
                                </div>
                                <div class="text-truncate small mt-1 text-center" style="max-width: 100%; fontSize: 11px;" x-text="media.name" :title="media.name"></div>
                            </div>
                        </template>
                    </div>
                    
                    @if($isEdit && $requestData->hasMedia('requests'))
                        <div class="mt-3">
                            <strong>Bukti Tersimpan Sebelumnya:</strong>
                            <div class="row g-2 mt-2">
                                @foreach($requestData->getMedia('requests') as $mediaItem)
                                    <div class="col-4">
                                        <a href="{{ $mediaItem->getUrl() }}" target="_blank">
                                            <img src="{{ $mediaItem->getUrl() }}" class="img-fluid rounded border">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-2">Gambar yang sudah disimpan via Edit tidak bisa dihapus di layar ini (harus via halaman detail). Silakan upload gambar baru jika perlu.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Rincian Item (Pengajuan Details)</h3>
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
                                        <input type="text" :name="`items[${index}][description]`" class="form-control" x-model="item.description" placeholder="Contoh: Beras" required>
                                    </td>
                                    <td>
                                        <input type="number" :name="`items[${index}][amount]`" class="form-control text-end" x-model.number="item.amount" min="0" required>
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
                    <div class="text-secondary small fw-bold text-uppercase tracking-wide">Total Pengajuan</div>
                    <div class="h2 mb-0 text-primary" x-text="formatRupiah(totalAmount)">Rp 0</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($type . '.request.index') }}" class="btn btn-link link-secondary px-3">
                        Batal
                    </a>
                    <button type="submit" name="action_type" value="draft" class="btn btn-outline-primary" :disabled="isUploading">
                        <i class="ti ti-device-floppy me-2"></i> Simpan Draft
                    </button>
                    <button type="submit" name="action_type" value="requested" class="btn btn-primary shadow-sm" :disabled="isUploading">
                        <i class="ti ti-send me-2"></i> {{ $isEdit ? 'Update & Ajukan' : 'Simpan & Ajukan' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

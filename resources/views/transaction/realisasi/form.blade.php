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
    $outstandingDetails = $outstandingDetails ?? [];
    $outstandingSummary = $outstandingSummary ?? null;

    // Existing details keyed by request_detail_id untuk matching
    $existingByRdId = [];
    if ($isEdit && $transactionData->details->isNotEmpty()) {
        foreach ($transactionData->details as $det) {
            if ($det->request_detail_id) {
                $existingByRdId[$det->request_detail_id] = $det;
            }
        }
    }

    if ($isEdit && !empty($outstandingDetails)) {
        // Auto-feed dari outstanding details, match dengan existing detail jika ada
        foreach ($outstandingDetails as $out) {
            $existing = $existingByRdId[$out['rd_id']] ?? null;
            $defaultItems[] = [
                'id' => $existing?->id,
                'description' => $out['description'],
                'amount' => $existing ? (float) $existing->amount : (float) $out['remaining_amount'],
                'request_detail_id' => $out['rd_id'],
                'outstanding' => $out,
            ];
        }
    } elseif ($isEdit && $transactionData->details->isNotEmpty()) {
        // Fallback: existing details tanpa outstanding
        foreach($transactionData->details as $det) {
            $defaultItems[] = [
                'id' => $det->id,
                'description' => $det->description,
                'amount' => (float) $det->amount,
                'request_detail_id' => $det->request_detail_id,
                'outstanding' => null,
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
            this.items.push({ id: null, description: '', amount: 0, request_detail_id: null, outstanding: null });
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
                    formData.append('folder', 'transactions');

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
    };
</script>

<form action="{{ $actionUrl }}" method="POST" x-data="transactionFormData" @submit="submitMainForm($event)" id="mainTransactionForm" x-cloak>
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

                    @if(!$readOnly)
                    <div class="mb-3">
                        <label class="form-label">Lampiran Bukti</label>
                        <input type="file" id="fileUploader" class="form-control" multiple @change="uploadFiles($event)" :disabled="isUploading">

                        <div x-show="isUploading" class="mt-2">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            <span class="text-muted">Mengunggah...</span>
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
                    </div>
                    @endif

                    @if($isEdit && $transactionData->hasMedia('transactions'))
                    <div class="mb-0">
                        <strong>Bukti Tersimpan Sebelumnya:</strong>
                        <div class="row g-2 mt-2">
                            @foreach($transactionData->getMedia('transactions') as $mediaItem)
                                @php $isImage = \Illuminate\Support\Str::startsWith($mediaItem->mime_type, 'image/'); @endphp
                                <div class="col-4">
                                    <a href="{{ $mediaItem->getUrl() }}" target="_blank" title="Lihat/Download">
                                        @if($isImage)
                                            <img src="{{ $mediaItem->getUrl() }}" class="img-fluid rounded border">
                                        @else
                                            <div class="border rounded bg-light p-3 text-center">
                                                <i class="ti ti-file-text text-muted h1 mb-0"></i>
                                            </div>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($isEdit && isset($transactionData->requestHeader) && $transactionData->requestHeader)
                    <div class="mb-0">
                        <div class="alert alert-info mb-0">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-user me-1"></i>
                                <span class="text-secondary">Pengaju:</span>
                                <strong>{{ $transactionData->requestHeader->creator->name ?? '-' }}</strong>
                                <span class="text-secondary ms-2" style="font-size: 0.85em;">{{ $transactionData->requestHeader->description ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($outstandingSummary)
                    <div class="mb-0 mt-3">
                        <div class="alert alert-warning mb-0">
                            <div class="row text-center">
                                <div class="col">
                                    <div class="text-secondary small">Total Pengajuan</div>
                                    <div class="fw-bold">@uang($outstandingSummary->total_request_amount)</div>
                                </div>
                                <div class="col">
                                    <div class="text-secondary small">Terealisasi</div>
                                    <div class="fw-bold text-purple">@uang($outstandingSummary->total_transaction_amount)</div>
                                </div>
                                <div class="col">
                                    <div class="text-secondary small">Sisa Outstanding</div>
                                    <div class="fw-bold text-orange">@uang($outstandingSummary->total_remaining_amount)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
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
                                <th class="text-end">Outstanding</th>
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
                                    <td class="text-end">
                                        <template x-if="item.outstanding">
                                            <div>
                                                <div class="small text-secondary">Dari: <span x-text="formatRupiah(item.outstanding.rd_amount)"></span></div>
                                                <div class="small text-secondary">Realisasi: <span x-text="formatRupiah(item.outstanding.total_realized)"></span></div>
                                                <div class="fw-bold" :class="item.amount > item.outstanding.remaining_amount ? 'text-danger' : 'text-success'" x-text="'Sisa: ' + formatRupiah(item.outstanding.remaining_amount)"></div>
                                            </div>
                                        </template>
                                        <template x-if="!item.outstanding">
                                            <span class="text-muted small">-</span>
                                        </template>
                                    </td>
                                    <td>
                                        <input type="number" :name="`items[${index}][amount]`" class="form-control text-end" x-model.number="item.amount" min="0" required {{ $readOnly ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        @if(!$readOnly)
                                        <button type="button" class="btn btn-icon btn-outline-danger" @click="removeItem(index)" :disabled="items.length === 1 || item.request_detail_id">
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

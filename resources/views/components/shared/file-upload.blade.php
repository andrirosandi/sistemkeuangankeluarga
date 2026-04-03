@props([
    'name' => 'file_id',
    'label' => 'Unggah File',
    'multiple' => false,
    'accept' => 'image/*',
    'currentValue' => null, // Can be a URL string or an array of URLs/IDs
    'endpoint' => route('api.upload'),
    'id' => 'upload-' . Str::random(8),
    'mode' => 'default'
])

<div {{ $attributes->merge(['class' => 'mb-3']) }}
     x-data='fileUploadComponent({
        name: "{{ $name }}",
        multiple: {{ $multiple ? "true" : "false" }},
        accept: "{{ $accept }}",
        endpoint: "{{ $endpoint }}",
        mode: "{{ $mode }}",
        currentValues: @json(is_array($currentValue) ? $currentValue : ($currentValue ? [$currentValue] : []))
     })'
     id="{{ $id }}">
    
    <label class="form-label">{{ $label }}</label>

    {{-- Dropzone Area --}}
    <div
        class="border border-dashed rounded p-4 text-center position-relative mb-2"
        style="cursor:pointer; min-height:140px; display:flex; align-items:center; justify-content:center; transition: background 0.2s; z-index:1;"
        :class="dropzoneHover ? 'bg-light border-primary' : 'bg-white'"
        @dragover.prevent="dropzoneHover = true"
        @dragleave.prevent="dropzoneHover = false"
        @drop.prevent="handleDrop($event)"
        @click="triggerFileInput()"
    >
        <div style="pointer-events:none;">
            {{-- State: Uploading (Global for Dropzone) --}}
            <template x-if="isUploading">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="text-muted small fw-bold" x-text="uploadStatus">Memproses...</div>
                </div>
            </template>

            {{-- State: Hover --}}
            <template x-if="!isUploading && dropzoneHover">
                <div class="text-primary text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg mb-2" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                    <div class="fw-bold">Lepas file di sini</div>
                </div>
            </template>

            {{-- State: Default / Single Preview --}}
            <template x-if="!isUploading && !dropzoneHover">
                <div>
                    {{-- If Single & Has File --}}
                    <template x-if="!multiple && files.length > 0">
                        <div class="text-center">
                            <template x-if="files[0].url">
                                <img :src="files[0].url" class="img-fluid rounded border shadow-sm mb-2" style="max-height:100px; object-fit:contain;">
                            </template>
                            <div class="text-primary small fw-bold">Klik untuk ganti file</div>
                        </div>
                    </template>

                    {{-- If Multiple or No File --}}
                    <template x-if="multiple || files.length === 0">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted mb-2" width="32" height="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /></svg>
                            <div class="text-muted">Tarik file ke sini atau <strong>klik</strong></div>
                            <small class="text-secondary small d-block mt-1">{{ $accept }}</small>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Hidden Input for Single File --}}
    <input type="file" x-ref="fileInput" class="d-none" :multiple="multiple" @change="handleSelect($event)" accept="{{ $accept }}">

    {{-- Multiple Files List --}}
    <template x-if="multiple && files.length > 0">
        <div class="list-group list-group-flush border rounded overflow-hidden">
            <template x-for="(file, index) in files" :key="index">
                <div class="list-group-item d-flex align-items-center justify-content-between p-2">
                    <div class="d-flex align-items-center gap-2">
                        <template x-if="file.url">
                            <img :src="file.url" class="rounded border" style="width:40px; height:40px; object-fit:cover;">
                        </template>
                        <div>
                            <div class="text-truncate" style="max-width: 150px; font-size: 0.85rem;" x-text="file.name"></div>
                            <div class="small" :class="file.status === 'error' ? 'text-danger' : 'text-muted'">
                                <span x-show="file.status === 'uploading'">Mengunggah...</span>
                                <span x-show="file.status === 'success'" class="text-success"><i class="ti ti-check small"></i> Berhasil</span>
                                <span x-show="file.status === 'error'" x-text="file.error"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-icon btn-ghost-danger btn-sm border-0" @click="removeFile(index)">
                        <i class="ti ti-x"></i>
                    </button>
                    
                    {{-- Hidden input for form submission --}}
                    <input type="hidden" :name="multiple ? name + '[]' : name" :value="file.id" x-show="file.id">
                </div>
            </template>
        </div>
    </template>

    {{-- Hidden input for Single mode success --}}
    <template x-if="!multiple && files.length > 0 && files[0].id">
        <input type="hidden" :name="name" :value="files[0].id">
    </template>

    <small class="text-secondary mt-1 d-block" x-text="uploadStatus" x-show="!multiple"></small>
</div>

import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global File Upload Component Logic
Alpine.data('fileUploadComponent', (config = {}) => ({
    name: config.name || 'file',
    multiple: config.multiple || false,
    accept: config.accept || 'image/*',
    endpoint: config.endpoint || '/api/upload-media',
    currentValues: config.currentValues || [], // Initial URLs or IDs

    files: [], // { id, url, status, error, name }
    isUploading: false,
    dropzoneHover: false,
    uploadStatus: '',

    init() {
        if (this.currentValues.length > 0) {
            this.files = this.currentValues.map(val => ({
                id: null,
                url: typeof val === 'string' ? val : null,
                status: 'success',
                name: 'Existing File'
            }));
        }
    },

    triggerFileInput() {
        this.$refs.fileInput.click();
    },

    handleSelect(event) {
        const selectedFiles = Array.from(event.target.files);
        this.processFiles(selectedFiles);
        event.target.value = ''; // Reset input
    },

    handleDrop(event) {
        this.dropzoneHover = false;
        const droppedFiles = Array.from(event.dataTransfer.files);
        this.processFiles(droppedFiles);
    },

    async processFiles(selectedFiles) {
        if (config.mode === 'settings-logo' && selectedFiles.length > 0) {
            const file = selectedFiles[0];
            this.files = []; // Reset visual
            this.isUploading = true;
            this.uploadStatus = 'Memproses Gambar...';

            try {
                // 1. Resize for Logo
                const logoFile = await this.resizeImage(file, 512, false);
                const logoObj = { name: 'Logo', status: 'uploading', url: URL.createObjectURL(logoFile) };
                this.files.push(logoObj);
                
                // 2. Resize for Favicon 
                const faviconFile = await this.resizeImage(file, 64, true);
                // We don't show favicon in the list for settings-logo mode to keep it clean, 
                // but we process it in the background

                this.uploadStatus = 'Mengunggah Logo...';
                const logoResult = await this.uploadToApi(logoFile);
                this.$dispatch('logo-uploaded', logoResult.media_id);
                logoObj.status = 'success';
                logoObj.id = logoResult.media_id;

                this.uploadStatus = 'Mengunggah Favicon...';
                const faviconResult = await this.uploadToApi(faviconFile);
                this.$dispatch('favicon-uploaded', faviconResult.media_id);
                this.uploadStatus = 'Logo & Favicon berhasil';
            } catch (e) {
                this.uploadStatus = 'Gagal: ' + e.message;
            } finally {
                this.isUploading = false;
                this.$dispatch('uploading-changed', { uploading: false });
            }
            return;
        }

        if (!this.multiple && selectedFiles.length > 0) {
            this.files = []; // Clear if single mode
            selectedFiles = [selectedFiles[0]];
        }

        for (const file of selectedFiles) {
            const fileObj = {
                file: file,
                name: file.name,
                status: 'uploading',
                url: URL.createObjectURL(file), // Preview URL
                id: null,
                error: null
            };
            
            this.files.push(fileObj);
            await this.uploadFile(fileObj);
        }
    },

    // Refactored helper for direct API call
    async uploadToApi(file) {
        const formData = new FormData();
        formData.append('file', file);
        const response = await fetch(this.endpoint, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Upload gagal');
        return result;
    },

    async uploadFile(fileObj) {
        this.isUploading = true;
        try {
            const formData = new FormData();
            formData.append('file', fileObj.file);

            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.error || 'Gagal mengunggah');

            fileObj.status = 'success';
            fileObj.id = result.media_id;
            fileObj.url = result.url;
            this.uploadStatus = 'Berhasil diunggah';

            // Dispatch dynamic event based on name (e.g. avatar-uploaded)
            this.$dispatch(`${this.name}-uploaded`, result.media_id);
        } catch (error) {
            fileObj.status = 'error';
            fileObj.error = error.message;
            this.uploadStatus = 'Gagal: ' + error.message;
        } finally {
            this.isUploading = this.files.some(f => f.status === 'uploading');
            this.$dispatch('uploading-changed', { uploading: this.isUploading });
        }
    },

    removeFile(index) {
        this.files.splice(index, 1);
    },

    // Special Helper for Image Resizing (Used in Settings)
    async resizeImage(file, size, isSquare = false) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    if (isSquare) {
                        const min = Math.min(width, height);
                        canvas.width = size;
                        canvas.height = size;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, (width - min) / 2, (height - min) / 2, min, min, 0, 0, size, size);
                    } else {
                        if (width > height) {
                            if (width > size) {
                                height = Math.round((height * size) / width);
                                width = size;
                            }
                        } else {
                            if (height > size) {
                                width = Math.round((width * size) / height);
                                height = size;
                            }
                        }
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                    }
                    canvas.toBlob((blob) => {
                        resolve(new File([blob], file.name, { type: 'image/webp' }));
                    }, 'image/webp', 0.8);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }
}));

Alpine.start();

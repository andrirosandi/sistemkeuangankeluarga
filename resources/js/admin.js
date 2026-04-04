import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import Alpine from 'alpinejs';
window.Alpine = Alpine;

import '@tabler/core/dist/js/tabler.esm.min.js';
import './datatable-handler.js';
import fileUploadComponent from './components/file-upload.js';

// Register components
Alpine.data('fileUploadComponent', fileUploadComponent);

// Jalankan Alpine
Alpine.start();

// Re-init Bootstrap components
document.addEventListener('DOMContentLoaded', () => {
    // Re-init Dropdowns (Fix for Alpine intercept)
    const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdowns.forEach(el => new bootstrap.Dropdown(el));
    
    // Re-init Modals (Ensuring manual calls work)
    const modals = document.querySelectorAll('.modal');
    modals.forEach(el => {
        // Hanya inisialisasi jika elemennya ada bos!
        if (el) new bootstrap.Modal(el);
    });
});
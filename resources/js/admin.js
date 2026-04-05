import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import htmx from 'htmx.org';
window.htmx = htmx;

import Alpine from 'alpinejs';
window.Alpine = Alpine;

import '@tabler/core/dist/js/tabler.esm.min.js';
import './datatable-handler.js';
import fileUploadComponent from './components/file-upload.js';

// Register components
Alpine.data('fileUploadComponent', fileUploadComponent);

// Jalankan Alpine
Alpine.start();

function initScripts() {
    // Re-init Dropdowns (Fix for Alpine intercept)
    const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdowns.forEach(el => bootstrap.Dropdown.getOrCreateInstance(el));
    
    // Re-init Modals (Ensuring manual calls work)
    const modals = document.querySelectorAll('.modal');
    modals.forEach(el => {
        if (el) new bootstrap.Modal(el);
    });
}

// Re-init Bootstrap components on first load
document.addEventListener('DOMContentLoaded', initScripts);

// Re-init after HTMX swaps content (SPA navigation)
document.addEventListener('htmx:afterSettle', initScripts);

// Add CSRF Token to all HTMX requests
document.body.addEventListener('htmx:configRequest', (event) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        event.detail.headers['X-CSRF-TOKEN'] = token;
    }
});
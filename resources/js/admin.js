import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import htmx from 'htmx.org';
window.htmx = htmx;

import Alpine from 'alpinejs';
window.Alpine = Alpine;

import '@tabler/core/dist/js/tabler.esm.min.js';
import './datatable-handler.js';
import fileUploadComponent from './components/file-upload.js';

// Register components BEFORE Alpine starts
Alpine.data('fileUploadComponent', fileUploadComponent);

// Theme management
const themeManager = {
    setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
        const themeIcon = document.getElementById('theme-icon');
        if (themeIcon) {
            themeIcon.className = theme === 'dark' ? 'ti ti-sun' : 'ti ti-moon';
        }
    },

    init() {
        const savedTheme = localStorage.getItem('theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        this.setTheme(savedTheme || (systemDark ? 'dark' : 'light'));
    },

    toggle() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        this.setTheme(currentTheme === 'dark' ? 'light' : 'dark');
    }
};

// Mobile sidebar management
const sidebarManager = {
    toggle() {
        const sidebar = document.querySelector('.navbar-vertical');
        const sidebarMenu = document.getElementById('sidebar-menu');
        sidebar?.classList.toggle('show');
        if (sidebarMenu) {
            sidebarMenu.classList.add('show');
        }
    },

    maybeCloseOnOutsideClick(e) {
        if (window.innerWidth < 1200) {
            const sidebar = document.querySelector('.navbar-vertical');
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            if (sidebar && !sidebar.contains(e.target) && mobileMenuToggle && !mobileMenuToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    }
};

themeManager.init();

// Event delegation for theme toggle
document.addEventListener('click', (e) => {
    if (e.target.closest('#theme-toggle')) {
        e.preventDefault();
        themeManager.toggle();
    }

    // Mobile menu toggle
    if (e.target.closest('#mobile-menu-toggle')) {
        sidebarManager.toggle();
    }
});

// Close sidebar on outside click
document.addEventListener('click', (e) => {
    sidebarManager.maybeCloseOnOutsideClick(e);
});

// Jalankan Alpine
Alpine.start();

function initScripts(target) {
    // Scope initialization to target element to avoid re-init existing components
    const scope = target || document;

    // Re-init Tabs
    scope.querySelectorAll('[data-bs-toggle="tab"]').forEach(el => bootstrap.Tab.getOrCreateInstance(el));

    // Re-init Dropdowns
    scope.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => bootstrap.Dropdown.getOrCreateInstance(el));

    // Re-init Collapse (critical for sidebar menus after HTMX body swap)
    scope.querySelectorAll('[data-bs-toggle="collapse"]').forEach(el => bootstrap.Collapse.getOrCreateInstance(el, {toggle: false}));

    // Re-init Tooltips
    scope.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => bootstrap.Tooltip.getOrCreateInstance(el));

    // Re-init Popovers
    scope.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => bootstrap.Popover.getOrCreateInstance(el));

    // Init Modals
    scope.querySelectorAll('.modal').forEach(el => {
        if (el) new bootstrap.Modal(el);
    });
}

// Initial load - init all Bootstrap components in the document
document.addEventListener('DOMContentLoaded', () => initScripts(document));

// Re-init after HTMX swaps content (SPA navigation)
document.addEventListener('htmx:afterSwap', (event) => {
    // Delay Alpine init slightly to allow scripts in swapped content to execute first
    setTimeout(() => {
        // Re-initialize Alpine on the new content
        Alpine.initTree(event.detail.target);

        // Re-init Bootstrap components inside the swapped target only
        initScripts(event.detail.target);
    }, 10);

    // Auto-dismiss flash messages on navigation
    document.querySelectorAll('.alert-dismissible.fade.show').forEach(alert => {
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(alert).close(), 2000);
    });
});

// Add CSRF Token to all HTMX requests
document.body.addEventListener('htmx:configRequest', (event) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        event.detail.headers['X-CSRF-TOKEN'] = token;
    }
});
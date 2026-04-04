import './bootstrap';

import Alpine from 'alpinejs';
import fileUploadComponent from './components/file-upload.js';

window.Alpine = Alpine;

// Global File Upload Component Logic
Alpine.data('fileUploadComponent', fileUploadComponent);

Alpine.start();


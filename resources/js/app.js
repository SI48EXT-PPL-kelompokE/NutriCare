import './bootstrap';
import '@fontsource-variable/inter';
import Alpine from 'alpinejs';
import { debouncedSearch } from './hooks/debounced-search';

// Initialize Alpine
window.Alpine = Alpine;
Alpine.data('debouncedSearch', debouncedSearch);
Alpine.start();

// Global utilities
window.toggleSidebar = function() {
    return {
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        }
    }
};

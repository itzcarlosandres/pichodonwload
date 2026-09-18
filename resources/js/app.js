import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

// Helper global para notificaciones Toast
window.showToast = function(message) {
    window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message } }));
};

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

// Re-ejecutar Lucide icons tras actualizaciones del DOM
document.addEventListener('lucide-refresh', () => {
    createIcons({ icons });
});

Alpine.start();

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

// Wrapper seguro que provee automáticamente todos los iconos si se invoca sin argumentos
const safeCreateIcons = (options = {}) => {
    return createIcons({
        icons,
        ...options,
    });
};

window.Alpine = Alpine;
window.createIcons = safeCreateIcons;
window.lucide = {
    createIcons: safeCreateIcons,
    icons,
};

// Helper global para notificaciones Toast
window.showToast = function(message) {
    window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message } }));
};

document.addEventListener('DOMContentLoaded', () => {
    safeCreateIcons();
});

// Re-ejecutar Lucide icons tras actualizaciones del DOM
document.addEventListener('lucide-refresh', () => {
    safeCreateIcons();
});

Alpine.start();


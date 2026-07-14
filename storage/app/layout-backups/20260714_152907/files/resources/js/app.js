import './bootstrap';
import './gis-map';

const registerServiceWorker = () => {
    if ('serviceWorker' in navigator && import.meta.env.PROD) {
        navigator.serviceWorker.register('/service-worker.js').catch((error) => {
            console.error('Service worker gagal didaftarkan:', error);
        });
    }
};

window.addEventListener('load', registerServiceWorker);

document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('.leaflet-container').forEach((element) => {
        if (element._leaflet_id) {
            setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
        }
    });
});

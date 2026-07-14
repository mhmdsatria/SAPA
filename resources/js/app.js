import './bootstrap';
import './gis-map';

const resolveTheme = () => {
    const stored = localStorage.getItem('theme');

    if (stored === 'dark' || stored === 'light') {
        return stored;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

window.applyTheme = (value) => {
    const theme = value === true || value === 'dark' ? 'dark' : 'light';
    const isDark = theme === 'dark';

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = theme;
    localStorage.setItem('theme', theme);

    const themeMeta = document.querySelector('meta[name="theme-color"]');
    if (themeMeta) {
        themeMeta.setAttribute('content', isDark ? '#020617' : '#ffffff');
    }

    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme, isDark } }));

    return isDark;
};

window.toggleTheme = () => window.applyTheme(resolveTheme() !== 'dark');
window.applyTheme(resolveTheme());

const registerServiceWorker = () => {
    if ('serviceWorker' in navigator && import.meta.env.PROD) {
        navigator.serviceWorker.register('/service-worker.js').catch((error) => {
            console.error('Service worker gagal didaftarkan:', error);
        });
    }
};

window.addEventListener('load', registerServiceWorker);

document.addEventListener('livewire:navigated', () => {
    window.applyTheme(resolveTheme());

    document.querySelectorAll('.leaflet-container').forEach((element) => {
        if (element._leaflet_id) {
            setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
        }
    });
});

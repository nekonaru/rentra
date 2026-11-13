import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-print]')) {
        window.print();
    }
});

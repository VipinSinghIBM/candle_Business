/**
 * Lumière Candle Co. - Interactive Boutique Scripts
 */

// Simple Toast Notification
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const isSuccess = type === 'success';
    toast.className = `transform translate-y-4 opacity-0 transition-all duration-300 pointer-events-auto flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl text-sm font-medium ${
        isSuccess ? 'bg-charcoal-900 text-cream-50 border border-gold-400/40' : 'bg-rose-900 text-white border border-rose-700'
    }`;
    
    toast.innerHTML = `
        <span class="${isSuccess ? 'text-gold-400' : 'text-rose-300'} font-bold">✧</span>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => {
        toast.classList.remove('translate-y-4', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    }, 10);

    // Auto remove after 3.5s
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-4', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Quantity Adjusters
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.qty-btn-minus').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const input = e.target.closest('.qty-container').querySelector('.qty-input');
            let val = parseInt(input.value) || 1;
            if (val > 1) {
                input.value = val - 1;
                input.dispatchEvent(new Event('change'));
            }
        });
    });

    document.querySelectorAll('.qty-btn-plus').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const input = e.target.closest('.qty-container').querySelector('.qty-input');
            let val = parseInt(input.value) || 1;
            input.value = val + 1;
            input.dispatchEvent(new Event('change'));
        });
    });
});

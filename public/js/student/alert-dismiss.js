document.addEventListener('DOMContentLoaded', function () {
    const message = document.getElementById('success-message');

    if (message) {
        setTimeout(function () {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';

            setTimeout(() => message.remove(), 500);
        }, 2000);
    }
});

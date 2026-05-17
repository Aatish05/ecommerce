(() => {
    'use strict';

    const html = document.documentElement;
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme) {
        html.setAttribute('data-bs-theme', storedTheme);
    }

    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const nextTheme = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', nextTheme);
        localStorage.setItem('theme', nextTheme);
    });

    document.querySelectorAll('.needs-validation').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const password = form.querySelector('[data-password-rules]');
            if (password && password.value.length > 0) {
                const valid = password.value.length >= 8 && /[A-Za-z]/.test(password.value) && /\d/.test(password.value);
                password.setCustomValidity(valid ? '' : 'Password must be at least 8 characters and include letters and numbers.');
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });

    document.querySelectorAll('[data-confirm]').forEach((button) => {
        button.addEventListener('click', (event) => {
            if (!window.confirm(button.dataset.confirm || 'Are you sure?')) {
                event.preventDefault();
            }
        });
    });

    const banner = document.getElementById('cookieBanner');
    if (banner && localStorage.getItem('cookiesAccepted') !== 'yes') {
        banner.classList.add('show');
    }

    document.getElementById('acceptCookies')?.addEventListener('click', () => {
        localStorage.setItem('cookiesAccepted', 'yes');
        banner?.classList.remove('show');
    });
})();

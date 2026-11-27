document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Lanjutkan tindakan ini?')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-alert] .alert-close').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('[data-alert]').remove();
        });
    });

    document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            document
                .querySelector(button.dataset.menuToggle)
                ?.classList.toggle('is-open');
        });
    });
});

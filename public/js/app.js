document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-generation-form]');
    const submitButton = document.querySelector('[data-submit-button]');

    if (!form || !submitButton) {
        return;
    }

    form.addEventListener('submit', () => {
        submitButton.disabled = true;
        submitButton.textContent = 'Generating...';
    });
});

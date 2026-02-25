document.addEventListener('ea.form.error', function (event) {
    const form = event.detail.form;
    const firstInvalid = form.querySelector(':invalid:not(:disabled)');

    if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus({ preventScroll: true });
    }
});

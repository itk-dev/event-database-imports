document.addEventListener('ea.form.error', function (event) {
    const form = event.detail.form;
    const firstInvalid = form.querySelector('input:invalid:not(:disabled), select:invalid:not(:disabled), textarea:invalid:not(:disabled)');

    if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus({ preventScroll: true });
    }
});

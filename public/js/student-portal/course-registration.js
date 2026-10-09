document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('studentCourseRegistration');
    if (!form) return;
    const boxes = [...form.querySelectorAll('.registration-course')];
    const message = document.getElementById('registrationLoad');
    const button = document.getElementById('registerCourses');
    const creditBar = document.getElementById('registrationCreditBar');
    const current = Number(form.dataset.current || 0);
    const maximum = Number(form.dataset.maximum || 21);
    const update = () => {
        const selected = boxes.filter((box) => box.checked);
        const total = current + selected.reduce((sum, box) => sum + Number(box.dataset.credits || 0), 0);
        const valid = selected.length > 0 && total <= maximum;
        message.className = total > maximum ? 'portal-load-message is-danger' : 'portal-load-message';
        message.innerHTML = `<i class="bi ${total > maximum ? 'bi-exclamation-circle' : 'bi-info-circle'}" aria-hidden="true"></i><span>Course load after registration: ${total.toFixed(1)} / ${maximum.toFixed(1)} credit hours.</span>`;
        button.disabled = !valid;
        boxes.forEach((box) => box.closest('tr')?.classList.toggle('is-selected', box.checked));
        if (creditBar) {
            creditBar.style.width = `${Math.min(100, maximum > 0 ? total / maximum * 100 : 0)}%`;
            creditBar.classList.toggle('is-danger', total > maximum);
        }
    };
    boxes.forEach((box) => box.addEventListener('change', update));
    form.addEventListener('submit', () => {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Registering...';
    });
    update();
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('studentCourseRegistration');
    if (!form) return;
    const boxes = [...form.querySelectorAll('.registration-course')];
    const message = document.getElementById('registrationLoad');
    const button = document.getElementById('registerCourses');
    const current = Number(form.dataset.current || 0);
    const maximum = Number(form.dataset.maximum || 21);
    const update = () => {
        const selected = boxes.filter((box) => box.checked);
        const total = current + selected.reduce((sum, box) => sum + Number(box.dataset.credits || 0), 0);
        const valid = selected.length > 0 && total <= maximum;
        message.className = total > maximum ? 'text-danger' : 'text-muted';
        message.textContent = `Course load after registration: ${total.toFixed(1)} / ${maximum.toFixed(1)} credit hours.`;
        button.disabled = !valid;
    };
    boxes.forEach((box) => box.addEventListener('change', update));
    update();
});

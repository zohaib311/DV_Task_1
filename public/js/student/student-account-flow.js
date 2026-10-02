document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-student-form], [data-teacher-form]').forEach((form) => {
        const account = form.querySelector('[name="user_id"]');
        const create = form.querySelector('[name="create_portal_account"]');
        const passwordBox = form.querySelector('[data-account-passwords]');
        const fields = ['name', 'email', 'phone'].map((name) => form.querySelector(`[name="${name}"]`)).filter(Boolean);
        const manual = Object.fromEntries(fields.map((field) => [field.name, field.value]));
        const refresh = () => {
            const linked = Boolean(account?.value);
            const option = account?.selectedOptions[0];
            if (linked && option) {
                fields.forEach((field) => { field.value = option.dataset[field.name] || ''; field.readOnly = true; field.classList.add('bg-light'); });
                if (create) { create.checked = false; create.disabled = true; }
            } else {
                fields.forEach((field) => { field.readOnly = false; field.classList.remove('bg-light'); if (!field.value) field.value = manual[field.name] || ''; });
                if (create) create.disabled = false;
            }
            if (passwordBox) passwordBox.hidden = linked || !create?.checked;
        };
        account?.addEventListener('change', refresh);
        create?.addEventListener('change', refresh);
        refresh();
    });
});

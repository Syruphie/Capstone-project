/**
 * Required-field validation for forms. Import only on pages that need it.
 */
export function attachRequiredFieldValidation(root = document) {
    const forms = root.querySelectorAll('form');
    forms.forEach((form) => {
        form.addEventListener('submit', function (e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach((field) => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });
}

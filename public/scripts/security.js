document.addEventListener('DOMContentLoaded', () => {
    const passwordInputs = document.querySelectorAll('input[type="password"]');

    passwordInputs.forEach(input => {
        // Create strength feedback element if it doesn't exist
        if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('password-strength-feedback')) {
            const feedback = document.createElement('div');
            feedback.className = 'password-strength-feedback';
            feedback.style.fontSize = '0.75rem';
            feedback.style.marginTop = '0.25rem';
            feedback.style.minHeight = '1.25rem';
            feedback.style.transition = 'all 0.3s ease';
            input.parentNode.insertBefore(feedback, input.nextSibling);
        }

        input.addEventListener('input', () => {
            const password = input.value;
            const feedback = input.nextElementSibling;

            if (password.length === 0) {
                feedback.textContent = '';
                return;
            }

            const requirements = [
                { regex: /.{8,}/, text: 'Min. 8 znaków' },
                { regex: /[A-Z]/, text: 'Wielka litera' },
                { regex: /[0-9]/, text: 'Cyfra' },
                { regex: /[!@#$%^&*(),.?":{}|<>]/, text: 'Znak specjalny' }
            ];

            const unmet = requirements.filter(r => !r.regex.test(password));

            if (unmet.length === 0) {
                feedback.textContent = 'Hasło silne ✓';
                feedback.style.color = '#10b981'; // green-500
            } else {
                feedback.textContent = 'Wymagane: ' + unmet.map(r => r.text).join(', ');
                feedback.style.color = '#ef4444'; // red-500
            }
        });
    });
});

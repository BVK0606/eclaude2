// Login/Register Auth JS
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(inputId + '-toggle');
    if (input.type === 'password') {
        input.type = 'text';
        toggle.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        toggle.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
// Auto-fill demo credentials
if (document.querySelectorAll('[data-demo]').length) {
    document.addEventListener('DOMContentLoaded', function() {
        const demoButtons = document.querySelectorAll('[data-demo]');
        demoButtons.forEach(button => {
            button.addEventListener('click', function() {
                const [username, password] = this.dataset.demo.split(':');
                document.getElementById('username').value = username;
                document.getElementById('password').value = password;
            });
        });
    });
}
// Password confirmation validation (register page)
if (document.getElementById('confirm_password')) {
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        if (password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
}
// Real-time password strength indicator (register page)
if (document.getElementById('password-strength')) {
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('password-strength');
        if (strengthBar) {
            let strength = 0;
            if (password.length >= 6) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const strengthColors = ['danger', 'warning', 'info', 'success', 'success'];
            strengthBar.className = `progress-bar bg-${strengthColors[strength - 1]}`;
            strengthBar.style.width = `${(strength / 5) * 100}%`;
            strengthBar.textContent = strengthLevels[strength - 1] || '';
        }
    });
}

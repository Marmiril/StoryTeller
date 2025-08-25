document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('userRegistrationForm');
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        let isValid = true;
        clearErrors();

        if (!username.value.match(/^[a-zA-Z0-9]{3,}$/)) {
            showError(username, "El nombre del usuario ha de contener al menos 3 caracteres.");
            isValid = false;
        } else {
            if (await checkDuplicateUsername(username.value)) {
                showError(username, "El nombre de usuario ya está en uso");
                isValid = false;
            }
        }

        if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            showError(email, "Por favor, indique un email válido.");
            isValid = false;
        } else {
            if (await checkDuplicateEmail(email.value)) {
                showError(email, "El email ya está registrado");
                isValid = false;
            }
        }

        if (!password.value.match(/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/)) {
            showError(password, "La contraseña ha de tener al menos 8 caracteres, 1 mayúscula, 1 minúscula y 1 número.");
            isValid = false;
        }

        if (password.value !== confirmPassword.value) {
            showError(confirmPassword, "Las contraseñas no coinciden.");
            isValid = false;
        }
        if (isValid) {
            try {
                form.submit(); 
            } catch (error) {
                console.error('Error al enviar el formulario:', error);
            }
        }
    });

    async function checkDuplicateEmail(email) {
        try {
            const response = await fetch('../php/check_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            });
            const data = await response.json();
            return data.exists;
        } catch (error) {
            console.error('Error al verificar email:', error);
            return false;
        }
    }


    function showError(input, message) {
        const formGroup = input.parentElement;
        const error = document.createElement('span');
        error.className = 'error-message';
        error.textContent = message;
        formGroup.appendChild(error);
        formGroup.classList.add('error');
    }

    function clearErrors() {
        const errors = document.querySelectorAll('.error-message');
        errors.forEach(error => error.remove());
        document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    }

    async function checkDuplicateUsername(username) {
        try {
            const response = await fetch('../php/check_username.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username: username })
            });
            const data = await response.json();
            return data.exists;
        } catch (error) {
            console.error('Error al verificar nombre de usuario:', error);
            return false;
        }
    }


    document.getElementById('btnClear').addEventListener('click', function () {
        document.getElementById('userRegistrationForm').reset();
        clearErrors(); 
    });
});

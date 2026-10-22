document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.querySelector("#login-form form");
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const togglePasswordButton = document.querySelector("#toggle-password");
    const loginButton = loginForm?.querySelector('button[type="submit"]');

    if (togglePasswordButton && passwordInput) {
        togglePasswordButton.addEventListener("click", function () {
            const isHidden = passwordInput.type === "password";

            passwordInput.type = isHidden ? "text" : "password";
            togglePasswordButton.textContent = isHidden ? "Hide password" : "Show password";
        });
    }

    if (loginForm && emailInput && passwordInput) {
        loginForm.addEventListener("submit", function (event) {
            const email = emailInput.value.trim();
            const password = passwordInput.value;

            if (!email || !password) {
                event.preventDefault();
                alert("Please enter both your email and password.");
                return;
            }

            if (loginButton) {
                loginButton.disabled = true;
                loginButton.textContent = "Logging in...";
            }
        });
    }
});


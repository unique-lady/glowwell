document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signup-form');

    signupForm.addEventListener('submit', (e) => {
        const password = signupForm.querySelector('input[name="password"]').value;
        const repassword = signupForm.querySelector('input[name="repassword"]').value;

        if (password !== repassword) {
            e.preventDefault();
            alert("Passwords do not match! Please check again.");
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const profileForm = document.getElementById('profile-form');

    profileForm.addEventListener('submit', (e) => {
        // تحقق بسيط قبل الإرسال
        const inputs = profileForm.querySelectorAll('input, select');
        let empty = false;

        inputs.forEach(input => {
            if (!input.value) empty = true;
        });

        if (empty) {
            e.preventDefault();
            alert("Please fill in all fields to complete your profile.");
        }
    });
});
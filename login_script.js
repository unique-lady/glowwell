document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const toast = document.getElementById('success-toast');

    // إذا كان التوست موجوداً (بسبب نجاح PHP)، قم بإخفائه بعد 3 ثوانٍ
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    // تفاعل بسيط عند الضغط على روابط ليست مفعلة بعد
    document.querySelector('.signup-link').addEventListener('click', () => {
        alert("Registration feature coming soon!");
    });
});
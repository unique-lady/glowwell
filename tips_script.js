function toggleFavorite(btn) {
    btn.classList.toggle('active');
    btn.classList.toggle('text-gray-300');
    btn.classList.toggle('text-pink-500');
    
    if(btn.classList.contains('active')) {
        // تأثير بسيط عند الضغط
        btn.style.transform = "scale(1.3)";
        setTimeout(() => btn.style.transform = "scale(1)", 200);
    }
}

// يمكن إضافة منطق هنا للبحث في النصائح إذا أردت مستقبلاً
console.log("Tips page ready to glow!");
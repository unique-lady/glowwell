document.addEventListener('DOMContentLoaded', () => {
    console.log("Progress page loaded. Animating charts...");
    
    // يمكن إضافة منطق هنا لجعل الرسم البياني يتفاعل عند مرور الماوس على النقاط
    const points = document.querySelectorAll('circle');
    points.forEach(point => {
        point.addEventListener('mouseenter', () => {
            point.setAttribute('r', '8');
            point.style.cursor = 'pointer';
        });
        point.addEventListener('mouseleave', () => {
            point.setAttribute('r', '5');
        });
    });
});
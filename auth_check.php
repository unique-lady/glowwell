<?php
// بما أننا قمنا بـ include 'config.php' في بداية ملفاتنا (والذي يحتوي على session_start)
// فلا داعي لإعادة تكرار session_start هنا.
// إذا كان هناك أي ملف لا يحتوي على config.php، تأكدي من تضمينه أولاً.

// 1. منع التخزين في الكاش (هذا الجزء ممتاز ولا يحتاج تغيير)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 2. التحقق من تسجيل الدخول
// بما أننا تأكدنا من بدء الجلسة في config.php، يمكننا الوصول لـ $_SESSION مباشرة
if (!isset($_SESSION['user_id'])) {
    header("Location: /glowwell/login.php"); 
    exit();
}
?>
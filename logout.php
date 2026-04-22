<?php
// 1. يجب بدء الجلسة أولاً للوصول للبيانات المراد حذفها
session_start();

// 2. مسح جميع متغيرات الجلسة من الذاكرة
$_SESSION = array();

// 3. حذف ملف تعريف الارتباط (Cookie) الخاص بالجلسة من متصفح المستخدم
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. تدمير الجلسة بالكامل من السيرفر
session_destroy();

// 5. التوجيه لصفحة تسجيل الدخول
header("Location: login.php");
exit();
?>
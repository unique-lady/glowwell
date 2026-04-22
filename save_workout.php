<?php
include 'config.php';

// 1. التحقق من الجلسة والبيانات القادمة
if (isset($_SESSION['user_id']) && isset($_POST['workout_name']) && isset($_POST['duration'])) {
    
    // تأمين البيانات
    $user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
    $w_name = mysqli_real_escape_string($conn, $_POST['workout_name']);
    $w_duration = (int)$_POST['duration']; // المدة بالثواني
    $w_date = date("Y-m-d");

    // 2. حساب السعرات المحروقة 
    // (0.133 سعرة لكل ثانية ≈ 8 سعرات للدقيقة)
    // استخدمنا round لجعل الرقم جميلاً (مثلاً 12.45 بدل 12.45333)
    $calories_burned = round($w_duration * 0.133, 2);

    // 3. إدخال البيانات في جدول activities
    // ملاحظة: تأكدي أن أسماء الأعمدة في قاعدة بياناتك تطابق (user_id, activity_name, duration, date, calories)
    $query = "INSERT INTO activities (user_id, activity_name, duration, date, calories) 
              VALUES ('$user_id', '$w_name', '$w_duration', '$w_date', '$calories_burned')";
    
    if (mysqli_query($conn, $query)) {
        echo "success"; // هذه الكلمة هي اللي ينتظرها الـ Fetch في صفحة الوورك آوت
    } else {
        // في حال وجود خطأ في القاعدة (للمبرمج فقط)
        echo "Database Error: " . mysqli_error($conn);
    }
} else {
    // في حال محاولة الدخول للملف مباشرة أو نقص بيانات
    echo "Missing Data or Unauthorized";
}
?>
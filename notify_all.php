<?php
// تضمين الاتصال بقاعدة البيانات والدالة الخاصة بك
require_once 'config.php';
require_once 'send_notification.php'; // تأكدي أن الاسم مطابق لملفك

$title = "🔥 تحديث جديد من GlowWell!";
$message = "لا تنسي تسجيل وجباتك اليومية ومتابعة تمارينك للوصول إلى هدفك.";

// جلب جميع التوكنز من قاعدة البيانات
$sql = "SELECT DISTINCT fcm_token FROM user_devices WHERE fcm_token IS NOT NULL AND fcm_token != ''";
$result = mysqli_query($conn, $sql);

$success_count = 0;
$fail_count = 0;

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $token = $row['fcm_token'];
        
        // استدعاء دالتك التي كتبتيها مسبقاً
        if (sendPushNotification($token, $title, $message)) {
            $success_count++;
        } else {
            $fail_count++;
        }
    }
    echo "<h3 style='color: green;'>✅ تم الانتهاء من الإرسال للجميع!</h3>";
    echo "<p>نجح إرسال: $success_count | فشل: $fail_count</p>";
} else {
    echo "<h3 style='color: red;'>❌ لا يوجد أي أجهزة مسجلة في قاعدة البيانات.</h3>";
}
?>
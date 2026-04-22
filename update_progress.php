<?php
include 'config.php';

$user_id = $_SESSION['user_id'];
$challenge_id = $_POST['challenge_id'];

// الحصول على الساعة الحالية
$current_hour = (int)date('H');

// إذا كانت الساعة قبل 3 صباحاً، نعتبر التاريخ هو تاريخ أمس
if ($current_hour < 3) {
    $today = date('Y-m-d', strtotime('-1 day'));
} else {
    $today = date('Y-m-d');
}

// محاولة إضافة سجل (بناءً على التاريخ المعدل)
$insert = mysqli_query($conn, "INSERT INTO challenge_logs (user_id, challenge_id, log_date) VALUES ('$user_id', '$challenge_id', '$today')");

if ($insert) {
    // نجحت العملية: تحديث العداد
    mysqli_query($conn, "UPDATE user_challenges SET progress_days = progress_days + 1 WHERE user_id = '$user_id' AND challenge_id = '$challenge_id'");
    echo "success";
} else {
    echo "already_logged";
}

// بعد تحديث التقدم، نضيف هذا الفحص:
$challenge = mysqli_query($conn, "SELECT * FROM challenges WHERE id = '$challenge_id'");
$ch_data = mysqli_fetch_assoc($challenge);

$user_prog = mysqli_query($conn, "SELECT progress_days FROM user_challenges WHERE user_id = '$user_id' AND challenge_id = '$challenge_id'");
$prog = mysqli_fetch_assoc($user_prog);

if ($prog['progress_days'] >= $ch_data['target_days']) {
    // 1. إضافة العملات (مع الهامش الذكي الذي اتفقنا عليه)
    $coins_to_add = $ch_data['reward_coins']; 
    mysqli_query($conn, "UPDATE users SET glow_coins = glow_coins + $coins_to_add WHERE id = '$user_id'");
    
    // 2. تحديث حالة التحدي لمكتمل
    mysqli_query($conn, "UPDATE user_challenges SET status = 'completed' WHERE user_id = '$user_id' AND challenge_id = '$challenge_id'");
    
    echo "reward_unlocked";
}
?>
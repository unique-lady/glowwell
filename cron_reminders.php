<?php
include 'config.php';
include 'send_notification.php';

$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// 1. تذكير الماء
$stmt = $conn->prepare("SELECT user_id, fcm_token FROM user_devices WHERE last_notification_water < (NOW() - INTERVAL 3 HOUR) OR last_notification_water IS NULL");
$stmt->execute();
$res_water = $stmt->get_result();
while($row = $res_water->fetch_assoc()) {
    $msg = "Drink a cup (250ml) of water! اشرب كوباً (250 مل) من الماء!";
    if (sendPushNotification($row['fcm_token'], "💧 GlowWell", $msg)) {
        $upd = $conn->prepare("UPDATE user_devices SET last_notification_water = ? WHERE user_id = ?");
        $upd->bind_param("si", $now, $row['user_id']);
        $upd->execute();
    }
}

// 2. تذكير الوجبات
$meals_data = ['Breakfast' => 'piece/قطعة', 'Lunch' => 'scoop/مغرفة', 'Dinner' => 'cup/كوب'];
foreach ($meals_data as $meal => $measurement) {
    $stmt = $conn->prepare("SELECT d.fcm_token, d.user_id FROM user_devices d WHERE NOT EXISTS (SELECT 1 FROM user_meals m WHERE m.user_id = d.user_id AND m.meal_type = ? AND DATE(m.created_at) = ?)");
    $stmt->bind_param("ss", $meal, $today);
    $stmt->execute();
    $res_meals = $stmt->get_result();
    
    while($row = $res_meals->fetch_assoc()) {
        $msg = "Don't forget your $meal! Take a $measurement. لا تنسَ وجبة الـ $meal! تناول $measurement.";
        if (sendPushNotification($row['fcm_token'], "🍴 GlowWell", $msg)) {
            $upd = $conn->prepare("UPDATE user_devices SET last_notification_meals = ? WHERE user_id = ?");
            $upd->bind_param("si", $now, $row['user_id']);
            $upd->execute();
        }
    }
}
echo "Notifications processed successfully.";
?>
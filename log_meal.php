<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $meal_name = $_POST['meal_name'];
    
    // تحويل أول حرف لكبير ليتطابق مع ENUM في قاعدة البيانات
    $meal_type = ucfirst(strtolower($_POST['meal_type'])); 
    
    $calories = intval($_POST['calories']);
    $protein = intval($_POST['protein']);
    $fat = intval($_POST['fat']);
    $carbs = intval($_POST['carbs']);

    // --- [تعديل وقت السهر: منطق الـ 3 فجراً] ---
    date_default_timezone_set('Asia/Riyadh');
    $now = new DateTime();
    $hour = (int)$now->format('H');

    // إذا كانت الساعة بين 12 منتصف الليل و 3 فجراً، نعتبرنا لسه في "يوم أمس"
    if ($hour < 3) {
        $today = $now->modify('-1 day')->format('Y-m-d');
    } else {
        $today = $now->format('Y-m-d');
    }

    $sql = "INSERT INTO meal_tracking (user_id, meal_name, meal_type, calories, protein, fat, carbs, eaten_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issiiiis", $user_id, $meal_name, $meal_type, $calories, $protein, $fat, $carbs, $today);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'error' => $conn->error]);
    }
    $stmt->close();
}
?>
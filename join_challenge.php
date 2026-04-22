<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "error";
    exit();
}

$user_id = $_SESSION['user_id'];
$challenge_id = $_POST['challenge_id'];

// التحقق إذا كان مشتركاً مسبقاً
$check = mysqli_query($conn, "SELECT id FROM user_challenges WHERE user_id = '$user_id' AND challenge_id = '$challenge_id'");

if (mysqli_num_rows($check) == 0) {
    // إضافة الاشتراك
    mysqli_query($conn, "INSERT INTO user_challenges (user_id, challenge_id, progress_days, status) VALUES ('$user_id', '$challenge_id', 0, 'active')");
    echo "success";
} else {
    echo "already_joined";
}
?>
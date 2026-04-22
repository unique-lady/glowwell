<?php
include 'config.php';

if (isset($_POST['glasses']) && isset($_POST['date'])) {
    $user_id = $_SESSION['user_id'];
    $glasses = (int)$_POST['glasses'];
    $date = mysqli_real_escape_string($conn, $_POST['date']);

    // يحفظ البيانات أو يحدّثها إذا كانت موجودة لنفس اليوم
    $sql = "INSERT INTO water_logs (user_id, glasses, log_date) 
            VALUES ($user_id, $glasses, '$date') 
            ON DUPLICATE KEY UPDATE glasses = $glasses";

    mysqli_query($conn, $sql);
}
?>
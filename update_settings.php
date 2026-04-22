<?php
include 'config.php';

if (isset($_POST['column']) && isset($_POST['value'])) {
    $user_id = $_SESSION['user_id'];
    $column = mysqli_real_escape_string($conn, $_POST['column']);
    $value = (int)$_POST['value'];

    $check = mysqli_query($conn, "SELECT id FROM user_settings WHERE user_id = $user_id");
    
    if (mysqli_num_rows($check) > 0) {
        $sql = "UPDATE user_settings SET $column = $value WHERE user_id = $user_id";
    } else {
        $sql = "INSERT INTO user_settings (user_id, $column) VALUES ($user_id, $value)";
    }

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
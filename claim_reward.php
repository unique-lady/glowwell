<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) exit("Unauthorized");

$user_id = $_SESSION['user_id'];
$challenge_id = $_POST['challenge_id'];

// استخدام Prepared Statements لمنع أي تلاعب
$stmt = $conn->prepare("SELECT reward_coins, reward_badge FROM challenges WHERE id = ?");
$stmt->bind_param("i", $challenge_id);
$stmt->execute();
$ch = $stmt->get_result()->fetch_assoc();

if ($ch) {
    $coins = (int)$ch['reward_coins'];
    $badge = $ch['reward_badge'];

    // تحديث آمن
    $upd = $conn->prepare("UPDATE users SET glow_coins = glow_coins + ?, badges = CONCAT(badges, ', ', ?) WHERE id = ?");
    $upd->bind_param("isi", $coins, $badge, $user_id);
    $upd->execute();

    $upd_ch = $conn->prepare("UPDATE user_challenges SET status = 'completed' WHERE user_id = ? AND challenge_id = ?");
    $upd_ch->bind_param("ii", $user_id, $challenge_id);
    $upd_ch->execute();
    
    echo "success";
}
?>
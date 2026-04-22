
<?php
session_start();
require_once 'config.php';

// استيراد مكتبة PHPMailer
require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_SESSION['reset_email'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

$email = $_SESSION['reset_email'];
$code = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

try {
    // 1. تحديث قاعدة البيانات
    $update = $pdo->prepare("UPDATE users SET reset_code = ?, reset_expiry = ? WHERE email = ?");
    $update->execute([$code, $expiry, $email]);

    // 2. إعداد إرسال الإيميل (نفس إعدادات Gmail الخاصة بك)
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'modemodemode07@gmail.com'; 
    $mail->Password   = 'whvg pbrv qqgl aizq';      // كود التطبيق الخاص بك
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('support@glowwell.com', 'GlowWell Support');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'New Verification Code - GlowWell';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; text-align: center; padding: 20px; border: 1px solid #eee; border-radius: 15px;'>
            <h2 style='color: #ed4b9e;'>New Verification Code</h2>
            <p>You requested a new code. Here it is:</p>
            <div style='font-size: 32px; font-weight: bold; color: #22c55e; margin: 20px 0;'>$code</div>
            <p style='color: #777;'>This code will expire in 15 minutes.</p>
        </div>";

    if($mail->send()){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Mail fail']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
}

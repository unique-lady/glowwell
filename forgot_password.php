<?php
/**
 * GlowWell - Forgot Password
 */

// 1. إظهار الأخطاء (Debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// --- نظام الترجمة الاحترافي ---
if (!isset($_SESSION['lang'])) { $_SESSION['lang'] = 'en'; }
if (isset($_GET['tplang'])) {
    $_SESSION['lang'] = ($_SESSION['lang'] == 'en') ? 'ar' : 'en';
    header("Location: forgot_password.php");
    exit;
}

$translations = [
    'en' => [
        'title' => 'Password Recovery - GlowWell',
        'header' => 'Password Recovery',
        'desc' => "Enter your email and we'll send you a secure verification code.",
        'email_label' => 'Email Address',
        'send_btn' => 'Send Reset Code',
        'back_tip' => 'Back to Login',
        'err_not_found' => 'This email is not registered with us.',
        'mail_subject' => 'Password Recovery Code - GlowWell',
        'mail_body_title' => 'Password Recovery',
        'mail_body_text' => 'Your verification code is:',
        'mail_body_footer' => 'This code will expire in 15 minutes.'
    ],
    'ar' => [
        'title' => 'استعادة كلمة المرور - GlowWell',
        'header' => 'استعادة كلمة المرور',
        'desc' => 'أدخل بريدك الإلكتروني وسنرسل لك رمز تحقق آمن.',
        'email_label' => 'البريد الإلكتروني',
        'send_btn' => 'إرسال رمز التحقق',
        'back_tip' => 'العودة لتسجيل الدخول',
        'err_not_found' => 'هذا البريد الإلكتروني غير مسجل لدينا.',
        'mail_subject' => 'رمز استعادة كلمة المرور - GlowWell',
        'mail_body_title' => 'استعادة كلمة المرور',
        'mail_body_text' => 'رمز التحقق الخاص بك هو:',
        'mail_body_footer' => 'هذا الرمز صالح لمدة 15 دقيقة فقط.'
    ]
];
$L = $translations[$_SESSION['lang']];
// -----------------------------

require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $code = rand(100000, 999999);
                $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));
                
                $update = $pdo->prepare("UPDATE users SET reset_code = ?, reset_expiry = ? WHERE email = ?");
                $update->execute([$code, $expiry, $email]);

                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'modemodemode07@gmail.com';
                $mail->Password   = 'whvg pbrv qqgl aizq';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('support@glowwell.com', 'GlowWell Support');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = $L['mail_subject'];
                $mail->Body    = "
                    <div dir='".($_SESSION['lang'] == 'ar' ? 'rtl' : 'ltr')."' style='font-family: Arial, sans-serif; text-align: center; padding: 20px; border: 1px solid #eee; border-radius: 15px;'>
                        <h2 style='color: #ed4b9e;'>{$L['mail_body_title']}</h2>
                        <p>{$L['mail_body_text']}</p>
                        <div style='font-size: 32px; font-weight: bold; color: #22c55e; margin: 20px 0;'>$code</div>
                        <p style='color: #777;'>{$L['mail_body_footer']}</p>
                    </div>";

                if($mail->send()){
                    $_SESSION['reset_email'] = $email;
                    header("Location: verify_code.php");
                    exit;
                }
            } else {
                $error_message = $L['err_not_found'];
            }
        } catch (Exception $e) {
            $error_message = "Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo ($_SESSION['lang'] == 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $L['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); }
        .logo-font { font-family: 'Outfit', sans-serif; } 
        .auth-card { position: relative; border-radius: 50px; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
        
        .back-arrow { position: absolute; top: 30px; left: 30px; color: #9ca3af; transition: 0.3s; z-index: 10; }
        [dir="rtl"] .back-arrow { left: auto; right: 30px; transform: scaleX(-1); }
        [dir="rtl"] .back-arrow:hover { transform: scaleX(-1) translateX(-3px); }
        .back-arrow:hover { color: #ed4b9e; transform: translateX(-3px); }

        .input-box { background-color: #f3f4f6; transition: 0.3s; border: 2px solid transparent; outline: none; }
        .input-box:focus { background-color: #fff; border-color: #fbcfe8; }
        .btn-pink { background-color: #ed4b9e; color: white; transition: 0.3s; }
        .btn-pink:hover { background-color: #d83a8a; transform: translateY(-1px); }

        [dir="rtl"] .pl-12 { padding-left: 1.25rem; padding-right: 3rem; }
        [dir="rtl"] .absolute.left-4 { left: auto; right: 1rem; }
        [dir="rtl"] .text-left { text-align: right; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="fixed bottom-6 right-6 z-50">
        <a href="?tplang=1" class="bg-white p-4 rounded-full shadow-2xl border border-gray-100 hover:scale-110 transition-all flex items-center group">
            <i data-lucide="globe" class="w-6 h-6 text-[#ed4b9e]"></i>
            <span class="max-w-0 overflow-hidden group-hover:max-w-xs group-hover:ms-2 transition-all duration-300 font-bold text-gray-700">
                <?php echo ($_SESSION['lang'] == 'en' ? 'العربية' : 'English'); ?>
            </span>
        </a>
    </div>

    <div class="auth-card bg-white p-12 w-full max-w-[480px] text-center">
        
        <a href="login.php" class="back-arrow" title="<?php echo $L['back_tip']; ?>">
            <i data-lucide="arrow-left" class="w-8 h-8"></i>
        </a>

        <div class="mb-12">
            <h1 class="text-5xl font-extrabold logo-font tracking-tight">
                <span style="color: #ed4b9e;">Glow</span><span style="color: #22c55e;">Well</span>
            </h1>
        </div>
        <h2 class="text-3xl font-extrabold text-gray-800 mb-2"><?php echo $L['header']; ?></h2>
        <p class="text-l text-gray-800 font-extrabold mb-8"><?php echo $L['desc']; ?></p>

        <?php if ($error_message): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-2xl mb-8 text-sm font-bold border border-red-100 flex items-center justify-center gap-2">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8 text-mid text-left">
            <div>
                <label class="block text-gray-700 font-bold mb-4 text-mid uppercase tracking-widest"><?php echo $L['email_label']; ?></label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5"></i>
                    <input type="email" name="email" class="w-full py-4 pl-12 pr-5 input-box rounded-2xl text-lg font-medium" placeholder="example@mail.com" required>
                </div>
            </div>
            <button type="submit" class="w-full py-5 btn-pink rounded-[25px] font-bold text-xl shadow-lg active:scale-95">
                <?php echo $L['send_btn']; ?>
            </button>
        </form>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
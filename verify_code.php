<?php
require_once 'config.php';

// --- نظام الترجمة الاحترافي ---
if (!isset($_SESSION['lang'])) { $_SESSION['lang'] = 'en'; }
if (isset($_GET['tplang'])) {
    $_SESSION['lang'] = ($_SESSION['lang'] == 'en') ? 'ar' : 'en';
    header("Location: verify_code.php");
    exit;
}

$translations = [
    'en' => [
        'title' => 'Verify Code - GlowWell',
        'header' => 'Check Your Email',
        'desc' => "We've sent a 6-digit code to",
        'verify_btn' => 'Verify Code',
        'resend_text' => "Didn't get the code?",
        'resend_link' => 'Resend Email',
        'back_tip' => 'Change Email',
        'err_expired' => 'This code has expired. Please request a new one.',
        'err_incorrect' => 'The code you entered is incorrect.',
        'err_system' => 'System error. Please try again.',
        'js_sending' => 'Sending...',
        'js_success' => 'A new code has been sent to your email!',
        'js_error' => 'System error, please try again.'
    ],
    'ar' => [
        'title' => 'التحقق من الرمز - GlowWell',
        'header' => 'تحقق من بريدك',
        'desc' => 'لقد أرسلنا رمزاً مكوناً من 6 أرقام إلى',
        'verify_btn' => 'تحقق الآن',
        'resend_text' => 'لم يصلك الرمز؟',
        'resend_link' => 'إعادة إرسال البريد',
        'back_tip' => 'تغيير البريد الإلكتروني',
        'err_expired' => 'انتهت صلاحية هذا الرمز. يرجى طلب رمز جديد.',
        'err_incorrect' => 'الرمز الذي أدخلته غير صحيح.',
        'err_system' => 'خطأ في النظام. يرجى المحاولة مرة أخرى.',
        'js_sending' => 'جاري الإرسال...',
        'js_success' => 'تم إرسال رمز جديد إلى بريدك الإلكتروني!',
        'js_error' => 'خطأ في النظام، يرجى المحاولة لاحقاً.'
    ]
];
$L = $translations[$_SESSION['lang']];
// -----------------------------

// التأكد أن المستخدم مر بالمرحلة الأولى
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$error_message = "";
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_code = trim($_POST['code'] ?? '');
    try {
        $stmt = $pdo->prepare("SELECT reset_code, reset_expiry FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $currentTime = date("Y-m-d H:i:s");
            if ($user_code === $user['reset_code']) {
                if ($currentTime <= $user['reset_expiry']) {
                    $_SESSION['code_verified'] = true;
                    header("Location: reset_password.php");
                    exit;
                } else {
                    $error_message = $L['err_expired'];
                }
            } else {
                $error_message = $L['err_incorrect'];
            }
        }
    } catch (PDOException $e) {
        $error_message = $L['err_system'];
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
        .auth-card { border-radius: 50px; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
        
        /* ضبط حقل الكود ليدعم الاتجاهات */
        .code-input { 
            background-color: #f3f4f6; 
            transition: 0.3s; 
            border: 2px solid transparent; 
            text-align: center; 
            letter-spacing: 0.8rem; 
            font-size: 2rem; 
            direction: ltr; /* الأرقام دائماً تُكتب من اليسار لليوم لسهولة الإدخال */
        }
        .code-input:focus { background-color: #fff; border-color: #fbcfe8; outline: none; }
        
        .btn-pink { background-color: #ed4b9e; color: white; transition: 0.3s; }
        .btn-pink:hover { background-color: #d83a8a; transform: translateY(-1px); }
        
        /* تنسيق سهم العودة */
        .back-arrow { position: absolute; top: 30px; left: 30px; color: #9ca3af; transition: 0.3s; }
        [dir="rtl"] .back-arrow { left: auto; right: 30px; transform: scaleX(-1); }
        .back-arrow:hover { color: #ed4b9e; transform: translateX(-3px); }
        [dir="rtl"] .back-arrow:hover { transform: scaleX(-1) translateX(-3px); }
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
        <a href="forgot_password.php" class="back-arrow" title="<?php echo $L['back_tip']; ?>">
            <i data-lucide="arrow-left" class="w-8 h-8"></i>
        </a>

        <div class="mb-10">
            <h1 class="text-5xl font-bold logo-font tracking-tight">
                <span style="color: #ed4b9e;">Glow</span><span style="color: #22c55e;">Well</span>
            </h1>
        </div>

        <h2 class="text-3xl font-extrabold text-gray-800 mb-2"><?php echo $L['header']; ?></h2>
        <p class="text-l text-gray-800 font-extrabold mb-10">
            <?php echo $L['desc']; ?> <br>
            <span class="text-gray-800 font-bold"><?php echo htmlspecialchars($email); ?></span>
        </p>

        <?php if ($error_message): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-2xl mb-8 text-sm font-bold border border-red-100 flex items-center justify-center gap-2">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <input type="text" name="code" maxlength="6" required class="w-full py-5 code-input rounded-2xl font-bold text-gray-800" placeholder="000000">
            <button type="submit" class="w-full py-5 btn-pink rounded-[25px] font-bold text-xl shadow-lg active:scale-95">
                <?php echo $L['verify_btn']; ?>
            </button>
        </form>

        <div class="mt-8 text-gray-600 font-semibold">
            <?php echo $L['resend_text']; ?> 
            <button type="button" onclick="resendCode()" id="resendBtn" class="text-red-500 font-bold hover:underline">
                <?php echo $L['resend_link']; ?>
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // نصوص الجافاسكريبت المترجمة
        const jsTexts = {
            sending: "<?php echo $L['js_sending']; ?>",
            success: "<?php echo $L['js_success']; ?>",
            error: "<?php echo $L['js_error']; ?>",
            original: "<?php echo $L['resend_link']; ?>"
        };

        function resendCode() {
            const btn = document.getElementById('resendBtn');
            btn.disabled = true;
            btn.innerText = jsTexts.sending;

            fetch('resend_handler.php')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(jsTexts.success);
                    btn.innerText = jsTexts.original;
                } else {
                    alert("Error: " + data.message);
                    btn.innerText = jsTexts.original;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert(jsTexts.error);
                btn.innerText = jsTexts.original;
                btn.disabled = false;
            })
            .finally(() => {
                setTimeout(() => {
                    btn.disabled = false;
                }, 30000); 
            });
        }
    </script>
</body>
</html>
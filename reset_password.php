<?php
/**
 * GlowWell - Reset Password Final Step
 */
require_once 'config.php';

// --- نظام الترجمة الاحترافي ---
if (!isset($_SESSION['lang'])) { $_SESSION['lang'] = 'en'; }
if (isset($_GET['tplang'])) {
    $_SESSION['lang'] = ($_SESSION['lang'] == 'en') ? 'ar' : 'en';
    header("Location: reset_password.php");
    exit;
}

$translations = [
    'en' => [
        'title' => 'Create New Password - GlowWell',
        'header' => 'Set your new password',
        'label_pass' => 'New Password',
        'label_confirm' => 'Confirm New Password',
        'update_btn' => 'Update Password',
        'back_tip' => 'Back to Verification',
        'success_msg' => 'Password reset successfully! Redirecting to login...',
        'err_system' => 'Database error. Please try again.',
        'req_text' => 'Password: must be 8 characters, letters & numbers',
        'js_match' => '✔ Passwords match and requirements met',
        'js_weak' => '✖ Weak password: Must be 8+ chars with letters & numbers',
        'js_no_match' => '✖ Passwords do not match'
    ],
    'ar' => [
        'title' => 'تعيين كلمة مرور جديدة - GlowWell',
        'header' => 'تعيين كلمة مرور جديدة',
        'label_pass' => 'كلمة المرور الجديدة',
        'label_confirm' => 'تأكيد كلمة المرور',
        'update_btn' => 'تحديث كلمة المرور',
        'back_tip' => 'العودة للتحقق',
        'success_msg' => 'تم تغيير كلمة المرور بنجاح! جاري تحويلك للدخول...',
        'err_system' => 'خطأ في قاعدة البيانات. يرجى المحاولة لاحقاً.',
        'req_text' => 'كلمة المرور: يجب أن تكون 8 رموز وتتضمن حروفاً وأرقاماً',
        'js_match' => '✔ كلمة المرور مطابقة ومستوفية للشروط',
        'js_weak' => '✖ كلمة مرور ضعيفة: يجب أن تكون 8+ رموز بحروف وأرقام',
        'js_no_match' => '✖ كلمتا المرور غير متطابقتين'
    ]
];
$L = $translations[$_SESSION['lang']];
// -----------------------------

if (!isset($_SESSION['code_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$success_msg = "";
$error_msg = "";
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? '';
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_code = NULL, reset_expiry = NULL WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);

        unset($_SESSION['code_verified']);
        unset($_SESSION['reset_email']);

        $success_msg = $L['success_msg'];
        header("refresh:3;url=login.php");
    } catch (PDOException $e) {
        $error_msg = $L['err_system'];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo ($_SESSION['lang'] == 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
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
        .back-arrow:hover { color: #ed4b9e; transform: translateX(-3px); }
        [dir="rtl"] .back-arrow:hover { transform: scaleX(-1) translateX(-3px); }

        .input-group { background-color: #f3f4f6; transition: 0.3s; border: 2px solid transparent; position: relative; }
        .input-group:focus-within { background-color: #fff; border-color: #fbcfe8; }
        
        .eye-btn { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #000; opacity: 0.7; cursor: pointer; z-index: 10; border: none; background: none; }
        [dir="rtl"] .eye-btn { right: auto; left: 15px; }
        [dir="rtl"] .pr-12 { padding-right: 1rem; padding-left: 3rem; }

        #submitBtn:disabled { background-color: #e2e8f0; color: #94a3b8; cursor: not-allowed; }
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

    <div class="auth-card bg-white p-10 w-full max-w-[480px] text-center">
        
        <a href="verify_code.php" class="back-arrow" title="<?php echo $L['back_tip']; ?>">
            <i data-lucide="arrow-left" class="w-8 h-8"></i>
        </a>

        <div class="mb-3">
            <h1 class="text-5xl font-bold logo-font tracking-tight">
                <span class="text-[#ed4b9e]">Glow</span> <span class="text-[#22c55e]">Well</span>
            </h1>
        </div>
        <p class="text-xl text-gray-800 font-extrabold mb-10"><?php echo $L['header']; ?></p>

        <?php if ($success_msg): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-2xl mb-6 text-sm font-bold border border-green-100 animate-pulse">
                🎉 <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="resetForm" class="space-y-6 text-left">
            <div>
                <label class="block text-gray-700 font-bold mb-4 text-mid uppercase tracking-widest <?php echo ($_SESSION['lang'] == 'ar' ? 'text-right' : ''); ?>">
                    <?php echo $L['label_pass']; ?>
                </label>
                <div class="input-group rounded-2xl">
                    <input type="password" name="password" id="pass1" class="w-full p-4 bg-transparent outline-none pr-12" placeholder="••••••••" required>
                    <button type="button" class="eye-btn" onclick="toggle('pass1', 'svg1')">
                        <svg id="svg1" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-bold mb-4 text-mid uppercase tracking-widest <?php echo ($_SESSION['lang'] == 'ar' ? 'text-right' : ''); ?>">
                    <?php echo $L['label_confirm']; ?>
                </label>
                <div class="input-group rounded-2xl">
                    <input type="password" id="pass2" class="w-full p-4 bg-transparent outline-none pr-12" placeholder="••••••••" required>
                    <button type="button" class="eye-btn" onclick="toggle('pass2', 'svg2')">
                        <svg id="svg2" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                <div id="vText" class="mt-3 text-sm font-semibold text-gray-400 transition-all duration-300 <?php echo ($_SESSION['lang'] == 'ar' ? 'text-right' : ''); ?>">
                    <?php echo $L['req_text']; ?>
                </div>
            </div>

            <button type="submit" id="submitBtn" disabled class="w-full py-4 bg-[#ed4b9e] text-white rounded-[25px] font-bold text-xl shadow-lg transition-all active:scale-95">
                <?php echo $L['update_btn']; ?>
            </button>
        </form>
    </div>

    <script>
        lucide.createIcons();

        const p1 = document.getElementById('pass1');
        const p2 = document.getElementById('pass2');
        const vText = document.getElementById('vText');
        const btn = document.getElementById('submitBtn');

        // نصوص التحقق المترجمة
        const jsL = {
            match: "<?php echo $L['js_match']; ?>",
            weak: "<?php echo $L['js_weak']; ?>",
            noMatch: "<?php echo $L['js_no_match']; ?>",
            original: "<?php echo $L['req_text']; ?>"
        };

        function toggle(id, svgId) {
            const input = document.getElementById(id);
            const svg = document.getElementById(svgId);
            if (input.type === 'password') {
                input.type = 'text';
                svg.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                svg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }

        function validate() {
            const v1 = p1.value;
            const v2 = p2.value;

            const isStrong = v1.length >= 8 && /[A-Za-z]/.test(v1) && /[0-9]/.test(v1);
            const isMatch = (v1 === v2 && v1 !== "");

            if (v1 === "" && v2 === "") {
                vText.className = "mt-3 text-sm font-semibold text-gray-400 <?php echo ($_SESSION['lang'] == 'ar' ? 'text-right' : ''); ?>";
                vText.innerText = jsL.original;
            } else if (isStrong && isMatch) {
                vText.className = "mt-3 text-sm font-semibold text-green-500 <?php echo ($_SESSION['lang'] == 'ar' ? 'text-right' : ''); ?>";
                vText.innerText = jsL.match;
                btn.disabled = false;
                btn.style.backgroundColor = "#ed4b9e";
            } else {
                vText.className = "mt-3 text-sm font-semibold text-red-500 <?php echo ($_SESSION['lang'] == 'ar' ? 'text-right' : ''); ?>";
                btn.disabled = true;
                btn.style.backgroundColor = "#e2e8f0";
                if (!isStrong) vText.innerText = jsL.weak;
                else vText.innerText = jsL.noMatch;
            }
        }

        p1.addEventListener('input', validate);
        p2.addEventListener('input', validate);
    </script>
</body>
</html>
<?php
// 1. تفعيل الجلسة وإظهار الأخطاء
include 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- نظام الترجمة الاحترافي ---
if (!isset($_SESSION['lang'])) { $_SESSION['lang'] = 'en'; }
if (isset($_GET['tplang'])) {
    $_SESSION['lang'] = ($_SESSION['lang'] == 'en') ? 'ar' : 'en';
    header("Location: signup.php");
    exit;
}

$translations = [
    'en' => [
        'title' => 'Sign Up - GlowWell',
        'header' => 'Create Account',
        'email_label' => 'Email Address',
        'pass_label' => 'Password',
        'conf_label' => 'Confirm Password',
        'v_text_init' => 'Password: 8+ characters, letters & numbers',
        'v_text_ok' => '✔ Passwords match and meet requirements',
        'v_text_weak' => '✖ Need 8+ chars with letters & numbers',
        'v_text_no_match' => '✖ Passwords do not match',
        'signup_btn' => 'Sign Up',
        'google_btn' => 'Sign up with Google',
        'have_account' => 'Already have an account?',
        'login_link' => 'Log in',
        'err_match' => 'Passwords do not match!',
        'err_exists' => 'Email already registered!',
        'err_db' => 'Database Error: '
    ],
    'ar' => [
        'title' => 'إنشاء حساب - GlowWell',
        'header' => 'إنشاء حساب جديد',
        'email_label' => 'البريد الإلكتروني',
        'pass_label' => 'كلمة المرور',
        'conf_label' => 'تأكيد كلمة المرور',
        'v_text_init' => 'كلمة المرور: 8 رموز فأكثر، حروف وأرقام',
        'v_text_ok' => '✔ كلمات المرور متطابقة وتستوفي الشروط',
        'v_text_weak' => '✖ يجب أن تكون 8 رموز وتتضمن حروفاً وأرقاماً',
        'v_text_no_match' => '✖ كلمات المرور غير متطابقة',
        'signup_btn' => 'إنشاء الحساب',
        'google_btn' => 'تسجيل بواسطة جوجل',
        'have_account' => 'لديك حساب بالفعل؟',
        'login_link' => 'تسجيل الدخول',
        'err_match' => 'كلمات المرور غير متطابقة!',
        'err_exists' => 'البريد الإلكتروني مسجل مسبقاً!',
        'err_db' => 'خطأ في قاعدة البيانات: '
    ]
];
$L = $translations[$_SESSION['lang']];
// -----------------------------

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, trim(strtolower($_POST['email'])));
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];

    if ($password !== $repassword) {
        $error_msg = $L['err_match'];
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error_msg = $L['err_exists'];
        } else {
            $sql = "INSERT INTO users (email, password) VALUES ('$email', '$hashed_password')";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['user_id'] = mysqli_insert_id($conn);
                echo "<script>window.location.href='setup_profile.php';</script>";
                exit();
            } else {
                $error_msg = $L['err_db'] . mysqli_error($conn);
            }
        }
    }
}
?>
<!doctype html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo ($_SESSION['lang'] == 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
    <title><?php echo $L['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); }
        .signup-card { border-radius: 50px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        .input-box { background-color: #f3f4f6; transition: 0.3s; border: 2px solid transparent; position: relative; }
        .input-box:focus-within { background-color: #fff; border-color: #fbcfe8; }
        .eye-btn { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af; cursor: pointer; }
        #submitBtn:disabled { background-color: #e2e8f0; color: #94a3b8; cursor: not-allowed; }
        
        /* تحسينات RTL */
        [dir="rtl"] .text-left { text-align: right; }
        [dir="rtl"] .eye-btn { right: auto; left: 15px; }
        [dir="rtl"] .pr-12 { padding-right: 1rem; padding-left: 3rem; }
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

    <div class="signup-card bg-white p-10 w-full max-w-[480px] text-center border border-gray-100">
        <div class="mb-4">
            <h1 class="text-5xl font-bold tracking-tight">
                <span style="color: #ed4b9e;">Glow</span> <span style="color: #22c55e;">Well</span>
            </h1>
        </div>
        <h1 class="text-xl font-semibold mb-6 text-gray-700"><?php echo $L['header']; ?></h1>
        
        <?php if($error_msg): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-2xl mb-6 text-sm font-bold border border-red-100 text-left">
                ⚠️ <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="signupForm" class="space-y-5 text-left">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?php echo $L['email_label']; ?></label>
                <input type="email" name="email" id="email" class="w-full p-4 input-box rounded-2xl outline-none" placeholder="example@mail.com" required>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?php echo $L['pass_label']; ?></label>
                <div class="input-box rounded-2xl">
                    <input type="password" name="password" id="pass1" class="w-full p-4 bg-transparent outline-none pr-12" placeholder="••••••••" required>
                    <button type="button" class="eye-btn" onclick="toggle('pass1', 'svg1')">
                        <svg id="svg1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2"><?php echo $L['conf_label']; ?></label>
                <div class="input-box rounded-2xl">
                    <input type="password" name="repassword" id="pass2" class="w-full p-4 bg-transparent outline-none pr-12" placeholder="••••••••" required>
                    <button type="button" class="eye-btn" onclick="toggle('pass2', 'svg2')">
                        <svg id="svg2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                <div id="vText" class="mt-3 text-sm font-semibold text-gray-400">
                    <?php echo $L['v_text_init']; ?>
                </div>
            </div>

            <button type="submit" id="submitBtn" disabled class="w-full py-4 bg-[#EC4899] text-white rounded-2xl font-bold text-xl shadow-lg transition-all active:scale-95">
                <?php echo $L['signup_btn']; ?>
            </button>

            <button type="button" onclick="handleGoogle()" class="w-full py-4 rounded-2xl font-bold text-xl flex items-center justify-center gap-3 shadow-md active:scale-95 transition-all bg-white border border-gray-200 text-gray-700">
                <svg width="22" height="22" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.66l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span><?php echo $L['google_btn']; ?></span>
            </button>
        </form>

        <p class="mt-8 text-gray-600 font-semibold">
            <?php echo $L['have_account']; ?> <a href="login.php" class="text-pink-500 hover:underline"><?php echo $L['login_link']; ?></a>
        </p>
    </div>

    <script>
        lucide.createIcons();
        const email = document.getElementById('email');
        const p1 = document.getElementById('pass1');
        const p2 = document.getElementById('pass2');
        const vText = document.getElementById('vText');
        const btn = document.getElementById('submitBtn');

        // نصوص الجافاسكريبت المترجمة
        const jsL = {
            ok: "<?php echo $L['v_text_ok']; ?>",
            weak: "<?php echo $L['v_text_weak']; ?>",
            noMatch: "<?php echo $L['v_text_no_match']; ?>"
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
            const eVal = email.value.trim();
            const v1 = p1.value;
            const v2 = p2.value;
            const isStrong = v1.length >= 8 && /[A-Za-z]/.test(v1) && /[0-9]/.test(v1);
            const isMatch = (v1 === v2 && v1 !== "");
            const isEmailOk = eVal.includes('@') && eVal.includes('.');

            if (isStrong && isMatch) {
                vText.className = "mt-3 text-sm font-semibold text-green-500";
                vText.innerText = jsL.ok;
            } else if (v1 !== "" || v2 !== "") {
                vText.className = "mt-3 text-sm font-semibold text-red-500";
                vText.innerText = !isStrong ? jsL.weak : jsL.noMatch;
            }

            btn.disabled = !(isStrong && isMatch && isEmailOk);
        }

        [email, p1, p2].forEach(el => el.addEventListener('input', validate));

        function handleGoogle() {
            fetch('google-auth-url.php')
                .then(response => response.json())
                .then(data => { if (data.url) window.location.href = data.url; })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
<?php
/**
 * GlowWell - Login Page
 */
require_once 'config.php';

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}

// --- نظام الترجمة الاحترافي (بدون جوجل) ---
if (!isset($_SESSION['lang'])) { $_SESSION['lang'] = 'en'; }
if (isset($_GET['tplang'])) {
    $_SESSION['lang'] = ($_SESSION['lang'] == 'en') ? 'ar' : 'en';
    header("Location: login.php");
    exit;
}

$translations = [
    'en' => [
        'title' => 'Login - GlowWell',
        'welcome' => 'Welcome back! Please login.',
        'email_label' => 'Email Address',
        'pass_label' => 'Password',
        'forgot' => 'Forgot password?',
        'remember' => 'Remember me',
        'login_btn' => 'Log In',
        'google_btn' => 'Log in with Google',
        'no_account' => "Don't have an account?",
        'signup' => 'Sign up'
    ],
    'ar' => [
        'title' => 'تسجيل الدخول - GlowWell',
        'welcome' => 'أهلاً بك مجدداً! يرجى تسجيل الدخول.',
        'email_label' => 'البريد الإلكتروني',
        'pass_label' => 'كلمة المرور',
        'forgot' => 'نسيت كلمة المرور؟',
        'remember' => 'تذكرني',
        'login_btn' => 'تسجيل الدخول',
        'google_btn' => 'الدخول بواسطة جوجل',
        'no_account' => 'ليس لديك حساب؟',
        'signup' => 'سجل الآن'
    ]
];
$L = $translations[$_SESSION['lang']];
// ---------------------------------------

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email']));
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, email, password, fullname, role FROM users WHERE LOWER(TRIM(email)) = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            if (empty(trim($user['password']))) {
                $error_message = ($_SESSION['lang'] == 'ar') ? "هذا الحساب مرتبط بجوجل. يرجى تسجيل الدخول عبر زر جوجل." : "This account is linked with Google. Please use the 'Log in with Google' button.";
            } 
            elseif (password_verify($password, trim($user['password']))) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role']; 

                // ==========================================
                // كود حفظ توكن الإشعارات بعد نجاح تسجيل الدخول
                // ==========================================
                if (isset($_POST['fcm_token']) && !empty($_POST['fcm_token'])) {
                    $token = mysqli_real_escape_string($conn, $_POST['fcm_token']);
                    $logged_in_user_id = $user['id'];
                    $sql_token = "INSERT INTO user_devices (user_id, fcm_token, last_updated) 
                                  VALUES ('$logged_in_user_id', '$token', NOW()) 
                                  ON DUPLICATE KEY UPDATE fcm_token = '$token', last_updated = NOW()";
                    mysqli_query($conn, $sql_token);
                }
                // ==========================================

                if (empty(trim($user['fullname']))) {
                    header("Location: setup_profile.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error_message = ($_SESSION['lang'] == 'ar') ? "كلمة المرور غير صحيحة." : "Invalid password. Please try again.";
            }
        } else {
            $error_message = ($_SESSION['lang'] == 'ar') ? "البريد الإلكتروني غير مسجل." : "Email not found. Please sign up first.";
        }
    } else {
        $error_message = ($_SESSION['lang'] == 'ar') ? "يرجى ملء جميع الحقول." : "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo ($_SESSION['lang'] == 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="google-site-verification" content="4dTnDTgjrojMCWcHOrFIo0KfdwGl_-rFe9niSKmYO3k" />
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
        .login-card { border-radius: 50px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        .input-box { background-color: #f3f4f6; transition: 0.3s; border: 2px solid transparent; outline: none; }
        .input-box:focus { background-color: #fff; border-color: #fbcfe8; }
        .btn-pink { background-color: #ed4b9e; color: white; transition: 0.3s; }
        .btn-pink:hover { background-color: #d83a8a; transform: translateY(-1px); }
        .btn-google { background-color: #2ecc71; color: white; transition: 0.3s; }
        .btn-google:hover { background-color: #27ae60; transform: translateY(-1px); }
        .red-link { color: #ef4444; font-weight: 700; transition: 0.2s; }
        .red-link:hover { color: #dc2626; text-decoration: underline; }
        
        /* تحسينات الـ RTL للـ Icons والـ Inputs */
        [dir="rtl"] .text-left { text-align: right; }
        [dir="rtl"] .text-right { text-align: left; }
        [dir="rtl"] .pr-12 { padding-right: 1rem; padding-left: 3rem; }
        [dir="rtl"] .absolute.right-5 { right: auto; left: 1.25rem; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="fixed bottom-6 right-6 z-50">
        <a href="?tplang=1" class="bg-white p-4 rounded-full shadow-2xl border border-gray-100 hover:scale-110 active:scale-95 transition-all flex items-center justify-center group">
            <i data-lucide="globe" class="w-6 h-6 text-[#ed4b9e]"></i>
            <span class="max-w-0 overflow-hidden group-hover:max-w-xs group-hover:ms-2 transition-all duration-300 font-bold text-gray-700">
                <?php echo ($_SESSION['lang'] == 'en' ? 'العربية' : 'English'); ?>
            </span>
        </a>
    </div>

    <div class="login-card bg-white p-12 w-full max-w-[480px] text-center">
        <div class="mb-4">
            <h1 class="text-5xl font-bold logo-font tracking-tight">
                <span style="color: #ed4b9e;">Glow</span> <span style="color: #2ecc71;">Well</span>
            </h1>
        </div>
        <p class="text-gray-800 font-semibold mb-10"><?php echo $L['welcome']; ?></p>

        <?php if ($error_message): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-2xl mb-6 text-sm font-bold border border-red-100">
                ⚠️ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form id="login-form" method="POST" class="space-y-6 text-left">
            <div>
                <label class="block text-gray-700 font-bold mb-2"><?php echo $L['email_label']; ?></label>
                <input type="email" name="email" autocomplete="username"
 class="w-full p-4 input-box rounded-2xl" placeholder="example@mail.com" required>
            </div>

            <div>
                <label class="block text-gray-700 font-bold mb-2"><?php echo $L['pass_label']; ?></label>
                <div class="relative flex items-center input-box rounded-2xl">
                    <input type="password" name="password" id="loginPass" autocomplete="current-password"
 class="w-full p-4 bg-transparent outline-none pr-12" placeholder="••••••••" required>
                    <button type="button" onclick="toggle('loginPass', 'eyeIcon')" class="absolute right-5 focus:outline-none">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <a href="forgot_password.php" class="text-[#ef4444] text-sm text-right w-full font-bold hover:underline block mt-2"><?php echo $L['forgot']; ?></a>
            </div> 

            <div class="flex items-center gap-3">
                <input type="checkbox" id="remember" name="remember" class="w-5 h-5 rounded border-gray-300 text-red-500 focus:ring-red-500 cursor-pointer">
                <label for="remember" class="text-gray-600 font-semibold text-base cursor-pointer"><?php echo $L['remember']; ?></label>
            </div>
        
            <div class="pt-4 space-y-4">
                <input type="hidden" name="fcm_token" id="fcm_token" value="">
                <button type="submit" class="w-full py-4 btn-pink rounded-2xl font-bold text-xl shadow-md active:scale-95 transition-all">
                    <?php echo $L['login_btn']; ?>
                </button>
                <button type="button" onclick="handleGoogle()" class="w-full py-4 btn-google rounded-2xl font-bold text-xl flex items-center justify-center gap-3 shadow-md active:scale-95 transition-all bg-white border border-gray-200 hover:bg-gray-50 text-gray-700">
                    <svg width="22" height="22" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.66l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span><?php echo $L['google_btn']; ?></span>
                </button>
            </div>
        </form>

        <div class="mt-8 text-gray-600 font-semibold">
            <?php echo $L['no_account']; ?> <a href="signup.php" class="red-link"><?php echo $L['signup']; ?></a>
             <span class="mx-2"> </span>
                <a href="privacy.php" class="text-xs text-gray-400 hover:underline"><?php echo ($_SESSION['lang'] == 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy'); ?></a>
        </div>
        
        
        <div class="w-full text-center mt-12 pb-10">
    <p class="text-gray-400 text-[10px] md:text-xs font-bold tracking-[0.2em] uppercase leading-relaxed">
        &copy; 2026 
        <span style="color: #ed4b9e;">Glow</span><span style="color: #2ecc71;">Well</span>. 
        <span class="block md:inline mt-1 md:mt-0">
            <?php echo ($_SESSION['lang'] == 'ar' ? 'جميع الحقوق محفوظة لرحلتك' : 'All Rights Reserved to Your Journey'); ?>
        </span>
    </p>
</div>

    </div>

    <script>
        lucide.createIcons();
        function toggle(id, svgId) {
            const input = document.getElementById(id);
            const svg = document.getElementById(svgId);
            if (input.type === 'password') {
                input.type = 'text';
                svg.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                svg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                svg.style.color = '#9ca3af';
            }
        }

        function handleGoogle() {
            fetch('google-auth-url.php')
                .then(response => response.json())
                .then(data => {
                    if (data.url) {
                        window.location.href = data.url; 
                    }
                })
                .catch(error => console.error('Error fetching Google URL:', error));
        }
    </script>
    
<script src="fb-app.js"></script>
<script src="fb-msg.js"></script>

<script>
    // إعدادات فايربيس الخاصة بمشروعك
    const firebaseConfig = {
        apiKey: "AIzaSyCI4EA4ZdYMeMNwtOfyFIHrk2bHbdKHYcs",
        projectId: "glowwell-ac819",
        messagingSenderId: "658820604328",
        appId: "1:658820604328:web:c680a36e6af611e2b4fd9d"
    };

    // تشغيل النظام
    if (typeof firebase !== 'undefined') {
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        const loginForm = document.getElementById('login-form');

        loginForm.onsubmit = async function(e) {
            e.preventDefault(); 
            
            try {
                // طلب إذن الإشعارات
                const permission = await Notification.requestPermission();
                
                if (permission === 'granted') {
                    // جلب التوكن الفريد لجهازك
                    const token = await messaging.getToken();
                    if (token) {
                        // وضع التوكن في الحقل المخفي لإرساله للـ PHP
                        document.getElementById('fcm_token').value = token;
                    }
                }
            } catch (error) {
                console.error("Firebase Local Error:", error);
            } finally {
                // إرسال الفورم وقاعدة البيانات بتستلم التوكن الآن
                loginForm.submit();
            }
        };
    } else {
        console.error("فشل تحميل ملفات الجافا سكريبت المحلية. تأكدي من المسار.");
    }
</script>

</body>
</html>
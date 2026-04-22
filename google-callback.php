
<?php
require_once 'config.php';

// 1. التأكد من وجود كود التحقق من جوجل
if (!isset($_GET['code'])) {
    header("Location: login.php?error=no_code");
    exit;
}

// 2. تبادل الكود بـ Access Token
$token_url = "https://oauth2.googleapis.com/token";
$post_params = [
    'code'          => $_GET['code'],
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code'
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_params));
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['access_token'])) {
    die("خطأ في الاتصال بجوجل: " . ($data['error_description'] ?? 'Unknown Error'));
}

// 3. جلب بيانات المستخدم (الإيميل والاسم)
$ch = curl_init("https://www.googleapis.com/oauth2/v2/userinfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $data['access_token']
]);
$user_response = curl_exec($ch);
curl_close($ch);

$user_info = json_decode($user_response, true);

if (!isset($user_info['email'])) {
    die("لم يتم استلام البريد الإلكتروني من جوجل.");
}

$email = $user_info['email'];
$name  = $user_info['name'] ?? 'Guest User'; // اسم احتياطي في حال لم يتوفر

// 4. فحص قاعدة البيانات (هل المستخدم مسجل مسبقاً؟)
$stmt = $pdo->prepare("SELECT id, fullname FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // --- السيناريو الأول: المستخدم موجود مسبقاً ---
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email']   = $email;
    $_SESSION['name']    = $user['fullname'];

    header("Location: dashboard.php"); // توجيه للوحة التحكم
    exit;

} else {
    // --- السيناريو الثاني: مستخدم جديد ---
    try {
        $insert = $pdo->prepare("INSERT INTO users (email, fullname, created_at) VALUES (?, ?, NOW())");
        $insert->execute([$email, $name]);

        // تخزين بيانات الجلسة للمستخدم الجديد
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['email']   = $email;
        $_SESSION['name']    = $name;

        header("Location: setup_profile.php"); // توجيه لصفحة إكمال الملف الشخصي
        exit;
    } catch (PDOException $e) {
        die("خطأ أثناء تسجيل المستخدم الجديد: " . $e->getMessage());
    }
}


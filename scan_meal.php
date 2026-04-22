<?php
/**
 * GlowWell - Meal Scan Page
 */
include 'config.php';
require_once 'auth_check.php';

// تحديد رابط سيرفر Flask
$cv_base = rtrim(getenv('GLOWWELL_CV_URL') ?: 'http://127.0.0.1:5001', '/');
$cv_scan_url = $cv_base . '/';
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell — مسح الوجبة (AI)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { margin: 0; background: #FCEEF4; font-family: system-ui, sans-serif; }
        .frame-wrap { max-width: 1100px; margin: 0 auto; padding: 1rem; }
        iframe { width: 100%; min-height: 80vh; border: 0; border-radius: 20px; background: #fff; box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="frame-wrap">
    <div class="text-center mb-4 mt-4">
        <a href="<?php echo htmlspecialchars($cv_scan_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="bg-pink-600 text-white px-6 py-2 rounded-full text-sm hover:bg-pink-700 transition inline-block">
            فتح الماسح في شاشة كاملة
        </a>
    </div>
    <iframe title="Meal Scanner" src="<?php echo htmlspecialchars($cv_scan_url, ENT_QUOTES, 'UTF-8'); ?>"></iframe>
</div>
</body>
</html>
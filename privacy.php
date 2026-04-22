<?php
include 'config.php';
$lang = $_SESSION['lang'] ?? 'en';
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

$texts = [
    'en' => [
        'title' => 'Privacy Policy',
        'intro' => 'At GlowWell, we care about your privacy and the security of your data.',
        'q1' => '1. What data do we collect?',
        'a1' => 'We collect your name and email when you register via Google or our form to personalize your fitness experience.',
        'q2' => '2. How do we use your data?',
        'a2' => 'Your data is used only to track your calories, workouts, and progress within the app.',
        'q3' => '3. Data Security',
        'a3' => 'We use SSL encryption to ensure your information is protected at all times.',
        'back' => 'Back to Login'
    ],
    'ar' => [
        'title' => 'سياسة الخصوصية',
        'intro' => 'في GlowWell، نحن نهتم بخصوصيتك وأمن بياناتك.',
        'q1' => '١. ما هي البيانات التي نجمعها؟',
        'a1' => 'نجمع اسمك وبريدك الإلكتروني عند التسجيل عبر جوجل أو النموذج الخاص بنا لتخصيص تجربتك الرياضية.',
        'q2' => '٢. كيف نستخدم بياناتك؟',
        'a2' => 'تُستخدم بياناتك فقط لتتبع سعراتك الحرارية، تمارينك، وتقدمك داخل التطبيق.',
        'q3' => '٣. أمن البيانات',
        'a3' => 'نستخدم تشفير SSL لضمان حماية معلوماتك في جميع الأوقات.',
        'back' => 'العودة لتسجيل الدخول'
    ]
];
$t = $texts[$lang];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> - GlowWell</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.9); border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="glass p-10 max-w-2xl w-full">
        <h1 class="text-3xl font-bold mb-6" style="color: #ed4b9e;"><?php echo $t['title']; ?></h1>
        <p class="text-gray-600 mb-8"><?php echo $t['intro']; ?></p>
        
        <div class="space-y-6 text-left <?php echo ($lang == 'ar' ? 'text-right' : ''); ?>">
            <div>
                <h3 class="font-bold text-gray-800"><?php echo $t['q1']; ?></h3>
                <p class="text-gray-600"><?php echo $t['a1']; ?></p>
            </div>
            <div>
                <h3 class="font-bold text-gray-800"><?php echo $t['q2']; ?></h3>
                <p class="text-gray-600"><?php echo $t['a2']; ?></p>
            </div>
            <div>
                <h3 class="font-bold text-gray-800"><?php echo $t['q3']; ?></h3>
                <p class="text-gray-600"><?php echo $t['a3']; ?></p>
            </div>
        </div>
        
        <div class="mt-10 pt-6 border-t border-gray-100">
            <a href="login.php" class="text-[#2ecc71] font-bold hover:underline"><?php echo $t['back']; ?></a>
        </div>
    </div>
</body>
</html>

<?php
// تضمين ملف الإعدادات واللغة
include 'config.php';

// تحديد اللغة والاتجاه
$current_lang = $_SESSION['lang'] ?? (isset($_GET['lang']) ? $_GET['lang'] : 'en');
$dir = ($current_lang == 'ar') ? 'rtl' : 'ltr';

// مصفوفة الترجمة الخاصة بصفحة About Us
$about_tr = [
    'en' => [
        'title' => 'Behind Every Glow, There’s a Story',
        'subtitle' => "GlowWell wasn't just built with code; it was born from a dream.",
        'sec1_title' => 'The Spark: A Quiet Revelation',
        'sec1_text' => 'Our journey began on a quiet night when we asked ourselves: "Why does living a healthy life feel so complicated?" Between cluttered calorie trackers and confusing workout plans, we felt a gap that stopped people from truly "glowing." That was the moment GlowWell was sparked—a vision to create a space that combines simplicity, beauty, and precision.',
        'sec2_title' => 'The Hustle: Nights of Passion',
        'sec2_text' => "The road wasn't easy. We spent months obsessing over every pixel, choosing the perfect emojis, and fine-tuning algorithms to ensure that every click on GlowWell feels like an achievement. We weren't just building a tracker; we were building a companion that understands your goals and celebrates every pound you lose or muscle you gain.",
        'sec3_title' => 'The Future: We Are Just Getting Started',
        'sec3_text' => 'What you see today is only the foundation. At GlowWell, we believe in constant evolution. We are working on a future where **Artificial Intelligence** analyzes your meals instantly, personalized workout paths adapt to your body, and a vibrant community of "Glowers" supports you every step of the way. Our journey with you has just begun.',
        'connect' => 'Connect with GlowWell Team ✨',
        'email_us' => 'Email Us',
        'follow_us' => 'Follow Us',
        'call_us' => 'Call Us',
        'footer' => '&copy; 2026 GLOWWELL. ALL RIGHTS RESERVED TO YOUR JOURNEY.'
    ],
    'ar' => [
        'title' => 'خلف كل توهج، هناك قصة',
        'subtitle' => 'لم يُبنَ GlowWell بمجرد أكواد برمجية؛ بل وُلد من حلم.',
        'sec1_title' => 'الشرارة: إلهام هادئ',
        'sec1_text' => 'بدأت رحلتنا في ليلة هادئة عندما سألنا أنفسنا: "لماذا تبدو الحياة الصحية معقدة للغاية؟" بين تطبيقات تتبع السعرات المزدحمة وخطط التمارين المربكة، شعرنا بفجوة تمنع الناس من "التوهج" الحقيقي. كانت تلك اللحظة التي انطلق فيها GlowWell—رؤية لإنشاء مساحة تجمع بين البساطة، الجمال، والدقة.',
        'sec2_title' => 'الكفاح: ليالٍ من الشغف',
        'sec2_text' => 'الطريق لم يكن سهلاً. قضينا شهوراً نهتم بكل بكسل، ونختار الرموز التعبيرية المثالية، ونضبط الخوارزميات لضمان أن كل نقرة في GlowWell تشعرك بالإنجاز. لم نكن نبني مجرد تطبيق تتبع؛ كنا نبني رفيقاً يفهم أهدافك ويحتفل بكل كيلوغرام تخسره أو عضلة تبنيها.',
        'sec3_title' => 'المستقبل: نحن فقط في البداية',
        'sec3_text' => 'ما تراه اليوم هو مجرد حجر الأساس. في GlowWell، نؤمن بالتطور المستمر. نحن نعمل على مستقبل يحلل فيه **الذكاء الاصطناعي** وجباتك فوراً، وتتكيف مسارات التمارين المخصصة مع جسمك، ويدعمك مجتمع حيوي من "المتوهجين" في كل خطوة. رحلتنا معك بدأت للتو.',
        'connect' => 'تواصل مع فريق GlowWell ✨',
        'email_us' => 'راسلنا بريدياً',
        'follow_us' => 'تابعنا',
        'call_us' => 'اتصل بنا',
        'footer' => '&copy; 2026 GLOWWELL. جميع الحقوق محفوظة لرحلتك.'
    ]
];

$t = $about_tr[$current_lang];
?>
<!doctype html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($current_lang == 'ar' ? 'من نحن' : 'About Us'); ?> - GlowWell</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #FCEEF4; margin: 0; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-radius: 40px; border: 1px solid rgba(255, 255, 255, 0.3); padding: 60px; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
        .gradient-text { background: linear-gradient(90deg, #EC4D9C, #2AC66A); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; }
        .contact-item { background: white; padding: 25px; border-radius: 24px; transition: all 0.3s; border: 1px solid #FCE7F3; text-align: center; }
        .contact-item:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(236, 77, 156, 0.15); }
        
        /* تحويل ذكي للحد الجانبي بناءً على الاتجاه */
        .story-section { 
            position: relative; 
            padding-inline-start: 24px; 
            border-inline-start: 4px solid #EC4D9C; 
            margin-bottom: 48px; 
        }
        .story-section.green { border-inline-start-color: #2AC66A; }
    </style>
</head>
<body class="min-h-screen">

    <?php include 'navbar.php'; ?>

    <main class="max-w-5xl mx-auto px-8 py-20">
        <div class="glass-card">
            <div class="text-center mb-20">
                <h1 class="text-6xl font-extrabold mb-6 gradient-text"><?php echo $t['title']; ?></h1>
                <p class="text-gray-500 text-xl font-medium"><?php echo $t['subtitle']; ?></p>
            </div>

            <div class="space-y-12 text-start leading-relaxed text-gray-700">
                
                <div class="story-section">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4 italic"><?php echo $t['sec1_title']; ?></h3>
                    <p class="text-lg">
                        <?php echo $t['sec1_text']; ?>
                    </p>
                </div>

                <div class="story-section green">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4 italic"><?php echo $t['sec2_title']; ?></h3>
                    <p class="text-lg">
                        <?php echo $t['sec2_text']; ?>
                    </p>
                </div>

                <div class="story-section">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4 italic"><?php echo $t['sec3_title']; ?></h3>
                    <p class="text-lg">
                        <?php echo $t['sec3_text']; ?>
                    </p>
                </div>
            </div>

            <div class="mt-28 pt-16 border-t border-pink-100">
                <h2 class="text-4xl font-bold text-center text-gray-800 mb-14 italic"><?php echo $t['connect']; ?></h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="contact-item">
                        <div class="text-4xl mb-4">📧</div>
                        <h4 class="font-bold text-gray-800 text-lg"><?php echo $t['email_us']; ?></h4>
                        <p class="text-pink-500 font-medium mt-2">glowwell.support@gmail.com</p>
                    </div>

                    <div class="contact-item">
                        <div class="text-4xl mb-4">📸</div>
                        <h4 class="font-bold text-gray-800 text-lg"><?php echo $t['follow_us']; ?></h4>
                        <p class="text-pink-500 font-medium mt-2">@GlowWell_Official</p>
                    </div>

                    <div class="contact-item">
                        <div class="text-4xl mb-4">📞</div>
                        <h4 class="font-bold text-gray-800 text-lg"><?php echo $t['call_us']; ?></h4>
                        <p class="text-pink-500 font-medium mt-2">+966 500 000 000</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center py-12 text-gray-400 text-sm font-medium tracking-wide">
        <?php echo $t['footer']; ?>
    </footer>

</body>
</html>
<?php
include 'config.php';

// التأكد من جلب ملف اللغة لتحديد اتجاه الصفحة والنصوص الثابتة 
// (نفترض أنه يتم تضمين مصفوفة $lang قبل أو داخل config.php، أو يمكنك إضافتها هنا)
$current_lang = $_SESSION['lang'] ?? (isset($_GET['lang']) ? $_GET['lang'] : 'en');
$dir = isset($lang['dir']) ? $lang['dir'] : ($current_lang == 'ar' ? 'rtl' : 'ltr');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$user_res = mysqli_query($conn, "SELECT health_goal FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_res);
$goal = $user['health_goal'] ?? 'General';

// جلب النصائح الخاصة بالهدف + النصائح العامة
$tips_query = mysqli_query($conn, "SELECT * FROM tips WHERE category = '$goal' OR category = 'General' ORDER BY id DESC");

// 1. مصفوفة لترجمة العناوين القادمة من قاعدة البيانات تماماً كما طلبت
$db_titles_ar = [
    'Stay Hydrated' => 'ابقَ رطباً',
    'High Protein Breakfast' => 'إفطار عالي البروتين',
    'Consistency is Key' => 'الاستمرارية هي المفتاح',
    'Sleep for Recovery' => 'النوم للتعافي',
    'Mindful Eating' => 'الأكل بوعي',
    'Fiber is Your Friend' => 'الألياف هي صديقتك'
];

// 2. مصفوفة لترجمة المحتوى (الـ Category/Content) القادم من قاعدة البيانات
$db_content_ar = [
    'Drinking water before meals can help reduce appetite' => 'شرب الماء قبل الوجبات يمكن أن يساعد في تقليل الشهية',
    'Starting your day with protein helps maintain muscle' => 'بدء يومك بالبروتين يساعد في الحفاظ على العضلات',
    'Small daily improvements are the key to long-term success' => 'التحسينات اليومية الصغيرة هي مفتاح النجاح على المدى الطويل',
    'Muscles grow while you sleep, not just when you work out' => 'تنمو العضلات أثناء النوم، وليس فقط عند ممارسة الرياضة',
    'Eat slowly and avoid distractions to better recognize fullness' => 'كل ببطء وتجنب المشتتات للتعرف بشكل أفضل على الشبع',
    'High-fiber foods keep you satisfied and improve your health' => 'الأطعمة الغنية بالألياف تبقيك راضياً وتحسن صحتك'
];

// 3. ترجمة الهدف الصحي ليتناسب مع العرض في الصفحة
$goal_ar = [
    'Lose Weight' => 'خسارة الوزن',
    'Build Muscle' => 'بناء العضلات',
    'General' => 'اللياقة العامة'
];
$display_goal = ($current_lang == 'ar' && isset($goal_ar[$goal])) ? $goal_ar[$goal] : $goal;
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <title>GlowWell | Health Tips ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            /* تدرج لوني عميق ليعطي إيحاء بالليل الوردي */
            background: linear-gradient(135deg, #fceef4 0%, #f7c5d9 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* تنسيق النجوم */
        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            filter: drop-shadow(0 0 5px white);
            animation: twinkle var(--duration) infinite ease-in-out;
            opacity: 0;
            z-index: 0;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.2; transform: scale(0.8); }
            50% { opacity: 0.9; transform: scale(1.2); }
        }

        /* كرت النصيحة بشكل Glassmorphism */
        .tip-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            z-index: 10;
            position: relative;
        }

        .header-section {
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body class="pb-12">

    <?php for($i=0; $i<50; $i++): 
        $size = rand(1, 4);
        $top = rand(0, 100);
        $left = rand(0, 100);
        $duration = rand(3, 6);
        $delay = rand(0, 5);
    ?>
        <div class="star" style="
            width: <?php echo $size; ?>px; 
            height: <?php echo $size; ?>px; 
            top: <?php echo $top; ?>%; 
            left: <?php echo $left; ?>%; 
            --duration: <?php echo $duration; ?>s; 
            animation-delay: <?php echo $delay; ?>s;
        "></div>
    <?php endfor; ?>

    <?php include 'navbar.php'; ?>

    <div class="max-w-6xl mx-auto px-6 mt-10">
        <div class="header-section text-center mb-12">
            <h1 class="text-5xl font-extrabold text-gray-800 tracking-tight">
                <?php echo isset($lang['daily_tips']) ? $lang['daily_tips'] : 'Daily <span class="text-pink-600 drop-shadow-sm">Glow Tips ✨</span>'; ?>
            </h1>
            <p class="text-pink-500 font-medium mt-2">
                <?php echo isset($lang['personalized_advice']) ? $lang['personalized_advice'] : 'Personalized advice for your'; ?> 
                <span class="underline decoration-white font-bold text-gray-700"><?php echo $display_goal; ?></span> 
                <?php echo isset($lang['journey']) ? $lang['journey'] : 'journey'; ?>.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="tipsContainer">
            <?php while($tip = mysqli_fetch_assoc($tips_query)): 
                
                // تحديد النص المعروض (إنجليزي أو عربي)
                $display_title = ($current_lang == 'ar' && isset($db_titles_ar[$tip['title']])) ? $db_titles_ar[$tip['title']] : $tip['title'];
                $display_content = ($current_lang == 'ar' && isset($db_content_ar[$tip['content']])) ? $db_content_ar[$tip['content']] : $tip['content'];
            ?>
            <div class="tip-card p-8 rounded-[40px] shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 group border-b-8 border-pink-300">
                <div class="tip-icon text-5xl mb-4 transform group-hover:scale-125 transition-transform duration-300">
                    <?php echo $tip['icon']; ?>
                </div>
                <h3 class="text-xl font-extrabold text-gray-800 mb-3"><?php echo $display_title; ?></h3>
                <p class="text-gray-600 leading-relaxed mb-6 font-medium"><?php echo $display_content; ?></p>
                <div class="flex justify-between items-center">
                    <button onclick="toggleFavorite(this)" class="favorite-btn text-gray-400 hover:text-red-500 text-2xl transition-all active:scale-150">❤️</button>
                    <span class="text-[10px] uppercase tracking-widest font-bold text-pink-400">#<?php echo $display_goal; ?></span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="tips_script.js"></script>
</body>
</html>
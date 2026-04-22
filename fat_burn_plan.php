<?php
include 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// استدعاء ملفات اللغة
$user_lang = $_SESSION['lang'] ?? 'en';
if ($user_lang == 'ar') {
    include_once 'lang_ar.php';
} else {
    include_once 'lang_en.php';
}
if (!isset($lang['dir'])) $lang['dir'] = 'ltr';

// --- نظام AJAX لحفظ التقدم عند النقر على الدائرة ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_day'])) {
    $day = intval($_POST['day']);
    
    // هل اليوم موجود مسبقاً؟
    $check = mysqli_query($conn, "SELECT * FROM plan_progress WHERE user_id = '$user_id' AND day_number = '$day'");
    
    if (mysqli_num_rows($check) > 0) {
        // إذا موجود، نحذفه (إلغاء الإنجاز)
        mysqli_query($conn, "DELETE FROM plan_progress WHERE user_id = '$user_id' AND day_number = '$day'");
        echo json_encode(['status' => 'removed']);
    } else {
        // إذا مو موجود، نضيفه (تم الإنجاز)
        mysqli_query($conn, "INSERT INTO plan_progress (user_id, day_number) VALUES ('$user_id', '$day')");
        echo json_encode(['status' => 'added']);
    }
    exit();
}

// جلب الأيام المنجزة لعرضها عند تحميل الصفحة
$progress_query = mysqli_query($conn, "SELECT day_number FROM plan_progress WHERE user_id = '$user_id'");
$completed_days = [];
while ($row = mysqli_fetch_assoc($progress_query)) {
    $completed_days[] = $row['day_number'];
}

// ==========================================
// قاعدة بيانات مصغرة (مصفوفة) للـ 30 يوم مع إضافة الترجمة العربية
// ==========================================
$workouts = [
    ['title' => '20 Min Full Body HIIT', 'title_ar' => '20 دقيقة هيت كامل للجسم', 'video' => 'https://www.youtube.com/embed/cbKkB3OAOWs'],
    ['title' => '15 Min Pilates Core', 'title_ar' => '15 دقيقة بيلاتس للبطن', 'video' => 'https://www.youtube.com/embed/K-PwT3zKjE4'],
    ['title' => '30 Min Cardio No Jumping', 'title_ar' => '30 دقيقة كارديو بدون قفز', 'video' => 'https://www.youtube.com/embed/gC_L9qAHVJ8'],
    ['title' => 'Relaxing Yoga & Stretch', 'title_ar' => 'يوغا واسترخاء وإطالة', 'video' => 'https://www.youtube.com/embed/sTANio_2E0Q'],
    ['title' => '10 Min Ab Workout', 'title_ar' => '10 دقائق تمرين بطن', 'video' => 'https://www.youtube.com/embed/1f8yoFFdkcY']
];

$meals = [
    ['name' => 'Oatmeal & Fresh Berries', 'name_ar' => 'شوفان بالتوت الطازج', 'desc' => '1/2 cup oats cooked in almond milk, topped with strawberries, blueberries, and a drizzle of honey.', 'desc_ar' => 'نصف كوب شوفان مطبوخ بحليب اللوز، مزين بالفراولة والتوت والعسل.', 'cal' => '320 kcal'],
    ['name' => 'Grilled Chicken Salad', 'name_ar' => 'سلطة الدجاج المشوي', 'desc' => 'Mixed greens, cherry tomatoes, cucumbers, 150g grilled chicken breast, balsamic dressing.', 'desc_ar' => 'خضار مشكلة، طماطم كرزية، خيار، 150 جرام صدر دجاج مشوي، تتبيلة بلسمك.', 'cal' => '400 kcal'],
    ['name' => 'Salmon & Quinoa', 'name_ar' => 'سلمون مع الكينوا', 'desc' => 'Baked salmon fillet with lemon, served with 1/2 cup of cooked quinoa and steamed asparagus.', 'desc_ar' => 'فيليه سلمون مخبوز بالليمون، يقدم مع نصف كوب كينوا مطبوخة وهليون سوتيه.', 'cal' => '450 kcal'],
    ['name' => 'Greek Yogurt Parfait', 'name_ar' => 'بارفيه الزبادي اليوناني', 'desc' => 'Low-fat Greek yogurt layered with granola and chopped nuts. Great for recovery.', 'desc_ar' => 'طبقات من الزبادي اليوناني قليل الدسم مع الجرانولا والمكسرات المقطعة.', 'cal' => '250 kcal'],
    ['name' => 'Avocado Toast & Egg', 'name_ar' => 'توست الأفوكادو والبيض', 'desc' => 'Whole wheat toast smashed with half an avocado, topped with a poached egg and chili flakes.', 'desc_ar' => 'خبز قمح كامل مع نصف حبة أفوكادو مهروسة، مغطى ببيضة مسلوقة ورقائق الفلفل.', 'cal' => '350 kcal']
];

$plan_data = [];
for ($i = 1; $i <= 30; $i++) {
    $workout = $workouts[$i % count($workouts)];
    $meal = $meals[$i % count($meals)];
    
    // جعل كل 7 أيام "يوم راحة"
    if ($i % 7 == 0) {
        $plan_data[$i] = [
            'type' => 'rest',
            'title' => 'Active Rest Day',
            'title_ar' => 'يوم راحة نشط',
            'desc' => 'Take a walk, stretch, and let your muscles recover. Drink plenty of water.',
            'desc_ar' => 'استمتع بالمشي أو الإطالة، ودع عضلاتك تستشفي. اشرب الكثير من الماء.',
            'meal' => $meals[0] // وجبة خفيفة للراحة
        ];
    } else {
        $plan_data[$i] = [
            'type' => 'active',
            'title' => 'Day ' . $i . ' Workout',
            'title_ar' => 'تمرين اليوم ' . $i,
            'video' => $workout['video'],
            'workout_title' => $workout['title'],
            'workout_title_ar' => $workout['title_ar'],
            'meal' => $meal
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $user_lang == 'ar' ? 'ar' : 'en'; ?>" dir="<?php echo $lang['dir']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>30-Day Glow & Burn Plan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: <?php echo $user_lang == 'ar' ? "'Cairo', sans-serif" : "'Poppins', sans-serif"; ?>; background-color: #FCEEF4; color: #374151; }
        .day-card { transition: all 0.3s ease; }
        .day-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(236, 72, 153, 0.2); }
        .check-circle { transition: all 0.3s ease; cursor: pointer; }
        .completed .check-circle { background-color: #EC4899; border-color: #EC4899; color: white; }
        .completed .card-header { opacity: 0.7; text-decoration: line-through; }
    </style>
</head>
<body class="min-h-screen pb-10">

    <?php include 'navbar.php'; ?>

    <header class="max-w-7xl mx-auto px-6 py-12 text-center">
        <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-800 mb-4">
            <?php echo $user_lang == 'ar' ? 'تحدي الـ 30 يوم' : '30-Day'; ?> <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-400">Glow & Burn</span>
        </h1>
        <p class="text-gray-500 max-w-2xl mx-auto"><?php echo $user_lang == 'ar' ? 'خطتك الشخصية للمتابعة الفورية. اتبع التمارين اليومية، استمتع بالوجبات، واضغط على الدائرة عند إتمام اليوم لتسجيل تقدمك!' : 'Your personalized, real-time tracking plan. Follow the daily workouts, enjoy the meals, and click the circle when you finish a day to track your progress!'; ?></p>
        
        <div class="mt-8 bg-white p-6 rounded-3xl shadow-lg inline-block border border-pink-100">
            <h3 class="text-sm font-bold text-pink-500 uppercase tracking-wider mb-2"><?php echo $user_lang == 'ar' ? 'مستوى تقدمك' : 'Your Progress'; ?></h3>
            <div class="flex items-center gap-4 <?php echo $user_lang == 'ar' ? 'flex-row-reverse' : ''; ?>">
                <div class="w-64 h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div id="progress-bar" class="h-full bg-gradient-to-r from-pink-400 to-pink-600 transition-all duration-500" style="width: <?php echo (count($completed_days) / 30) * 100; ?>%"></div>
                </div>
                <span id="progress-text" class="font-bold text-gray-800"><?php echo count($completed_days); ?>/30</span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($plan_data as $day_num => $data): 
                $is_completed = in_array($day_num, $completed_days);
            ?>
            
            <div class="day-card bg-white rounded-[2rem] p-6 shadow-md border border-pink-50 flex flex-col <?php echo $is_completed ? 'completed' : ''; ?>" id="card-day-<?php echo $day_num; ?>">
                
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                    <div class="card-header text-<?php echo $user_lang == 'ar' ? 'right' : 'left'; ?>">
                        <span class="text-xs font-bold text-pink-400 tracking-wider uppercase"><?php echo $user_lang == 'ar' ? 'اليوم' : 'Day'; ?> <?php echo $day_num; ?></span>
                        <h3 class="text-xl font-bold text-gray-800"><?php echo $user_lang == 'ar' ? $data['title_ar'] : $data['title']; ?></h3>
                    </div>
                    <div onclick="toggleDay(<?php echo $day_num; ?>)" class="check-circle w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center text-transparent hover:border-pink-500">
                        <iconify-icon icon="lucide:check" class="text-xl"></iconify-icon>
                    </div>
                </div>

                <div class="flex-grow space-y-6 text-<?php echo $user_lang == 'ar' ? 'right' : 'left'; ?>">
                    <?php if ($data['type'] == 'active'): ?>
                        <div class="rounded-xl overflow-hidden shadow-sm relative pt-[56.25%] bg-gray-100">
                            <iframe class="absolute top-0 left-0 w-full h-full" src="<?php echo $data['video']; ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 flex items-center gap-2 <?php echo $user_lang == 'ar' ? 'flex-row-reverse justify-end' : ''; ?>"><iconify-icon icon="lucide:play-circle" class="text-pink-500"></iconify-icon> <?php echo $user_lang == 'ar' ? $data['workout_title_ar'] : $data['workout_title']; ?></p>
                        </div>
                    <?php else: ?>
                        <div class="rounded-xl overflow-hidden shadow-sm bg-pink-50 p-8 text-center border border-pink-100">
                            <iconify-icon icon="lucide:bed-double" class="text-5xl text-pink-300 mb-3"></iconify-icon>
                            <p class="text-sm text-gray-600"><?php echo $user_lang == 'ar' ? $data['desc_ar'] : $data['desc']; ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h4 class="text-xs font-bold text-pink-500 uppercase flex items-center gap-1 mb-2 <?php echo $user_lang == 'ar' ? 'flex-row-reverse justify-end' : ''; ?>">
                            <iconify-icon icon="lucide:utensils"></iconify-icon> <?php echo $user_lang == 'ar' ? 'تفاصيل الوجبة اليومية' : 'Daily Meal Info'; ?>
                        </h4>
                        <p class="font-bold text-gray-800 text-sm"><?php echo $user_lang == 'ar' ? $data['meal']['name_ar'] : $data['meal']['name']; ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo $user_lang == 'ar' ? $data['meal']['desc_ar'] : $data['meal']['desc']; ?></p>
                        <span class="inline-block mt-3 px-3 py-1 bg-white border border-gray-200 text-xs font-bold text-gray-600 rounded-lg">
                            🔥 <?php echo $data['meal']['cal']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function toggleDay(dayNum) {
            const card = document.getElementById('card-day-' + dayNum);
            
            fetch('fat_burn_plan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'toggle_day=1&day=' + dayNum
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'added') {
                    card.classList.add('completed');
                } else if(data.status === 'removed') {
                    card.classList.remove('completed');
                }
                updateProgressBar();
            })
            .catch(error => console.error('Error:', error));
        }

        function updateProgressBar() {
            const totalCompleted = document.querySelectorAll('.day-card.completed').length;
            const percentage = (totalCompleted / 30) * 100;
            
            document.getElementById('progress-text').innerText = totalCompleted + '/30';
            document.getElementById('progress-bar').style.width = percentage + '%';
        }
    </script>
</body>
</html>
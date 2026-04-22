<?php
include 'config.php';
include 'auth_check.php';
$user_lang = $_SESSION['lang'] ?? 'en';
if ($user_lang == 'ar') {
    include 'lang_ar.php';
} else {
    include 'lang_en.php';
}

// --- [ضبط التوقيت ومنطق الـ 3 فجراً] ---
date_default_timezone_set('Asia/Riyadh');
$now = new DateTime();
$hour = (int)$now->format('H');

if ($hour < 3) {
$today = $now->modify('-1 day')->format('Y-m-d');
} else {
$today = $now->format('Y-m-d');
}

$user_id = $_SESSION['user_id'];

// جلب إعدادات المستخدم (للتنبيهات)
$settings_q = mysqli_query($conn, "SELECT water_reminder FROM user_settings WHERE user_id = $user_id");
$user_settings = mysqli_fetch_assoc($settings_q);
$is_water_on = isset($user_settings['water_reminder']) ? (int)$user_settings['water_reminder'] : 1;

// --- [جلب بيانات الموية] ---
$water_res = mysqli_query($conn, "SELECT glasses FROM water_logs WHERE user_id = $user_id AND log_date = '$today'");
$water_row = mysqli_fetch_assoc($water_res);
$current_glasses = $water_row['glasses'] ?? 0;
$water_percentage = min(100, ($current_glasses / 8) * 100);

// --- [هيكل الأحداث] ---
$dynamic_events = [
'Breakfast' => ['icon' => '☕', 'items' => [], 'time' => '08:00 AM', 'display' => $lang['timeline.breakfast']],
'Lunch' => ['icon' => '🍴', 'items' => [], 'time' => '12:00 PM', 'display' => $lang['timeline.lunch']],
'Workout' => ['icon' => '🏋️', 'items' => [], 'time' => '04:00 PM', 'display' => $lang['timeline.workout']],
'Dinner' => ['icon' => '🥗', 'items' => [], 'time' => '07:00 PM', 'display' => $lang['timeline.dinner']],
'Snacks' => ['icon' => '🍎', 'items' => [], 'time' => '09:00 PM', 'display' => $lang['timeline.snacks']],
];

// --- [الدمج السحري: جلب الوجبات مع الـ ID للحذف] ---
$meals_combined_query = "
(SELECT id, meal_type, meal_name, created_at as log_time, 'manual' as source
FROM user_meals
WHERE user_id = $user_id AND DATE(created_at) = '$today')
UNION ALL
(SELECT id, meal_type, meal_name, created_at as log_time, 'plan' as source
FROM meal_tracking
WHERE user_id = $user_id AND eaten_date = '$today')
ORDER BY log_time ASC";

$meals_result = mysqli_query($conn, $meals_combined_query);

while ($row = mysqli_fetch_assoc($meals_result)) {
$type = $row['meal_type'];
if (isset($dynamic_events[$type])) {
    $display_name = $row['meal_name'];
    if($row['source'] == 'plan') {
        $display_name .= " 📋";
    }
    // تخزين البيانات كمصفوفة لتشمل الـ ID والمصدر للأزرار
    $dynamic_events[$type]['items'][] = [
        'id' => $row['id'],
        'name' => $display_name,
        'source' => $row['source']
    ];
    $dynamic_events[$type]['time'] = date('h:i A', strtotime($row['log_time']));
}
}

// --- [جلب التمارين] ---
$workout_query = "SELECT activity_name, duration, created_at FROM activities
WHERE user_id = $user_id AND DATE(created_at) = '$today'";
$workout_result = mysqli_query($conn, $workout_query);

while ($row = mysqli_fetch_assoc($workout_result)) {
$workout_time = date('h:i A', strtotime($row['created_at']));
$display_text = $row['activity_name'] . " (" . $row['duration'] . "s)";
// التمارين لا تملك حالياً حذف بنفس منطق الوجبات، لذا نضعها كنص
$dynamic_events['Workout']['items'][] = ['name' => $display_text, 'id' => null];
$dynamic_events['Workout']['time'] = $workout_time;
}
?>

<!doctype html>
<html lang="<?= $current_lang ?>" dir="<?= $lang['dir'] ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GlowWell - Daily Timeline</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
body {
background-color: #f5d8e5;
font-family: 'Poppins', sans-serif;
overflow-x: hidden;
min-height: 100vh;
}
.star { position: absolute; background: white; border-radius: 50%; animation: twinkle var(--d) infinite ease-in-out; z-index: 0; }
@keyframes twinkle { 0%, 100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 1; transform: scale(1.3); } }

.progress-bar-glow { box-shadow: 0 0 10px #FF85A2; }
.eng-num { font-family: 'Poppins', sans-serif !important; }

/* ستايل أزرار الأكشن السريع */
.action-btn { opacity: 0; transition: opacity 0.2s; }
.meal-row:hover .action-btn { opacity: 1; }
</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<?php for($i=0; $i<40; $i++): $d = rand(2, 5); ?>
<div class="star" style="width:3px; height:3px; top:<?=rand(0,100)?>%; left:<?=rand(0,100)?>%; --d:<?=$d?>s; animation-delay:<?=rand(0,5)?>s;"></div>
<?php endfor; ?>

<main class="relative z-10 max-w-6xl mx-auto mt-16 px-6">
<div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-4">
<div>
<h1 class="text-4xl font-bold text-gray-800 tracking-tight"><?= $lang['timeline.title'] ?></h1>
<p class="text-pink-500 font-medium text-sm mt-2"><?= $lang['timeline.showing_logs'] ?><span class="eng-num"><?php echo _n(date('M d, Y', strtotime($today))); ?></span></p>
</div>
</div>

<div class="relative">
<div class="hidden md:block absolute top-7 left-0 w-full h-0.5 border-t-2 border-dotted border-gray-400 z-0"></div>

<div class="relative z-10 flex flex-col md:flex-row justify-between items-start gap-12 md:gap-4 overflow-x-auto pb-6">
<?php foreach ($dynamic_events as $title => $data): ?>
<div class="flex flex-row md:flex-col items-center md:text-center w-full md:w-1/5 group">
<?php $hasData = !empty($data['items']); ?>

<div class="w-14 h-14 <?php echo $hasData ? 'bg-[#FF85A2]' : 'bg-gray-300'; ?> rounded-full flex items-center justify-center mb-0 md:mb-4 shadow-lg shrink-0 transition-all duration-300 group-hover:scale-110">
<span class="text-white text-2xl"><?php echo $data['icon']; ?></span>
</div>

<div class="ml-6 md:ml-0">
<h3 class="font-bold text-gray-800 text-lg mb-1"><?php echo $data['display']; ?></h3>
<?php if (!$hasData): ?>
<p class="text-gray-400 text-sm italic"><?= $lang['timeline.nothing_logged'] ?></p>
<?php else: ?>
<div class="space-y-2">
<?php foreach ($data['items'] as $item): ?>
<div class="meal-row flex items-center justify-between md:justify-center gap-2">
    <p class="text-gray-600 text-sm font-medium leading-snug"><?php echo htmlspecialchars($item['name']); ?></p>
    
    <?php if($item['id']): // إذا كانت وجبة ولها ID ?>
    <div class="action-btn flex gap-1">
        <a href="meals.php?delete_id=<?php echo $item['id']; ?>&source=<?php echo $item['source']; ?>" 
           onclick="return confirm('<?= addslashes($lang['timeline.delete_confirm']) ?>')"
           class="text-red-400 hover:text-red-600 transition">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<p class="text-[#FF85A2] text-[10px] mt-2 font-bold uppercase tracking-widest eng-num"><?php echo _n($data['time']); ?></p>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>

<?php if ($is_water_on === 1): ?>
<div class="mt-20 flex justify-center md:justify-start px-4">
<div class="bg-white/80 backdrop-blur-sm rounded-[25px] p-6 shadow-sm flex flex-col md:flex-row items-center gap-6 border border-pink-100 w-full md:w-fit min-w-[350px]">
<div class="bg-pink-50 p-4 rounded-2xl text-[#FF85A2]">
<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
</svg>
</div>
<div class="flex-1 w-full text-start">
<div class="flex justify-between items-center mb-2">
<p class="text-gray-700 font-bold"><?= $lang['timeline.hydration'] ?></p>
<span class="text-[#FF85A2] font-extrabold eng-num"><?php echo _n($current_glasses); ?>/<?= _n(8) ?> <small class="text-[10px] text-gray-400"><?= $lang['timeline.glasses'] ?></small></span>
</div>
<div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
<div class="bg-[#FF85A2] h-full transition-all duration-700 progress-bar-glow" style="width: <?php echo $water_percentage; ?>%"></div>
</div>
<p class="text-[10px] text-gray-400 mt-2"><?= _n($lang['timeline.goal_2l']) ?></p>
</div>
</div>
</div>
<?php endif; ?>

</div>
</main>
</body>
</html>
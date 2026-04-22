<?php
include 'config.php';
// ملاحظة: navbar.php يحتوي على منطق اللغة والـ session، لذا استدعاؤه يكفي لجلب $lang
include 'navbar.php'; 

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// 1. جلب بيانات المستخدم الأساسية
$user_query = mysqli_query($conn, "SELECT weight, health_goal, fullname, daily_goal FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$weight = $user_data['weight'] ?? '--';
$daily_calories_target = intval($user_data['daily_goal'] ?? 2000); 

// --- ضبط التوقيت ومنطق الـ 3 فجراً ---
date_default_timezone_set('Asia/Riyadh'); 
$now = new DateTime();
if ((int)$now->format('H') < 3) {
    $today = $now->modify('-1 day')->format('Y-m-d');
} else {
    $today = $now->format('Y-m-d');
}

// 2. حساب السعرات المحروقة لليوم (Burned)
$cal_query = mysqli_query($conn, "SELECT SUM(calories) as total FROM activities WHERE user_id = '$user_id' AND date = '$today'");
$cal_data = mysqli_fetch_assoc($cal_query);
$burned_today = round($cal_data['total'] ?? 0, 1);

// 3. حساب السعرات المأكولة لليوم (Eaten)
$eaten_manual = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(calories) as total FROM user_meals WHERE user_id = '$user_id' AND DATE(created_at) = '$today'"));
$eaten_plan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(calories) as total FROM meal_tracking WHERE user_id = '$user_id' AND eaten_date = '$today'"));
$total_eaten = ($eaten_manual['total'] ?? 0) + ($eaten_plan['total'] ?? 0);

// 4. حساب صافي الرصيد
$net_balance = ($daily_calories_target + $burned_today) - $total_eaten;

// منطق الرسائل التحفيزية
$feedback_msg = "✨ You are doing amazing!";
$msg_color = "text-pink-500";
if ($total_eaten > ($daily_calories_target + $burned_today)) {
    $feedback_msg = "⚠️ Your intake is a bit high, try a quick walk! ⚠️";
    $msg_color = "text-orange-500";
} elseif ($burned_today > 0 && $total_eaten <= $daily_calories_target) {
    $feedback_msg = "🔥 Amazing! You are in a high fat-burning zone today! 🏆";
    $msg_color = "text-green-500";
}

// 5. جلب بيانات الرسم البياني
$chart_query = mysqli_query($conn, "SELECT date, SUM(calories) as daily FROM activities WHERE user_id = '$user_id' GROUP BY date ORDER BY date DESC LIMIT 7");
$days_data = [];
while($r = mysqli_fetch_assoc($chart_query)) { $days_data[] = $r; }
$days_data = array_reverse($days_data);
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $lang['dir']; ?>">
<head>
    <meta charset="UTF-8">
    <title>GlowWell | <?php echo $lang['progress']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f5d8e5; overflow-x: hidden; min-height: 100vh; }
        .star { position: absolute; background: white; border-radius: 50%; animation: twinkle var(--d) infinite ease-in-out; z-index: 0; }
        @keyframes twinkle { 0%, 100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 1; transform: scale(1.3); } }
        .card-interactive { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); cursor: pointer; }
        .card-interactive:hover { transform: translateY(-15px) rotate(3deg) scale(1.05); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15); z-index: 20; }
        .bar-grow { transform-origin: bottom; animation: barGrow 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        @keyframes barGrow { 0% { transform: scaleY(0); } 100% { transform: scaleY(1); } }
        .glass-morphism { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(12px); border-radius: 40px; border: 2px solid rgba(255, 255, 255, 0.5); }
        .bar-fixed { width: 38px; background: linear-gradient(to top, #ec4899, #f9a8d4); border-radius: 12px 12px 4px 4px; position: relative; box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3); }
        .eng-num { font-family: 'Outfit', sans-serif !important; }
    </style>
</head>
<body class="pb-10">

    <?php for($i=0; $i<40; $i++): $d = rand(2, 5); ?>
        <div class="star" style="width:3px; height:3px; top:<?=rand(0,100)?>%; left:<?=rand(0,100)?>%; --d:<?=$d?>s; animation-delay:<?=rand(0,5)?>s;"></div>
    <?php endfor; ?>

    <main class="max-w-4xl mx-auto px-6 py-10 relative z-10">
        <div class="text-center mb-10">
            <h1 class="text-5xl font-black text-gray-800 tracking-tight"><?php echo $lang['progress_glow_details']; ?></h1>
            <p class="<?= $msg_color ?> font-bold mt-2 animate-pulse"><?= $feedback_msg ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
            <div class="card-interactive bg-green-100 p-6 rounded-[40px] shadow-sm text-center border-2 border-white">
                <span class="text-3xl block mb-2">🔥</span>
                <p class="text-[10px] font-black text-green-600 uppercase tracking-widest"><?php echo $lang['burned']; ?></p>
                <p class="text-2xl font-black text-gray-800 mt-1 eng-num"><?= $burned_today ?> <span class="text-sm">cal</span></p>
            </div>
            
            <div class="card-interactive bg-blue-100 p-6 rounded-[40px] shadow-sm text-center border-2 border-white">
                <span class="text-3xl block mb-2">🍱</span>
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest"><?php echo $lang['eaten']; ?></p>
                <p class="text-2xl font-black text-gray-800 mt-1 eng-num"><?= $total_eaten ?> <span class="text-sm">cal</span></p>
            </div>

            <div class="card-interactive bg-purple-100 p-6 rounded-[40px] shadow-sm text-center border-2 border-white">
                <span class="text-3xl block mb-2">💎</span>
                <p class="text-[10px] font-black text-purple-600 uppercase tracking-widest"><?php echo $lang['remaining_cal']; ?></p>
                <p class="text-2xl font-black text-gray-800 mt-1 eng-num"><?= $net_balance ?> <span class="text-sm">cal</span></p>
            </div>

            <div class="card-interactive bg-yellow-100 p-6 rounded-[40px] shadow-sm text-center border-2 border-white">
                <span class="text-3xl block mb-2">⚖️</span>
                <p class="text-[10px] font-black text-yellow-600 uppercase tracking-widest"><?php echo $lang['weight_lbl']; ?></p>
                <p class="text-2xl font-black text-gray-800 mt-1 eng-num"><?= $weight ?> <span class="text-sm">kg</span></p>
            </div>
        </div>

        <div class="glass-morphism p-10 shadow-xl">
            <h2 class="text-2xl font-black text-gray-800 mb-12"><?php echo $lang['weekly_progress']; ?> 📊</h2>
            <div class="flex items-end justify-around h-52 border-b-2 border-pink-100 pb-2">
                <?php if(!empty($days_data)): 
                    $max = max(array_column($days_data, 'daily')) ?: 1;
                    foreach($days_data as $index => $day):
                        $h_px = ($day['daily'] / $max) * 170;
                        $h_px = max($h_px, 20); ?>
                    <div class="flex flex-col items-center flex-1 group">
                        <div class="bar-fixed bar-grow" style="height: <?=$h_px?>px; animation-delay: <?=$index * 0.1?>s;">
                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                <?= round($day['daily'], 1) ?> cal
                            </div>
                        </div>
                        <p class="text-[11px] font-black text-pink-600 mt-4 uppercase"><?= date('D', strtotime($day['date'])) ?></p>
                        <p class="text-[9px] text-gray-400 font-bold eng-num"><?= date('m/d', strtotime($day['date'])) ?></p>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-gray-400 italic py-10"><?php echo $lang['no_activities']; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="workouts.php" class="inline-block bg-white text-pink-500 px-12 py-4 rounded-full font-black text-sm uppercase tracking-widest shadow-lg hover:bg-pink-500 hover:text-white transition-all transform hover:scale-110 active:scale-90 border-2 border-pink-100">
                + <?php echo $lang['workout_btn']; ?>
            </a>
        </div>
    </main>

</body>
</html>
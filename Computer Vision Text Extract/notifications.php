<?php
session_start();
include 'config.php';
include 'auth_check.php';
date_default_timezone_set('Asia/Riyadh');

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$current_hour = (int)date('H');

$dynamic_notifications = [];

// التحقق من الجدولين في وقت واحد
$check_dinner = mysqli_query($conn, "
    SELECT id FROM user_meals 
    WHERE user_id = $user_id AND meal_type = 'Dinner' AND DATE(created_at) = '$today'
    UNION ALL
    SELECT id FROM meal_tracking 
    WHERE user_id = $user_id AND meal_type = 'Dinner' AND DATE(created_at) = '$today'
");

if (mysqli_num_rows($check_dinner) == 0 && $current_hour >= 19) {
    $dynamic_notifications[] = [
        "title" => "Missing Dinner?",
        "desc" => "You haven't logged your dinner yet. Try a low-carb option!",
        "icon" => '<svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path><path d="M7 2v20"></path><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path></svg>',
        "time" => "7:00 PM",
        "has_toggle" => false
    ];
}

$check_workout_q = mysqli_query($conn, "SELECT id FROM activities
WHERE user_id = $user_id
AND DATE(created_at) = '$today'
LIMIT 1");

if (mysqli_num_rows($check_workout_q) == 0) {

if ($current_hour >= 16) {

$suggest_q = mysqli_query($conn, "SELECT activity_name FROM activities WHERE user_id = $user_id LIMIT 1");
$suggest = mysqli_fetch_assoc($suggest_q);
$act_name = ($suggest) ? $suggest['activity_name'] : "Your daily routine";

$dynamic_notifications[] = [
"title" => "Time for a workout",
"desc" => "You haven't exercised yet today! Stay active with: " . htmlspecialchars($act_name),
"icon" => '<svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
"time" => "4:00 PM",
"has_toggle" => false
];
}
}

$dynamic_notifications[] = [
"title" => "Reminder",
"desc" => "Don't forget to drink 2L of water today.",
"icon" => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<path d="M9 18h6"></path>
<path d="M10 22h4"></path>
<path d="M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V17a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7z"></path>
<line x1="12" y1="2" x2="12" y2="2"></line>
<line x1="5.07" y1="5.07" x2="5.07" y2="5.07"></line>
<line x1="2" y1="12" x2="2" y2="12"></line>
<line x1="5.07" y1="18.93" x2="5.07" y2="18.93"></line>
<line x1="18.93" y1="18.93" x2="18.93" y2="18.93"></line>
<line x1="22" y1="12" x2="22" y2="12"></line>
<line x1="18.93" y1="5.07" x2="18.93" y2="5.07"></line>
</svg>',
"time" => "",
"has_toggle" => true
];

$settings_q = mysqli_query($conn, "SELECT water_reminder FROM user_settings WHERE user_id = $user_id");
$user_settings = mysqli_fetch_assoc($settings_q);

$is_water_on = isset($user_settings['water_reminder']) ? $user_settings['water_reminder'] : 1;
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GlowWell - Notifications</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
body { background-color: #f5d8e5; font-family: 'Poppins', sans-serif;overflow-x: hidden; min-height:100vh; }
/* Ø­Ø±ÙƒØ©️ Ø§Ù„Ù†Ø¬ÙˆÙ… */
.star { position: absolute; background: white; border-radius: 50%; animation: twinkle var(--d) infinite ease-in-out; z-index: 0; }
@keyframes twinkle { 0%, 100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 1; transform: scale(1.3); } }
.notif-card { background: white; border-radius: 25px; border: 1px solid #FCE7F3; transition: transform 0.2s; }
.notif-card:hover { transform: translateY(-2px); }
</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<?php for($i=0; $i<40; $i++): $d = rand(2, 5); ?>
<div class="star" style="width:3px; height:3px; top:<?=rand(0,100)?>%; left:<?=rand(0,100)?>%; --d:<?=$d?>s; animation-delay:<?=rand(0,5)?>s;"></div>
<?php endfor; ?>
<main class="max-w-6xl mx-auto mt-16 px-6">
<h1 class="text-4xl font-bold text-gray-800 mb-12 text-left px-4 tracking-tight">Notifications</h1>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto pb-20">

<?php foreach ($dynamic_notifications as $note): ?>
<div class="notif-card p-6 shadow-sm flex items-center gap-5">

<div class="text-gray-700 shrink-0">
<?php echo $note['icon']; ?>
</div>

<div class="flex-1">
<h3 class="font-bold text-gray-800 text-lg leading-tight">
<?php echo $note['title']; ?>
</h3>
<p class="text-gray-400 text-sm mt-1 leading-snug">
<?php echo $note['desc']; ?>
</p>
<?php if (!empty($note['time'])): ?>
<p class="text-gray-400 text-xs mt-2 font-bold uppercase tracking-wider">
<?php echo $note['time']; ?>
</p>
<?php endif; ?>
</div>

<?php if ($note['has_toggle']): ?>
<label class="relative inline-flex items-center cursor-pointer">
<input type="checkbox"
class="sr-only peer"
onchange="updateSetting('water_reminder', this.checked)"
<?php echo ($is_water_on) ? 'checked' : ''; ?>>

<div class="w-12 h-6 bg-gray-300 rounded-full peer
peer-checked:bg-[#FF85A2]
transition-all duration-300 shadow-inner">
</div>

<div class="absolute left-1 w-4 h-4 bg-white rounded-full shadow-sm
transition-all duration-300
peer-checked:translate-x-6">
</div>
</label>
<?php endif; ?>

</div>
<?php endforeach; ?>

</div>
</main>
<script src="../assets/js/update_settings.js"></script>
</body>
</html>
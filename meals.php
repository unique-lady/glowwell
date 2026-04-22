<?php
include 'config.php';
require_once 'auth_check.php';
mysqli_set_charset($conn, "utf8mb4");

// استدعاء ملفات اللغة
$user_lang = $_SESSION['lang'] ?? 'en';
if ($user_lang == 'ar') {
    include 'lang_ar.php';
} else {
    include 'lang_en.php';
}

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// --- [ضبط التوقيت ومنطق الـ 3 فجراً] ---
date_default_timezone_set('Asia/Riyadh');
$now = new DateTime();
$hour = (int)$now->format('H');
if ($hour < 3) {
    $today = $now->modify('-1 day')->format('Y-m-d');
} else {
    $today = $now->format('Y-m-d');
}

// دالة تحويل الأرقام العربية إلى إنجليزية لضمان صحة الحسابات
function to_eng_num($str) {
    $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $english = ['0','1','2','3','4','5','6','7','8','9'];
    return str_replace($arabic, $english, $str);
}

// --- [1. نظام الحساب الذكي للسعرات] ---
$user_query = mysqli_query($conn, "SELECT weight, goal, daily_goal FROM users WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($user_query);

$user_weight = floatval($user_data['weight'] ?? 70);
$user_goal_type = strtolower($user_data['goal'] ?? 'lose');

if (empty($user_data['daily_goal']) || $user_data['daily_goal'] == 0) {
    if (strpos($user_goal_type, 'gain') !== false || strpos($user_goal_type, 'بناء') !== false) {
        $daily_goal = round($user_weight * 30);
    } else {
        $daily_goal = round($user_weight * 22);
    }
    mysqli_query($conn, "UPDATE users SET daily_goal = $daily_goal WHERE id = $user_id");
} else {
    $daily_goal = intval($user_data['daily_goal']);
}

// زيادة التقدير بنسبة 10% للأمان عند خسارة الوزن (اختياري حسب طلبك السابق)
// $daily_goal = round($daily_goal * 1.1); 

if (isset($_POST['update_goal'])) {
    $new_goal = intval(to_eng_num($_POST['daily_goal']));
    mysqli_query($conn, "UPDATE users SET daily_goal = $new_goal WHERE id = $user_id");
    header("Location: meals.php"); exit();
}

// --- [2. توزيع الماكروز] ---
if (strpos($user_goal_type, 'gain') !== false || strpos($user_goal_type, 'بناء') !== false) {
    $user_fitness_goal_text = $lang['goal_muscle_gain'] ?? "Muscle Gain Plan";
    $goal_pro  = ($daily_goal * 0.30) / 4;
    $goal_fat  = ($daily_goal * 0.25) / 9;
    $goal_carb = ($daily_goal * 0.45) / 4;
} else {
    $user_fitness_goal_text = $lang['goal_weight_loss'] ?? "Weight Loss Plan";
    $goal_pro  = ($daily_goal * 0.40) / 4;
    $goal_fat  = ($daily_goal * 0.30) / 9;
    $goal_carb = ($daily_goal * 0.30) / 4;
}

// --- [3. معالجة الحذف والإضافة] ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $source = $_GET['source'] ?? 'manual';
    if ($source === 'plan') {
        mysqli_query($conn, "DELETE FROM meal_tracking WHERE id = $delete_id AND user_id = $user_id");
    } else {
        mysqli_query($conn, "DELETE FROM user_meals WHERE id = $delete_id AND user_id = $user_id");
    }
    header("Location: meals.php"); exit();
}

if (isset($_POST['add_meal'])) {
    $type = mysqli_real_escape_string($conn, $_POST['meal_type']);
    $name = mysqli_real_escape_string($conn, $_POST['meal_name']);
    $qnt = floatval(to_eng_num($_POST['quantity'] ?? 1));
    if ($qnt <= 0) $qnt = 1;

    $cal  = round(intval(to_eng_num($_POST['calories'])) * $qnt);
    $pro  = round(intval(to_eng_num($_POST['protein'] ?? 0)) * $qnt);
    $fat  = round(intval(to_eng_num($_POST['fat'] ?? 0)) * $qnt);
    $carb = round(intval(to_eng_num($_POST['carbs'] ?? 0)) * $qnt);

    if (!empty($name)) {
        $final_dt = $today . " " . date('H:i:s');
        mysqli_query($conn, "INSERT INTO user_meals (user_id, meal_type, meal_name, calories, protein, fat, carbs, created_at)
        VALUES ('$user_id', '$type', '$name', '$cal', '$pro', '$fat', '$carb', '$final_dt')");
    }
    header("Location: meals.php"); exit();
}

// --- [4. حساب الإجماليات] ---
$stats_q = mysqli_query($conn, "SELECT SUM(calories) as s_cal, SUM(protein) as s_pro, SUM(fat) as s_fat, SUM(carbs) as s_carb FROM user_meals WHERE user_id = '$user_id' AND DATE(created_at) = '$today'");
$stats = mysqli_fetch_assoc($stats_q);
$plans_q = mysqli_query($conn, "SELECT SUM(calories) as p_cal, SUM(protein) as p_pro, SUM(fat) as p_fat, SUM(carbs) as p_carb FROM meal_tracking WHERE user_id = '$user_id' AND eaten_date = '$today'");
$plans_data = mysqli_fetch_assoc($plans_q);

$total_calories = intval($stats['s_cal'] ?? 0) + intval($plans_data['p_cal'] ?? 0);
$total_protein = intval($stats['s_pro'] ?? 0) + intval($plans_data['p_pro'] ?? 0);
$total_fat = intval($stats['s_fat'] ?? 0) + intval($plans_data['p_fat'] ?? 0);
$total_carbs = intval($stats['s_carb'] ?? 0) + intval($plans_data['p_carb'] ?? 0);
$remaining = $daily_goal - $total_calories;

// الموية
$water_q = mysqli_query($conn, "SELECT glasses FROM water_logs WHERE user_id = $user_id AND log_date = '$today'");
$water_data = mysqli_fetch_assoc($water_q);
$current_water = intval($water_data['glasses'] ?? 0);

// بيانات الرسم البياني
$labels = []; $data_values = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D', strtotime($date)); 
    $r1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(calories) as total FROM user_meals WHERE user_id=$user_id AND DATE(created_at)='$date'"));
    $r2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(calories) as total FROM meal_tracking WHERE user_id=$user_id AND eaten_date='$date'"));
    $val = intval($r1['total'] ?? 0) + intval($r2['total'] ?? 0);
    $data_values[] = $val;
}
$js_labels = json_encode($labels);
$js_data = json_encode($data_values);

function get_circle_data($current, $target) {
    if ($target <= 0) return ['offset' => 125.6, 'color' => '#f3f4f6'];
    $perc = ($current / $target) * 100;
    return ['offset' => 125.6 - (min(100, $perc) / 100) * 125.6, 'color' => ($perc > 100) ? '#ef4444' : ''];
}
?>

<!doctype html>
<html lang="<?php echo $user_lang; ?>" dir="<?php echo $lang['dir'] ?? 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GlowWell - <?php echo $lang['meals'] ?? 'Meals'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #FDF2F8; font-family: 'Poppins', sans-serif; margin: 0; }
        .pink-card { background: white; border-radius: 30px; border: 1px solid #FCE7F3; }
        .input-pink { background: #FFF5F8; border: 1px solid #FCE7F3; border-radius: 15px; padding: 12px; outline: none; width: 100%; }
        .circle-chart { transform: rotate(-90deg); transition: all 0.5s ease; }
        .water-glass { cursor: pointer; transition: transform 0.2s; filter: grayscale(1); opacity: 0.3; }
        .water-glass.filled { filter: grayscale(0); opacity: 1; transform: scale(1.1); }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body class="p-0">
<?php include 'navbar.php'; ?>

<div class="max-w-5xl mx-auto mt-8 px-6">
    <a href="scan_meal.php" class="pink-card flex flex-wrap items-center justify-center gap-3 p-5 shadow-sm mb-8 text-gray-800 font-bold no-underline hover:shadow-md transition border border-pink-100">
        <span class="text-3xl">📷</span>
        <span><?php echo $lang['ai_meal_scan'] ?? 'AI Meal Scan'; ?></span>
    </a>

    <div class="pink-card p-10 shadow-sm mb-10 text-center">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-6 px-4">
            <div class="bg-pink-50 p-4 rounded-2xl border border-pink-100 flex items-center shadow-sm">
                <form method="POST" class="flex items-center gap-3">
                    <div class="flex flex-col items-start text-<?php echo ($user_lang == 'ar') ? 'right' : 'left'; ?>">
                        <label class="text-[10px] text-pink-400 font-bold uppercase tracking-tighter"><?php echo $user_fitness_goal_text; ?></label>
                        <div class="flex items-center gap-1">
                            <input type="number" name="daily_goal" id="goalInput" value="<?php echo number_format($daily_goal, 0, '.', ''); ?>" class="w-20 font-bold text-2xl text-gray-800 outline-none bg-transparent">
                            <div class="flex flex-col">
                                <button type="button" onclick="changeGoal(50)" class="text-pink-500 hover:text-pink-700 leading-none text-lg">▲</button>
                                <button type="button" onclick="changeGoal(-50)" class="text-pink-500 hover:text-pink-700 leading-none text-lg">▼</button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="update_goal" class="bg-pink-500 text-white text-xs font-bold px-5 py-2.5 rounded-xl hover:bg-pink-600 shadow-md transition"><?php echo $lang['save'] ?? 'Save'; ?></button>
                </form>
            </div>
            
            <div class="hidden md:block text-3xl text-gray-200 font-light">-</div>
            
            <div>
                <span class="text-2xl font-bold <?php echo ($total_calories > $daily_goal) ? 'text-red-500' : 'text-pink-500'; ?>">
                    <?php echo number_format($total_calories, 0, '.', ''); ?>
                </span>
                <p class="text-xs text-gray-400 uppercase tracking-widest"><?php echo $lang['food'] ?? 'Food'; ?></p>
            </div>

            <div class="text-3xl text-gray-200 font-light">=</div>

            <div>
                <span class="text-4xl font-extrabold <?php echo ($remaining < 0) ? 'text-red-500' : 'text-blue-500'; ?>">
                    <?php echo number_format($remaining, 0, '.', ''); ?>
                </span>
                <p class="text-xs text-gray-400 uppercase tracking-widest"><?php echo ($remaining < 0) ? ($lang['over_limit'] ?? 'Over Limit') : ($lang['remaining'] ?? 'Remaining'); ?></p>
            </div>
        </div>
        
        <div class="flex justify-center gap-8 md:gap-16 pt-6 border-t border-pink-50 flex-wrap">
            <?php 
            $macros = [
                ['label' => 'Protein', 'val' => $total_protein, 'goal' => $goal_pro, 'color' => '#ec4899', 'text' => 'text-pink-400'],
                ['label' => 'Fat', 'val' => $total_fat, 'goal' => $goal_fat, 'color' => '#facc15', 'text' => 'text-yellow-500'],
                ['label' => 'Carbs', 'val' => $total_carbs, 'goal' => $goal_carb, 'color' => '#3b82f6', 'text' => 'text-blue-500']
            ];
            foreach ($macros as $m): 
                $c_data = get_circle_data($m['val'], $m['goal']);
                $stroke_color = $c_data['color'] ?: $m['color'];
            ?>
            <div class="flex flex-col items-center">
                <div class="relative w-16 h-16 mb-2">
                    <svg class="circle-chart w-16 h-16">
                        <circle cx="32" cy="32" r="20" stroke="#f3f4f6" stroke-width="4" fill="transparent"/>
                        <circle cx="32" cy="32" r="20" stroke="<?php echo $stroke_color; ?>" stroke-width="4" fill="transparent" 
                                stroke-dasharray="125.6" stroke-dashoffset="<?php echo $c_data['offset']; ?>" stroke-linecap="round"/>
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-gray-700">
                        <?php echo number_format($m['val'], 0, '.', ''); ?>g
                    </span>
                </div>
                <span class="text-[10px] <?php echo $m['text']; ?> font-bold uppercase tracking-widest"><?php echo $m['label']; ?></span>
                <span class="text-[9px] text-gray-400"><?php echo $lang['target'] ?? 'Target: '; ?><?php echo number_format($m['goal'], 0, '.', ''); ?>g</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="pink-card p-6 mb-10 shadow-sm flex flex-col md:flex-row items-center justify-between px-10 gap-4">
        <div class="text-center md:text-<?php echo ($user_lang == 'ar') ? 'right' : 'left'; ?>">
            <h3 class="text-gray-700 font-bold flex items-center gap-2">💧 <?php echo $lang['water_intake'] ?? 'Water Intake'; ?></h3>
            <p class="text-xs text-gray-400"><?php echo $lang['resets_daily'] ?? 'Resets daily at 3:00 AM'; ?></p>
        </div>
        <div class="flex flex-wrap justify-center gap-3" id="waterContainer">
            <?php for($i=1; $i<=8; $i++): ?>
                <span class="water-glass text-3xl <?php echo ($i <= $current_water) ? 'filled' : ''; ?>" onclick="updateWater(<?php echo $i; ?>)">🥛</span>
            <?php endfor; ?>
        </div>
        <div class="text-pink-500 font-bold text-lg" id="waterCountDisplay"><?php echo number_format($current_water, 0, '.', ''); ?>/8</div>
    </div>

    <div class="pink-card p-6 mb-10 shadow-sm">
        <h3 class="text-gray-700 font-bold mb-4 px-4 italic">📊 <?php echo $lang['weekly_insights'] ?? 'Weekly Insights'; ?></h3>
        <div style="height: 180px;"><canvas id="weeklyChart"></canvas></div>
    </div>

   <form method="POST" class="pink-card p-6 md:p-8 mb-10 shadow-sm relative text-<?php echo ($user_lang == 'ar') ? 'right' : 'left'; ?>">
    <h2 class="font-bold text-gray-700 mb-6 italic flex items-center gap-2">
        <span>✨</span> <?php echo $lang['add_food'] ?? 'Add Food'; ?>
    </h2>
    
    <div class="grid grid-cols-12 gap-3 items-end">
        
        <div class="col-span-4 md:col-span-2">
            <label class="text-[9px] text-pink-400 font-bold uppercase mb-1 block"><?php echo $lang['type'] ?? 'Type'; ?></label>
            <select name="meal_type" class="input-pink text-xs px-2 h-[45px]">
                <option value="Breakfast"><?php echo $lang['breakfast'] ?? 'Breakfast'; ?></option>
                <option value="Lunch"><?php echo $lang['lunch_dinner'] ?? 'Lunch'; ?></option>
                <option value="Dinner"><?php echo $lang['lunch_dinner'] ?? 'Dinner'; ?></option>
                <option value="Snacks"><?php echo $lang['snacks_desserts'] ?? 'Snacks'; ?></option>
            </select>
        </div>

        <div class="col-span-8 md:col-span-6 relative">
            <label class="text-[9px] text-pink-400 font-bold uppercase mb-1 block"><?php echo $lang['food_name'] ?? 'Food Name'; ?></label>
            <input type="text" name="meal_name" id="food_search" placeholder="Search..." class="input-pink h-[45px] text-sm" autocomplete="off" required>
            <div id="search_results" class="hidden absolute top-full left-0 right-0 bg-white border z-50 rounded-xl shadow-lg max-h-40 overflow-y-auto"></div>
        </div>

        <div class="col-span-4 md:col-span-2">
            <label class="text-[9px] text-pink-400 font-bold uppercase mb-1 block"><?php echo $lang['qty'] ?? 'Qty'; ?></label>
            <input type="number" step="0.1" name="quantity" id="qnt_input" value="1" class="input-pink h-[45px] text-center font-bold text-pink-600">
        </div>

        <div class="col-span-4 md:col-span-2">
            <label class="text-[9px] text-pink-400 font-bold uppercase mb-1 block"><?php echo $lang['kcal'] ?? 'kcal'; ?></label>
            <input type="number" name="calories" id="cal_input" placeholder="0" class="input-pink h-[45px]" required>
        </div>

        
    </div>

    <div class="grid grid-cols-3 gap-3 mt-6">
        <div>
            <label class="text-[9px] text-gray-400 font-bold uppercase mb-1 block"><?php echo $lang['protein_g'] ?? 'Prot(g)'; ?></label>
            <input type="number" name="protein" id="pro_input" placeholder="0" class="input-pink h-[40px] text-sm">
        </div>
        <div>
            <label class="text-[9px] text-gray-400 font-bold uppercase mb-1 block"><?php echo $lang['fat_g'] ?? 'Fat(g)'; ?></label>
            <input type="number" name="fat" id="fat_input" placeholder="0" class="input-pink h-[40px] text-sm">
        </div>
        <div>
            <label class="text-[9px] text-gray-400 font-bold uppercase mb-1 block"><?php echo $lang['carb_g'] ?? 'Carb(g)'; ?></label>
            <input type="number" name="carbs" id="carb_input" placeholder="0" class="input-pink h-[40px] text-sm">
        </div>


        
    </div>

    <div class="mt-4 text-center md:text-<?php echo ($user_lang == 'ar') ? 'right' : 'left'; ?>">
        <a href="meal_plans.php" class="text-[10px] text-pink-400 hover:text-pink-600 font-semibold underline">
            + <?php echo $lang['add_from_plan'] ?? 'Add from your meal plan'; ?>
        </a>
    </div>
    <div class="col-span-4 md:col-span-12 lg:col-span-2 mt-4">
            <button type="submit" name="add_meal" class="w-full bg-pink-500 text-white font-bold h-[45px] rounded-2xl hover:bg-pink-600 shadow-md transition-all active:scale-95 text-sm">
                <?php echo $lang['log_meal'] ?? 'Log'; ?>
            </button>
        </div>
</form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pb-10 text-<?php echo ($user_lang == 'ar') ? 'right' : 'left'; ?>">
        <?php $types = ['Breakfast', 'Lunch', 'Dinner', 'Snacks']; foreach($types as $type): 
            $manual_res = mysqli_query($conn, "SELECT * FROM user_meals WHERE user_id=$user_id AND meal_type='$type' AND DATE(created_at)='$today'");
            $plan_res = mysqli_query($conn, "SELECT * FROM meal_tracking WHERE user_id=$user_id AND meal_type='$type' AND eaten_date='$today'");
            
            // ترجمة العنوان للقسم
            if ($type == 'Breakfast') $type_trans = $lang['breakfast'] ?? 'Breakfast';
            elseif ($type == 'Lunch') $type_trans = $lang['lunch_dinner'] ?? 'Lunch';
            elseif ($type == 'Dinner') $type_trans = $lang['lunch_dinner'] ?? 'Dinner';
            else $type_trans = $lang['snacks_desserts'] ?? 'Snacks';
        ?>
        <div class="pink-card p-6 shadow-sm mb-6">
            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2"><?php echo $type_trans; ?></h3>
            <?php while($m = mysqli_fetch_assoc($manual_res)): ?>
                <div class="flex justify-between items-center py-3 border-b border-pink-50 last:border-0">
                    <div><p class="font-semibold text-gray-800"><?php echo htmlspecialchars($m['meal_name']); ?></p>
                    <p class="text-[10px] text-gray-400">P:<?php echo number_format($m['protein'], 0, '.', ''); ?>g | F:<?php echo number_format($m['fat'], 0, '.', ''); ?>g | C:<?php echo number_format($m['carbs'], 0, '.', ''); ?>g</p></div>
                    <div class="flex items-center gap-4"><span class="text-pink-500 font-bold"><?php echo number_format($m['calories'], 0, '.', ''); ?> kcal</span>
                    <a href="meals.php?delete_id=<?php echo $m['id']; ?>&source=manual" class="text-red-300 hover:text-red-500"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a></div>
                </div>
            <?php endwhile; ?>
            <?php while($p = mysqli_fetch_assoc($plan_res)): ?>
                <div class="flex justify-between items-center py-3 border-b border-pink-50 last:border-0 bg-blue-50/30 rounded-lg px-2 mb-1">
                    <div><p class="font-semibold text-gray-800"><?php echo htmlspecialchars($p['meal_name']); ?> <span class="text-[9px] bg-blue-100 text-blue-500 px-1.5 py-0.5 rounded italic">Plan</span></p>
                    <p class="text-[10px] text-gray-400">P:<?php echo number_format($p['protein'], 0, '.', ''); ?>g | F:<?php echo number_format($p['fat'], 0, '.', ''); ?>g | C:<?php echo number_format($p['carbs'], 0, '.', ''); ?>g</p></div>
                    <div class="flex items-center gap-4"><span class="text-pink-500 font-bold"><?php echo number_format($p['calories'], 0, '.', ''); ?> kcal</span>
                    <a href="meals.php?delete_id=<?php echo $p['id']; ?>&source=plan" class="text-red-300 hover:text-red-500"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a></div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function changeGoal(amount) {
    const input = document.getElementById('goalInput');
    let currentVal = parseInt(input.value) || 0;
    input.value = currentVal + amount;
}

const ctx = document.getElementById('weeklyChart').getContext('2d');
new Chart(ctx, { 
    type: 'line', 
    data: { 
        labels: <?php echo $js_labels; ?>, 
        datasets: [{ 
            label: 'Calories', 
            data: <?php echo $js_data; ?>, 
            borderColor: '#ec4899', 
            backgroundColor: 'rgba(236, 72, 153, 0.1)', 
            fill: true, 
            tension: 0.4 
        }] 
    }, 
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { legend: { display: false } } 
    } 
});

function updateWater(count) {
    const glasses = document.querySelectorAll('.water-glass');
    glasses.forEach((g, i) => i < count ? g.classList.add('filled') : g.classList.remove('filled'));
    document.getElementById('waterCountDisplay').innerText = count + '/8';
    fetch('update_water.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `glasses=${count}&date=<?php echo $today; ?>` });
}

const searchInput = document.getElementById('food_search');
const resultsDiv = document.getElementById('search_results');
searchInput.addEventListener('input', function() {
    let q = this.value.trim();
    if (q.length < 1) { resultsDiv.classList.add('hidden'); return; }
    fetch('fetch_food.php?query=' + encodeURIComponent(q)).then(res => res.json()).then(data => {
        resultsDiv.innerHTML = '';
        if(data.length > 0) {
            resultsDiv.classList.remove('hidden');
            data.forEach(item => {
                let d = document.createElement('div');
                d.className = 'p-3 hover:bg-pink-50 cursor-pointer border-b border-gray-50 text-sm flex justify-between';
                // عرض الاسم مع القياس (Scoop, Cup, etc) كما في الـ Database
                let displayText = item.food_name;
                if(item.measurement) displayText += ` (${item.measurement})`;
                
                d.innerHTML = `<span><b>${displayText}</b></span> <span class="text-pink-500 font-bold">${item.calories} kcal</span>`;
                d.onclick = () => {
                    searchInput.value = displayText;
                    document.getElementById('cal_input').value = item.calories;
                    document.getElementById('pro_input').value = item.protein;
                    document.getElementById('fat_input').value = item.fat;
                    document.getElementById('carb_input').value = item.carbs;
                    resultsDiv.classList.add('hidden');
                };
                resultsDiv.appendChild(d);
            });
        } else {
            resultsDiv.classList.add('hidden');
        }
    });
});
</script>
</body>
</html>
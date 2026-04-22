<?php
include 'config.php';

// Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// --- AJAX Progress System ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_day'])) {
    $day = intval($_POST['day']);
    $check = mysqli_query($conn, "SELECT * FROM plan_progress WHERE user_id = '$user_id' AND day_number = '$day'");
    
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM plan_progress WHERE user_id = '$user_id' AND day_number = '$day'");
        echo json_encode(['status' => 'removed']);
    } else {
        mysqli_query($conn, "INSERT INTO plan_progress (user_id, day_number) VALUES ('$user_id', '$day')");
        echo json_encode(['status' => 'added']);
    }
    exit();
}

// Fetch completed days
$progress_query = mysqli_query($conn, "SELECT day_number FROM plan_progress WHERE user_id = '$user_id'");
$completed_days = [];
while ($row = mysqli_fetch_assoc($progress_query)) {
    $completed_days[] = $row['day_number'];
}

// ==========================================
// Professional 30-Day Data
// ==========================================
$workouts = [
    ['title' => '20 Min Full Body HIIT', 'video' => 'https://www.youtube.com/embed/cbKkB3OAOWs'],
    ['title' => 'Strength & Toning', 'video' => 'https://www.youtube.com/embed/K-PwT3zKjE4'],
    ['title' => 'Fat Burn No Jumping', 'video' => 'https://www.youtube.com/embed/gC_L9qAHVJ8'],
    ['title' => 'Core & Abs Blast', 'video' => 'https://www.youtube.com/embed/1f8yoFFdkcY']
];

$meals = [
    ['name' => 'High Protein Breakfast', 'desc' => '3 Eggs + 1 Avocado. High healthy fats for energy.', 'cal' => '420 kcal'],
    ['name' => 'Lean Muscle Lunch', 'desc' => '150g Grilled Chicken + 5 spoons Brown Rice + Salad.', 'cal' => '580 kcal'],
    ['name' => 'Metabolism Snack', 'desc' => '1 scoop Raw Nuts OR 1 cup Greek Yogurt.', 'cal' => '280 kcal'],
    ['name' => 'Light Recovery Dinner', 'desc' => '1 can Tuna (drained) OR 1 cup Cottage Cheese + Greens.', 'cal' => '350 kcal']
];

$plan_data = [];
for ($i = 1; $i <= 30; $i++) {
    $workout = $workouts[$i % count($workouts)];
    $meal = $meals[$i % count($meals)];
    
    if ($i % 7 == 0) {
        $plan_data[$i] = [
            'type' => 'rest',
            'title' => 'Rest & Recovery',
            'desc' => 'Focus on mobility, light walking, and 7-8 hours of sleep.',
            'meal' => $meals[3] 
        ];
    } else {
        $plan_data[$i] = [
            'type' => 'active',
            'title' => 'Day ' . $i,
            'video' => $workout['video'],
            'workout_title' => $workout['title'],
            'meal' => $meal
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>30-Day Fat Burn Fast-Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FDF2F7; color: #1e293b; }
        .day-card { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); border: 1px solid rgba(236, 72, 153, 0.1); }
        .day-card:hover { transform: translateY(-8px); box-shadow: 0 25px 50px -12px rgba(236, 72, 153, 0.15); }
        .check-circle { transition: all 0.3s ease; cursor: pointer; }
        .completed { border-color: #ec4899 !important; background-color: #fff !important; }
        .completed .check-circle { background-color: #ec4899; border-color: #ec4899; color: white; }
        .completed .video-container, .completed .meal-box { opacity: 0.5; filter: grayscale(0.5); }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="min-h-screen pb-20">

    <?php include 'navbar.php'; ?>

    <header class="max-w-7xl mx-auto px-6 py-16 text-center">
        <h1 class="text-5xl lg:text-6xl font-extrabold text-slate-900 mb-6 tracking-tight">
            30-Day <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500">Fast-Track Burn</span>
        </h1>
        <p class="text-slate-500 max-w-2xl mx-auto text-lg">Master your metabolism with our high-intensity 30-day program. Track your progress daily, eat for fuel, and transform your body.</p>
        
        <div class="flex flex-wrap justify-center gap-4 mt-10">
            <button onclick="window.print()" class="no-print bg-white text-pink-500 border-2 border-pink-500 font-bold py-3 px-8 rounded-2xl hover:bg-pink-50 transition flex items-center gap-2">
                <iconify-icon icon="lucide:printer"></iconify-icon> Print Plan
            </button>
            
            <div class="bg-white p-6 rounded-3xl shadow-xl shadow-pink-100/50 inline-block border border-pink-50">
                <h3 class="text-xs font-black text-pink-500 uppercase tracking-[0.2em] mb-3">Overall Completion</h3>
                <div class="flex items-center gap-5">
                    <div class="w-48 md:w-64 h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div id="progress-bar" class="h-full bg-gradient-to-r from-pink-400 to-rose-500 transition-all duration-1000" style="width: <?php echo (count($completed_days) / 30) * 100; ?>%"></div>
                    </div>
                    <span id="progress-text" class="font-black text-slate-800"><?php echo count($completed_days); ?>/30 Days</span>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6">
        
        <section class="no-print bg-slate-900 p-8 rounded-[2.5rem] shadow-2xl mb-16 text-white overflow-hidden relative">
            <div class="relative z-10 grid md:grid-cols-2 gap-10 items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2 flex items-center gap-2">
                        <iconify-icon icon="lucide:calculator" class="text-pink-400"></iconify-icon> BMI Quick Checker
                    </h2>
                    <p class="text-slate-400 text-sm mb-6">Check your body mass index before you start the challenge.</p>
                    <div class="flex gap-4 mb-4">
                        <input type="number" id="bmi-weight" placeholder="Weight (kg)" class="flex-1 bg-white/10 border border-white/10 px-4 py-3 rounded-xl outline-none focus:ring-2 focus:ring-pink-500">
                        <input type="number" id="bmi-height" placeholder="Height (cm)" class="flex-1 bg-white/10 border border-white/10 px-4 py-3 rounded-xl outline-none focus:ring-2 focus:ring-pink-500">
                    </div>
                    <button onclick="calculateBMI()" class="w-full bg-pink-500 hover:bg-pink-600 font-bold py-3 rounded-xl transition">Analyze My Stats</button>
                </div>
                <div id="bmi-result-card" class="bg-white/5 p-8 rounded-3xl border border-white/10 text-center hidden backdrop-blur-md">
                    <div class="text-slate-400 text-xs uppercase tracking-widest">Your Result</div>
                    <div id="bmi-value" class="text-5xl font-black my-2">--</div>
                    <div id="bmi-status" class="inline-block px-6 py-1.5 rounded-full text-xs font-bold bg-pink-500/20 text-pink-400">--</div>
                </div>
            </div>
            <iconify-icon icon="lucide:activity" class="absolute -right-10 -bottom-10 text-[200px] text-white/5 rotate-12"></iconify-icon>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($plan_data as $day_num => $data): 
                $is_completed = in_array($day_num, $completed_days);
            ?>
            
            <div class="day-card bg-white rounded-[2.5rem] p-7 flex flex-col <?php echo $is_completed ? 'completed' : ''; ?>" id="card-day-<?php echo $day_num; ?>">
                
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <span class="inline-block px-4 py-1 rounded-full bg-pink-50 text-pink-500 text-[10px] font-black uppercase tracking-widest mb-2">Day <?php echo $day_num; ?></span>
                        <h3 class="text-xl font-extrabold text-slate-800 leading-tight"><?php echo $data['title']; ?></h3>
                    </div>
                    <div onclick="toggleDay(<?php echo $day_num; ?>)" class="check-circle w-12 h-12 rounded-2xl border-2 border-slate-100 flex items-center justify-center text-transparent hover:border-pink-500 hover:text-pink-500 transition-all">
                        <iconify-icon icon="lucide:check" class="text-2xl"></iconify-icon>
                    </div>
                </div>

                <div class="flex-grow space-y-6">
                    <?php if ($data['type'] == 'active'): ?>
                        <div class="video-container rounded-3xl overflow-hidden shadow-inner relative pt-[56.25%] bg-slate-100">
                            <iframe class="absolute top-0 left-0 w-full h-full" src="<?php echo $data['video']; ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                        <div class="flex items-center gap-3 text-sm font-bold text-slate-700">
                            <iconify-icon icon="lucide:play" class="text-pink-500 text-xl"></iconify-icon>
                            <?php echo $data['workout_title']; ?>
                        </div>
                    <?php else: ?>
                        <div class="rounded-3xl bg-gradient-to-br from-pink-50 to-rose-50 p-8 text-center border border-pink-100 flex flex-col items-center">
                            <iconify-icon icon="lucide:moon" class="text-5xl text-pink-300 mb-3"></iconify-icon>
                            <p class="text-sm font-medium text-pink-800"><?php echo $data['desc']; ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="meal-box bg-slate-50 p-5 rounded-3xl border border-slate-100 relative overflow-hidden">
                        <h4 class="text-[10px] font-black text-pink-500 uppercase tracking-widest flex items-center gap-1 mb-3">
                            <iconify-icon icon="lucide:utensils-crosshair"></iconify-icon> Performance Meal
                        </h4>
                        <p class="font-bold text-slate-800 text-sm mb-1"><?php echo $data['meal']['name']; ?></p>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4"><?php echo $data['meal']['desc']; ?></p>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 text-[10px] font-black text-slate-600 rounded-full">
                            <iconify-icon icon="lucide:flame" class="text-orange-500"></iconify-icon> <?php echo $data['meal']['cal']; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>
        </div>
    </main>

    <script>
        // AJAX Progress Toggle
        function toggleDay(dayNum) {
            const card = document.getElementById('card-day-' + dayNum);
            
            fetch(window.location.href, {
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
            });
        }

        function updateProgressBar() {
            const totalCompleted = document.querySelectorAll('.day-card.completed').length;
            const percentage = (totalCompleted / 30) * 100;
            document.getElementById('progress-text').innerText = totalCompleted + '/30 Days';
            document.getElementById('progress-bar').style.width = percentage + '%';
        }

        // BMI Calculator
        function calculateBMI() {
            const weight = parseFloat(document.getElementById('bmi-weight').value);
            const height = parseFloat(document.getElementById('bmi-height').value);
            if (!weight || !height) return alert("Please fill both fields.");
            
            const bmi = (weight / ((height / 100) ** 2)).toFixed(1);
            const resultCard = document.getElementById('bmi-result-card');
            const status = document.getElementById('bmi-status');
            
            resultCard.classList.remove('hidden');
            document.getElementById('bmi-value').innerText = bmi;
            
            if(bmi < 18.5) { status.innerText = "Underweight"; status.className = "inline-block px-6 py-1.5 rounded-full text-xs font-bold bg-yellow-500/20 text-yellow-500"; }
            else if(bmi < 25) { status.innerText = "Healthy"; status.className = "inline-block px-6 py-1.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-500"; }
            else { status.innerText = "Overweight"; status.className = "inline-block px-6 py-1.5 rounded-full text-xs font-bold bg-rose-500/20 text-rose-500"; }
        }
    </script>
</body>
</html>
<?php
include 'config.php'; 
$user_lang = $_SESSION['lang'] ?? 'en';
if ($user_lang == 'ar') {
    include 'lang_ar.php';
} else {
    include 'lang_en.php';
}

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$goal_steps = 10000;

$queryToday = "SELECT steps_count FROM steps_log WHERE user_id = '$user_id' AND log_date = '$today'";
$resultToday = mysqli_query($conn, $queryToday);
$current_steps = 0;
if ($row = mysqli_fetch_assoc($resultToday)) {
    $current_steps = intval($row['steps_count']);
}

$progress_percentage = min(($current_steps / $goal_steps) * 100, 100);

$weekly_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('D', strtotime($date)); 
    
    $queryWeek = "SELECT steps_count FROM steps_log WHERE user_id = '$user_id' AND log_date = '$date'";
    $resWeek = mysqli_query($conn, $queryWeek);
    $steps = 0;
    if ($rowW = mysqli_fetch_assoc($resWeek)) {
        $steps = intval($rowW['steps_count']);
    }
    $weekly_data[] = ['day' => $dayName, 'val' => $steps];
}
?>

<!DOCTYPE html>
<html lang="<?= $current_lang ?>" dir="<?= $lang['dir'] ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell | <?= $lang['steps_tracker.title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Outfit', sans-serif; 
            background: linear-gradient(180deg, #f5d8e5 0%, #edb5cf 100%); 
            overflow-x: hidden !important; 
            min-height: 100vh; 
        }
        .star { position: absolute; background: white; border-radius: 50%; animation: twinkle var(--d) infinite ease-in-out; z-index: 0; }
        @keyframes twinkle { 0%, 100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 1; transform: scale(1.3); } }
        
        .glass-card { 
            background: rgba(255, 255, 255, 0.75); 
            backdrop-filter: blur(12px); 
            border-radius: 40px; 
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 40px rgba(131, 24, 67, 0.1);
        }
        
        .bar-gradient {
            background: linear-gradient(180deg, #ec4899 0%, #be185d 100%);
            box-shadow: 0 4px 10px rgba(236, 72, 153, 0.3);
            transition: height 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .progress-circle-bg { 
            stroke: rgba(255, 255, 255, 0.3); 
        }
        .progress-circle-main { 
            stroke: #ec4899;
            transition: stroke-dashoffset 1s ease-in-out; 
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <?php for($i=0; $i<40; $i++): $d = rand(2, 5); ?>
        <div class="star" style="width:3px; height:3px; top:<?=rand(0,100)?>%; left:<?=rand(0,100)?>%; --d:<?=$d?>s; animation-delay:<?=rand(0,5)?>s;"></div>
    <?php endfor; ?>

    <main class="max-w-6xl mx-auto px-6 py-10 relative z-10">
        <header class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-[#be185d] tracking-tight"><?= $lang['steps_tracker.title'] ?></h1>
            <p class="text-[#be185d] font-medium text-sm mt-2"><?= $lang['steps_tracker.track_daily'] ?></p>
        </header>

        <div class="flex flex-col lg:flex-row items-start justify-center gap-10">
            
            <div class="w-full lg:w-1/2 flex flex-col items-center">
                <div class="relative w-72 h-72 flex items-center justify-center">
                    <svg class="w-full h-full -rotate-90" viewBox="0 0 200 200">
                        <circle class="progress-circle-bg" stroke-width="12" cx="100" cy="100" r="85" fill="transparent"></circle>
                        <circle id="progressCircle" class="progress-circle-main" stroke-width="14" stroke-linecap="round" cx="100" cy="100" r="85" fill="transparent" 
                                stroke-dasharray="534" stroke-dashoffset="<?= 534 - (534 * $progress_percentage / 100) ?>"></circle>
                    </svg>

                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span id="currentStepsDisplay" class="text-6xl font-extrabold text-[#be185d]"><?= _n(number_format($current_steps)) ?></span>
                        <span class="text-lg font-semibold text-[#be185d]"><?= $lang['steps_tracker.steps_today'] ?></span>
                        
                        <div class="mt-4 bg-white/40 backdrop-blur-md px-4 py-2 rounded-full border border-white/50 text-sm font-bold text-[#be185d]">
                            <?= $lang['steps_tracker.goal'] ?><?= _n(number_format($goal_steps)) ?><?= $lang['steps_tracker.steps'] ?>
                        </div>
                    </div>
                </div>

                <div id="progressText" class="mt-6 flex flex-col items-center text-[#be185d]">
                    <p class="font-extrabold text-2xl tracking-wide"><?= _n(number_format($current_steps)) ?> / <?= _n(number_format($goal_steps)) ?></p>
                    <p class="font-bold text-lg opacity-90"><?= _n(round($progress_percentage)) ?>%<?= $lang['steps_tracker.completed'] ?></p>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-8 w-full max-w-sm">
                    <button onclick="syncSteps()" class="px-8 py-4 bg-pink-600 hover:bg-pink-700 text-white rounded-3xl font-bold shadow-lg shadow-pink-200 transition-all active:scale-95">
                         <?= $lang['steps_tracker.sync_steps'] ?>
                    </button>
                    <button id="addStepsBtn" onclick="addStepsSession()" disabled class="px-8 py-4 bg-white/60 border-2 border-white/50 text-[#be185d] rounded-3xl font-bold disabled:opacity-50 cursor-not-allowed transition-all">
                        <?= $lang['steps_tracker.add_steps'] ?>
                    </button>
                </div>
                <p id="statusMsg" class="text-center text-sm font-bold text-[#be185d]/80 mt-4 hidden"></p>
            </div>

            <div class="w-full lg:w-1/2">
                <div class="glass-card px-6 pt-8 pb-12">
                    <h3 class="text-center text-xl font-extrabold text-[#be185d] mb-8 tracking-tight"><?= $lang['steps_tracker.steps_this_week'] ?></h3>
                    
                    <div class="relative h-64 pl-8 pt-6"> 
                        <div class="absolute inset-x-0 top-6 bottom-0 pl-8 flex flex-col justify-between pointer-events-none">
                            <?php 
                            $y_labels = [
                                $lang['chart.10k'], 
                                $lang['chart.8k'], 
                                $lang['chart.6k'], 
                                $lang['chart.4k'], 
                                $lang['chart.2k'], 
                                $lang['chart.0']
                            ];
                            foreach($y_labels as $label): ?>
                            <div class="relative w-full h-[1px] bg-white/30">
                                <span class="absolute -left-9 top-1/2 -translate-y-1/2 text-[10px] font-bold text-pink-700/60 w-8 text-right"><?= $label ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="relative z-10 w-full h-full flex justify-between px-1">
                            <?php foreach($weekly_data as $i_day => $data): 
                                $maxStepsForChart = max($goal_steps, $current_steps);
                                $percentageForHeight = ($data['val'] / $maxStepsForChart) * 100;
                                $percentageForHeight = max(2, min($percentageForHeight, 100));
                                $isToday = ($i_day === array_key_last($weekly_data));
                            ?>
                            <div class="flex-1 h-full relative flex items-center justify-center group">
                                <span <?= $isToday ? 'id="todayBarLabel"' : '' ?> class="absolute text-[9px] font-extrabold text-[#be185d] tracking-tighter" style="bottom: <?= $percentageForHeight ?>%; margin-bottom: 5px;"><?= $data['val'] > 0 ? _n(number_format($data['val'])) : '' ?></span>
                                <div <?= $isToday ? 'id="todayBar"' : '' ?> class="w-full max-w-[26px] bar-gradient rounded-t-xl absolute bottom-0" style="height: <?= $percentageForHeight ?>%;"></div>
                                <span class="absolute top-full mt-3 text-[11px] font-extrabold text-pink-800/70 w-full text-center uppercase"><?= $lang[$data['day']] ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Single source of truth — no separate displaySteps
        let totalSteps = <?= $current_steps ?>;
        const goalSteps = <?= $goal_steps ?>;
        let goalCelebrated = <?= ($current_steps >= $goal_steps) ? 'true' : 'false' ?>;

        const locale = '<?= $current_lang === 'ar' ? 'ar-EG' : 'en-US' ?>';
        const nf = new Intl.NumberFormat(locale);

        function updateUI() {
            // Progress % is capped at 100 for the ring — but we show real total in text
            let progress_percentage = Math.min((totalSteps / goalSteps) * 100, 100);

            document.getElementById('currentStepsDisplay').innerText = nf.format(totalSteps);

            document.getElementById('progressText').innerHTML = `
                <p class="font-extrabold text-2xl tracking-wide">${nf.format(totalSteps)} / ${nf.format(goalSteps)}</p>
                <p class="font-bold text-lg opacity-90">${nf.format(Math.round(progress_percentage))}%<?= addslashes($lang['steps_tracker.completed']) ?></p>
            `;

            // Ring stays full once goal is reached
            let dashOffset = 534 - (534 * progress_percentage / 100);
            document.getElementById('progressCircle').style.strokeDashoffset = dashOffset;

            // Enable the Add Steps button once goal is hit
            let addBtn = document.getElementById('addStepsBtn');
            if (totalSteps >= goalSteps) {
                addBtn.disabled = false;
                addBtn.classList.remove('opacity-50', 'cursor-not-allowed');

                // Celebrate once
                if (!goalCelebrated) {
                    goalCelebrated = true;
                    showGoalBanner();
                }
            } else {
                addBtn.disabled = true;
                addBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        function showGoalBanner() {
            let banner = document.createElement('div');
            banner.id = 'goalBanner';
            banner.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-pink-600 text-white font-extrabold text-center px-6 py-3 rounded-2xl shadow-xl text-sm animate-bounce';
            banner.innerText = '<?= addslashes($lang['steps_tracker.goal_reached']) ?>';
            document.body.appendChild(banner);
            setTimeout(() => banner.remove(), 3500);
        }

        // "Add Steps" clicked after goal — sensor already running, just confirm to user
        function addStepsSession() {
            alert('<?= addslashes($lang['steps_tracker.keep_walking']) ?>');
        }

        // Sync steps to DB — then dynamically update chart without page reload
        function syncSteps() {
            const formData = new FormData();
            formData.append('steps', totalSteps);

            fetch('save_steps.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    updateChartToday(totalSteps);
                    showToast('✅ ' + nf.format(totalSteps) + '<?= addslashes($lang['steps_tracker.steps_synced']) ?>');
                } else {
                    showToast('<?= addslashes($lang['steps_tracker.error']) ?>' + (data.message || '<?= addslashes($lang['steps_tracker.unknown_error']) ?>'), true);
                }
            })
            .catch(err => {
                console.error('Sync error:', err);
                showToast('<?= addslashes($lang['steps_tracker.failed_server']) ?>', true);
            });
        }

        // Dynamically update today's chart bar without page reload
        function updateChartToday(newSteps) {
            const bar = document.getElementById('todayBar');
            const label = document.getElementById('todayBarLabel');
            if (!bar || !label) return;

            const maxSteps = Math.max(goalSteps, newSteps);
            let newPct = (newSteps / maxSteps) * 100;
            newPct = Math.max(2, Math.min(newPct, 100));

            bar.style.height = newPct + '%';
            label.style.bottom = newPct + '%';
            label.innerText = nf.format(newSteps);
        }

        // Non-blocking toast notification
        function showToast(message, isError = false) {
            let existing = document.getElementById('syncToast');
            if (existing) existing.remove();

            let toast = document.createElement('div');
            toast.id = 'syncToast';
            toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-6 py-3 rounded-2xl shadow-xl font-bold text-sm text-white transition-all duration-500 opacity-0';
            toast.style.background = isError ? '#dc2626' : '#be185d';
            toast.innerText = message;
            document.body.appendChild(toast);

            // Fade in
            requestAnimationFrame(() => {
                requestAnimationFrame(() => { toast.style.opacity = '1'; });
            });

            // Fade out and remove after 3s
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        function handleMotion(event) {
            let acc = event.accelerationIncludingGravity;
            if (!acc || acc.x === null) return;

            let magnitude = Math.sqrt(acc.x**2 + acc.y**2 + acc.z**2);
            let currentTime = new Date().getTime();

            // Step spike detection — always accumulate regardless of goal
            if (magnitude > 12 && (currentTime - (window.lastStepTime || 0) > 350)) {
                totalSteps++;
                window.lastStepTime = currentTime;
                updateUI();
            }
        }

        window.addEventListener('load', () => {
            // iOS 13+ requires permission from a user gesture
            if (typeof DeviceMotionEvent !== 'undefined' && typeof DeviceMotionEvent.requestPermission === 'function') {
                // Show a tap-to-enable message
                let msg = document.getElementById('statusMsg');
                if (msg) {
                    msg.innerText = '<?= addslashes($lang['steps_tracker.ios_tap']) ?>';
                    msg.classList.remove('hidden');
                }
                document.body.addEventListener('click', () => {
                    DeviceMotionEvent.requestPermission().then(state => {
                        if (state === 'granted') {
                            window.addEventListener('devicemotion', handleMotion);
                            if (msg) msg.classList.add('hidden');
                        } else {
                            if (msg) msg.innerText = '<?= addslashes($lang['steps_tracker.mobile_only']) ?>';
                        }
                    });
                }, { once: true });
            } else if (typeof DeviceMotionEvent !== 'undefined') {
                // Android / non-iOS — start immediately
                window.addEventListener('devicemotion', handleMotion);
            } else {
                let msg = document.getElementById('statusMsg');
                if (msg) {
                    msg.innerText = '<?= addslashes($lang['steps_tracker.mobile_only']) ?>';
                    msg.classList.remove('hidden');
                }
            }

            updateUI();
        });
    </script>
</body>
</html>
<?php
/**
 * صفحة التمارين المتكاملة - GlowWell
 * تدعم الترجمة الكاملة للأسماء، الخطوات، المؤقت، والرسائل دون المساس بقاعدة البيانات.
 */
include 'config.php';
require_once 'auth_check.php';

// 1. نظام إدارة اللغات
$user_lang = $_SESSION['lang'] ?? 'en';
if ($user_lang == 'ar') {
    include_once 'lang_ar.php';
} else {
    include_once 'lang_en.php';
}
$dir = $lang['dir'] ?? 'ltr';

$user_id = $_SESSION['user_id'];
$user_res = mysqli_query($conn, "SELECT health_goal FROM users WHERE id = '" . mysqli_real_escape_string($conn, (string) $user_id) . "'");
$user = mysqli_fetch_assoc($user_res);

// تحديد الهدف للعرض
$raw_goal = $user['health_goal'] ?? 'Build Muscle';
$goal_display = $raw_goal;
if ($user_lang == 'ar') {
    if ($raw_goal == 'Lose Weight') $goal_display = "خسارة الوزن";
    elseif ($raw_goal == 'Gain Weight') $goal_display = "زيادة الوزن";
    elseif (strpos($raw_goal, 'Muscle') !== false) $goal_display = "بناء العضلات";
    elseif ($raw_goal == 'General Fitness') $goal_display = "اللياقة العامة";
}

$goal_esc = mysqli_real_escape_string($conn, $raw_goal);
// جلب التمارين من قاعدة البيانات (ستأتي باللغة الإنجليزية كما هي)
$exercises = mysqli_query($conn, "SELECT * FROM workouts WHERE category = '$goal_esc'");

// ==========================================
// قاموس ترجمة التمارين وخطواتها (ترجمة فورية للواجهة فقط)
// ==========================================
$workout_dict = [
    'Pushups' => [
        'name_ar' => 'تمرين الضغط',
        'steps_ar' => 'ضع يديك على الأرض بعرض الكتفين|انزل بجسمك حتى يقترب صدرك من الأرض|ادفع جسمك للأعلى للعودة للبداية'
    ],
    'Squats' => [
        'name_ar' => 'سكوات (القرفصاء)',
        'steps_ar' => 'قف مع المباعدة بين قدميك|اثنِ ركبتيك كأنك تجلس على كرسي|حافظ على استقامة ظهرك|عد إلى وضع الوقوف'
    ],
    'Plank' => [
        'name_ar' => 'تمرين البلانك',
        'steps_ar' => 'استند على ساعديك وأصابع قدميك|حافظ على استقامة جسمك كلوح خشبي|شد عضلات بطنك وابقى ثابتاً'
    ],
    'Jumping Jacks' => [
        'name_ar' => 'قفز مع فتح القدمين',
        'steps_ar' => 'قف مستقيماً مع ضم القدمين|اقفز وافتح قدميك وارفع ذراعيك|اقفز للعودة لوضع البداية'
    ],
    'Crunches' => [
        'name_ar' => 'تمرين طحن البطن',
        'steps_ar' => 'استلقِ على ظهرك واثنِ ركبتيك|ضع يديك خلف رأسك|ارفع كتفيك عن الأرض بتقلص عضلات البطن|انزل ببطء'
    ],
    'Lunges' => [
        'name_ar' => 'تمرين الاندفاع (الطعن)',
        'steps_ar' => 'خذ خطوة للأمام بقدم واحدة|انزل حتى تشكل ركبتاك زاوية 90 درجة|ادفع للعودة للبداية وبدل القدمين'
    ],
    'Burpees' => [
        'name_ar' => 'تمرين بيربي',
        'steps_ar' => 'انزل لوضع القرفصاء وضع يديك على الأرض|اقفز بقدميك للخلف لوضع البلانك|قم بتمرين ضغط واحد|اقفز بقدميك للأمام ثم اقفز عالياً'
    ],
    'High Knees' => [
        'name_ar' => 'رفع الركبتين',
        'steps_ar' => 'قف مستقيماً|ارفع ركبتك اليمنى نحو صدرك|بدل بسرعة مع الركبة اليسرى كأنك تركض بمكانك'
    ],
    'Mountain Climbers' => [
        'name_ar' => 'تسلق الجبل',
        'steps_ar' => 'ابدأ بوضع البلانك|اسحب ركبتك اليمنى نحو صدرك|بدل بسرعة بين القدمين باستمرار'
    ],
    'Glute Bridges' => [
        'name_ar' => 'جسر الأرداف',
        'steps_ar' => 'استلقِ على ظهرك مع ثني الركبتين|ارفع حوضك للأعلى حتى يستقيم جسمك|شد عضلات الأرداف وانزل ببطء'
    ],
    'Pull-ups' => [
        'name_ar' => 'تمرين العقلة',
        'steps_ar' => 'أمسك البار بيديك أوسع من كتفيك|اسحب جسمك للأعلى حتى يعبر ذقنك البار|انزل ببطء وتحكم'
    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo $user_lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell | <?php echo $lang['workouts'] ?? 'Workouts'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&family=Cairo:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #FCEEF4; 
            margin: 0; 
            font-family: <?php echo $user_lang == 'ar' ? "'Cairo', sans-serif" : "'Poppins', sans-serif"; ?>; 
        }
        .workout-card { transition: all 0.25s ease; }
        .workout-card:hover { transform: translateY(-4px); }
        /* تحسينات الاتجاه */
        [dir="rtl"] .text-left { text-align: right; }
        [dir="rtl"] .right-8 { right: auto; left: 2rem; }
    </style>
</head>
<body class="min-h-screen">
<?php include 'navbar.php'; ?>

<div class="max-w-6xl mx-auto px-6 mt-10 pb-16">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-extrabold text-gray-800">
            <?php echo $lang['your_workout_plan'] ?? 'Your Workout Plan'; ?>
        </h1>
        <p class="text-gray-500 mt-2"><?php echo htmlspecialchars($goal_display); ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <a href="ai_workouts.php" class="workout-card bg-white p-8 rounded-[40px] shadow-sm hover:shadow-xl cursor-pointer text-center no-underline block text-inherit border border-pink-100">
            <div class="text-6xl mb-4">🤖</div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo $lang['ai_workout_plan'] ?? 'AI Workout Plan'; ?></h3>
            <p class="text-pink-400 font-bold mt-2">Gemini AI</p>
            <span class="mt-6 inline-block px-8 py-3 bg-pink-500 text-white rounded-full font-bold"><?php echo $lang['open_btn'] ?? 'Open'; ?></span>
        </a>

        <?php while ($row = mysqli_fetch_assoc($exercises)): 
            $db_name = trim($row['name']);
            $db_steps = trim($row['steps']);
            
            $ex_name = $db_name;
            $ex_steps = $db_steps;

            // استبدال الاسم والخطوات بالعربي إذا كانت لغة المستخدم عربية وموجودة في القاموس
            if ($user_lang == 'ar' && isset($workout_dict[$db_name])) {
                $ex_name = $workout_dict[$db_name]['name_ar'];
                $ex_steps = $workout_dict[$db_name]['steps_ar'];
            }
        ?>
        <div class="workout-card bg-white p-8 rounded-[40px] shadow-sm hover:shadow-xl cursor-pointer text-center border border-pink-50"
             onclick="openTrainer('<?php echo addslashes($ex_name); ?>', '<?php echo addslashes($ex_steps); ?>', <?php echo (int) $row['duration_seconds']; ?>, '<?php echo addslashes($row['icon']); ?>', '<?php echo addslashes($db_name); ?>')">
            <div class="text-6xl mb-4"><?php echo htmlspecialchars($row['icon']); ?></div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($ex_name); ?></h3>
            <p class="text-pink-400 font-bold mt-2"><?php echo (int) $row['duration_seconds']; ?><?php echo $lang['seconds_abbr'] ?? 's'; ?></p>
            <button type="button" class="mt-6 px-8 py-3 bg-pink-500 text-white rounded-full font-bold"><?php echo $lang['start_btn'] ?? 'Start'; ?></button>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="trainerModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-md z-[1000] flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg p-10 rounded-[50px] relative shadow-xl">
        <button type="button" onclick="closeTrainer()" class="absolute top-8 right-8 text-gray-400 text-2xl">✕</button>
        <div class="text-center">
            <div id="m-icon" class="text-7xl mb-4"></div>
            <h2 id="m-name" class="text-3xl font-extrabold text-gray-800 mb-6"></h2>
            
            <div class="text-left mb-4 px-2">
                <span class="font-bold text-pink-600"><?php echo $lang['steps_title'] ?? 'Instructions'; ?>:</span>
            </div>
            <div id="m-steps" class="text-left space-y-4 mb-8 bg-pink-50 p-6 rounded-[30px] max-h-48 overflow-y-auto"></div>

            <div class="mb-8 relative flex items-center justify-center">
                <svg class="w-32 h-32 transform -rotate-90">
                    <circle class="stroke-gray-100 stroke-[5] fill-none" cx="64" cy="64" r="60"></circle>
                    <circle id="timerCircle" class="stroke-pink-500 stroke-[5] fill-none transition-all duration-1000" cx="64" cy="64" r="60" stroke-dasharray="377" stroke-dashoffset="0"></circle>
                </svg>
                <span id="timerText" class="absolute text-4xl font-bold">00</span>
            </div>
            
            <button id="startBtn" type="button" onclick="startTimer()" class="w-full bg-pink-500 text-white py-4 rounded-3xl font-bold text-xl shadow-lg">
                <?php echo $lang['start_timer'] ?? 'Start Timer'; ?>
            </button>
            <button id="doneBtn" type="button" onclick="completeWorkout()" class="hidden w-full bg-green-500 text-white py-4 rounded-3xl font-bold text-xl">
                <?php echo $lang['well_done'] ?? 'Well Done! ✓'; ?>
            </button>
        </div>
    </div>
</div>

<script>
let timer, timeLeft, totalTime;
let currentDbName = ''; // المتغير الذي سيحفظ الاسم الإنجليزي الأصلي لرفعه لقاعدة البيانات
const userLang = "<?php echo $user_lang; ?>";

function openTrainer(name, steps, duration, icon, dbName) {
    document.getElementById('m-name').innerText = name;
    document.getElementById('m-icon').innerText = icon;
    document.getElementById('timerText').innerText = duration;
    
    // حفظ الاسم الأصلي لتمريره لاحقاً لملف الحفظ
    currentDbName = dbName;
    
    // معالجة الخطوات وفصلها بالرمز |
    const stepsArray = steps.split('|');
    document.getElementById('m-steps').innerHTML = stepsArray.map(s => `<p class='text-gray-700 font-medium'>• ${s.trim()}</p>`).join('');
    
    timeLeft = duration;
    totalTime = duration;
    updateCircle(0);

    document.getElementById('trainerModal').classList.remove('hidden');
    document.getElementById('startBtn').classList.remove('hidden');
    document.getElementById('doneBtn').classList.add('hidden');
}

function startTimer() {
    document.getElementById('startBtn').classList.add('hidden');
    timer = setInterval(() => {
        timeLeft--;
        document.getElementById('timerText').innerText = timeLeft;
        
        let offset = ((totalTime - timeLeft) / totalTime) * 377;
        updateCircle(offset);

        if (timeLeft <= 0) {
            clearInterval(timer);
            document.getElementById('doneBtn').classList.remove('hidden');
            document.getElementById('timerText').innerText = "💪";
        }
    }, 1000);
}

function updateCircle(offset) {
    const circle = document.getElementById('timerCircle');
    if(circle) circle.style.strokeDashoffset = offset;
}

function closeTrainer() {
    clearInterval(timer);
    document.getElementById('trainerModal').classList.add('hidden');
}

function completeWorkout() {
    // نستخدم currentDbName (الاسم الإنجليزي الأصلي) ليتم إرساله للقاعدة
    const workoutName = currentDbName; 
    const duration = totalTime; 

    const formData = new URLSearchParams();
    formData.append('workout_name', workoutName);
    formData.append('duration', duration);

    fetch('save_workout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(r => r.text())
    .then(data => {
        if(data.trim() === "success") {
            window.location.href = 'progress.php';
        } else {
            const errText = (userLang === 'ar') ? "حدث خطأ أثناء الحفظ: " : "Save error: ";
            alert(errText + data);
        }
    })
    .catch(err => {
        const connText = (userLang === 'ar') ? "فشل الاتصال بالسيرفر" : "Connection failed";
        alert(connText);
    });
}
</script>
</body>
</html>
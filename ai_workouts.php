<?php
/**
 * عملي (مهمة AI): اقتراح تمارين عبر Google Gemini + خطة بديلة عند فشل الـ API.
 * يقرأ gemini_api_key.php ويستخدم بيانات المستخدم من جدول users.
 */
include 'config.php';
require_once 'auth_check.php';

// استدعاء ملفات اللغة
$user_lang = $_SESSION['lang'] ?? 'en';
if ($user_lang == 'ar') {
    include_once 'lang_ar.php';
} else {
    include_once 'lang_en.php';
}
if (!isset($lang['dir'])) $lang['dir'] = 'ltr';

$user_id = (int) $_SESSION['user_id'];
$user_row = null;
$user_q = mysqli_query($conn, "SELECT age, weight, height, gender, health_goal, activity_level, goal FROM users WHERE id = $user_id");
if ($user_q && $row = mysqli_fetch_assoc($user_q)) {
    $user_row = $row;
}

$ai_title = null;
$ai_items = null;
$ai_error = null;
$submitted_goal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goal'])) {
    $submitted_goal = trim($_POST['goal']);
    $allowed = ['lose', 'gain', 'muscle'];
    if (in_array($submitted_goal, $allowed, true)) {
        if (!function_exists('curl_init')) {
            $ai_error = 'cURL is not enabled on the server.';
        } else {
            require_once __DIR__ . '/gemini_api_key.php';
            $key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
            if ($key === '' || $key === 'YOUR_GEMINI_API_KEY_HERE') {
                $ai_error = 'Gemini API key is not set in gemini_api_key.php';
            } else {
                $goal_labels = [
                    'lose' => 'weight loss',
                    'gain' => 'weight gain',
                    'muscle' => 'muscle building',
                ];
                $label = $goal_labels[$submitted_goal];

                $context_parts = [];
                if ($user_row) {
                    if (!empty($user_row['age'])) {
                        $context_parts[] = 'Age: ' . (int) $user_row['age'];
                    }
                    if (!empty($user_row['weight'])) {
                        $context_parts[] = 'Weight: ' . floatval($user_row['weight']) . ' kg';
                    }
                    if (!empty($user_row['height'])) {
                        $context_parts[] = 'Height: ' . floatval($user_row['height']) . ' cm';
                    }
                    if (!empty($user_row['gender'])) {
                        $context_parts[] = 'Gender: ' . trim($user_row['gender']);
                    }
                    if (!empty($user_row['activity_level'])) {
                        $context_parts[] = 'Activity: ' . $user_row['activity_level'];
                    }
                    if (!empty($user_row['health_goal'])) {
                        $context_parts[] = 'Goal: ' . $user_row['health_goal'];
                    }
                }
                $context_line = !empty($context_parts)
                    ? 'User profile: ' . implode(', ', $context_parts) . ".\n\n"
                    : '';

                // هنا عدلت السطر بذكاء: سيطلب من الـ AI الرد باللغة العربية إذا كانت لغة الموقع عربية!
                $requested_language = ($user_lang == 'ar') ? "Arabic" : "English";
                $prompt = $context_line . "You are a professional fitness coach. Weekly workout plan for: {$label}. Include training days, exercise types, duration or sets, nutrition and sleep tips. Output 8–12 lines, one clear bullet per line, in {$requested_language} language only.";

                $payload = [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.6, 'maxOutputTokens' => 2048],
                ];
                $headers = ['Content-Type: application/json', 'x-goog-api-key: ' . $key];
                $models_to_try = ['gemini-2.5-flash', 'gemini-2.5-flash-lite', 'gemini-2.0-flash', 'gemini-flash-latest'];
                $response = null;
                $http = 0;
                foreach ($models_to_try as $model) {
                    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent");
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_HTTPHEADER => $headers,
                        CURLOPT_POSTFIELDS => json_encode($payload),
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_TIMEOUT => 30,
                    ]);
                    $response = curl_exec($ch);
                    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($http === 200) {
                        break;
                    }
                }
                if ($http === 200 && $response) {
                    $data = json_decode($response, true);
                    $text = '';
                    if (!empty($data['candidates'][0]['content']['parts'][0]['text'])) {
                        $text = trim($data['candidates'][0]['content']['parts'][0]['text']);
                    }
                    if ($text !== '') {
                        $lines = preg_split('/\r\n|\r|\n/', $text);
                        $items = [];
                        foreach ($lines as $line) {
                            $line = trim(preg_replace('/^[\-\*•]\s*/', '', $line));
                            if ($line !== '') {
                                $items[] = $line;
                            }
                        }
                        if (!empty($items)) {
                            // الترجمة التلقائية لعنوان الـ AI
                            $ai_title = ($user_lang == 'ar' ? 'خطة أسبوعية لـ ' : 'Weekly plan for ') . ($lang['goal_' . str_replace(' ', '_', $label)] ?? $label);
                            $ai_items = array_slice($items, 0, 14);
                        } else {
                            $ai_error = 'Empty AI response';
                        }
                    } else {
                        $ai_error = 'Empty AI response';
                    }
                } else {
                    $err = $response ? json_decode($response, true) : [];
                    $ai_error = isset($err['error']['message']) ? $err['error']['message'] : ('HTTP ' . $http);
                }
            }
        }
    }
}

// الخطط البديلة في حال تعطل الـ API
$static = [
    'lose' => [
        'title' => $user_lang == 'ar' ? 'تمارين خسارة الوزن' : 'Weight loss workouts',
        'items' => $user_lang == 'ar' ? ['كارديو + مقاومة خفيفة.', '3-4 أيام كارديو + يومين قوة.', '2-3 مجموعات، 12-15 تكرار.', '30-45 دقيقة كارديو.', 'عجز بالسعرات وشرب ماء وفير.'] : ['Cardio + light resistance.', '3–4 cardio days + 2 strength days.', '2–3 sets, 12–15 reps.', '30–45 min cardio.', 'Calorie deficit and stay hydrated.'],
    ],
    'gain' => [
        'title' => $user_lang == 'ar' ? 'تمارين زيادة الوزن' : 'Weight gain workouts',
        'items' => $user_lang == 'ar' ? ['تمارين مركبة + فائض سعرات.', '3-4 أيام رفع أثقال أسبوعياً.', '3-4 مجموعات، 8-12 تكرار.', 'بروتين وكربوهيدرات كافية.', 'كارديو خفيف مرتين أسبوعياً.'] : ['Compound lifts + caloric surplus.', '3–4 lifting days per week.', '3–4 sets, 8–12 reps.', 'Enough protein and carbs.', 'Light cardio twice a week.'],
    ],
    'muscle' => [
        'title' => $user_lang == 'ar' ? 'بناء العضلات' : 'Muscle building',
        'items' => $user_lang == 'ar' ? ['تقسيم تدريب العضلات.', '4-5 أيام تدريب أسبوعياً.', '3-4 مجموعات، 8-12 تكرار.', 'بروتين ~1.6-2 جم/كجم من الوزن.', 'نوم 7-8 ساعات.'] : ['Body-part split routine.', '4–5 training days per week.', '3–4 sets, 8–12 reps.', 'Protein ~1.6–2 g/kg body weight.', 'Sleep 7–8 hours.'],
    ],
];
?>
<!doctype html>
<html lang="<?php echo $user_lang == 'ar' ? 'ar' : 'en'; ?>" dir="<?php echo $lang['dir']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
    <title>GlowWell — AI Workouts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #FCEEF4; 
            margin: 0; 
            font-family: <?php echo $user_lang == 'ar' ? "'Cairo', sans-serif" : "'Poppins', sans-serif"; ?>; 
        }
        .ai-wrapper { max-width: 900px; margin: 2rem auto; padding: 1.5rem; }
        .ai-card { background: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #FCE7F3; }
        .ai-header { padding: 1.5rem 2rem; border-bottom: 1px solid #FDE2F3; display: flex; align-items: center; gap: 1rem; }
        .ai-icon { width: 46px; height: 46px; border-radius: 16px; background: #fdf2ff; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .goal-select { width: 100%; max-width: 320px; padding: 14px 18px; border: 1px solid #FCE7F3; border-radius: 16px; background: #FFF5F8; font-size: 1rem; }
        .btn-suggest { background: #ec4899; color: white; font-weight: 700; padding: 14px 32px; border-radius: 16px; border: none; cursor: pointer; }
        .btn-suggest:hover { background: #db2777; }
        .result-box { margin-top: 2rem; padding: 1.5rem 2rem; background: #FDF2F8; border-radius: 20px; border: 1px solid #FCE7F3; }
        .result-box.hidden { display: none; }
        .result-list { list-style: none; padding: 0; margin: 0; }
        .result-list li { padding: 0.5rem 0; border-bottom: 1px solid #FBCFE8; }
        .result-list li:last-child { border-bottom: none; }
        
        /* دعم الاتجاه للـ RTL */
        [dir="rtl"] .ai-header { flex-direction: row-reverse; text-align: right; }
        [dir="rtl"] .text-left { text-align: right; }
        [dir="rtl"] .result-list li { text-align: right; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="ai-wrapper">
    <div class="ai-card">
        <div class="ai-header">
            <div class="ai-icon">💪</div>
            <div>
                <div class="text-xl font-bold text-gray-800"><?php echo $lang['workout_suggestions'] ?? 'Workout suggestions'; ?></div>
                <div class="text-sm text-gray-500"><?php echo $lang['choose_goal_ai'] ?? 'Choose your goal and get an AI plan (Gemini).'; ?></div>
            </div>
        </div>
        <div class="p-8">
            <form method="post" id="suggestForm">
                <label class="block text-gray-700 font-semibold mb-2 text-<?php echo $user_lang == 'ar' ? 'right' : 'left'; ?>"><?php echo $lang['goal_label'] ?? 'Goal'; ?></label>
                <div class="flex flex-col md:flex-row gap-4 items-start <?php echo $user_lang == 'ar' ? 'md:flex-row-reverse' : ''; ?>">
                    <select name="goal" class="goal-select" required>
                        <option value=""><?php echo $lang['choose_option'] ?? '— Choose —'; ?></option>
                        <option value="lose" <?php echo $submitted_goal === 'lose' ? 'selected' : ''; ?>><?php echo $lang['goal_weight_loss'] ?? 'Weight loss'; ?></option>
                        <option value="gain" <?php echo $submitted_goal === 'gain' ? 'selected' : ''; ?>><?php echo $lang['goal_muscle_gain'] ?? 'Weight gain'; ?></option>
                        <option value="muscle" <?php echo $submitted_goal === 'muscle' ? 'selected' : ''; ?>><?php echo $lang['goal_muscle_gain'] ?? 'Muscle building'; ?></option>
                    </select>
                    <button type="submit" name="suggest" class="btn-suggest w-full md:w-auto"><?php echo $lang['suggest_btn'] ?? 'Suggest'; ?></button>
                </div>
            </form>

            <div id="resultBox" class="result-box <?php echo ($ai_title !== null || $ai_error !== null) ? '' : 'hidden'; ?>">
                <?php if ($ai_title !== null && !empty($ai_items)): ?>
                    <h3 class="text-lg font-bold text-pink-900 mb-2 text-<?php echo $user_lang == 'ar' ? 'right' : 'left'; ?>"><?php echo htmlspecialchars($ai_title); ?></h3>
                    <p class="text-xs text-pink-600 mb-3 text-<?php echo $user_lang == 'ar' ? 'right' : 'left'; ?>"><?php echo $lang['from_gemini'] ?? 'From Gemini'; ?></p>
                    <ul class="result-list">
                        <?php foreach ($ai_items as $item): ?>
                            <li class="text-gray-800">• <?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php elseif ($ai_error !== null && in_array($submitted_goal, ['lose', 'gain', 'muscle'], true)):
                    $s = $static[$submitted_goal];
                ?>
                    <h3 class="text-lg font-bold text-amber-800 mb-2 text-<?php echo $user_lang == 'ar' ? 'right' : 'left'; ?>"><?php echo $lang['fallback_plan'] ?? 'Fallback plan:'; ?> <?php echo htmlspecialchars($s['title']); ?></h3>
                    <p class="text-xs text-gray-600 mb-3 text-<?php echo $user_lang == 'ar' ? 'right' : 'left'; ?>"><?php echo htmlspecialchars($ai_error); ?></p>
                    <ul class="result-list">
                        <?php foreach ($s['items'] as $item): ?>
                            <li class="text-gray-800">• <?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
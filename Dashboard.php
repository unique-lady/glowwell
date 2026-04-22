<?php
include 'config.php';
// تأكدي أن السيرفر يعرف اللغة (بما أن navbar.php يُستدعى لاحقاً، يفضل التأكد من اللغة هنا أيضاً)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$current_lang = $_SESSION['lang'] ?? 'en';
include_once ($current_lang === 'ar') ? 'lang_ar.php' : 'lang_en.php';

$user_name = "User"; 
$user_emoji = "👤"; 

if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $query = "SELECT fullname, gender FROM users WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        if(!empty($row['fullname'])) {
            $parts = explode(' ', trim($row['fullname']));
            $user_name = htmlspecialchars($parts[0]);
        }
        $gender = strtolower(trim($row['gender']));
        if (strpos($gender, 'mal') !== false) { $user_emoji = "👨🏻"; }
        elseif (strpos($gender, 'fem') !== false) { $user_emoji = "👩🏻"; }
    }
}
?>
<!doctype html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $lang['dir']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #FCEEF4; margin: 0; }
        .stat-card { background: white; border-radius: 24px; transition: all 0.3s ease; height: 180px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .icon-bg { width: 60px; height: 60px; border-radius: 18px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 28px; }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navbar.php'; ?>
    
    <main class="max-w-6xl mx-auto px-8 py-12 <?php echo ($current_lang === 'ar') ? 'text-right' : 'text-left'; ?>">
        <div class="mb-10">
            <h1 class="text-5xl font-bold text-gray-800 mb-2">
                <?php echo $lang['welcome']; ?><span class="text-pink-500"><?php echo $user_name; ?></span>
            </h1>
            <p class="text-gray-500 text-xl font-medium"><?php echo $lang['overview']; ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="progress.php" class="stat-card shadow-sm"><div class="icon-bg bg-pink-100">📈</div><span class="font-bold text-gray-800 text-lg"><?php echo $lang['progress']; ?></span></a>
            <a href="meals.php" class="stat-card shadow-sm"><div class="icon-bg bg-orange-100">🍴</div><span class="font-bold text-gray-800 text-lg"><?php echo $lang['meals']; ?></span></a>
            <a href="workouts.php" class="stat-card shadow-sm"><div class="icon-bg bg-purple-100">💪</div><span class="font-bold text-gray-800 text-lg"><?php echo $lang['workouts']; ?></span></a>
            <a href="timeline.php" class="stat-card shadow-sm"><div class="icon-bg bg-blue-100">🕒</div><span class="font-bold text-gray-800 text-lg"><?php echo $lang['timeline']; ?></span></a>
            <a href="tips.php" class="stat-card shadow-sm"><div class="icon-bg bg-yellow-100">⭐</div><span class="font-bold text-gray-800 text-lg"><?php echo $lang['tips']; ?></span></a>
            <a href="meal_plans.php" class="stat-card shadow-sm"><div class="icon-bg bg-green-100">🍽️</div><span class="font-bold text-gray-800 text-lg"><?php echo $lang['meal_plans']; ?></span></a>
            <a href="challenges.php" class="stat-card shadow-sm"><div class="icon-bg bg-red-100">🥇</div><span class="font-bold text-gray-800 text-lg"><?php echo $lang['challenges']; ?></span></a>
            <a href="store.php" class="stat-card shadow-sm"><div class="icon-bg bg-sky-100">🛒</div><span class="font-bold text-gray-800 text-lg"><?php echo $lang['store']; ?></span></a>
            <a href="steps_tracker.php" class="stat-card shadow-sm"><div class="icon-bg bg-indigo-100">👟</div><span class="font-bold text-gray-800 text-lg"><?php echo $lang['step_tracker']; ?></span></a>
        </div>
    </main>

    <script type="module">...</script>
</body>
</html>
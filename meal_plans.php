<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// جلب اللغة
$current_lang = $_SESSION['lang'] ?? 'en';
include_once ($current_lang === 'ar') ? 'lang_ar.php' : 'lang_en.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT Health_goal FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_goal = $stmt->get_result()->fetch_assoc()['Health_goal'] ?? 'General Fitness';
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $lang['dir']; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GlowWell - <?php echo $lang['meal_plans']; ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="meal_style.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<style>
    #healthToggle:checked ~ .toggle-bg { background-color: #ec4899; }
    #healthToggle:checked ~ .dot { transform: <?php echo ($current_lang === 'ar') ? 'translateX(-100%)' : 'translateX(100%)'; ?>; }
    /* تعديل بسيط للـ Dot في العربي ليكون الانتقال صحيح */
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="dashboard-container">
    <header class="hero-section">
        <div class="hero-content fade-in">
            <span class="badge"><?php echo $lang['meal_plans_title']; ?></span>
            <h1><?php echo $lang['fuel_body']; ?></h1>
            <p>
                <?php echo $lang['hello']; ?> <?php echo $_SESSION['user_fullname'] ?? $lang['champion']; ?>! 
                <?php echo $lang['based_on_goal']; ?> <?php echo htmlspecialchars($user_goal); ?> <?php echo $lang['curated_options']; ?>
            </p>
        </div>
    </header>

    <div class="diet-selector fade-in">
        <span class="diet-label"><?php echo $lang['diet_type']; ?></span>
        <button class="diet-btn active" onclick="setDiet('Balanced', this)"><?php echo $lang['balanced']; ?></button>
        <button class="diet-btn" onclick="setDiet('Low Carb', this)"><?php echo $lang['low_carb']; ?></button>
        <button class="diet-btn" onclick="setDiet('Keto', this)"><?php echo $lang['keto']; ?></button>
    </div>

    <div class="flex justify-center mt-4 mb-6 fade-in">
        <label class="flex items-center cursor-pointer bg-white px-5 py-3 rounded-full shadow-sm border border-pink-100 hover:shadow-md transition">
            <div class="relative">
                <input type="checkbox" id="healthToggle" class="sr-only" onchange="toggleHealthFilter()">
                <div class="block bg-gray-200 w-10 h-6 rounded-full transition-colors duration-300 toggle-bg"></div>
                <div class="dot absolute <?php echo ($current_lang === 'ar') ? 'right-1' : 'left-1'; ?> top-1 bg-white w-4 h-4 rounded-full transition-transform duration-300"></div>
            </div>
            <div class="<?php echo ($current_lang === 'ar') ? 'mr-3' : 'ml-3'; ?> text-sm font-bold text-gray-700"><?php echo $lang['health_filter']; ?></div>
        </label>
    </div>

    <div class="category-tabs">
        <button class="tab-item active" onclick="filterMeals('breakfast')"><?php echo $lang['breakfast']; ?></button>
        <button class="tab-item" onclick="filterMeals('lunch')"><?php echo $lang['lunch_dinner']; ?></button>
        <button class="tab-item" onclick="filterMeals('snacks')"><?php echo $lang['snacks_desserts']; ?></button>
    </div>

    <div class="meals-grid" id="mealsGrid"></div>
</div>

<div id="recipeModal" class="modal-overlay">
    <div class="modal-card">
        <button class="close-modal" onclick="closeModal()">×</button>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

<script> 
    const userGoalFromPHP = "<?php echo htmlspecialchars($user_goal); ?>"; 
    const currentLang = "<?php echo $current_lang; ?>"; // نمرر اللغة للسكربت الخارجي
</script>
<script src="meal_script.js"></script>
</body>
</html>
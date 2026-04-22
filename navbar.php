<?php
// 1. استدعاء ملف الإعدادات
include_once 'config.php';

// === [سحر الترجمة يبدأ من هنا] ===
// نتأكد إن الجلسة شغالة عشان نحفظ لغة المستخدم
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// إذا المستخدم ضغط على زر تغيير اللغة، نحفظها له
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// اللغة الافتراضية إنجليزي لو أول مرة يدخل
$current_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

// نستدعي القاموس المناسب
if ($current_lang === 'ar') {
    include_once 'lang_ar.php';
} else {
    include_once 'lang_en.php';
}
// ===================================

// 2. جلب إيموجي المستخدم (كودك الأصلي ما لمسناه)
$current_emoji = "👤"; 
if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $result = mysqli_query($conn, "SELECT gender FROM users WHERE id='$id'");
    if ($row = mysqli_fetch_assoc($result)) {
        $gender = strtolower(trim($row['gender']));
        $current_emoji = ($gender === 'male') ? "👨🏻" : (($gender === 'female') ? "👩🏻" : "👤");
    }
}
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

<style>
    /* --- التنسيقات الأساسية وتصميمك الأصلي --- */
    html { overflow-y: auto !important; height: auto !important; }
    body { 
        overflow-x: hidden !important; 
        overflow-y: auto !important; 
        height: auto !important; 
        -webkit-overflow-scrolling: touch;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .nav-custom { 
        background: white; padding: 0.8rem 1.5rem; display: flex; 
        justify-content: space-between; align-items: center; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); width: 100%; 
        position: sticky; top: 0; z-index: 1000; font-family: 'Poppins', sans-serif; 
    }
    .nav-links-group { display: flex; align-items: center; gap: 15px; }
    .nav-item { color: #4A4A4A; text-decoration: none; font-weight: 500; font-size: 14px; }
.nav-logo { 
    font-size: 1.5rem; 
    font-weight: 700; 
    text-decoration: none; 
    display: flex; 
    align-items: center;
    /* أضيفي هذا السطر */
    direction: ltr !important; 
}
    /* --- 3. القائمة والإطار --- */
    .dropdown { position: relative; display: inline-block; }
    .dropdown-content {
        display: none; 
        position: absolute; 
        /* ركزي هنا: خلينا الاتجاه يتغير مع اللغة عشان تطلع القائمة بيرفكت */
        <?php echo $lang['dropdown_align']; ?> 
        background-color: #fff;
        min-width: 160px; box-shadow: 0px 8px 16px rgba(0,0,0,0.1);
        border-radius: 8px; z-index: 1000; margin-top: 10px; border: 1px solid #eee;
    }
    .dropdown-content a { color: #4A4A4A; padding: 10px 15px; display: block; text-decoration: none; font-size: 13px; cursor: pointer; }
    .dropdown-content a:hover { background-color: #f9f9f9; color: #EC4D9C; }
    .pfp-wrapper {
        display: flex; align-items: center; justify-content: center;
        width: 35px; height: 35px; border-radius: 50%;
        cursor: pointer; border: 2px solid #ddd;
    }
    .vip-border { border: 2px solid #FFD700 !important; box-shadow: 0 0 8px #FFD700; }

    /* --- 4. ستايل الليلي الأصلي (Midnight) --- */
    .midnight-black {
        background-color: #121212 !important; 
        color: #ffffff !important;
    }
    .midnight-black .nav-custom { background-color: #1e1e1e !important; }
    .midnight-black .nav-item { color: #ffffff !important; }

    /* --- 5. ستايل الفي اي بي الذهبي (جديد) --- */
    .gold-vip-theme {
        background-color: #FFF8E1 !important; 
        color: #856404 !important;
    }
    .gold-vip-theme .nav-custom { background-color: #FFD700 !important; }
    .gold-vip-theme .nav-item { color: #5d4702 !important; }

    @media (max-width: 768px) {
        .nav-custom { padding: 0.8rem 0.5rem; }
        .nav-item { font-size: 11px !important; }
    }
</style>

<nav class="nav-custom" dir="<?php echo $lang['dir']; ?>">
    <a href="dashboard.php" class="nav-logo">
        <span style="color: #EC4D9C;">Glow</span><span style="color: #2AC66A;">Well</span>
    </a>
    <div class="nav-links-group">
        <a href="dashboard.php" class="nav-item"><?php echo $lang['dashboard']; ?></a>
        <a href="about.php" class="nav-item"><?php echo $lang['about']; ?></a>
        <a href="notifications.php" class="nav-item"><?php echo $lang['alerts']; ?></a>
        <a href="logout.php" class="nav-item" style="color: #ff4d4d; font-weight: bold;"><?php echo $lang['exit']; ?></a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>
            <a href="admin_panel/admin_dashboard.php" class="nav-item" style="font-weight: bold;"><?php echo $lang['admin']; ?></a>
        <?php endif; ?>

        <div class="dropdown">
            <div class="pfp-wrapper <?php echo (isset($is_vip_theme) && $is_vip_theme) ? 'vip-border' : ''; ?>" onclick="toggleDrop()">
                <span style="font-size: 20px;"><?php echo $current_emoji; ?></span>
            </div>
            <div id="userDrop" class="dropdown-content">
                <a href="update_profile.php"><?php echo $lang['update_prof']; ?></a>
                
                <?php if (isset($is_vip_theme) && $is_vip_theme): ?>
                    <a onclick="toggleGoldTheme()" style="color: #B8860B; font-weight: bold;"><?php echo $lang['gold_theme']; ?></a>
                <?php endif; ?>

                <a onclick="toggleNightMode()"><?php echo $lang['dark_mode']; ?></a>
                
<a href="<?php echo isset($lang['switch_lang_link']) ? $lang['switch_lang_link'] : '#'; ?>">
    <?php echo isset($lang['switch_lang_text']) ? $lang['switch_lang_text'] : 'Language'; ?>
</a>            </div>
        </div>
    </div>
</nav>

<script>
// السكربت حقك الجميل نفسه ما تغير فيه حرف
function toggleDrop() {
    var drop = document.getElementById("userDrop");
    drop.style.display = (drop.style.display === "block") ? "none" : "block";
}

function toggleNightMode() {
    const body = document.body;
    body.classList.remove('gold-vip-theme'); 
    const isDark = body.classList.toggle('midnight-black'); 
    localStorage.setItem('glowwell_theme', isDark ? 'dark' : 'normal');
    document.getElementById("userDrop").style.display = "none";
}

function toggleGoldTheme() {
    const body = document.body;
    body.classList.remove('midnight-black'); 
    const isGold = body.classList.toggle('gold-vip-theme');
    localStorage.setItem('glowwell_theme', isGold ? 'gold' : 'normal');
    document.getElementById("userDrop").style.display = "none";
}

window.onclick = function(event) {
    if (!event.target.closest('.dropdown')) {
        var drop = document.getElementById("userDrop");
        if (drop) drop.style.display = "none";
    }
}

document.addEventListener('DOMContentLoaded', function() {
const navDir = document.querySelector('.nav-custom').getAttribute('dir');
    
    // تطبيق هذا الاتجاه على الـ body الخاص بالصفحة كاملة
    document.body.setAttribute('dir', navDir);

    // تطبيق محاذاة النصوص بناءً على الاتجاه
    if(navDir === 'rtl') {
        document.body.style.textAlign = 'right';
    } else {
        document.body.style.textAlign = 'left';
    }
    const saved = localStorage.getItem('glowwell_theme');
    if (saved === 'dark') document.body.classList.add('midnight-black');
    if (saved === 'gold') document.body.classList.add('gold-vip-theme');
});
</script>
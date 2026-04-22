<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// 1. جلب البيانات الحالية
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);

// 2. معالجة الحفظ
if (isset($_POST['save_profile'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $age      = !empty($_POST['age']) ? intval($_POST['age']) : "NULL";
    $height   = !empty($_POST['height']) ? floatval($_POST['height']) : "NULL";
    $weight   = !empty($_POST['weight']) ? floatval($_POST['weight']) : "NULL";
    $gender   = mysqli_real_escape_string($conn, $_POST['gender']);
    $goal     = mysqli_real_escape_string($conn, $_POST['health_goal']);
    $activity = mysqli_real_escape_string($conn, $_POST['activity_level']);

    $update_sql = "UPDATE users SET 
                    fullname='$fullname', email='$email', age=$age, 
                    height=$height, weight=$weight, gender='$gender', 
                    health_goal='$goal', activity_level='$activity' 
                    WHERE id='$user_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        header("Location: dashboard.php?status=updated");
        exit();
    } else {
        die("خطأ في الحفظ: " . mysqli_error($conn));
    }
}
?>
<!doctype html>
<html lang="<?php echo isset($lang['dir']) && $lang['dir'] == 'rtl' ? 'ar' : 'en'; ?>" dir="<?php echo $lang['dir'] ?? 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
    <title><?php echo $lang['update_profile_title'] ?? 'Update Profile'; ?> - GlowWell</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #FCEEF4; margin: 0; transition: all 0.5s ease; }
        .profile-container { background: white; border-radius: 30px; padding: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .input-field { background: #F9FAFB; border: 1px solid #F3F4F6; border-radius: 12px; padding: 12px 16px; width: 100%; outline: none; transition: all 0.3s; color: #4B5563; }
        .input-field:focus { border-color: #EC4D9C; box-shadow: 0 0 0 2px #FCE7F3; }
        label { display: block; font-weight: 600; color: #1F2937; margin-bottom: 10px; font-size: 14px; text-align: <?php echo isset($lang['dir']) && $lang['dir'] == 'rtl' ? 'right' : 'left'; ?>; }

        /* --- ثيم Midnight Gold الفخم --- */
        body.midnight-gold {
            background-color: #0B0B0B !important;
            color: #D4AF37 !important;
        }
        body.midnight-gold .profile-container, 
        body.midnight-gold .nav-custom {
            background: #161616 !important;
            border: 1px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
        }
        body.midnight-gold label, body.midnight-gold h1 { color: #D4AF37 !important; }
        body.midnight-gold .input-field { background: #1f1f1f; border-color: #333; color: #eee; }
        body.midnight-gold .vip-btn { background: #D4AF37 !important; color: #000 !important; }

        /* ستايل زر الـ VIP */
        .vip-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #000 100%);
            border: 1px solid #D4AF37;
            position: relative;
            overflow: hidden;
        }
        .vip-card::after {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent, rgba(212,175,55,0.1), transparent);
            transform: rotate(45deg); animation: shine 3s infinite;
        }
        @keyframes shine { 0% { transform: translateX(-100%) rotate(45deg); } 100% { transform: translateX(100%) rotate(45deg); } }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navbar.php'; ?>
    
    <main class="max-w-6xl mx-auto px-6 lg:px-12 py-16">
        <h1 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-12 <?php echo isset($lang['dir']) && $lang['dir'] == 'rtl' ? 'text-right' : 'text-left'; ?>"><?php echo $lang['update_profile_title'] ?? 'Update Profile'; ?></h1>

        <div class="mb-12 grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <?php if (isset($is_vip_theme) && $is_vip_theme): ?>
            <div class="vip-card p-6 rounded-3xl flex flex-col items-center justify-center text-center shadow-2xl border-2 border-[#D4AF37]">
                <iconify-icon icon="mdi:gold" class="text-4xl text-[#D4AF37] mb-2"></iconify-icon>
                <h3 class="text-[#D4AF37] font-bold mb-4"><?php echo $lang['midnight_theme'] ?? 'Midnight Gold Theme'; ?></h3>
                <button onclick="toggleMidnightTheme()" id="themeBtn" class="px-6 py-2 bg-[#D4AF37] text-black font-bold rounded-full text-sm transition hover:scale-105">
                    <?php echo $lang['activate_theme'] ?? 'Activate Theme'; ?>
                </button>
            </div>
            <?php endif; ?>

            <?php if (isset($has_elite_badge) && $has_elite_badge): ?>
            <div class="bg-white p-6 rounded-3xl flex flex-col items-center justify-center text-center shadow-lg border border-gray-100">
                <div class="relative">
                    <iconify-icon icon="solar:shield-star-bold-duotone" class="text-6xl text-[#D4AF37]"></iconify-icon>
                    <span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span></span>
                </div>
                <h3 class="text-gray-800 font-bold mt-2"><?php echo $lang['elite_challenger'] ?? 'Elite Challenger'; ?></h3>
                <p class="text-xs text-gray-400 italic"><?php echo $lang['global_badge'] ?? 'Global Recognition Badge'; ?></p>
            </div>
            <?php endif; ?>

        </div>

        <div class="profile-container">
            <div class="mb-10">
                <label class="mb-4 text-lg"><?php echo $lang['my_earned_badges'] ?? 'My Earned Badges'; ?></label>
                <div class="flex flex-wrap gap-2">
                    <?php 
                    if (!empty($user['badges'])) {
                        $badges_array = explode(',', $user['badges']); 
                        foreach ($badges_array as $b) {
                            $b = trim($b);
                            if (!empty($b)) {
                                echo '<span class="px-4 py-2 bg-pink-100 text-pink-700 rounded-full text-sm font-semibold border border-pink-200 shadow-sm">🏅 ' . htmlspecialchars($b) . '</span>';
                            }
                        }
                    } else {
                        echo '<p class="text-gray-400 text-sm italic">' . ($lang['no_badges'] ?? 'No badges earned yet. Keep challenging yourself!') . '</p>';
                    }
                    ?>
                </div>
            </div>

            <form method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8 <?php echo isset($lang['dir']) && $lang['dir'] == 'rtl' ? 'text-right' : 'text-left'; ?>">
                    <div><label><?php echo $lang['full_name'] ?? 'Full Name'; ?></label><input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" class="input-field"></div>
                    <div><label><?php echo $lang['age'] ?? 'Age'; ?></label><input type="number" name="age" value="<?php echo $user['age'] ?? ''; ?>" class="input-field"></div>
                    <div><label><?php echo $lang['email_address'] ?? 'Email Address'; ?></label><input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="input-field"></div>
                    <div><label><?php echo $lang['height_cm'] ?? 'Height (cm)'; ?></label><input type="number" step="0.1" name="height" value="<?php echo $user['height'] ?? ''; ?>" class="input-field"></div>
                    <div><label><?php echo $lang['weight_kg'] ?? 'Weight (kg)'; ?></label><input type="number" step="0.1" name="weight" value="<?php echo $user['weight'] ?? ''; ?>" class="input-field"></div>
                    <div><label><?php echo $lang['gender_label'] ?? 'Gender'; ?></label>
                        <select name="gender" class="input-field">
                            <option value=""><?php echo $lang['select_gender'] ?? 'Select Gender'; ?></option>
                            <option value="male" <?php if(($user['gender']??"")=="male")echo "selected";?>><?php echo $lang['male'] ?? 'Male'; ?></option>
                            <option value="female" <?php if(($user['gender']??"")=="female")echo "selected";?>><?php echo $lang['female'] ?? 'Female'; ?></option>
                        </select>
                    </div>
                    <div><label><?php echo $lang['health_goal_label'] ?? 'Health Goal'; ?></label>
                        <select name="health_goal" class="input-field">
                            <option value=""><?php echo $lang['select_goal'] ?? 'Select Health Goal'; ?></option>
                            <option value="Lose Weight" <?php if(($user['health_goal']??"")=="Lose Weight")echo "selected";?>><?php echo $lang['goal_lose_weight'] ?? 'Lose Weight'; ?></option>
                            <option value="Build Muscle" <?php if(($user['health_goal']??"")=="Build Muscle")echo "selected";?>><?php echo $lang['goal_build_muscle'] ?? 'Build Muscle'; ?></option>
                        </select>
                    </div>
                    <div><label><?php echo $lang['activity_level_label'] ?? 'Activity Level'; ?></label>
                        <select name="activity_level" class="input-field">
                            <option value=""><?php echo $lang['select_activity'] ?? 'Select Activity Level'; ?></option>
                            <option value="Sedentary" <?php if(($user['activity_level']??"")=="Sedentary")echo "selected";?>><?php echo $lang['sedentary'] ?? 'Sedentary'; ?></option>
                            <option value="Moderate" <?php if(($user['activity_level']??"")=="Moderate")echo "selected";?>><?php echo $lang['moderate'] ?? 'Moderate'; ?></option>
                            <option value="Active" <?php if(($user['activity_level']??"")=="Active")echo "selected";?>><?php echo $lang['active'] ?? 'Active'; ?></option>
                        </select>
                    </div>
                </div>
                <div class="flex <?php echo isset($lang['dir']) && $lang['dir'] == 'rtl' ? 'justify-start' : 'justify-end'; ?> gap-4 mt-10">
                    <a href="dashboard.php" class="px-10 py-3 bg-white border border-gray-200 text-gray-500 font-bold rounded-xl hover:bg-gray-50 transition"><?php echo $lang['cancel'] ?? 'Cancel'; ?></a>
                    <button type="submit" name="save_profile" class="px-10 py-3 bg-[#EC4899] text-white font-bold rounded-xl hover:bg-pink-600 transition shadow-lg"><?php echo $lang['save_changes'] ?? 'Save Changes'; ?></button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function toggleMidnightTheme() {
            const body = document.body;
            const btn = document.getElementById('themeBtn');
            
            body.classList.toggle('midnight-gold');
            
            if (body.classList.contains('midnight-gold')) {
                localStorage.setItem('glowwell_theme', 'midnight');
                if(btn) btn.innerText = '<?php echo $lang['deactivate_theme'] ?? 'Deactivate Theme'; ?>';
            } else {
                localStorage.setItem('glowwell_theme', 'light');
                if(btn) btn.innerText = '<?php echo $lang['activate_theme'] ?? 'Activate Theme'; ?>';
            }
        }

        if (localStorage.getItem('glowwell_theme') === 'midnight') {
            document.body.classList.add('midnight-gold');
            const btn = document.getElementById('themeBtn');
            if(btn) btn.innerText = '<?php echo $lang['deactivate_theme'] ?? 'Deactivate Theme'; ?>';
        }
    </script>
</body>
</html>
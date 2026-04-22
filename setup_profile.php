<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. جلب البيانات الحالية
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);

// 2. معالجة الحفظ عند الضغط على زر الحفظ
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
        // بعد الحفظ يتم تحويل المستخدم إلى الداشبورد
        header("Location: dashboard.php?status=updated");
        exit();
    } else {
        $message = "<p class='text-red-500 mb-4'>Error saving profile. ❌</p>";
    }
}

/**
 * 🔹 منطقة الإصلاح الذكي (Smart Fix):
 * نستخدم (string) ونظام التحقق من الوجود (?? '') لضمان أن الدوال لا تستقبل NULL
 * هذا يحمي حسابات البنات الجدد من ظهور رسالة الـ Deprecated
 * ولا يؤثر أبداً على حسابك لأن بياناتك موجودة أصلاً.
 */
$current_gender = strtolower(trim((string)($user['gender'] ?? '')));
$current_fullname = htmlspecialchars((string)($user['fullname'] ?? ''));
$current_email = htmlspecialchars((string)($user['email'] ?? ''));
$current_age = htmlspecialchars((string)($user['age'] ?? ''));
$current_height = htmlspecialchars((string)($user['height'] ?? ''));
$current_weight = htmlspecialchars((string)($user['weight'] ?? ''));
$current_goal = (string)($user['health_goal'] ?? ''); // تأكدي من مطابقة اسم العمود في الداتابيز (health_goal)
$current_activity = (string)($user['activity_level'] ?? '');
?>

<!doctype html>
<html lang="<?php echo isset($lang['dir']) && $lang['dir'] == 'rtl' ? 'ar' : 'en'; ?>" dir="<?php echo $lang['dir'] ?? 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell - <?php echo $lang['setup_profile_title'] ?? 'Setup Profile'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #FDF2F8; font-family: 'Poppins', sans-serif; }
        .profile-container { background: white; border-radius: 30px; padding: 50px; max-width: 700px; margin: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .input-field { background: #F9FAFB; border: 1px solid #F3F4F6; border-radius: 12px; padding: 12px 16px; width: 100%; outline: none; transition: all 0.3s; color: #4B5563; }
        .input-field:focus { border-color: #EC4D9C; box-shadow: 0 0 0 2px #FCE7F3; }
        label { display: block; font-weight: 600; color: #1F2937; margin-bottom: 10px; font-size: 14px; text-align: <?php echo isset($lang['dir']) && $lang['dir'] == 'rtl' ? 'right' : 'left'; ?>;}
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="profile-container w-full">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center"><?php echo $lang['setup_profile_title'] ?? 'Setup Your Profile'; ?></h2>

        <?php echo $message; ?>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 <?php echo isset($lang['dir']) && $lang['dir'] == 'rtl' ? 'text-right' : 'text-left'; ?>">

                <div>
                    <label><?php echo $lang['full_name'] ?? 'Full Name'; ?></label>
                    <input type="text" name="fullname" value="<?php echo $current_fullname; ?>" class="input-field">
                </div>

                <div>
                    <label><?php echo $lang['email_address'] ?? 'Email Address'; ?></label>
                    <input type="email" name="email" value="<?php echo $current_email; ?>" class="input-field">
                </div>

                <div>
                    <label><?php echo $lang['age'] ?? 'Age'; ?></label>
                    <input type="number" name="age" value="<?php echo $current_age; ?>" class="input-field">
                </div>

                <div>
                    <label><?php echo $lang['height_cm'] ?? 'Height (cm)'; ?></label>
                    <input type="number" step="0.1" name="height" value="<?php echo $current_height; ?>" class="input-field">
                </div>

                <div>
                    <label><?php echo $lang['weight_kg'] ?? 'Weight (kg)'; ?></label>
                    <input type="number" step="0.1" name="weight" value="<?php echo $current_weight; ?>" class="input-field">
                </div>

                <div>
                    <label><?php echo $lang['gender_label'] ?? 'Gender'; ?></label>
                    <select name="gender" class="input-field">
                        <option value=""><?php echo $lang['select_gender'] ?? 'Select Gender'; ?></option>
                        <option value="male" <?php if($current_gender=='male') echo 'selected'; ?>><?php echo $lang['male'] ?? 'Male'; ?></option>
                        <option value="female" <?php if($current_gender=='female') echo 'selected'; ?>><?php echo $lang['female'] ?? 'Female'; ?></option>
                    </select>
                </div>

                <div>
                    <label><?php echo $lang['health_goal_label'] ?? 'Health Goal'; ?></label>
                    <select name="health_goal" class="input-field">
                        <option value=""><?php echo $lang['select_goal'] ?? 'Select Goal'; ?></option>
                        <option value="Lose Weight" <?php if($current_goal=="Lose Weight") echo 'selected'; ?>><?php echo $lang['goal_lose_weight'] ?? 'Lose Weight'; ?></option>
                        <option value="Build Muscle" <?php if($current_goal=="Build Muscle") echo 'selected'; ?>><?php echo $lang['goal_build_muscle'] ?? 'Build Muscle'; ?></option>
                    </select>
                </div>

                <div>
                    <label><?php echo $lang['activity_level_label'] ?? 'Activity Level'; ?></label>
                    <select name="activity_level" class="input-field">
                        <option value=""><?php echo $lang['select_activity'] ?? 'Select Activity'; ?></option>
                        <option value="Sedentary" <?php if($current_activity=="Sedentary") echo 'selected'; ?>><?php echo $lang['sedentary'] ?? 'Sedentary'; ?></option>
                        <option value="Moderate" <?php if($current_activity=="Moderate") echo 'selected'; ?>><?php echo $lang['moderate'] ?? 'Moderate'; ?></option>
                        <option value="Active" <?php if($current_activity=="Active") echo 'selected'; ?>><?php echo $lang['active'] ?? 'Active'; ?></option>
                    </select>
                </div>

            </div>

            <button type="submit" name="save_profile" class="w-full bg-pink-500 text-white font-bold py-4 mt-6 rounded-2xl hover:bg-pink-600 transition shadow-lg transform hover:-translate-y-1">
                <?php echo $lang['save_profile_btn'] ?? 'Save Profile'; ?>
            </button>
        </form>
    </div>

</body>
</html>
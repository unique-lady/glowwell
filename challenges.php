<?php
// 1. استخدام include_once لمنع تكرار استدعاء الملف وتجنب أخطاء السيرفر
include_once 'config.php';

// التأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// استدعاء ملفات اللغة
$user_lang = $_SESSION['lang'] ?? 'en';
if ($user_lang == 'ar') {
    include_once 'lang_ar.php';
} else {
    include_once 'lang_en.php';
}

// جلب بيانات المستخدم
$u_result = mysqli_query($conn, "SELECT fullname, glow_coins, badges, gender FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($u_result);

// جلب التحديات
$challenges = mysqli_query($conn, "SELECT * FROM challenges");
?>

<!DOCTYPE html>
<html lang="<?php echo $user_lang; ?>" dir="<?php echo $lang['dir'] ?? 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell - <?php echo $lang['challenges_arena'] ?? 'Challenges Arena'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* كود الـ CSS الخاص بك مضاف له دعم الخط العربي */
        body { 
            background-color: #fdf2f8; 
            font-family: <?php echo ($user_lang == 'ar') ? "'Cairo', sans-serif" : "'Poppins', sans-serif"; ?>; 
            color: #4A4A4A; 
            margin: 0; 
            padding: 0;
            padding-bottom: 50px; 
        }

        .container { 
            max-width: 1100px; 
            margin: 0 auto; 
            padding: 2rem 1rem; 
        }
        
        .page-header { text-align: center; margin-bottom: 2.5rem; }
        .page-header h1 { font-size: 2.2rem; font-weight: 700; color: #EC4D9C; margin-bottom: 0.5rem; }
        
        .coins-balance-card {
            background: white; padding: 1rem 1.5rem; border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: inline-flex;
            align-items: center; gap: 12px; margin-bottom: 2rem;
        }
        .coins-balance-card img { width: 35px; height: 35px; }
        .coins-balance-card span { font-size: 1.4rem; font-weight: 700; color: #4A4A4A; }

        .leaderboard-link {
            display: inline-flex; align-items: center; gap: 8px;
            background: #fff; padding: 10px 20px; border-radius: 50px;
            box-shadow: 0 4px 10px rgba(236, 77, 156, 0.2);
            color: #EC4D9C; font-weight: 600; text-decoration: none;
            transition: 0.3s; margin-left: 15px;
        }
        .leaderboard-link:hover { transform: scale(1.05); background: #fff0f7; }

        .challenges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
        
        .challenge-card {
            background: white; border-radius: 25px; padding: 1.8rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03); display: flex; flex-direction: column;
            transition: 0.3s;
        }
        .challenge-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(236, 77, 156, 0.1); }

        .card-icon { font-size: 2.5rem; margin-bottom: 1rem; }
        .card-title { font-size: 1.2rem; font-weight: 600; color: #333; }
        .card-desc { font-size: 0.9rem; color: #777; margin: 1rem 0; flex-grow: 1; }

        .progress-container { background: #eee; border-radius: 10px; height: 10px; margin: 10px 0; overflow: hidden; }
        .progress-bar { background: #2AC66A; height: 100%; width: 0%; transition: width 0.5s ease; } 

        .rewards-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 1rem; margin-top: 1rem; }
        .reward-item { display: flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.9rem; }
        .reward-coin-icon { width: 20px; height: 20px; }
        
        .btn-action {
            border: none; padding: 12px; border-radius: 50px; width: 100%; 
            margin-top: 1rem; cursor: pointer; font-weight: 600; transition: 0.3s;
        }
        .join-btn { background-color: #EC4D9C; color: white; }
        .log-btn { background-color: #2AC66A; color: white; }
        .claim-btn { background-color: #FFD700; color: #333; }

        .navbar-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            background-color: #fff;
            margin-bottom: 20px;
        }
        .navbar-wrapper .navbar {
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<div class="navbar-wrapper">
    <?php include_once 'navbar.php'; ?>
</div>

<div class="container">
    <div class="page-header">
        <h1><?php echo $lang['challenges_arena'] ?? 'Challenges Arena'; ?></h1>
        <p><?php echo $lang['challenges_desc'] ?? 'Complete challenges, earn Badges, and collect GlowCoins!'; ?></p>
    </div>

    <div style="text-align: center; margin-bottom: 2rem; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
        <div class="coins-balance-card" style="margin-bottom: 0;">
            <img src="glowcoin.png" alt="GlowCoin"> 
            <span><?php echo number_format($user['glow_coins']); ?></span>
            <label style="color:#888; margin-left: 5px; margin-right: 5px;"><?php echo $lang['glowcoins'] ?? 'GlowCoins'; ?></label>
        </div>
        
        <a href="leaderboard.php" class="coins-balance-card" style="margin-bottom: 0; text-decoration: none; color: #4A4A4A; transition: 0.3s;">
            <span>🏆</span> 
            <span style="font-size: 1.1rem; font-weight: 600; color: #EC4D9C; margin-left: 5px; margin-right: 5px;"><?php echo $lang['leaderboard'] ?? 'Leaderboard'; ?></span>
        </a>
    </div>

    <div class="challenges-grid">
        <?php while($ch = mysqli_fetch_assoc($challenges)): 
            $prog_query = mysqli_query($conn, "SELECT progress_days, status FROM user_challenges WHERE user_id = '$user_id' AND challenge_id = '".$ch['id']."'");
            $prog_data = mysqli_fetch_assoc($prog_query);
            $progress_days = $prog_data ? $prog_data['progress_days'] : 0;
            $status = $prog_data ? $prog_data['status'] : '';
            $percentage = ($ch['target_days'] > 0) ? ($progress_days / $ch['target_days']) * 100 : 0;
            
            // قراءة العنوان والوصف من الداتابيس (بافتراض أن عمود title_ar و description_ar موجودان، وإلا نعرض الإنجليزي)
            $display_title = ($user_lang == 'ar' && isset($ch['title_ar']) && !empty($ch['title_ar'])) ? $ch['title_ar'] : $ch['title_en'];
            $display_desc = ($user_lang == 'ar' && isset($ch['description_ar']) && !empty($ch['description_ar'])) ? $ch['description_ar'] : $ch['description'];
        ?>
            <div class="challenge-card">
                <div class="card-icon"><?php echo $ch['icon']; ?></div>
                <div class="card-title"><?php echo htmlspecialchars($display_title); ?></div>
                <div class="card-desc"><?php echo htmlspecialchars($display_desc); ?></div>
                
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%;"></div> 
                </div>
                <small style="color:#888;"><?php echo $lang['progress_text']; ?> <?php echo $progress_days; ?> / <?php echo $ch['target_days']; ?> <?php echo $lang['days_text']; ?></small>

                <div class="rewards-row">
                    <div class="reward-item">
                        <img src="glowcoin.png" class="reward-coin-icon">
                        <span style="color:#2AC66A;">+<?php echo $ch['reward_coins']; ?></span>
                    </div>
                    <div class="reward-item">
                        <span>🏅</span>
                        <span style="color:#EC4D9C;"><?php echo htmlspecialchars($ch['reward_badge']); ?></span>
                    </div>
                </div>
                
                <?php if (!$prog_data): ?>
                    <button class="btn-action join-btn" data-id="<?php echo $ch['id']; ?>"><?php echo $lang['join_challenge']; ?></button>
                <?php else: ?>
                    <?php if ($progress_days >= $ch['target_days'] && $status == 'pending_reward'): ?>
                        <button class="btn-action claim-btn" data-id="<?php echo $ch['id']; ?>"><?php echo $lang['claim_rewards']; ?></button>
                    <?php endif; ?>

                    <?php 
                    $today = ((int)date('H') < 3) ? date('Y-m-d', strtotime('-1 day')) : date('Y-m-d');
                    $check_log = mysqli_query($conn, "SELECT id FROM challenge_logs WHERE user_id = '$user_id' AND challenge_id = '".$ch['id']."' AND log_date = '$today'");
                    
                    if (mysqli_num_rows($check_log) == 0): ?>
                        <button class="btn-action log-btn" data-id="<?php echo $ch['id']; ?>"><?php echo $lang['log_daily_progress']; ?></button>
                    <?php else: ?>
                        <button class="btn-action" disabled style="background-color: #ccc; cursor: default;"><?php echo $lang['logged_today']; ?></button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
document.querySelectorAll('.join-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let challengeId = this.getAttribute('data-id');
        fetch('join_challenge.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'challenge_id=' + challengeId
        })
        .then(res => res.text())
        .then(data => {
            if(data === "success") location.reload();
            else alert(data);
        });
    });
});

document.querySelectorAll('.log-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let challengeId = this.getAttribute('data-id');
        Swal.fire({
            title: '<?php echo $lang["js_great_progress"]; ?>',
            text: '<?php echo $lang["js_keep_it_up"]; ?>',
            icon: 'success',
            confirmButtonColor: '#2AC66A',
            confirmButtonText: '<?php echo $lang["js_awesome"]; ?>'
        }).then(() => {
            fetch('update_progress.php', {
                method: 'POST',
                body: 'challenge_id=' + challengeId,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            })
            .then(res => res.text())
            .then(data => {
                if(data === "success") {
                    location.reload();
                } else {
                    alert('<?php echo $lang["js_already_logged"]; ?>');
                }
            });
        });
    });
});

document.querySelectorAll('.claim-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let challengeId = this.getAttribute('data-id');
        fetch('claim_reward.php', {
            method: 'POST',
            body: 'challenge_id=' + challengeId,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .then(res => res.text())
        .then(data => {
            if(data === "success") location.reload();
        });
    });
});
</script>

</body>
</html>
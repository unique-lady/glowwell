<?php
include 'config.php';

session_start();
$user_lang = $_SESSION['lang'] ?? 'en';
if ($user_lang == 'ar') {
    include_once 'lang_ar.php';
} else {
    include_once 'lang_en.php';
}

$current_user_id = $_SESSION['user_id'];
$top_users = mysqli_query($conn, "SELECT id, fullname, glow_coins FROM users ORDER BY glow_coins DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="<?php echo $user_lang; ?>" dir="<?php echo $lang['dir'] ?? 'ltr'; ?>">
<head>
    <title>GlowWell - <?php echo $lang['leaderboard'] ?? 'Leaderboard'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: <?php echo ($user_lang == 'ar') ? "'Cairo', sans-serif" : "'Poppins', sans-serif"; ?>; }
    </style>
</head>
<body class="bg-[#FDF2F8] p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-xl p-8">
        <h1 class="text-3xl font-bold text-center text-pink-600 mb-8"><?php echo $lang['top_champions'] ?? '🏆 Top GlowWell Champions'; ?></h1>
        
        <table class="w-full text-<?php echo ($user_lang == 'ar') ? 'right' : 'left'; ?>">
            <thead>
                <tr class="border-b text-gray-400 uppercase text-sm">
                    <th class="py-3 px-2"><?php echo $lang['rank'] ?? 'Rank'; ?></th>
                    <th class="py-3 px-2"><?php echo $lang['name'] ?? 'Name'; ?></th>
                    <th class="py-3 px-2 text-<?php echo ($user_lang == 'ar') ? 'left' : 'right'; ?>"><?php echo $lang['glowcoins'] ?? 'GlowCoins'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                while($u = mysqli_fetch_assoc($top_users)): 
                    $is_me = ($u['id'] == $current_user_id);
                ?>
                <tr class="border-b <?php echo $is_me ? 'bg-pink-50' : ''; ?>">
                    <td class="py-4 px-2 font-bold text-gray-700"><?php echo $rank++; ?></td>
                    <td class="py-4 px-2 font-semibold text-gray-800">
                        <?php echo htmlspecialchars($u['fullname']); ?>
                        <?php if($is_me) echo ' <span class="text-xs text-pink-500 font-bold mx-2">' . ($lang['you'] ?? '(You 🌟)') . '</span>'; ?>
                    </td>
                    <td class="py-4 px-2 text-<?php echo ($user_lang == 'ar') ? 'left' : 'right'; ?> text-green-600 font-bold">
                        <?php echo $u['glow_coins']; ?>
                        <img src="glowCoin.png" class="inline w-5 h-5 mx-1">
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="mt-8 text-center">
            <a href="challenges.php" class="inline-block bg-pink-100 text-pink-600 font-bold px-6 py-2 rounded-full hover:bg-pink-200 transition">
                <?php echo ($user_lang == 'ar') ? 'العودة للتحديات' : 'Back to Challenges'; ?>
            </a>
        </div>
    </div>
</body>
</html>
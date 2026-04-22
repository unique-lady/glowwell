<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// استدعاء ملفات اللغة
$user_lang = $_SESSION['lang'] ?? 'en';
if ($user_lang == 'ar') {
    include_once 'lang_ar.php';
} else {
    include_once 'lang_en.php';
}
if (!isset($lang['dir'])) $lang['dir'] = 'ltr';

// توليد رمز حماية للعملية (Token)
if (empty($_SESSION['payment_token'])) {
    $_SESSION['payment_token'] = bin2hex(random_bytes(32)); 
}

$amount = isset($_GET['amount']) ? intval($_GET['amount']) : 0;
$price = isset($_GET['price']) ? $_GET['price'] : '0.00';

if ($amount <= 0) {
    die("Invalid amount.");
}
?>
<!DOCTYPE html>
<html lang="<?php echo $user_lang == 'ar' ? 'ar' : 'en'; ?>" dir="<?php echo $lang['dir']; ?>">
<head>
    <meta charset="UTF-8">
    <title>Secure Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: <?php echo $user_lang == 'ar' ? "'Cairo', sans-serif" : "sans-serif"; ?>; }
    </style>
</head>
<body class="bg-gray-50 h-screen flex items-center justify-center font-sans">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-96 text-center border border-pink-100">
        <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo $user_lang == 'ar' ? 'تأكيد عملية الشحن' : 'Checkout Simulation'; ?></h2>
        <p class="text-gray-500 mb-6"><?php echo $user_lang == 'ar' ? 'أنت على وشك شراء رصيد إضافي لمحفظتك.' : 'You are about to purchase coins.'; ?></p>
        
        <div class="bg-pink-50 p-4 rounded-xl mb-6 flex justify-between items-center <?php echo $user_lang == 'ar' ? 'flex-row-reverse' : ''; ?>">
            <span class="font-bold text-pink-600"><?= number_format($amount) ?> GC</span>
            <span class="font-bold text-gray-800">$<?= htmlspecialchars($price) ?></span>
        </div>

        <form action="process_recharge.php" method="POST">
            <input type="hidden" name="amount" value="<?= $amount ?>">
            <input type="hidden" name="auth_token" value="<?= $_SESSION['payment_token'] ?>">
            
            <button type="submit" class="w-full bg-pink-500 text-white font-bold py-3 rounded-xl hover:bg-pink-600 transition">
                <?php echo $user_lang == 'ar' ? 'ادفع الآن' : 'Pay Now'; ?>
            </button>
        </form>
        <a href="store.php" class="block mt-4 text-sm text-gray-400 hover:text-gray-600"><?php echo $user_lang == 'ar' ? 'إلغاء والعودة للمتجر' : 'Cancel Return to Store'; ?></a>
    </div>
</body>
</html>
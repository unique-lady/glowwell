<?php
include 'config.php';

// التحقق من تسجيل الدخول
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
if (!isset($lang['dir'])) $lang['dir'] = 'ltr';

// جلب بيانات المستخدم والرصيد
$u_result = mysqli_query($conn, "SELECT fullname, glow_coins FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($u_result);

// -- التعديل هنا: فلترة المنتجات بناءً على القسم (Category) --
$cat_filter = "";
if(isset($_GET['cat']) && !empty($_GET['cat'])) {
    $safe_cat = mysqli_real_escape_string($conn, $_GET['cat']);
    $cat_filter = " WHERE item_type = '$safe_cat'";
}

// جلب المنتجات من المتجر (مع الفلترة إن وجدت)
$items_query = mysqli_query($conn, "SELECT * FROM store_items" . $cat_filter);

// جلب المشتريات السابقة للمستخدم
$purchased_query = mysqli_query($conn, "SELECT item_id FROM user_purchases WHERE user_id = '$user_id'");
$purchased_items = [];
while($row = mysqli_fetch_assoc($purchased_query)) { 
    $purchased_items[] = $row['item_id']; 
}

// --- الإضافة الجديدة: التحقق من وجود طلب شحن معلق ---
$pending_check_query = mysqli_query($conn, "SELECT amount FROM recharge_requests WHERE user_id = '$user_id' AND status = 'pending' LIMIT 1");
$has_pending_request = false;
$pending_amount = 0;
if (mysqli_num_rows($pending_check_query) > 0) {
    $has_pending_request = true;
    $pending_data = mysqli_fetch_assoc($pending_check_query);
    $pending_amount = $pending_data['amount'];
}
// --------------------------------------------------------
?>

<!DOCTYPE html>
<html lang="<?php echo $user_lang == 'ar' ? 'ar' : 'en'; ?>" dir="<?php echo $lang['dir']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="موقع GlowWell - رفيقك الأول للتمارين، الوجبات الصحية، وتتبع سعراتك بكل سهولة وتوهج.">
<meta name="keywords" content="GlowWell, قلو ويل, قلوويل, تمارين رياضية, وجبات صحية, سعرات حرارية">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell Store | Premium Wellness Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link href="https://api.fontshare.com/v2/css?f[]=plus-jakarta-sans@400,500,600,700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --glow-pink: #FF8FB1; --glow-bg: #FFF9FB; }
        html { scroll-behavior: smooth; } /* تمكين التمرير الناعم */
        body { font-family: <?php echo $user_lang == 'ar' ? "'Cairo', sans-serif" : "'Plus Jakarta Sans', sans-serif"; ?>; background-color: var(--glow-bg); color: #374151; }
        .soft-shadow { box-shadow: 0 20px 40px -15px rgba(255, 143, 177, 0.12); }
        .soft-card { border-radius: 2.5rem; background: white; border: 1px solid rgba(255, 143, 177, 0.05); }
        ::-webkit-scrollbar { display: none; }
        /* دعم الاتجاه للـ RTL */
        [dir="rtl"] .text-left { text-align: right; }
    </style>
</head>
<body class="min-h-screen flex flex-col pb-24 lg:pb-0 relative overflow-x-hidden">

    <?php include 'navbar.php'; ?>

    <?php if($has_pending_request): ?>
    <div class="max-w-7xl mx-auto px-8 mt-6 w-full">
        <div style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 10px; text-align: center; border: 1px solid #ffeeba; font-weight: bold;">
            <?php echo $user_lang == 'ar' ? "⏳ طلبك بشحن $pending_amount GC قيد المراجعة حالياً، سيتم تحديث رصيدك فور موافقة الإدارة." : "⏳ Your request for $pending_amount GC is under review. Balance will be updated upon approval."; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-8 pt-10 w-full flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-gray-800 tracking-tight flex items-center gap-3">
                GlowWell <span class="text-pink-500 text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-400"><?php echo $user_lang == 'ar' ? 'المتجر' : 'Store'; ?></span>
            </h1>
            <p class="text-gray-500 text-sm mt-2 font-medium"><?php echo $user_lang == 'ar' ? 'افتح خططاً مميزة، وصفات حصرية، ومحتوى خاص.' : 'Unlock premium plans, recipes, and exclusive assets.'; ?></p>
        </div>
        
        <div class="flex items-center gap-3 bg-white p-2.5 rounded-[2rem] shadow-lg shadow-pink-100/50 border border-pink-50 w-full md:w-auto justify-between md:justify-start">
            <div class="bg-gradient-to-r from-pink-50 to-rose-50 px-6 py-3 rounded-[1.5rem] flex items-center gap-4 border border-pink-100/50">
                <img src="glowCoin.png" class="w-10 h-10 object-contain drop-shadow-md" alt="GC" onerror="this.style.display='none'">
                <div class="flex flex-col"> 
                    <span class="text-[10px] text-pink-500 font-bold uppercase tracking-widest leading-none mb-1"><?php echo $user_lang == 'ar' ? 'رصيدك' : 'Your Balance'; ?></span> 
                    <span id="user-balance" class="text-2xl font-black text-gray-900 leading-none"><?= number_format($user['glow_coins']) ?> <span class="text-sm text-pink-500">GC</span></span>
                </div>
            </div>

            <button onclick="toggleCart()" class="h-16 w-16 bg-gray-900 text-white hover:bg-pink-500 hover:shadow-lg hover:shadow-pink-200 rounded-[1.5rem] flex items-center justify-center relative transition-all duration-300 transform hover:-translate-y-1">
                <iconify-icon icon="lucide:shopping-cart" class="text-2xl"></iconify-icon>
                <span id="cart-badge" class="absolute -top-2 <?php echo $user_lang == 'ar' ? '-left-2' : '-right-2'; ?> w-6 h-6 bg-pink-500 text-white text-xs flex items-center justify-center rounded-full border-[3px] border-white font-black shadow-sm hidden">0</span>
            </button>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-8 py-10 w-full flex flex-col lg:flex-row gap-10">
        
        <aside class="w-full lg:w-72 shrink-0 space-y-8">
            <div class="soft-card p-6 soft-shadow">
                <h3 class="text-lg font-bold text-gray-800 mb-6 px-2"><?php echo $user_lang == 'ar' ? 'الأقسام' : 'Categories'; ?></h3>
                <div class="space-y-2">
                    <a href="store.php" class="w-full flex items-center justify-between px-4 py-3 <?= !isset($_GET['cat']) ? 'bg-pink-500 text-white shadow-md shadow-pink-100' : 'text-gray-500 hover:bg-pink-50/50' ?> rounded-2xl font-bold transition-all"> 
                        <span class="flex items-center gap-3"><iconify-icon icon="lucide:layout-grid"></iconify-icon> <?php echo $user_lang == 'ar' ? 'الكل' : 'All Items'; ?></span> 
                    </a>
                    <a href="store.php?cat=Recipe" class="w-full flex items-center justify-between px-4 py-3 <?= (isset($_GET['cat']) && $_GET['cat']=='Recipe') ? 'bg-pink-500 text-white shadow-md shadow-pink-100' : 'text-gray-500 hover:bg-pink-50/50' ?> rounded-2xl transition-all font-medium group"> 
                        <span class="flex items-center gap-3"><iconify-icon icon="lucide:utensils"></iconify-icon> <?php echo $user_lang == 'ar' ? 'وصفات حصرية' : 'Premium Recipes'; ?></span> 
                    </a>
                    <a href="store.php?cat=Diet Plan" class="w-full flex items-center justify-between px-4 py-3 <?= (isset($_GET['cat']) && $_GET['cat']=='Diet Plan') ? 'bg-pink-500 text-white shadow-md shadow-pink-100' : 'text-gray-500 hover:bg-pink-50/50' ?> rounded-2xl transition-all font-medium group"> 
                        <span class="flex items-center gap-3"><iconify-icon icon="lucide:calendar-check"></iconify-icon> <?php echo $user_lang == 'ar' ? 'خطط غذائية' : 'Diet Plans'; ?></span> 
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-br from-pink-500 to-rose-400 soft-card p-6 text-white overflow-hidden relative shadow-lg"> 
                <div class="relative z-10">
                    <h3 class="text-lg font-bold mb-2"><?php echo $user_lang == 'ar' ? 'تحتاج رصيد إضافي؟' : 'Need More Coins?'; ?></h3>
                    <p class="text-xs text-pink-100 mb-6"><?php echo $user_lang == 'ar' ? 'اشحن رصيدك الآن لفتح المحتوى الحصري والخطط المميزة.' : 'Instantly top up your balance and unlock premium content.'; ?></p>
                    <div class="space-y-3">
                        <a href="checkout.php?amount=500&price=1.99" class="w-full bg-white/20 hover:bg-white/30 p-3 rounded-2xl flex items-center justify-between transition-all border border-white/20 block">
                            <span class="text-xs font-bold">500 Coins</span>
                            <span class="text-xs bg-white text-pink-600 px-3 py-1 rounded-lg font-bold">$1.99</span>
                        </a>
                        <a href="checkout.php?amount=1500&price=4.99" class="w-full bg-white/20 hover:bg-white/30 p-3 rounded-2xl flex items-center justify-between transition-all border border-white/20 block">
                            <span class="text-xs font-bold">1500 Coins</span>
                            <span class="text-xs bg-white text-pink-600 px-3 py-1 rounded-lg font-bold">$4.99</span>
                        </a>
                    </div>
                </div>
                <iconify-icon icon="lucide:coins" class="absolute -bottom-10 <?php echo $user_lang == 'ar' ? '-left-10' : '-right-10'; ?> text-[180px] text-white/10 rotate-12"></iconify-icon>
            </div>
        </aside>

        <div class="flex-1">
            <div class="flex items-center justify-between mb-8 px-4"> 
                <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <iconify-icon icon="lucide:sparkles" class="text-pink-400"></iconify-icon> <?php echo $user_lang == 'ar' ? 'محتوى حصري' : 'Exclusive Assets'; ?>
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                <?php 
                // التحقق إذا كان هناك منتجات
                if(mysqli_num_rows($items_query) > 0):
                    while($item = mysqli_fetch_assoc($items_query)): 
                        $is_owned = in_array($item['item_id'], $purchased_items);
                ?>
                <div class="soft-card p-6 soft-shadow group flex flex-col">
                    <div class="relative w-full aspect-[4/3] bg-pink-50/50 rounded-[2rem] mb-6 flex items-center justify-center overflow-hidden">
                        <iconify-icon icon="lucide:gem" class="text-6xl text-pink-300 group-hover:scale-110 transition-transform duration-500"></iconify-icon>
                        <div class="absolute top-4 <?php echo $user_lang == 'ar' ? 'right-4' : 'left-4'; ?> bg-white/80 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-black text-pink-500 shadow-sm uppercase tracking-wider">
                            <?= htmlspecialchars($item['item_type']) ?>
                        </div>
                    </div>
                    
                    <h4 class="text-lg font-bold text-gray-800 mb-2"><?= htmlspecialchars($user_lang == 'ar' && !empty($item['item_name_ar']) ? $item['item_name_ar'] : $item['item_name_en']) ?></h4>
                    <p class="text-xs text-gray-400 mb-6 flex-grow"><?= htmlspecialchars($user_lang == 'ar' && !empty($item['description_ar']) ? $item['description_ar'] : $item['description_en']) ?></p>
                    
                    <div class="flex items-center justify-between mt-auto">
                        <?php if($is_owned): ?>
                            <span class="text-sm font-bold text-emerald-500 flex items-center gap-1"><iconify-icon icon="lucide:check-circle"></iconify-icon> <?php echo $user_lang == 'ar' ? 'مملوك' : 'Owned'; ?></span>
                            <a href="<?= htmlspecialchars($item['content_link']) ?>" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-200 transition"><?php echo $user_lang == 'ar' ? 'دخول' : 'Access'; ?></a>
                        <?php else: ?>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400"><?php echo $user_lang == 'ar' ? 'السعر' : 'Price'; ?></span>
                                <span class="text-lg font-black text-gray-800"><?= number_format($item['price_coins']) ?> <span class="text-pink-500 text-xs">GC</span></span>
                            </div>
                            <button onclick="addToCart(<?= $item['item_id'] ?>, '<?= addslashes($user_lang == 'ar' && !empty($item['item_name_ar']) ? $item['item_name_ar'] : $item['item_name_en']) ?>', <?= $item['price_coins'] ?>)" class="w-12 h-12 bg-gray-900 text-white rounded-2xl flex items-center justify-center hover:bg-pink-500 transition-colors shadow-lg">
                                <iconify-icon icon="lucide:shopping-bag" class="text-xl"></iconify-icon>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="col-span-full text-center py-10 text-gray-500">
                        <?php echo $user_lang == 'ar' ? 'لا توجد عناصر في هذا القسم حالياً.' : 'No items found in this category.'; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div id="cart-sidebar" class="fixed inset-y-0 <?php echo $user_lang == 'ar' ? 'left-0 border-r translate-x-[-100%]' : 'right-0 border-l translate-x-full'; ?> w-full sm:w-96 bg-white shadow-2xl z-[60] transform transition-transform duration-500 border-pink-100 flex flex-col">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2"><iconify-icon icon="lucide:shopping-cart" class="text-pink-500"></iconify-icon> <?php echo $user_lang == 'ar' ? 'السلة' : 'Your Cart'; ?></h2>
            <button onclick="toggleCart()" class="p-2 bg-gray-50 text-gray-500 rounded-xl hover:bg-gray-100"><iconify-icon icon="lucide:x"></iconify-icon></button>
        </div>
        
        <div id="cart-items" class="flex-1 overflow-y-auto p-6 space-y-4">
            <p id="empty-cart-msg" class="text-gray-400 text-sm text-center mt-10"><?php echo $user_lang == 'ar' ? 'سلة المشتريات فارغة.' : 'Your cart is empty.'; ?></p>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <span class="text-gray-500 font-bold"><?php echo $user_lang == 'ar' ? 'الإجمالي' : 'Total'; ?></span>
                <span class="text-2xl font-black text-gray-800"><span id="cart-total">0</span> <span class="text-pink-500 text-sm">GC</span></span>
            </div>
            <button onclick="processCheckout()" id="checkout-btn" class="w-full py-4 bg-pink-500 text-white rounded-2xl font-bold shadow-lg shadow-pink-200 hover:bg-pink-600 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <?php echo $user_lang == 'ar' ? 'إتمام الشراء' : 'Complete Purchase'; ?>
            </button>
        </div>
    </div>

    <div id="cart-overlay" onclick="toggleCart()" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300"></div>

    <button id="scrollToTopBtn" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-8 <?php echo $user_lang == 'ar' ? 'left-8' : 'right-8'; ?> bg-gray-900 text-white p-4 rounded-2xl shadow-xl hover:bg-pink-500 transition-all duration-300 transform scale-0 z-50">
        <iconify-icon icon="lucide:arrow-up" class="text-2xl"></iconify-icon>
    </button>

    <script>
        let cart = [];
        const isRtl = document.documentElement.dir === 'rtl';

        function toggleCart() {
            const sidebar = document.getElementById('cart-sidebar');
            const overlay = document.getElementById('cart-overlay');
            const hiddenClass = isRtl ? 'translate-x-[-100%]' : 'translate-x-full';
            
            if (sidebar.classList.contains(hiddenClass)) {
                sidebar.classList.remove(hiddenClass);
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                sidebar.classList.add(hiddenClass);
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        function addToCart(id, name, price) {
            if(cart.find(item => item.id === id)) {
                alert(isRtl ? 'العنصر موجود بالفعل في السلة!' : 'Item is already in your cart!');
                return;
            }
            cart.push({id, name, price});
            updateCartUI();
            
            const hiddenClass = isRtl ? 'translate-x-[-100%]' : 'translate-x-full';
            if(document.getElementById('cart-sidebar').classList.contains(hiddenClass)) {
                toggleCart();
            }
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCartUI();
        }

        function updateCartUI() {
            const itemsContainer = document.getElementById('cart-items');
            const emptyMsg = document.getElementById('empty-cart-msg');
            const totalElement = document.getElementById('cart-total');
            const badge = document.getElementById('cart-badge');
            const checkoutBtn = document.getElementById('checkout-btn');

            let total = 0;
            itemsContainer.innerHTML = '';

            if(cart.length === 0) {
                emptyMsg.style.display = 'block';
                badge.classList.add('hidden');
                checkoutBtn.disabled = true;
                itemsContainer.appendChild(emptyMsg);
            } else {
                emptyMsg.style.display = 'none';
                badge.classList.remove('hidden');
                badge.textContent = cart.length;
                checkoutBtn.disabled = false;

                cart.forEach(item => {
                    total += item.price;
                    itemsContainer.innerHTML += `
                        <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                            <div>
                                <h5 class="text-sm font-bold text-gray-800">${item.name}</h5>
                                <span class="text-xs font-bold text-pink-500">${item.price.toLocaleString()} GC</span>
                            </div>
                            <button onclick="removeFromCart(${item.id})" class="text-gray-300 hover:text-red-500 transition">
                                <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                            </button>
                        </div>
                    `;
                });
            }

            totalElement.textContent = total.toLocaleString();
        }

        async function processCheckout() {
            if(cart.length === 0) return;
            
            const btn = document.getElementById('checkout-btn');
            btn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="animate-spin text-xl"></iconify-icon> ${isRtl ? 'جاري المعالجة...' : 'Processing...'}`;
            btn.disabled = true;

            let hasError = false;

            for(let item of cart) {
                try {
                    const response = await fetch('process_purchase.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'item_id=' + item.id
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if(!data.success) {
                        alert(isRtl ? `فشل شراء ${item.name}: ${data.message}` : `Failed to purchase ${item.name}: ${data.message}`);
                        hasError = true;
                    }
                } catch (error) {
                    console.error("Fetch Error:", error);
                    alert(isRtl ? 'حدث خطأ في الخادم أثناء الدفع. يرجى التحقق من لوحة التحكم.' : 'Server error occurred during checkout. Please check the console.');
                    hasError = true;
                }
            }

            if(!hasError) {
                const hasVIP = cart.some(item => item.id === 3);
                if (hasVIP) {
                    alert(isRtl ? '🎉 تمت عملية الشراء بنجاح! لقد قمت بفتح حساب VIP وحصلت على رصيد هدية مجانية!' : '🎉 Purchase completed successfully! You have unlocked VIP and received a Gift Bonus in your balance!');
                } else {
                    alert(isRtl ? 'تمت عملية الشراء بنجاح!' : 'Purchase completed successfully!');
                }
            }
            location.reload();
        }

        window.onscroll = function() {
            const btn = document.getElementById("scrollToTopBtn");
            if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
                btn.classList.remove("scale-0");
                btn.classList.add("scale-100");
            } else {
                btn.classList.remove("scale-100");
                btn.classList.add("scale-0");
            }
        };
    </script>
</body>
</html>
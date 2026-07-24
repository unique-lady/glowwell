<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowWell - Fitness & Health Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); font-family: 'Outfit', sans-serif; }
    </style>
    <!-- ربط ملف المانيفست -->
<link rel="manifest" href="/manifest.json">

<!-- إعدادات أبل (iOS) لجعله يبدو كتطبيق حقيقي -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="GlowWell">
<link rel="apple-touch-icon" href="/icon.png">

<!-- لون شريط المهام في الأندرويد -->
<meta name="theme-color" content="#ed4b9e">
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6 text-center">

    <div class="mb-10">
        <h1 class="text-6xl font-bold tracking-tight mb-4">
            <span style="color: #ed4b9e;">Glow</span><span style="color: #2ecc71;">Well</span>
        </h1>
        <div class="h-1 w-20 bg-[#ed4b9e] mx-auto rounded-full"></div>
    </div>

    <div class="max-w-2xl mb-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-4 text-pretty">Empower Your Health Journey</h2>
        <p class="text-gray-600 text-lg leading-relaxed">
            GlowWell is a specialized platform designed for fitness enthusiasts to track daily caloric intake, manage personalized workout routines, and visualize health progress through an intuitive dashboard. Our mission is to make fitness accessible and data-driven for everyone.
        </p>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 w-full max-w-md">
        <a href="login.php" class="flex-1 py-4 bg-[#ed4b9e] text-white rounded-2xl font-bold text-xl shadow-lg hover:scale-105 transition-transform">
            Go to Login
        </a>
        <a href="signup.php" class="flex-1 py-4 bg-white text-[#2ecc71] border-2 border-[#2ecc71] rounded-2xl font-bold text-xl shadow-sm hover:bg-[#f0fdf4] transition-all">
            Join Now
        </a>
    </div>

    <footer class="mt-20 border-t border-gray-200 pt-8 w-full max-w-4xl">
        <div class="flex justify-center gap-8 text-gray-400 font-semibold text-sm uppercase tracking-widest">
            <a href="privacy.php" class="hover:text-[#ed4b9e] transition-colors">Privacy Policy</a>
            <a href="about.php" class="hover:text-[#2ecc71] transition-colors">About Us</a>
        </div>
        <p class="mt-6 text-gray-400 text-xs">&copy; 2026 GlowWell Digital Health Platform. All Rights Reserved.</p>
    </footer>

</body>
</html>
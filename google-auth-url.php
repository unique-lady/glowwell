
<?php
// google-auth-url.php
require_once 'config.php';

$params = [
    'client_id'     => trim(GOOGLE_CLIENT_ID),
    'redirect_uri'  => trim(GOOGLE_REDIRECT_URI),
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'offline',
    // --- هذا السطر هو الحل السحري ---
    'prompt'        => 'select_account' 
];

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

// إرسال الرابط كـ JSON لكي يفهمه الجافا سكريبت في صفحة اللوق إن
header('Content-Type: application/json');
echo json_encode(['url' => $auth_url]);
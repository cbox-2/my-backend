<?php
require_once __DIR__ . '/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ========== إعدادات Google ==========
function getGoogleConfig() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM oauth_providers WHERE provider = 'google' LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ========== بدء عملية تسجيل الدخول ==========
if ($action === 'login') {
    $config = getGoogleConfig();
    
    if (!$config || !$config['enabled'] || !$config['client_id']) {
        echo json_encode(['success' => false, 'message' => 'Google login is not configured']);
        exit;
    }
    
    // توليد state لل CSRF protection
    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;
    
    $params = [
        'client_id' => $config['client_id'],
        'redirect_uri' => $config['redirect_uri'],
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => $state,
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    
    $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    
    echo json_encode(['success' => true, 'url' => $authUrl]);
    exit;
}

// ========== استجابة Google (Callback) ==========
if ($action === 'callback') {
    $config = getGoogleConfig();
    
    if (!$config || !$config['enabled']) {
        echo json_encode(['success' => false, 'message' => 'Google login is not enabled']);
        exit;
    }
    
    // التحقق من state
    if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['google_oauth_state'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid state parameter']);
        exit;
    }
    
    unset($_SESSION['google_oauth_state']);
    
    // التحقق من وجود error
    if (isset($_GET['error'])) {
        echo json_encode(['success' => false, 'message' => 'Google error: ' . $_GET['error']]);
        exit;
    }
    
    $code = $_GET['code'] ?? '';
    if (!$code) {
        echo json_encode(['success' => false, 'message' => 'No authorization code received']);
        exit;
    }
    
    // تبديل code بـ access token
    $tokenResponse = exchangeCodeForToken($code, $config);
    if (!$tokenResponse) {
        echo json_encode(['success' => false, 'message' => 'Failed to exchange code for token']);
        exit;
    }
    
    // جلب بيانات المستخدم من Google
    $userInfo = getUserInfoFromGoogle($tokenResponse['access_token']);
    if (!$userInfo) {
        echo json_encode(['success' => false, 'message' => 'Failed to get user info from Google']);
        exit;
    }
    
    // البحث عن المستخدم المرتبط
    $stmt = $pdo->prepare("SELECT * FROM user_social_accounts WHERE provider = 'google' AND provider_user_id = ?");
    $stmt->execute([$userInfo['sub']]);
    $socialAccount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($socialAccount) {
        // مستخدم موجود - تحديث آخر دخول
        $stmt = $pdo->prepare("UPDATE user_social_accounts SET last_login = NOW(), access_token = ?, refresh_token = ?, token_expires = ?, provider_name = ?, provider_email = ?, provider_picture = ? WHERE id = ?");
        $stmt->execute([
            $tokenResponse['access_token'],
            $tokenResponse['refresh_token'] ?? $socialAccount['refresh_token'],
            date('Y-m-d H:i:s', time() + ($tokenResponse['expires_in'] ?? 3600)),
            $userInfo['name'],
            $userInfo['email'],
            $userInfo['picture'],
            $socialAccount['id']
        ]);
        
        $userId = $socialAccount['user_id'];
    } else {
        // مستخدم جديد - البحث عن حساب بنفس البريد
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$userInfo['email']]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            // ربط بالحساب الموجود
            $userId = $existingUser['id'];
        } else {
            // إنشاء مستخدم جديد
            $username = preg_replace('/[^a-zA-Z0-9_]/', '', $userInfo['name']);
            if (strlen($username) < 2) $username = 'user_' . time();
            
            // التأكد من عدم التكرار
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $username .= '_' . substr(md5(uniqid()), 0, 4);
            }
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, level, avatar) VALUES (?, ?, ?, 'user', 2, ?)");
            $stmt->execute([
                $username,
                $userInfo['email'],
                password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                $userInfo['picture']
            ]);
            $userId = $pdo->lastInsertId();
        }
        
        // إنشاء سجل Social Account
        $stmt = $pdo->prepare("INSERT INTO user_social_accounts (user_id, provider, provider_user_id, provider_email, provider_name, provider_picture, access_token, refresh_token, token_expires, last_login) VALUES (?, 'google', ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $userId,
            $userInfo['sub'],
            $userInfo['email'],
            $userInfo['name'],
            $userInfo['picture'],
            $tokenResponse['access_token'],
            $tokenResponse['refresh_token'] ?? null,
            date('Y-m-d H:i:s', time() + ($tokenResponse['expires_in'] ?? 3600))
        ]);
    }
    
    // حفظ الجلسة
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $userInfo['name'];
    $_SESSION['google_login'] = true;
    
    // تحديث آخر دخول في جدول users
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?");
    $stmt->execute([$_SERVER['REMOTE_ADDR'] ?? 'unknown', $userId]);
    
    // إعادة التوجيه للصفحة الرئيسية
    echo '<script>window.top.location.href = "/public/admin/";</script>';
    exit;
}

// ========== حفظ إعدادات Google ==========
if ($action === 'save_settings') {
    header('Content-Type: text/html; charset=utf-8');
    
    $clientId = trim($_POST['client_id'] ?? '');
    $clientSecret = trim($_POST['client_secret'] ?? '');
    $redirectUri = trim($_POST['redirect_uri'] ?? '');
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE oauth_providers SET client_id = ?, client_secret = ?, redirect_uri = ?, enabled = ? WHERE provider = 'google'");
        $stmt->execute([$clientId, $clientSecret, $redirectUri, $enabled]);
        
        echo '<html><body><script>ld=1;parent.setmsg("fgoogle","Google OAuth settings saved",1);parent.rcvdformresponse("t_fgoogle");</script></body></html>';
    } catch (PDOException $e) {
        echo '<html><body><script>ld=1;parent.setmsg("fgoogle","Error saving settings",2);parent.rcvdformresponse("t_fgoogle");</script></body></html>';
    }
    exit;
}

// ========== جلب الإحصائيات ==========
if ($action === 'stats') {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM user_social_accounts WHERE provider = 'google'");
    $total = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM user_social_accounts WHERE provider = 'google' AND last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $active = $stmt->fetchColumn();
    
    echo json_encode(['success' => true, 'total' => $total, 'active' => $active]);
    exit;
}

// ========== فصل الحساب ==========
if ($action === 'unlink') {
    header('Content-Type: text/html; charset=utf-8');
    
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($userId < 1) {
        echo '<html><body><script>ld=1;parent.setmsg("fgoogle","Invalid user ID",2);parent.rcvdformresponse("t_fgoogle");</script></body></html>';
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM user_social_accounts WHERE user_id = ? AND provider = 'google'");
        $stmt->execute([$userId]);
        
        echo '<html><body><script>ld=1;parent.setmsg("fgoogle","Google account unlinked",1);parent.rcvdformresponse("t_fgoogle");</script></body></html>';
    } catch (PDOException $e) {
        echo '<html><body><script>ld=1;parent.setmsg("fgoogle","Error unlinking account",2);parent.rcvdformresponse("t_fgoogle");</script></body></html>';
    }
    exit;
}

// ========== دوال مساعدة ==========
function exchangeCodeForToken($code, $config) {
    $postData = [
        'code' => $code,
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function getUserInfoFromGoogle($accessToken) {
    $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// ========== Default ==========
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;
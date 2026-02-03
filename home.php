<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

try {
    $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {

        session_destroy();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    die('حدث خطأ: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg,
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg,
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin-bottom: 10px;
        }

        .content {
            padding: 40px;
        }

        .welcome-message {
            background:
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-right: 4px solid
        }

        .welcome-message h2 {
            color:
            margin-bottom: 10px;
        }

        .welcome-message p {
            color:
            line-height: 1.6;
        }

        .user-info {
            background:
            border: 2px solid
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .user-info h3 {
            color:
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color:
            width: 150px;
        }

        .info-value {
            color:
            flex: 1;
        }

        .logout-btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg,
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: transform 0.2s;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
        }

        .actions {
            text-align: center;
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 الصفحة الرئيسية</h1>
            <p>نظام تسجيل الدخول باستخدام PHP و PDO</p>
        </div>

        <div class="content">
            <div class="welcome-message">
                <h2>مرحباً، <?php echo htmlspecialchars($user['username']); ?>! 👋</h2>
                <p>تم تسجيل دخولك بنجاح إلى النظام. هذه هي الصفحة الرئيسية المحمية التي لا يمكن الوصول إليها إلا بعد تسجيل الدخول.</p>
            </div>

            <div class="user-info">
                <h3>معلومات الحساب</h3>

                <div class="info-row">
                    <div class="info-label">رقم المستخدم:</div>
                    <div class="info-value">
                </div>

                <div class="info-row">
                    <div class="info-label">اسم المستخدم:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">البريد الإلكتروني:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">تاريخ التسجيل:</div>
                    <div class="info-value"><?php echo date('Y-m-d H:i:s', strtotime($user['created_at'])); ?></div>
                </div>
            </div>

            <div class="actions">
                <a href="logout.php" class="logout-btn">تسجيل الخروج</a>
            </div>
        </div>
    </div>
</body>
</html>

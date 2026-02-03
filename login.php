<?php
// إعدادات أمان الجلسة
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

// توليد توكن CSRF إذا لم يكن موجوداً
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// إذا كان المستخدم مسجل دخول بالفعل، إعادة توجيهه للصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

require_once 'config.php';

$error = '';

// التحقق من إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // التحقق من توكن CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('خطأ في التحقق من أمان النموذج (CSRF)');
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // التحقق من صحة البيانات
    if (empty($username) || empty($password)) {
        $error = 'جميع الحقول مطلوبة';
    } else {
        try {
            // البحث عن المستخدم في قاعدة البيانات
            $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user) {
                // التحقق من كلمة المرور باستخدام password_verify
                if (password_verify($password, $user['password'])) {
                    // تسجيل الدخول ناجح - إنشاء الجلسة
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    
                    // إعادة التوجيه للصفحة الرئيسية
                    header('Location: home.php');
                    exit;
                } else {
                    $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
                }
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
            }
        } catch (PDOException $e) {
            $error = 'حدث خطأ: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-right: 4px solid #c33;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>تسجيل الدخول</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="username">اسم المستخدم أو البريد الإلكتروني:</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">تسجيل الدخول</button>
        </form>
        
        <div class="links">
            <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
        </div>
    </div>
</body>
</html>

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

require_once 'config.php';

$error = '';
$success = '';

// التحقق من إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // التحقق من توكن CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('خطأ في التحقق من أمان النموذج (CSRF)');
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // التحقق من صحة البيانات
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صالح';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif ($password !== $confirm_password) {
        $error = 'كلمات المرور غير متطابقة';
    } else {
        try {
            // التحقق من عدم وجود المستخدم مسبقاً
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل';
            } else {
                // تشفير كلمة المرور باستخدام password_hash
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // إدراج المستخدم الجديد
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                
                $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول';
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
    <title>إنشاء حساب جديد</title>
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
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
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
        
        .success {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-right: 4px solid #3c3;
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
        <h2>إنشاء حساب جديد</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="username">اسم المستخدم:</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit">إنشاء حساب</button>
        </form>
        
        <div class="links">
            <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
        </div>
    </div>
</body>
</html>

<?php
// إعدادات قاعدة البيانات
// إعدادات البيئة (تغييرها إلى 'production' عند النشر الدائم)
define('ENVIRONMENT', 'development'); 

if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// إعدادات قاعدة البيانات (يفضل استخدام متغيرات البيئة في الاستضافة الدائمة)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'login_system');
define('DB_USER', getenv('DB_USER') ?: 'webapp');
define('DB_PASS', getenv('DB_PASS') ?: 'webapp123');
define('DB_CHARSET', 'utf8mb4');

// إنشاء اتصال PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => (ENVIRONMENT === 'development') ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    if (ENVIRONMENT === 'development') {
        die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
    } else {
        die("عذراً، حدث خطأ فني. يرجى المحاولة لاحقاً.");
    }
}
?>

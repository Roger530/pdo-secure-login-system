<?php
session_start();

// إذا كان المستخدم مسجل دخول، توجيهه للصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
} else {
    // إذا لم يكن مسجل دخول، توجيهه لصفحة تسجيل الدخول
    header('Location: login.php');
}
exit;
?>

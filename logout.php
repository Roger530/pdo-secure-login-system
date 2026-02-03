<?php
session_start();

// تدمير جميع بيانات الجلسة
$_SESSION = array();

// حذف ملف تعريف الارتباط الخاص بالجلسة
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// تدمير الجلسة
session_destroy();

// إعادة التوجيه لصفحة تسجيل الدخول
header('Location: login.php');
exit;
?>

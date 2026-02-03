# نظام تسجيل الدخول باستخدام PHP و PDO

## نظرة عامة

هذا المشروع عبارة عن نظام تسجيل دخول كامل تم تطويره باستخدام **PHP** و **PDO** و **MySQL**، مع تطبيق أفضل ممارسات الأمان من خلال تشفير كلمات المرور باستخدام **Hash** (bcrypt).

## المطور

**المطور: ابراهيم**

**Secure Login System using PHP, PDO, and MySQL with CSRF and Session protection. Developed by Ibrahim.**

---

## المتطلبات

لتشغيل هذا المشروع على نظام Kali Linux أو أي نظام Linux آخر، تحتاج إلى:

- **PHP 7.4** أو أحدث (تم الاختبار على PHP 8.1)
- **MySQL 8.0** أو أحدث
- **Apache** أو **خادم PHP المدمج**

## تثبيت المتطلبات على Kali Linux

قم بتشغيل الأوامر التالية لتثبيت جميع المتطلبات:

```bash
sudo apt update
sudo apt install -y php php-mysql php-pdo mysql-server apache2
```

## إعداد قاعدة البيانات

### 1. بدء خدمة MySQL

```bash
sudo service mysql start
```

### 2. إنشاء قاعدة البيانات والجدول

قم بتشغيل السكريبت المرفق لإنشاء قاعدة البيانات:

```bash
sudo mysql < setup_database.sql
```

أو يمكنك إنشاء قاعدة البيانات يدوياً:

```sql
CREATE DATABASE IF NOT EXISTS login_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE login_system;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. إنشاء مستخدم MySQL للتطبيق

```bash
sudo mysql -e "CREATE USER IF NOT EXISTS 'webapp'@'localhost' IDENTIFIED BY 'webapp123';"
sudo mysql -e "GRANT ALL PRIVILEGES ON login_system.* TO 'webapp'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

## تشغيل التطبيق

### الطريقة 1: استخدام خادم PHP المدمج (للتطوير)

```bash
cd /path/to/pdo_login_system
php -S localhost:8080
```

ثم افتح المتصفح على: `http://localhost:8080`

### الطريقة 2: استخدام Apache

```bash
# نسخ الملفات إلى مجلد Apache
sudo cp -r /path/to/pdo_login_system /var/www/html/

# بدء خدمة Apache
sudo service apache2 start
```

ثم افتح المتصفح على: `http://localhost/pdo_login_system`

## هيكل المشروع

```
pdo_login_system/
├── config.php              # ملف الاتصال بقاعدة البيانات (PDO)
├── index.php               # الصفحة الرئيسية (توجيه تلقائي)
├── register.php            # صفحة إنشاء حساب جديد
├── login.php               # صفحة تسجيل الدخول
├── home.php                # الصفحة الرئيسية (محمية)
├── logout.php              # صفحة تسجيل الخروج
├── setup_database.sql      # سكريبت إنشاء قاعدة البيانات
├── test_results.txt        # نتائج الاختبار
└── README.md               # هذا الملف
```

## شرح الملفات

### 1. config.php

يحتوي على إعدادات الاتصال بقاعدة البيانات باستخدام **PDO** مع تفعيل خيارات الأمان:

- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` - عرض الأخطاء كاستثناءات
- `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC` - إرجاع النتائج كمصفوفات ترابطية
- `PDO::ATTR_EMULATE_PREPARES => false` - تعطيل محاكاة Prepared Statements

### 2. register.php

صفحة إنشاء حساب جديد تتضمن:

- **التحقق من صحة البيانات** (Validation)
- **التحقق من عدم تكرار اسم المستخدم أو البريد الإلكتروني**
- **تشفير كلمة المرور** باستخدام `password_hash()` مع خوارزمية bcrypt
- **استخدام Prepared Statements** لمنع SQL Injection
- **تصميم احترافي** مع رسائل خطأ ونجاح واضحة

### 3. login.php

صفحة تسجيل الدخول تتضمن:

- **التحقق من بيانات المستخدم** في قاعدة البيانات
- **التحقق من كلمة المرور** باستخدام `password_verify()` لمقارنة كلمة المرور المدخلة مع Hash المخزن
- **إنشاء جلسة Session** للمستخدم بعد تسجيل الدخول الناجح
- **التوجيه التلقائي** للصفحة الرئيسية بعد تسجيل الدخول

### 4. home.php

الصفحة الرئيسية المحمية تتضمن:

- **حماية الصفحة** - لا يمكن الوصول إليها إلا بعد تسجيل الدخول
- **عرض معلومات المستخدم** من قاعدة البيانات
- **زر تسجيل الخروج**
- **تصميم احترافي** مع عرض رسالة ترحيب شخصية

### 5. logout.php

صفحة تسجيل الخروج تتضمن:

- **تدمير جميع بيانات الجلسة**
- **حذف ملف تعريف الارتباط** (Cookie)
- **التوجيه التلقائي** لصفحة تسجيل الدخول

## ميزات الأمان

### 1. تشفير كلمات المرور (Password Hashing)

يستخدم المشروع دالة `password_hash()` مع خوارزمية **bcrypt** لتشفير كلمات المرور:

```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

كلمات المرور **لا يتم تخزينها كنص عادي** أبداً في قاعدة البيانات، بل يتم تخزين Hash فقط.

### 2. التحقق من كلمات المرور (Password Verification)

عند تسجيل الدخول، يتم استخدام `password_verify()` للتحقق من صحة كلمة المرور:

```php
if (password_verify($password, $user['password'])) {
    // تسجيل الدخول ناجح
}
```

### 3. منع SQL Injection

يستخدم المشروع **Prepared Statements** مع PDO لمنع هجمات SQL Injection:

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
```

### 4. إدارة الجلسات (Session Management)

- استخدام `session_start()` في بداية كل صفحة
- تخزين معلومات المستخدم في `$_SESSION`
- التحقق من تسجيل الدخول قبل الوصول للصفحات المحمية

### 5. التحقق من صحة البيانات (Input Validation)

- التحقق من عدم وجود حقول فارغة
- التحقق من صحة البريد الإلكتروني باستخدام `filter_var()`
- التحقق من طول كلمة المرور (6 أحرف على الأقل)
- التحقق من تطابق كلمة المرور وتأكيدها

### 6. حماية من XSS

استخدام `htmlspecialchars()` عند عرض أي بيانات من المستخدم:

```php
echo htmlspecialchars($username);
```

## كيفية الاستخدام

### 1. إنشاء حساب جديد

- افتح صفحة `register.php`
- أدخل اسم المستخدم والبريد الإلكتروني وكلمة المرور
- اضغط على "إنشاء حساب"
- ستظهر رسالة نجاح، ثم يمكنك تسجيل الدخول

### 2. تسجيل الدخول

- افتح صفحة `login.php`
- أدخل اسم المستخدم (أو البريد الإلكتروني) وكلمة المرور
- اضغط على "تسجيل الدخول"
- سيتم توجيهك تلقائياً للصفحة الرئيسية

### 3. الصفحة الرئيسية

- بعد تسجيل الدخول، ستظهر الصفحة الرئيسية
- تحتوي على معلوماتك الشخصية
- يمكنك تسجيل الخروج من خلال الزر المخصص

## نتائج الاختبار

تم اختبار جميع مكونات النظام بنجاح:

✅ **صفحة التسجيل** - تعمل بشكل صحيح وتخزن كلمات المرور كـ Hash  
✅ **صفحة تسجيل الدخول** - تتحقق من Hash بشكل صحيح  
✅ **الصفحة الرئيسية** - محمية ولا يمكن الوصول إليها بدون تسجيل دخول  
✅ **تسجيل الخروج** - يدمر الجلسة بشكل صحيح  
✅ **تشفير كلمات المرور** - يستخدم bcrypt ($2y$10$...)  
✅ **PDO و Prepared Statements** - تعمل بشكل صحيح  

يمكنك الاطلاع على التفاصيل الكاملة في ملف `test_results.txt`.

## التخصيص

### تغيير إعدادات قاعدة البيانات

قم بتعديل ملف `config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'login_system');
define('DB_USER', 'webapp');
define('DB_PASS', 'webapp123');
```

### تغيير التصميم

جميع الصفحات تحتوي على CSS مضمن في نفس الملف، يمكنك تعديل الألوان والتصميم حسب رغبتك.

## الأخطاء الشائعة وحلولها

### خطأ: "فشل الاتصال بقاعدة البيانات"

**الحل:**
- تأكد من تشغيل خدمة MySQL: `sudo service mysql start`
- تأكد من صحة بيانات الاتصال في `config.php`
- تأكد من إنشاء المستخدم `webapp` ومنحه الصلاحيات

### خطأ: "Access denied for user 'root'@'localhost'"

**الحل:**
- استخدم مستخدم MySQL مخصص بدلاً من root
- قم بتشغيل الأوامر في قسم "إنشاء مستخدم MySQL للتطبيق"

### الصفحة لا تظهر بشكل صحيح

**الحل:**
- تأكد من أن الخادم يعمل على المنفذ الصحيح
- تأكد من عدم وجود أخطاء PHP في السجلات
- تحقق من أذونات الملفات: `chmod 644 *.php`

## الترخيص

هذا المشروع تعليمي ومفتوح المصدر، يمكنك استخدامه وتعديله بحرية.

---

**ملاحظة مهمة:** هذا المشروع مخصص للأغراض التعليمية. في بيئة الإنتاج، يُنصح بإضافة المزيد من ميزات الأمان مثل:
- HTTPS/SSL
- CSRF Protection
- Rate Limiting
- Two-Factor Authentication (2FA)
- Password Reset Functionality
- Email Verification

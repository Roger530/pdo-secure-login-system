# التفاصيل التقنية للمشروع

## البنية التقنية

### 1. قاعدة البيانات (MySQL)

تم إنشاء قاعدة بيانات باسم `login_system` تحتوي على جدول واحد `users` بالهيكل التالي:

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `id` | INT (Primary Key, Auto Increment) | معرف المستخدم الفريد |
| `username` | VARCHAR(50) UNIQUE | اسم المستخدم (فريد) |
| `email` | VARCHAR(100) UNIQUE | البريد الإلكتروني (فريد) |
| `password` | VARCHAR(255) | كلمة المرور المشفرة (Hash) |
| `created_at` | TIMESTAMP | تاريخ إنشاء الحساب |

**الفهارس (Indexes):**
- فهرس على `username` لتسريع البحث
- فهرس على `email` لتسريع البحث

### 2. الاتصال بقاعدة البيانات (PDO)

تم استخدام **PDO (PHP Data Objects)** للاتصال بقاعدة البيانات بدلاً من mysqli لأنه:

- يدعم عدة أنواع من قواعد البيانات
- أكثر أماناً مع Prepared Statements
- يوفر معالجة أفضل للأخطاء

**إعدادات PDO المستخدمة:**

```php
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // رفع استثناءات عند الأخطاء
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // إرجاع مصفوفات ترابطية
    PDO::ATTR_EMULATE_PREPARES   => false,                   // استخدام Prepared Statements حقيقية
];
```

### 3. تشفير كلمات المرور

#### password_hash()

تستخدم دالة `password_hash()` مع `PASSWORD_DEFAULT` والتي تستخدم حالياً خوارزمية **bcrypt**:

```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

**خصائص bcrypt:**
- تولد Salt عشوائي تلقائياً
- تستغرق وقتاً طويلاً نسبياً (مما يجعل هجمات Brute Force صعبة)
- الناتج بطول 60 حرف يبدأ بـ `$2y$10$`

**مثال على Hash:**
```
$2y$10$teutQ7BlERZrfQOCY0hh.upcYGCNFRcWsIUOeHaahzkr9Lo1JdeH6
```

حيث:
- `$2y$` = خوارزمية bcrypt
- `10` = تكلفة الحساب (Cost Factor)
- الباقي = Salt + Hash

#### password_verify()

للتحقق من كلمة المرور، يتم استخدام:

```php
if (password_verify($password, $hashed_password)) {
    // كلمة المرور صحيحة
}
```

هذه الدالة:
- تستخرج Salt من Hash المخزن
- تشفر كلمة المرور المدخلة بنفس Salt
- تقارن النتيجة مع Hash المخزن

### 4. منع SQL Injection

تم استخدام **Prepared Statements** في جميع الاستعلامات:

```php
// ❌ خطأ - عرضة لـ SQL Injection
$sql = "SELECT * FROM users WHERE username = '$username'";

// ✅ صحيح - آمن من SQL Injection
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
```

**كيف تعمل Prepared Statements:**
1. يتم إرسال الاستعلام إلى MySQL أولاً (مع placeholders)
2. يتم إرسال البيانات بشكل منفصل
3. MySQL يعالج البيانات كقيم فقط، وليس كأوامر SQL

### 5. منع XSS (Cross-Site Scripting)

تم استخدام `htmlspecialchars()` عند عرض أي بيانات من المستخدم:

```php
echo htmlspecialchars($username);
```

هذا يحول الأحرف الخاصة إلى HTML entities:
- `<` → `&lt;`
- `>` → `&gt;`
- `"` → `&quot;`
- `'` → `&#039;`

### 6. إدارة الجلسات (Session Management)

#### بدء الجلسة

```php
session_start();
```

يجب استدعاؤها في بداية كل صفحة قبل أي output.

#### تخزين البيانات

```php
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
```

#### التحقق من تسجيل الدخول

```php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
```

#### تدمير الجلسة

```php
$_SESSION = array();                          // مسح جميع المتغيرات
setcookie(session_name(), '', time()-42000);  // حذف Cookie
session_destroy();                            // تدمير الجلسة
```

### 7. التحقق من صحة البيانات (Validation)

#### التحقق من البريد الإلكتروني

```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'البريد الإلكتروني غير صالح';
}
```

#### التحقق من طول كلمة المرور

```php
if (strlen($password) < 6) {
    $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
}
```

#### التحقق من تطابق كلمات المرور

```php
if ($password !== $confirm_password) {
    $error = 'كلمات المرور غير متطابقة';
}
```

### 8. التوجيه (Redirection)

تم استخدام `header()` للتوجيه:

```php
header('Location: home.php');
exit;  // مهم جداً لإيقاف تنفيذ الكود
```

**ملاحظة:** يجب استدعاء `exit` بعد `header()` لمنع تنفيذ باقي الكود.

## تدفق البيانات (Data Flow)

### عملية التسجيل (Register)

```
1. المستخدم يملأ النموذج
   ↓
2. PHP يتحقق من صحة البيانات
   ↓
3. PHP يتحقق من عدم وجود username/email مكرر (PDO + Prepared Statement)
   ↓
4. PHP يشفر كلمة المرور (password_hash)
   ↓
5. PHP يخزن البيانات في قاعدة البيانات (PDO + Prepared Statement)
   ↓
6. عرض رسالة نجاح
```

### عملية تسجيل الدخول (Login)

```
1. المستخدم يدخل username/email وكلمة المرور
   ↓
2. PHP يبحث عن المستخدم في قاعدة البيانات (PDO + Prepared Statement)
   ↓
3. PHP يتحقق من كلمة المرور (password_verify)
   ↓
4. إذا كانت صحيحة: إنشاء Session وتخزين بيانات المستخدم
   ↓
5. التوجيه للصفحة الرئيسية
```

### الوصول للصفحة الرئيسية (Home)

```
1. المستخدم يحاول الوصول لـ home.php
   ↓
2. PHP يتحقق من وجود Session
   ↓
3. إذا لم توجد: التوجيه لـ login.php
   ↓
4. إذا وجدت: جلب بيانات المستخدم من قاعدة البيانات
   ↓
5. عرض الصفحة الرئيسية مع البيانات
```

## الأمان والحماية

### ما تم تطبيقه ✅

1. **تشفير كلمات المرور** - bcrypt مع Salt عشوائي
2. **Prepared Statements** - منع SQL Injection
3. **htmlspecialchars()** - منع XSS
4. **Session Management** - حماية الصفحات
5. **Input Validation** - التحقق من صحة البيانات
6. **UNIQUE Constraints** - منع تكرار البيانات

### ما يمكن إضافته للإنتاج 🔒

1. **HTTPS/SSL** - تشفير الاتصال
2. **CSRF Protection** - منع هجمات Cross-Site Request Forgery
3. **Rate Limiting** - منع Brute Force Attacks
4. **Password Strength Meter** - قياس قوة كلمة المرور
5. **Email Verification** - التحقق من البريد الإلكتروني
6. **Password Reset** - استعادة كلمة المرور
7. **Two-Factor Authentication** - مصادقة ثنائية
8. **Login Attempts Logging** - تسجيل محاولات الدخول
9. **Account Lockout** - قفل الحساب بعد محاولات فاشلة
10. **Remember Me** - تذكر تسجيل الدخول

## متطلبات الأداء

- **الذاكرة:** 256 MB كحد أدنى
- **المعالج:** أي معالج حديث
- **قاعدة البيانات:** MySQL 5.7+ أو MariaDB 10.2+
- **PHP:** 7.4+ (يفضل 8.0+)

## الاختبار

تم اختبار النظام بنجاح على:
- ✅ Ubuntu 22.04 LTS
- ✅ Kali Linux 2024
- ✅ PHP 8.1.2
- ✅ MySQL 8.0.45

## الخلاصة

هذا المشروع يوضح كيفية بناء نظام تسجيل دخول آمن باستخدام:
- **PDO** للاتصال بقاعدة البيانات
- **Prepared Statements** لمنع SQL Injection
- **password_hash()** و **password_verify()** لتشفير كلمات المرور
- **Sessions** لإدارة حالة المستخدم
- **Input Validation** للتحقق من صحة البيانات

جميع الممارسات المستخدمة تتبع معايير الأمان الحديثة وتوصيات OWASP.

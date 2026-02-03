# دليل التشغيل السريع

## خطوات التشغيل على Kali Linux

### 1. تثبيت المتطلبات

```bash
sudo apt update
sudo apt install -y php php-mysql php-pdo mysql-server
```

### 2. بدء MySQL

```bash
sudo service mysql start
```

### 3. إنشاء قاعدة البيانات

```bash
cd /path/to/pdo_login_system
sudo mysql < setup_database.sql
```

### 4. إنشاء مستخدم MySQL

```bash
sudo mysql -e "CREATE USER IF NOT EXISTS 'webapp'@'localhost' IDENTIFIED BY 'webapp123';"
sudo mysql -e "GRANT ALL PRIVILEGES ON login_system.* TO 'webapp'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 5. تشغيل الخادم

```bash
php -S localhost:8080
```

### 6. فتح المتصفح

افتح المتصفح على: `http://localhost:8080`

---

## بيانات الاختبار

يمكنك استخدام هذه البيانات للاختبار:

- **اسم المستخدم:** test_user
- **البريد الإلكتروني:** test@example.com
- **كلمة المرور:** password123

أو قم بإنشاء حساب جديد من صفحة التسجيل.

---

## ملاحظات مهمة

✅ كلمات المرور مشفرة باستخدام bcrypt  
✅ يستخدم PDO مع Prepared Statements  
✅ محمي من SQL Injection و XSS  
✅ إدارة جلسات آمنة  

للمزيد من التفاصيل، راجع ملف `README.md`

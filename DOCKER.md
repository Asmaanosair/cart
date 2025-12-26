# Docker Setup Guide

دليل بسيط لتشغيل المشروع باستخدام Docker

---

## المتطلبات

- Docker Desktop مُثبت على جهازك
- Git (لتحميل المشروع)

---

## الخطوات

### 1️⃣ تحميل المشروع
```bash
git clone <repository-url>
cd cart
```

### 2️⃣ تشغيل Docker
```bash
docker-compose up -d --build
```

### 3️⃣ انتظر قليلاً (2-3 دقائق)
Docker سيقوم بـ:
- تحميل الـ dependencies
- إنشاء قاعدة البيانات
- تشغيل المشروع

### 4️⃣ افتح المتصفح
```
http://localhost:8080
```

**تم! المشروع يعمل الآن 🎉**

---

## Commands مفيدة

### عرض الـ logs
```bash
docker-compose logs -f
```

### إيقاف المشروع
```bash
docker-compose down
```

### إعادة التشغيل
```bash
docker-compose restart
```

### تشغيل commands داخل Docker
```bash
docker exec -it cart_app php artisan <command>
```

**أمثلة:**
```bash
# إرسال تنبيهات المخزون المنخفض
docker exec -it cart_app php artisan stock:notify-low --queue

# إرسال تقرير المبيعات اليومي
docker exec -it cart_app php artisan sales:report-daily --queue

# فتح Laravel Tinker
docker exec -it cart_app php artisan tinker

# إعادة بناء قاعدة البيانات
docker exec -it cart_app php artisan migrate:fresh --seed
```

---

## المشاكل الشائعة

### المنفذ 8080 مستخدم؟
عدّل `docker-compose.yml`:
```yaml
ports:
  - "8081:80"  # غيّر 8080 إلى أي رقم آخر
```

### مشكلة في الـ permissions؟
```bash
docker exec -it cart_app chmod -R 775 /var/www/storage
docker exec -it cart_app chown -R www-data:www-data /var/www/storage
```

### Docker لا يعمل؟
تأكد من تشغيل Docker Desktop

---

## ملفات Docker

### `docker-compose.yml`
الملف الرئيسي - يحتوي على إعدادات الـ containers

### `docker/app/Dockerfile`
إعدادات PHP + Laravel

### `docker/app/entrypoint.sh`
السكريبت الذي يعمل عند تشغيل الـ container:
- تثبيت dependencies
- إنشاء database
- تشغيل queue worker
- تشغيل scheduler

### `docker/nginx/default.conf`
إعدادات Nginx web server

---

## ماذا يحدث عند تشغيل `docker-compose up`؟

```
1. بناء الـ Docker images
2. إنشاء الـ containers
3. نسخ .env.example إلى .env
4. تثبيت composer dependencies
5. توليد application key
6. تشغيل migrations + seeders
7. تشغيل queue worker (للإيميلات)
8. تشغيل scheduler (للتقارير اليومية)
9. تشغيل الـ web server
```

---

## الخلاصة

**تشغيل المشروع:**
```bash
docker-compose up -d --build
```

**إيقاف المشروع:**
```bash
docker-compose down
```

**فتح المشروع:**
```
http://localhost:8080
```

**كل شيء جاهز! 🚀**

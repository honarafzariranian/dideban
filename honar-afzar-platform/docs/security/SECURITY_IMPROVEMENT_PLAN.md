# برنامه بهبود امنیتی پروژه هنر افزار ایرانیان

**تاریخ ایجاد:** ۱۴۰۴/۰۶/۰۴  
**وضعیت:** در حال اجرا

---

## 📋 فاز ۱: اقدامات فوری (این هفته)

### 1. Rate Limiting
**وضعیت:** ❌ انجام نشده  
**اولویت:** بالا  
**توضیح:** اضافه کردن محدودیت تعداد درخواست به تمام API endpoints

**کد مورد نیاز:**
```php
// routes/api.php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 تلاش در دقیقه

Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:3,1'); // 3 ثبت‌نام در دقیقه

// General API rate limiting
Route::middleware('throttle:60,1')->group(function () {
    // All API routes
});
```

### 2. Authorization Policies
**وضعیت:** ❌ انجام نشده  
**اولویت:** بالا  
**توضیح:** ایجاد Policy برای تمام Models

**فایل‌های مورد نیاز:**
```
app/Policies/
├── WarehousePolicy.php
├── ProductPolicy.php
├── StockMovementPolicy.php
├── PurchaseOrderPolicy.php
└── SupplierPolicy.php
```

### 3. CSRF Protection
**وضعیت:** ⚠️ جزئی  
**اولویت:** بالا  
**توضیح:** اضافه کردن CSRF token به API endpoints

---

## 📋 فاز ۲: اقدامات مهم (این ماه)

### 4. Input Sanitization
**وضعیت:** ❌ انجام نشده  
**اولویت:** متوسط  
**توضیح:** اضافه کردن sanitize به تمام فیلدهای متنی

**راه حل:**
```php
// app/Http/Middleware/SanitizeInput.php
class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            $value = strip_tags($value);
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        });
        
        $request->merge($input);
        
        return $next($request);
    }
}
```

### 5. Password Complexity
**وضعیت:** ❌ انجام نشده  
**اولویت:** متوسط  
**توضیح:** اضافه کردن قوانین پیچیدگی رمز عبور

**کد مورد نیاز:**
```php
'password' => [
    'required',
    'string',
    'min:8',
    'confirmed',
    'regex:/[A-Z]/',      //至少 یک حرف بزرگ
    'regex:/[a-z]/',      //至少 یک حرف کوچک
    'regex:/[0-9]/',      //至少 یک عدد
    'regex:/[@$!%*#?&]/', //至少 یک کاراکتر خاص
],
```

### 6. Session Management
**وضعیت:** ❌ انجام نشده  
**اولویت:** متوسط  
**توضیح:** مدیریت نشست‌های کاربر

**قابلیت‌های مورد نیاز:**
- نمایش نشست‌های فعال
- حذف نشست‌های دیگر
- محدودیت تعداد نشست
- لاگ‌اوت خودکار

### 7. Audit Logging
**وضعیت:** ⚠️ جزئی  
**اولویت:** متوسط  
**توضیح:** لاگ کردن تمام عملیات حساس

**عملیات مورد نیاز:**
- ایجاد/ویرایش/حذف رکوردها
- تأیید/لغو سفارشات
- تغییر رمز عبور
- تغییر مجوزها
- لاگ‌اوت

---

## 📋 فاز ۳: بهبودها (۳ ماه آینده)

### 8. File Upload Security
**وضعیت:** ⚠️ جزئی  
**اولویت:** پایین  
**توضیح:** اضافه کردن امنیت آپلود فایل

**بهبودها:**
- بررسی extension فایل
- بررسی double extension
- محدودیت حجم فایل
- اسکن ویروس
- ذخیره خارج از public directory

### 9. API Versioning
**وضعیت:** ❌ انجام نشده  
**اولویت:** پایین  
**توضیح:** اضافه کردن versioning به API

**ساختار پیشنهادی:**
```
routes/
├── api.php
│   ├── v1/
│   │   ├── auth.php
│   │   ├── inventory.php
│   │   └── ...
│   └── v2/
│       └── ...
```

### 10. API Documentation
**وضعیت:** ❌ انجام نشده  
**اولویت:** پایین  
**توضیح:** ایجاد مستندات کامل API

**ابزار پیشنهادی:** Swagger/OpenAPI

---

## 📋 فاز ۴: تست و تأیید (۶ ماه آینده)

### 11. Security Testing
**وضعیت:** ❌ انجام نشده  
**توضیح:** تست‌های امنیتی خودکار

**تست‌های مورد نیاز:**
- SQL Injection tests
- XSS tests
- CSRF tests
- Authorization bypass tests
- Rate limiting tests

### 12. Penetration Testing
**وضعیت:** ❌ انجام نشده  
**توضیح:** تست نفوذ توسط متخصص

---

## 📊 پیشرفت کلی

| فاز | وضعیت | درصد تکمیل |
|-----|--------|------------|
| فاز ۱ | در حال اجرا | 0% |
| فاز ۲ | شروع نشده | 0% |
| فاز ۳ | شروع نشده | 0% |
| فاز ۴ | شروع نشده | 0% |

**کل:** 0% تکمیل شده

---

## 🎯 اقدامات بعدی

1. **امروز:** شروع به پیاده‌سازی Rate Limiting
2. **این هفته:** ایجاد Authorization Policies
3. **این ماه:** پیاده‌سازی Input Sanitization
4. **۳ ماه آینده:** تکمیل امنیت فایل‌ها و API Documentation

---

**مسئول پیگیری:** Buffy - Codebuff  
**تاریخ آخرین بروزرسانی:** ۱۴۰۴/۰۶/۰۴

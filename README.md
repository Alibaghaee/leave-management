# Leave Management

سامانهٔ بک‌اند سازمانی برای مدیریت مرخصی‌ها (Laravel).  
شامل CRUD درخواست مرخصی، مسیر تأیید (HR → Manager → CEO)، ثبت لاگ‌ها (auditing)، و مکانیزم گزارش‌گیری/تجمیع روزانه (aggregation) مقیاس‌پذیر.

---

## فهرست مطالب

- [شروع سریع](#شروع-سریع)
- [معماری](#معماری)
- [احراز هویت](#احراز-هویت)
- [مسیرها / Endpoints کلی](#مسیرها--endpoints-کلی)
- [قوانین مسیر تأیید (Approval pipeline)](#قوانین-خط-لوله-تأیید-approval-pipeline)
- [Aggregation / Reporting](#aggregation--reporting)
- [نمونه درخواست / پاسخ (Examples)](#نمونه-درخواست--پاسخ-examples)
- [اجرا، صف‌ها و زمان‌بندی (Queue & Scheduler)](#اجرا-صف‌ها-و-زمان‌بندی-queue--scheduler)
- [تست و lint](#تست-و-lint)
- [کدهای خطا / Error codes](#کدهای-خطا--error-codes)
- [OpenAPI / Swagger](#openapi--swagger)
- [نکات توسعه‌دهنده / Contributing](#نکات-توسعه‌دهنده--contributing)
- [لایسنس](#لایسنس)

---

## شروع سریع

نیازمندی‌ها: PHP 8.x، Composer، دیتابیس (MySQL/Postgres/SQLite)، Docker اختیاری

```bash
git clone <repo-url>
cd <repo>

cp .env.example .env
composer install
php artisan key:generate

php artisan migrate --seed

php artisan serve
```

### دریافت توکن Sanctum
```php
php artisan tinker
$user = \App\Models\User::first();
$token = $user->createToken('api')->plainTextToken;
```

در درخواست‌ها ارسال کنید:
```
Authorization: Bearer <TOKEN>
```

---

## معماری

- لایه‌ای: Controllers → DTOs → Services → Repositories → Models
- خروجی API مبتنی بر JsonResource (فرمت `{ data, meta, links }`)
- مراحل تأیید (Stage) با `order`, `role`, `min_days` پیکربندی شده‌اند
- استفاده از لاگ‌ها برای:
    - auditing
    - idempotency
    - رد/تأیید چندمرحله‌ای

مدل‌ها: Employee، LeaveRequest، Stage، LeaveLog  
گزارش‌گیری از طریق summary tables: daily/monthly/yearly

---

## احراز هویت

کل API در مسیر `/api/v1` با middleware زیر محافظت شده:

```
auth:sanctum
```

کلاینت‌ها باید **Bearer Token** ارائه دهند.

---

## مسیرها / Endpoints کلی

```
GET    /api/v1/employees
POST   /api/v1/employees
GET    /api/v1/employees/{id}
PUT    /api/v1/employees/{id}
DELETE /api/v1/employees/{id}

GET    /api/v1/leave-requests
POST   /api/v1/leave-requests
GET    /api/v1/leave-requests/{id}
PUT    /api/v1/leave-requests/{id}
DELETE /api/v1/leave-requests/{id}
POST   /api/v1/leave-requests/{id}/approve
POST   /api/v1/leave-requests/{id}/reject

GET    /api/v1/stages
POST   /api/v1/stages
GET    /api/v1/stages/{id}
PUT    /api/v1/stages/{id}
DELETE /api/v1/stages/{id}

GET    /api/v1/leave-logs
POST   /api/v1/leave-logs

GET    /api/v1/employee-leave-summaries/daily
POST   /api/v1/employee-leave-summaries/daily/aggregate
```

---

## قوانین مسیر تأیید (Approval pipeline)

### نقش‌ها و محدودیت‌ها
- HR → همیشه می‌تواند تأیید کند
- Manager → فقط برای کارکنانی که `employee.manager_id == manager.id`
- CEO → فقط زمانی که `days_count >= min_days` مرحلهٔ CEO باشد

### نوع خطاها طبق تست‌ها:
- اگر کاربر **کاملاً غیرمجاز** باشد → `403 Forbidden`
- اگر manager بخواهد درخواست کسی را که زیرمجموعه‌اش نیست approve کند → `422 Unprocessable Entity`

### مراحل
- Stage شامل:
    - name
    - role
    - order
    - min_days
    - next_stage_id

### Idempotency
`idempotency_key` برای جلوگیری از انجام دوبارهٔ approve/reject استفاده می‌شود  
اگر همان کلید قبلاً مصرف شده باشد، عملیات بدون هیچ تغییری برمی‌گردد.

---

## Aggregation / Reporting

- جدول‌های تجمیعی → روزانه/ماهانه/سالانه
- اجرای دستی:
```bash
php artisan leave:aggregate-employee-leave-daily --date=2025-11-16
```

API:
```
POST /api/v1/employee-leave-summaries/daily/aggregate
```

Query:
```
GET /api/v1/employee-leave-summaries/daily?employee_id=1&date=2025-12-01
```

Aggregation ایمن، idempotent و queue-based است.

---

## نمونه درخواست / پاسخ (Examples)

### ایجاد مرخصی روزانه
```json
POST /api/v1/leave-requests

{
  "leave_type": "annual",
  "start_date": "2025-12-01",
  "end_date": "2025-12-03",
  "reason": "Vacation"
}
```

پاسخ:
```json
{
  "data": {
    "id": 12,
    "employee_id": 3,
    "employee_name": "Ali",
    "leave_type": "annual",
    "days_count": 3,
    "status": "pending_hr",
    "current_stage_id": 1
  }
}
```

### ایجاد مرخصی ساعتی (نیم روز)
```json
{
  "leave_type": "hourly",
  "start_date": "2025-12-10",
  "end_date": "2025-12-10",
  "start_time": "09:00",
  "end_time": "13:00",
  "reason": "Doctor appointment"
}
```

days_count = `(hours / 8)` → مثال بالا = `0.5`

### تأیید مرحله‌ای
```json
POST /api/v1/leave-requests/12/approve
{
  "comment": "OK",
  "idempotency_key": "hr-12-approve"
}
```

---

## اجرا، صف‌ها و زمان‌بندی (Queue & Scheduler)

### Queue worker
```bash
php artisan queue:work
```

### Cron برای schedule:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

پیشنهاد: در Docker دو سرویس جدا برای worker و scheduler.

---

## تست و lint

```bash
php artisan test
php artisan test --parallel
```

در صورت وجود:
```bash
composer cs-check
composer phpstan
```

---

## کدهای خطا / Error codes

| وضعیت | توضیح |
|-------|--------|
| 200 | موفق |
| 201 | ایجاد شد |
| 204 | حذف شد |
| 401 | احراز هویت ناموفق |
| 403 | مجوز کافی نیست |
| 422 | خطای اعتبارسنجی / قوانین کسب‌وکار |
| 500 | خطای سرور |

نمونه خطای 422:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "start_date": ["required"]
  }
}
```

---

## OpenAPI / Swagger

فایل اصلی:
```
openapi.yaml
```

سازگار با Swagger UI / Redoc.  
شامل:
- security scheme برای Bearer token
- تعریف کامل schemaها
- مسیرهای همهٔ منابع

---

## نکات توسعه‌دهنده / Contributing

- پیروی از PSR-12
- ایجاد Pull Request همراه با تست
- عدم تغییر در رفتار pipeline بدون Test
- قبل از merge اجرای کامل
  ```
  php artisan test
  ```

---

## لایسنس

این پروژه با **MIT License** منتشر شده است.


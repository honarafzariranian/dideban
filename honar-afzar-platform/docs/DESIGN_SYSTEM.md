# Honar Afzar Iranian Design System

## Overview

The Honar Afzar Iranian Design System is a comprehensive UI framework designed for RTL-first, Persian-native enterprise applications.

## Design Principles

### 1. RTL-First (راست‌چین)
- All layouts default to RTL direction
- Components are designed with RTL in mind
- Mixed content (Persian + English) handled gracefully

### 2. Persian-First (فارسی اول)
- Primary language: Persian (فارسی)
- Font: Vazirmatn
- Number formatting: Persian numbers where appropriate
- Date formatting: Persian calendar (Jalali)

### 3. Enterprise-Grade
- Professional, clean, minimal design
- Information-dense where appropriate
- Consistent across all products

### 4. Accessible
- WCAG 2.1 compliant
- Keyboard navigation
- Screen reader support
- High contrast ratios

---

## Colors

### Primary Colors
```css
--color-primary-50: #eff6ff;
--color-primary-100: #dbeafe;
--color-primary-200: #bfdbfe;
--color-primary-300: #93c5fd;
--color-primary-400: #60a5fa;
--color-primary-500: #3b82f6;
--color-primary-600: #2563eb;
--color-primary-700: #1d4ed8;
--color-primary-800: #1e40af;
--color-primary-900: #1e3a8a;
```

### Secondary Colors
```css
--color-secondary-50: #f8fafc;
--color-secondary-100: #f1f5f9;
--color-secondary-200: #e2e8f0;
--color-secondary-300: #cbd5e1;
--color-secondary-400: #94a3b8;
--color-secondary-500: #64748b;
--color-secondary-600: #475569;
--color-secondary-700: #334155;
--color-secondary-800: #1e293b;
--color-secondary-900: #0f172a;
```

### Status Colors
```css
--color-success-500: #22c55e;
--color-warning-500: #f59e0b;
--color-danger-500: #ef4444;
--color-info-500: #3b82f6;
```

### Product Colors
```css
--color-andookhtiar: #3b82f6;  /* Inventory - Blue */
--color-fishk: #22c55e;        /* Payroll - Green */
--color-diyara: #8b5cf6;       /* CRM - Purple */
--color-fan-hesab: #f59e0b;    /* Accounting - Yellow */
--color-nameh-yar: #ef4444;    /* Correspondence - Red */
```

---

## Typography

### Font Family
```css
--font-family-primary: 'Vazirmatn', sans-serif;
--font-family-mono: 'Fira Code', monospace;
```

### Font Sizes
```css
--text-xs: 0.75rem;    /* 12px */
--text-sm: 0.875rem;   /* 14px */
--text-base: 1rem;     /* 16px */
--text-lg: 1.125rem;   /* 18px */
--text-xl: 1.25rem;    /* 20px */
--text-2xl: 1.5rem;    /* 24px */
--text-3xl: 1.875rem;  /* 30px */
--text-4xl: 2.25rem;   /* 36px */
```

### Font Weights
```css
--font-light: 300;
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;
```

---

## Spacing

```css
--space-0: 0;
--space-1: 0.25rem;    /* 4px */
--space-2: 0.5rem;     /* 8px */
--space-3: 0.75rem;    /* 12px */
--space-4: 1rem;       /* 16px */
--space-5: 1.25rem;    /* 20px */
--space-6: 1.5rem;     /* 24px */
--space-8: 2rem;       /* 32px */
--space-10: 2.5rem;    /* 40px */
--space-12: 3rem;      /* 48px */
```

---

## Border Radius

```css
--radius-none: 0;
--radius-sm: 0.25rem;   /* 4px */
--radius-md: 0.375rem;  /* 6px */
--radius-lg: 0.5rem;    /* 8px */
--radius-xl: 0.75rem;   /* 12px */
--radius-2xl: 1rem;     /* 16px */
--radius-full: 9999px;
```

---

## Shadows

```css
--shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
--shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
--shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
--shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
```

---

## Components

### Buttons

#### Primary Button
```html
<button class="btn btn-primary">
  دکمه اصلی
</button>
```

#### Secondary Button
```html
<button class="btn btn-secondary">
  دکمه فرعی
</button>
```

#### Danger Button
```html
<button class="btn btn-danger">
  دکمه خطر
</button>
```

### Forms

#### Input
```html
<div class="form-group">
  <label class="form-label">نام</label>
  <input type="text" class="form-input" placeholder="نام را وارد کنید">
</div>
```

#### Select
```html
<div class="form-group">
  <label class="form-label">دپارتمان</label>
  <select class="form-select">
    <option>انتخاب کنید</option>
  </select>
</div>
```

#### Checkbox
```html
<div class="form-check">
  <input type="checkbox" class="form-check-input">
  <label class="form-check-label">فعال</label>
</div>
```

### Tables

```html
<div class="table-container">
  <table class="table">
    <thead>
      <tr>
        <th>نام</th>
        <th>ایمیل</th>
        <th>عملیات</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>علی</td>
        <td>ali@example.com</td>
        <td>
          <button class="btn btn-sm btn-primary">ویرایش</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

### Cards

```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">عنوان کارت</h3>
  </div>
  <div class="card-body">
    <p>محتوای کارت</p>
  </div>
  <div class="card-footer">
    <button class="btn btn-primary">ذخیره</button>
  </div>
</div>
```

### Modals

```html
<div class="modal">
  <div class="modal-overlay"></div>
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">عنوان</h3>
      <button class="modal-close">&times;</button>
    </div>
    <div class="modal-body">
      <p>محتوا</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary">لغو</button>
      <button class="btn btn-primary">تأیید</button>
    </div>
  </div>
</div>
```

### Alerts

```html
<div class="alert alert-success">
  عملیات با موفقیت انجام شد
</div>

<div class="alert alert-danger">
  خطا در انجام عملیات
</div>

<div class="alert alert-warning">
  هشدار: این عملیات غیرقابل بازگشت است
</div>

<div class="alert alert-info">
  اطلاعات: لطفاً فرم را تکمیل کنید
</div>
```

### Badges

```html
<span class="badge badge-success">فعال</span>
<span class="badge badge-danger">غیرفعال</span>
<span class="badge badge-warning">در انتظار</span>
<span class="badge badge-info">جدید</span>
```

### Pagination

```html
<div class="pagination">
  <button class="pagination-btn" disabled>قبلی</button>
  <button class="pagination-btn active">۱</button>
  <button class="pagination-btn">۲</button>
  <button class="pagination-btn">۳</button>
  <button class="pagination-btn">بعدی</button>
</div>
```

---

## Layout

### Sidebar Navigation
```html
<div class="layout">
  <aside class="sidebar">
    <div class="sidebar-header">
      <img src="/logo.svg" alt="لوگو">
      <span>هنر افزار ایرانیان</span>
    </div>
    <nav class="sidebar-nav">
      <a href="/dashboard" class="nav-item active">
        <i class="icon-dashboard"></i>
        <span>داشبورد</span>
      </a>
      <a href="/inventory" class="nav-item">
        <i class="icon-warehouse"></i>
        <span>اندوختیار</span>
      </a>
    </nav>
  </aside>
  <main class="main-content">
    <header class="header">
      <div class="header-right">
        <button class="btn-icon">
          <i class="icon-notification"></i>
        </button>
        <div class="user-menu">
          <img src="/avatar.jpg" alt="کاربر">
          <span>علی رضایی</span>
        </div>
      </div>
    </header>
    <div class="content">
      <!-- Page content -->
    </div>
  </main>
</div>
```

### RTL Layout
```html
<html dir="rtl" lang="fa">
<body>
  <!-- RTL layout -->
</body>
</html>
```

---

## Icons

### Icon Library
Use Lucide icons or custom SVG icons.

### Icon Sizes
```css
--icon-sm: 16px;
--icon-md: 20px;
--icon-lg: 24px;
--icon-xl: 32px;
```

---

## Responsive Breakpoints

```css
--breakpoint-sm: 640px;    /* Mobile */
--breakpoint-md: 768px;    /* Tablet */
--breakpoint-lg: 1024px;   /* Laptop */
--breakpoint-xl: 1280px;   /* Desktop */
--breakpoint-2xl: 1536px;  /* Large Desktop */
```

---

## Accessibility

### Focus States
```css
:focus-visible {
  outline: 2px solid var(--color-primary-500);
  outline-offset: 2px;
}
```

### Skip Link
```html
<a href="#main-content" class="skip-link">
  رد شدن به محتوای اصلی
</a>
```

### ARIA Labels
```html
<button aria-label="بستن">
  <i class="icon-close"></i>
</button>

<nav aria-label="منوی اصلی">
  <!-- Navigation -->
</nav>
```

---

## RTL Considerations

### Margins & Padding
```css
/* Use logical properties */
margin-inline-start: 1rem;  /* margin-left in LTR, margin-right in RTL */
margin-inline-end: 1rem;    /* margin-right in LTR, margin-left in RTL */
padding-inline-start: 1rem;
padding-inline-end: 1rem;
```

### Text Alignment
```css
text-align: start;  /* left in LTR, right in RTL */
text-align: end;    /* right in LTR, left in RTL */
```

### Borders
```css
border-inline-start: 1px solid #ccc;
border-inline-end: 1px solid #ccc;
```

---

## Number Formatting

### Persian Numbers
```javascript
const toPersianNumbers = (num) => {
  const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
  return num.toString().replace(/\d/g, d => persianDigits[d]);
};
```

### Currency Formatting
```javascript
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fa-IR', {
    style: 'currency',
    currency: 'IRR',
  }).format(amount);
};
```

---

## Date Formatting

### Persian Calendar (Jalali)
```javascript
import jalaali from 'jalaali-js';

const toJalali = (date) => {
  const j = jalaali.toJalaali(date);
  return `${j.jy}/${j.jm}/${j.jd}`;
};
```

---

## Component Examples

### Data Table
```html
<div class="data-table">
  <div class="data-table-header">
    <div class="data-table-search">
      <input type="search" placeholder="جستجو...">
    </div>
    <div class="data-table-actions">
      <button class="btn btn-primary">افزودن</button>
      <button class="btn btn-secondary">خروجی</button>
    </div>
  </div>
  <table class="table">
    <!-- Table content -->
  </table>
  <div class="data-table-footer">
    <div class="data-table-info">
      نمایش ۱ تا ۱۰ از ۱۰۰ رکورد
    </div>
    <div class="data-table-pagination">
      <!-- Pagination -->
    </div>
  </div>
</div>
```

### Form Layout
```html
<form class="form-layout">
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">نام</label>
      <input type="text" class="form-input">
    </div>
    <div class="form-group">
      <label class="form-label">نام خانوادگی</label>
      <input type="text" class="form-input">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">ایمیل</label>
      <input type="email" class="form-input">
    </div>
    <div class="form-group">
      <label class="form-label">تلفن</label>
      <input type="tel" class="form-input">
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">ذخیره</button>
    <button type="button" class="btn btn-secondary">لغو</button>
  </div>
</form>
```

---

*Last Updated: 1404/06 (August 2025)*

# Requirements Matrix - Honar Afzar Iranian ERP System

## Executive Summary

**Project:** Honar Afzar Iranian (هنر افزار ایرانیان) Enterprise Resource Planning System  
**Date:** 1404 (2025)  
**Document Type:** Master Implementation Plan  
**Scope:** Complete enterprise software ecosystem

---

## Products Overview

| # | Product | Persian Name | Description | Status |
|---|---------|--------------|-------------|--------|
| 1 | Andookhtiar | اندوختیار | Inventory/Warehouse Management | planned |
| 2 | Fishk | فیشک | Payroll/Salary Management | planned |
| 3 | Diyara | دیارا | CRM/Customer Relationship Management | planned |
| 4 | Fan-Hesab | فن حساب | Accounting/Financial Management | planned |
| 5 | Nameh-Yar | نامه یار | Internal Correspondence/Administrative Automation | planned |
| 6 | Hastama | هستما | Attendance System (EXISTING - Future Integration) | existing |

---

## Module Requirements

### 1. Identity & Authentication

| Requirement | Description | Priority | Product |
|-------------|-------------|----------|---------|
| User Registration | Email/phone-based registration | High | Core |
| Login | Username/password authentication | High | Core |
| 2FA | Two-factor authentication (OTP) | High | Core |
| Password Reset | Secure password reset flow | High | Core |
| Session Management | Active session tracking | Medium | Core |
| Device Management | Device registration/tracking | Medium | Core |
| Social Login | Google/GitHub OAuth (optional) | Low | Core |
| IP Whitelisting | Restrict login by IP | Medium | Core |
| Audit Logging | Track all auth events | High | Core |

### 2. Authorization & RBAC

| Requirement | Description | Priority | Product |
|-------------|-------------|----------|---------|
| Role-Based Access | Define roles with permissions | High | Core |
| Permission Groups | Group permissions by module | High | Core |
| Product Access | Control access per product | High | Core |
| Module Access | Control access per module | High | Core |
| API Access | Token-based API authorization | High | Core |
| Admin Roles | Super admin, organization admin | High | Core |

### 3. Organization Management

| Requirement | Description | Priority | Product |
|-------------|-------------|----------|---------|
| Organization Hierarchy | Companies, branches, departments | High | Core |
| Team Management | Create/manage teams | Medium | Core |
| User Profiles | Personal info, avatar, preferences | Medium | Core |
| Language/Prefs | Persian/English, timezone | Medium | Core |

### 4. Multi-Tenancy

| Requirement | Description | Priority | Product |
|-------------|-------------|----------|---------|
| Data Isolation | Complete data separation per org | High | Core |
| Tenant-aware Queries | Automatic tenant scoping | High | Core |
| Cross-tenant Prevention | Prevent data leakage | High | Core |
| Tenant Configuration | Per-tenant settings | Medium | Core |

### 5. File Management

| Requirement | Description | Priority | Product |
|-------------|-------------|----------|---------|
| File Upload | Secure file upload | High | Core |
| File Storage | Local/S3 storage abstraction | High | Core |
| Access Control | Permission-based file access | High | Core |
| File Metadata | Type, size, version tracking | Medium | Core |
| Image Preview | Thumbnail generation | Medium | Core |
| Document Export | PDF/Excel/CSV export | High | Core |

### 6. Notifications

| Requirement | Description | Priority | Product |
|-------------|-------------|----------|---------|
| In-App Notifications | Real-time notifications | High | Core |
| Email Notifications | Transactional emails | High | Core |
| SMS Integration | SMS notifications (optional) | Medium | Core |
| Push Notifications | Mobile push (Flutter) | Medium | Core |
| Notification Preferences | User notification settings | Medium | Core |
| Templates | Reusable notification templates | Medium | Core |

### 7. Search & Reporting

| Requirement | Description | Priority | Product |
|-------------|-------------|----------|---------|
| Global Search | Search across all modules | High | Core |
| Advanced Filters | Complex filter combinations | High | Core |
| Report Builder | Custom report creation | High | Core |
| Dashboard | Real-time dashboards | High | Core |
| Export Reports | PDF/Excel/CSV export | High | Core |

---

## Product-Specific Requirements

### 8. Andookhtiar (Inventory Management)

| Requirement | Description | Priority | Module |
|-------------|-------------|----------|--------|
| Warehouse Management | Create/manage warehouses | High | Warehouse |
| Product Catalog | Products, categories, units | High | Products |
| Stock Tracking | Real-time stock levels | High | Stock |
| Purchase Orders | PO creation and tracking | High | Purchasing |
| Supplier Management | Supplier profiles and evaluation | Medium | Purchasing |
| Barcode/QR | Barcode/QR code generation | High | Products |
| Stock Movements | Receipt, issue, transfer, adjustment | High | Stock |
| Inventory Counts | Physical count and reconciliation | High | Stock |
| Serial Numbers | Serial/batch tracking | Medium | Products |
| Expiration Dates | Track expiry dates | Medium | Products |
| Hardware Integration | RFID/NFC, data collectors | Medium | Hardware |
| Offline Mode | Offline data collection | Medium | Hardware |
| API Integration | REST API for devices | Medium | Hardware |

### 9. Fishk (Payroll Management)

| Requirement | Description | Priority | Module |
|-------------|-------------|----------|--------|
| Employee Records | Employee profiles and contracts | High | Employees |
| Salary Components | Base salary, allowances, deductions | High | Salary |
| Attendance Integration | Link to Hastama (future) | High | Integration |
| Payroll Calculation | Automated payroll processing | High | Payroll |
| Overtime Calculation | Overtime rules and calculation | Medium | Payroll |
| Leave Management | Leave tracking and deduction | Medium | Leave |
| Tax Calculation | Tax computation | High | Payroll |
| Insurance | Social security/insurance | High | Payroll |
| Payslip Generation | Digital payslips | High | Payroll |
| Approval Workflow | Multi-level payroll approval | High | Workflow |
| History & Audit | Complete payroll history | High | Audit |
| Anomaly Detection | AI-powered anomaly detection | Low | AI |

### 10. Diyara (CRM)

| Requirement | Description | Priority | Module |
|-------------|-------------|----------|--------|
| Lead Management | Lead capture and tracking | High | Leads |
| Customer Profiles | Customer information management | High | Customers |
| Contact Management | Multiple contacts per customer | High | Contacts |
| Company Management | Company accounts | High | Companies |
| Sales Pipeline | Visual pipeline management | High | Pipeline |
| Opportunities | Opportunity tracking | High | Opportunities |
| Activities | Tasks, calls, meetings | High | Activities |
| Campaigns | Marketing campaign management | Medium | Marketing |
| Customer History | Complete interaction history | High | History |
| Customer Scoring | AI-powered lead scoring | Low | AI |
| Sales Analytics | Sales performance analytics | High | Analytics |
| Dashboards | CRM dashboards | High | Dashboard |
| AI Insights | AI-assisted insights | Low | AI |

### 11. Fan-Hesab (Accounting)

| Requirement | Description | Priority | Module |
|-------------|-------------|----------|--------|
| Chart of Accounts | Account structure management | High | Accounts |
| Journal Entries | Debit/credit journal entries | High | Journal |
| Vouchers | Voucher management | High | Vouchers |
| Invoices | Customer/supplier invoices | High | Invoices |
| Payments | Payment processing | High | Payments |
| Expenses | Expense tracking | High | Expenses |
| Income | Income tracking | High | Income |
| Bank Accounts | Bank account management | High | Banking |
| Reconciliation | Bank reconciliation | Medium | Banking |
| Receivables | Accounts receivable | High | AR |
| Payables | Accounts payable | High | AP |
| Fiscal Years | Fiscal year management | High | Periods |
| Period Closing | Month/year end closing | High | Periods |
| Financial Reports | Balance sheet, P&L, etc. | High | Reports |
| Approval Workflow | Financial document approval | High | Workflow |
| Data Locking | Prevent modification of finalized data | High | Integrity |

### 12. Nameh-Yar (Correspondence)

| Requirement | Description | Priority | Module |
|-------------|-------------|----------|--------|
| Incoming Letters | Receive and track incoming | High | Letters |
| Outgoing Letters | Create and send outgoing | High | Letters |
| Internal Letters | Internal department memos | High | Letters |
| Document Routing | Route to recipients | High | Routing |
| Approvals | Multi-level approval workflow | High | Workflow |
| Attachments | File attachments | High | Files |
| Versioning | Document version control | Medium | Versioning |
| Deadlines | Deadline tracking | Medium | Deadlines |
| Reminders | Automated reminders | Medium | Notifications |
| Search | Full-text search | High | Search |
| Archival | Document archival | Medium | Archive |

---

## Cross-Product Integrations

| Integration | From | To | Description | Priority |
|-------------|------|----|-------------|----------|
| Inventory → Accounting | Andookhtiar | Fan-Hesab | Purchase invoices, inventory valuation | High |
| Inventory → Correspondence | Andookhtiar | Nameh-Yar | Approval requests, documents | Medium |
| Payroll → Accounting | Fishk | Fan-Hesab | Salary payments, accounting entries | High |
| Payroll → Attendance | Fishk | Hastama | Attendance data consumption | High |
| CRM → Accounting | Diyara | Fan-Hesab | Customer invoices, payments | High |
| CRM → Inventory | Diyara | Andookhtiar | Product/customer context | Medium |
| Correspondence → All | Nameh-Yar | All | Approvals, documents | High |

---

## Security Requirements

| Requirement | Description | Priority |
|-------------|-------------|----------|
| OWASP ASVS 5.0 | Application security verification | High |
| Password Hashing | bcrypt/Argon2 | High |
| Rate Limiting | Brute-force protection | High |
| CSRF Protection | Cross-site request forgery | High |
| XSS Prevention | Cross-site scripting prevention | High |
| SQL Injection | Parameterized queries | High |
| Input Validation | Server-side validation | High |
| Output Encoding | Prevent injection | High |
| Encryption at Rest | Sensitive data encryption | High |
| Encryption in Transit | TLS/SSL | High |
| API Security | JWT, rate limiting | High |
| File Upload Security | Extension/MIME validation | High |
| Audit Trail | Immutable audit logs | High |
| Session Security | Token rotation, expiry | Medium |
| MFA Ready | Multi-factor architecture | Medium |

---

## Hardware Integration Requirements

| Requirement | Description | Priority |
|-------------|-------------|----------|
| RFID/NFC | Device reading capability | Medium |
| Data Collectors | Android-based devices | Medium |
| Barcode Scanners | 1D/2D barcode support | Medium |
| MQTT/WebSocket | Real-time device communication | Medium |
| Offline Mode | Offline data collection | Medium |
| Device Authentication | Secure device registration | Medium |
| OTA Updates | Over-the-air firmware updates | Low |
| Sync Protocol | Offline-to-online sync | Medium |

---

## AI Requirements

| Requirement | Description | Priority |
|-------------|-------------|----------|
| AI Provider Interface | Pluggable AI abstraction | Medium |
| Demand Forecasting | Inventory prediction | Low |
| Anomaly Detection | Financial/payroll anomalies | Low |
| Customer Scoring | CRM lead scoring | Low |
| Document Classification | Correspondence categorization | Low |
| Intelligent Search | AI-enhanced search | Low |

---

## Compliance & Standards

| Standard | Description | Priority |
|----------|-------------|----------|
| ISO/IEC 27001 | Information security management | High |
| OWASP ASVS 5.0 | Application security verification | High |
| OWASP Top 10 | Web application security risks | High |
| WCAG 2.1 | Web content accessibility | Medium |
| GDPR | Data protection (if applicable) | Medium |

---

## Technical Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP, Laravel |
| Frontend | Vue 3, Inertia.js |
| Mobile | Flutter, Dart |
| Database | MySQL/PostgreSQL |
| Cache | Redis |
| Queue | Redis/Database |
| Search | Laravel Scout/Meilisearch |
| Storage | Local/S3 |
| Build | Vite |
| RTL | Tailwind CSS |
| Typography | Vazirmatn |

---

## Priority Phases

### Phase 1: Foundation (MVP)
- Core Identity & Authentication
- Organization Management
- Multi-tenancy
- Basic RBAC
- Design System (RTL/Persian)
- API Infrastructure

### Phase 2: Core Products
- Andookhtiar (Inventory)
- Fan-Hesab (Accounting)
- Fishk (Payroll)
- Diyara (CRM)
- Nameh-Yar (Correspondence)

### Phase 3: Integrations
- Cross-product integrations
- Hastama integration boundary
- API documentation

### Phase 4: Advanced Features
- AI integration
- Hardware integration
- Mobile app (Flutter)
- Advanced analytics

### Phase 5: Hardening
- Security hardening
- Performance optimization
- Testing & QA
- Documentation

---

*Last Updated: 1404/06 (August 2025)*

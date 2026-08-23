# Master Implementation Roadmap (Phases 1 — 16)

**Application**: LifeBlood Blood Bank & Donor Operations Platform  
**Transformation Plan**: Progressive Enhancement without full rewrite  

---

## 1. Executive Phased Progression Matrix

```text
PHASE 0 ──► PHASE 1 ──► PHASE 2 ──► PHASE 3 ──► PHASE 4 ──► PHASE 5
(Audit &    (Backend     (Database   (UI/UX       (Admin      (Donor
 Architecture) Foundation) Domain)   Design System) Dashboard) Portal)
                                                                 │
                                                                 ▼
PHASE 11 ◄─ PHASE 10 ◄─ PHASE 9 ◄── PHASE 8 ◄── PHASE 7 ◄── PHASE 6
(Automation  (Donations & (Blood     (Unit Bag   (Hospital   (Donor
 Notifications) Appts)     Requests)  Tracking)   Portal)     Eligibility)
    │
    ▼
PHASE 12 ──► PHASE 13 ──► PHASE 14 ──► PHASE 15 ──► PHASE 16
(Analytics & (Granular    (REST API    (Testing &   (Production &
 Reports)     RBAC)        v1)          Security)    CI/CD)
```

---

## 2. Phase-by-Phase Technical Roadmap

### PHASE 0 — Full Repository Audit & Architecture Documentation (**COMPLETED**)
- **Scope**: Complete audit of routes, controllers, models, migrations, security posture, frontend stack, and test coverage. Generation of 7 documentation files in `/docs/`.
- **Breaking Changes Risk**: None (Documentation only).
- **Verification Gate**: 34 PHPUnit tests passing.

---

### PHASE 1 — Backend Foundation & Security Hardening
- **Scope**:
  - Implement Form Request classes for all admin and donor form submissions.
  - Implement Eloquent Policies (`AppointmentPolicy`, `DonorPolicy`, `BloodRequestPolicy`) to eliminate IDOR risks.
  - Refactor controller inline logic into Domain Services (`EligibilityService`, `InventoryService`, `BloodRequestService`).
  - Introduce `DB::transaction()` with pessimistic locking (`lockForUpdate()`) for inventory allocation to eliminate race conditions.
- **Potential Breaking Changes**: Route middleware signature changes.
- **Verification Gate**: New Form Request and Policy PHPUnit tests passing.

---

### PHASE 2 — Database & Domain Model Improvements
- **Scope**:
  - Add missing foreign key indexes and compound performance indexes (`(blood_group_id, status, expiry_date)`).
  - Deprecate and drop legacy `admins` table safely.
  - Introduce `blood_units` table for individual bag tracking (ISBT-128 compliance ready).
  - Introduce `hospitals` and `branches` tables for multi-center support.
- **Potential Breaking Changes**: Migration table drops.
- **Verification Gate**: `php artisan migrate:fresh --seed` runs cleanly; all tests pass.

---

### PHASE 3 — Professional UI/UX Design System
- **Scope**:
  - Establish medical-grade Tailwind CSS color system (Primary Crimson `#dc2626`, Medical Slate, Status Badges).
  - Create reusable Blade components: `<x-stat-card>`, `<x-status-badge>`, `<x-table-filter>`, `<x-empty-state>`, `<x-submit-button>`.
  - Standardize 375px mobile viewport responsiveness across all views.
- **Potential Breaking Changes**: UI view markup refactoring.
- **Verification Gate**: Visual verification across all views at 375px and 1440px viewports.

---

### PHASE 4 — Professional Admin Dashboard
- **Scope**:
  - Refactor `DashboardController` using `DashboardMetricsService`.
  - Add real-time stock alert widgets, critical blood request queues, and recent activity feeds.
- **Verification Gate**: Admin dashboard renders statistics accurately under 100ms.

---

### PHASE 5 — Professional Donor Portal
- **Scope**:
  - Redesign donor dashboard with eligibility countdown clock, digital donor ID card preview, and donation milestone badges.
  - Add appointment cancellation and rescheduling modal.
- **Verification Gate**: Donor appointment lifecycle tests passing.

---

### PHASE 6 — Donor Management & Eligibility
- **Scope**:
  - Formalize `EligibilityService` supporting 56-day whole blood rule, 14-day platelet rule, and medical deferral reasons.
  - Add donor health screening questionnaire.
- **Verification Gate**: `DonorEligibilityTest` suite passing with deferral edge cases.

---

### PHASE 7 — Hospital & Patient Management
- **Scope**:
  - Implement Hospital Requisition Portal for registered hospitals to submit blood orders.
  - Implement hospital user authentication and order tracking dashboard.
- **Verification Gate**: Hospital requisition submission & fulfillment feature tests.

---

### PHASE 8 — Professional Blood Inventory & Batch/Unit Tracking
- **Scope**:
  - Implement unit-level barcode tracking, component type separation (PRBC, FFP, Platelets), storage location logging, and automatic expiration status updates.
- **Verification Gate**: Barcode serial lookup & unit status state machine tests.

---

### PHASE 9 — Blood Requests & Allocation Workflow
- **Scope**:
  - Implement 4-state blood allocation workflow: `Requested` -> `Reserved` -> `Cross-matched` -> `Dispensed`.
  - Add automated inventory reservation rollback on request rejection.
- **Verification Gate**: Multi-admin concurrent allocation feature test under transaction locks.

---

### PHASE 10 — Donation & Appointment Workflow
- **Scope**:
  - Implement end-to-end appointment checking, phlebotomy collection logging, blood bag testing (HIV, Hepatitis B/C, Syphilis), and stock check-in.
- **Verification Gate**: Complete donation-to-inventory pipeline feature test.

---

### PHASE 11 — Notifications & Automation
- **Scope**:
  - Expand Resend email notifications with SMS/WhatsApp notification channel interface.
  - Add automated daily cron scheduler (`app:mark-expired-blood`, `app:send-eligibility-reminders`).
- **Verification Gate**: Mocked notification channel dispatch tests.

---

### PHASE 12 — Reports & Analytics
- **Scope**:
  - Build exportable PDF/Excel reports for blood bank compliance, monthly collection stats, waste analysis, and donor retention.
- **Verification Gate**: Report generation binary stream & CSV output tests.

---

### PHASE 13 — Granular RBAC & Multi-Branch Architecture
- **Scope**:
  - Integrate Spatie Laravel-Permission (`SuperAdmin`, `LabTechnician`, `InventoryManager`, `Receptionist`).
  - Add branch scoping middleware for multi-center deployments.
- **Verification Gate**: Granular permission matrix PHPUnit tests.

---

### PHASE 14 — API v1
- **Scope**:
  - Expose versioned REST API endpoints (`/api/v1/donors`, `/api/v1/inventory`, `/api/v1/requests`) guarded by Laravel Sanctum tokens.
  - Implement JsonApiSerializer & API rate limiting.
- **Verification Gate**: Sanctum API authentication & response schema tests.

---

### PHASE 15 — Testing, Security Testing & Performance
- **Scope**:
  - Expand test coverage to >90%.
  - Run automated security dependency audits (`composer audit`), OWASP ZAP scans, and query optimization (`Clockwork` / `Telescope`).
- **Verification Gate**: Zero high/critical security vulnerabilities; all queries executed under 50ms.

---

### PHASE 16 — CI/CD, Monitoring & Production Hardening
- **Scope**:
  - Configure GitHub Actions CI workflow for automated testing, static analysis (`Larastan`), and zero-downtime deployment pipelines.
  - Configure Health Check monitoring (`spatie/laravel-health`).
- **Verification Gate**: Green CI pipeline build on `master` and `main` branches.

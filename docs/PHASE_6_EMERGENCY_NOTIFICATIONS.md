# Phase 6 — Emergency Blood Requests, Notifications & Clinical Communication Workflow

## Overview

Phase 6 implements a comprehensive **Clinical Communication & Emergency Blood Request Workflow**. It integrates an **Emergency Priority Operations Queue** for administrators, an ABO/Rh **Blood Group Compatibility Matrix**, server-side **Donor Eligibility Filtering**, and an IDOR-protected **User Notification Center** for Admin, Hospital, and Donor users.

All inventory allocations continue to preserve `blood_units` as the **Single Source of Truth** using **FEFO (First Expire, First Out)** pessimistic row locking (`lockForUpdate()`).

---

## 1. Domain Architecture & Schema

### Database Migrations
1. `2026_08_23_184000_create_user_notifications_table.php`:
   - Stores structured user notifications (`user_id`, `type`, `title`, `message`, `data`, `read_at`).
2. `2026_08_23_184100_add_required_by_to_blood_requests_table.php`:
   - Adds `urgency_level` and `required_by` timestamp to `blood_requests`.

---

## 2. Services & Business Logic

### `BloodGroupCompatibilityService`
- Encapsulates medical ABO/Rh compatibility:
  - `O-`: Universal red cell donor compatible with all groups (`O-`, `O+`, `A-`, `A+`, `B-`, `B+`, `AB-`, `AB+`).
  - `O+`: Compatible with `O+`, `A+`, `B+`, `AB+`.
  - `A-`: Compatible with `A-`, `A+`, `AB-`, `AB+`.
  - `A+`: Compatible with `A+`, `AB+`.
  - `B-`: Compatible with `B-`, `B+`, `AB-`, `AB+`.
  - `B+`: Compatible with `B+`, `AB+`.
  - `AB-`: Compatible with `AB-`, `AB+`.
  - `AB+`: Compatible with `AB+`.

### `NotificationService`
- **`notifyAdminEmergencyRequest()`**: Sends immediate critical alerts to all admins when an emergency requisition is filed.
- **`notifyEligibleDonors()`**: Queries donors matching compatible blood groups and validates eligibility server-side via `DonorEligibilityService` (checking active status, medical deferrals, and 56-day donation interval). Ineligible or deferred donors are strictly excluded.
- **`notifyHospitalStatusChange()`**: Alerts requesting hospital user when a requisition is approved, rejected, allocated, or dispensed.
- **Exception Safety**: Notification delivery exceptions are caught and logged; failures never corrupt underlying inventory transactions.

---

## 3. Admin Priority Operations Queue

- **Controller**: [`App\Http\Controllers\Admin\EmergencyRequestAdminController`](file:///C:/Users/My%20PC/OneDrive/Desktop/blood-donor-management-laravel/app/Http/Controllers/Admin/EmergencyRequestAdminController.php)
- **Route**: `/admin/emergency-requests`
- **Server-Side Priority Sorting**:
  1. Priority order: `emergency` -> `urgent` -> `routine`
  2. Earliest `required_by` ascending
  3. Oldest `created_at` ascending
- Eager loading (`with(['patient', 'hospitalEntity', 'user'])`) prevents N+1 query overhead.

---

## 4. User Notification Center & IDOR Protection

- **Controller**: [`App\Http\Controllers\NotificationCenterController`](file:///C:/Users/My%20PC/OneDrive/Desktop/blood-donor-management-laravel/app/Http/Controllers/NotificationCenterController.php)
- **Routes**:
  - Admin: `/admin/notifications-feed`
  - Hospital: `/hospital/notifications`
  - Donor: `/donor/notifications`
- **IDOR Protection**: `markAsRead()` verifies `(int)$notification->user_id === (int)auth()->id()`. Cross-tenant manipulation returns `403 Forbidden`.

---

## 5. Verification Summary

- **Total Test Suite**: 77 passed (178 assertions) — **100% PASS**
- **Test File**: [`Phase6EmergencyNotificationTest.php`](file:///C:/Users/My%20PC/OneDrive/Desktop/blood-donor-management-laravel/tests/Feature/Phase6EmergencyNotificationTest.php)
- **Zero Regressions**: All 72 existing Phase 1–5 tests pass cleanly alongside 5 new Phase 6 feature tests.

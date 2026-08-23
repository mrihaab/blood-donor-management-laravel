# Phase 4 — Blood Bank Operational Workflows & Domain Architecture

## Overview

Phase 4 transforms the Blood Bank & Donor Operations Platform into a clinical operational workflow system. Physical blood stock is strictly governed by `blood_units` as the **Single Source of Truth**, while all inventory reservations, allocations, and dispensings follow **FEFO (First Expire, First Out)** with pessimistic database locking (`lockForUpdate()`).

---

## Key Components Implemented

### 1. Donor Deferral & Detailed Eligibility Engine
- **Entity**: `DonorDeferral` (`temporary`, `permanent`)
- **Service**: `DonorDeferralService` & `DonorEligibilityService`
- **Rules Enforced**:
  - Inactive account status blocks booking.
  - Active temporary or permanent medical deferral blocks booking.
  - 56-day recovery interval between donations.

### 2. Pre-Donation Screening Workflow
- **Entity**: `DonorScreening`
- **Service**: `DonorScreeningService`
- **Clinical Vitals Measured**:
  - Blood Pressure, Pulse (bpm), Temperature (°C), Weight (kg), Hemoglobin (g/dL).
- **Automated Deferrals**:
  - Hemoglobin < 12.5 g/dL, Temperature > 37.5°C, or Weight < 50.0 kg automatically records a medical deferral and transitions appointment status to `deferred`.

### 3. Appointment Lifecycle State Machine
- **Lifecycle**: `scheduled` -> `checked_in` -> `screening` -> `donation_in_progress` -> `completed`
- **Terminal Paths**: `cancelled`, `no_show`, `deferred`
- **Enforcement**: `AppointmentService::transitionState()` rejects arbitrary or invalid backward status jumps.

### 4. Inventory Safety & FEFO Allocation Algorithm
- **Allocation Rule**: `orderBy('expiry_date', 'asc')->lockForUpdate()`
- **Guarantees**:
  - Earliest expiring `BloodUnit` is allocated first.
  - Race conditions prevented via database row-level locking.
  - Expired or discarded units cannot be allocated or dispensed.

### 5. Blood Request Operational Lifecycle
- **Lifecycle**: `pending` -> `under_review` -> `approved` -> `allocated` -> `dispensed` -> `completed`
- **Auditing**: Every approval or dispensing logs an immutable `InventoryTransaction` record.

---

## Verification Summary

- **Total Test Suite**: 65 passed (150 assertions) — **100% PASS**
- **Test File**: [`Phase4OperationalWorkflowTest.php`](file:///C:/Users/My%20PC/OneDrive/Desktop/blood-donor-management-laravel/tests/Feature/Phase4OperationalWorkflowTest.php)

# Phase 5 — Hospital Portal & Blood Requisition Workflow

## Overview

Phase 5 introduces a dedicated **Hospital Portal** for clinical partners. Hospital users can manage patient records belonging to their facility and submit blood requisitions directly to central blood bank operations. All inventory allocations continue to derive from `blood_units` as the **Single Source of Truth** using **FEFO (First Expire, First Out)** pessimistic row locking.

---

## Domain & Security Architecture

### 1. Hospital User Domain
- **Migration**: `2026_08_23_181000_add_hospital_id_to_users_table.php`
- **User Model**: Adds `hospital_id` foreign key and `hospital()` relationship.
- **Server Identity Protection**: Requisition creation automatically binds `hospital_id = auth()->user()->hospital_id`. Client-supplied hospital IDs are ignored.

### 2. Hospital Authorization & IDOR Protection
- **`HospitalPolicy`**: Ensures hospital users only access their own dashboard (`/hospital/dashboard`).
- **`PatientPolicy`**: Enforces patient record isolation so Hospital A cannot view, create, or update patients belonging to Hospital B.
- **`BloodRequestPolicy`**: Restricts requisition viewing to the associated hospital. Administrative operations (`approve`, `reject`, `allocate`, `dispense`) remain strictly admin-only.

### 3. Clinical Requisition Workflow
- **Form Request Validation**: `HospitalRequisitionRequest` validates that selected `patient_id` belongs to the authenticated user's hospital.
- **FEFO Unit Allocation**: Admin approval of a requisition triggers `BloodRequestService::approveRequest()`, which selects available units by `expiry_date ASC` under pessimistic lock (`lockForUpdate()`) and logs an `allocated` `InventoryTransaction`.

---

## Verification Summary

- **Total Test Suite**: 72 passed (165 assertions) — **100% PASS**
- **Test File**: [`Phase5HospitalPortalTest.php`](file:///C:/Users/My%20PC/OneDrive/Desktop/blood-donor-management-laravel/tests/Feature/Phase5HospitalPortalTest.php)
- **IDOR Protection**: Verified via automated feature tests.

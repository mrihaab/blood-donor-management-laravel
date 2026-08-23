# Inventory Architecture Specification

**Application**: LifeBlood Blood Bank & Donor Operations Platform  
**Target Entity**: `blood_units` (Single Source of Truth)  

---

## 1. Single Source of Truth Architecture

Prior to Phase 2 Remediation, stock availability was tracked as simple scalar integers in the aggregate `blood_inventory` table. Following remediation:

- **Primary Source of Truth**: `blood_units` (Individual physical barcode bag records).
- **Derived Aggregate Layer**: `blood_inventory` (Read-only aggregate representation computed dynamically from `blood_units`).
- **No Dual Independent Writes**: Arbitrary manual increments/decrements to `blood_inventory` are eliminated. All stock additions occur via physical unit intake (`DonationService` / `BloodUnitService`).

```text
 Physical Donation / Intake
            │
            ▼
      [BloodUnit] ──(State Transition)──► [InventoryTransaction]
  (Barcode / Bag Level)                      (Auditable Log)
            │
            ▼ (Dynamic COUNT / SUM)
     [BloodInventory]
 (Derived Read-Only Aggregate)
```

---

## 2. Dynamic Expiration Calculation

Whole Blood and component products expire based on component-specific shelf life rather than hardcoded dates:

| Component Code | Name | Storage Temp | Shelf Life |
| :--- | :--- | :--- | :--- |
| `WB` | Whole Blood | 2°C - 6°C | 35 days |
| `PRBC` | Packed Red Blood Cells | 2°C - 6°C | 42 days |
| `PLT` | Platelets | 20°C - 24°C | 5 days |
| `FFP` | Fresh Frozen Plasma | -18°C or colder | 365 days |
| `CRYO` | Cryoprecipitate | -18°C or colder | 365 days |

During donation intake, `DonationService` fetches `BloodComponent::where('code', $code)->first()` and calculates:
`expiry_date = collection_date + component->shelf_life_days`

---

## 3. Concurrency & Locking Strategy

- All stock allocation queries in `BloodRequestService` and `BloodInventoryService` execute within a `DB::transaction()` using **pessimistic row locking** (`lockForUpdate()`).
- In concurrent request scenarios where available units = 1, only one request acquires the lock and transitions the `BloodUnit` status to `allocated`. Concurrent requests attempting to allocate the same unit receive a `RuntimeException` and roll back atomically without negative stock balances.

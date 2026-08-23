# Blood Unit Lifecycle & State Machine Specification

**Application**: LifeBlood Blood Bank & Donor Operations Platform  
**Service Guard**: `App\Services\BloodUnitService`  

---

## 1. State Machine Lifecycle Diagram

```text
               ┌─────────────┐
               │  AVAILABLE  │
               └──────┬──────┘
                      │
        ┌─────────────┼─────────────┐
        │             │             │
        ▼             ▼             ▼
  ┌───────────┐ ┌───────────┐ ┌───────────┐
  │ RESERVED  │ │  EXPIRED  │ │ DISCARDED │
  └─────┬─────┘ └───────────┘ └───────────┘
        │            (Terminal)    (Terminal)
        ▼
  ┌───────────┐
  │ ALLOCATED │
  └─────┬─────┘
        │
        ▼
  ┌───────────┐
  │ DISPENSED │
  └───────────┘
   (Terminal)
```

---

## 2. Transition Rules Matrix

| Current Status | Target Status | Allowed? | Domain Action / Service Trigger |
| :--- | :--- | :--- | :--- |
| `available` | `reserved` | **YES** | Hold units for pending requisition (`BloodInventoryService::reserveUnits`) |
| `available` | `allocated` | **YES** | Directly allocate units to approved request (`BloodRequestService::approveRequest`) |
| `available` | `expired` | **YES** | Dynamic expiration scan (`BloodInventoryService::processExpiredUnits`) |
| `available` | `discarded` | **YES** | Spoilage / lab quarantine discard |
| `reserved` | `allocated` | **YES** | Finalize allocation for blood request |
| `allocated` | `dispensed` | **YES** | Physical dispensing to hospital/patient (`BloodRequestService::dispenseRequest`) |
| `dispensed` | *Any* | **NO** | **Forbidden** (Terminal state) |
| `expired` | *Any* | **NO** | **Forbidden** (Terminal state) |
| `discarded` | *Any* | **NO** | **Forbidden** (Terminal state) |

All state changes are enforced server-side by `BloodUnitService::transitionStatus()`. Attempting an invalid transition throws an `\InvalidArgumentException`.

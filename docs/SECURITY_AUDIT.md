# Security Audit & Vulnerability Assessment

**Application**: LifeBlood Blood Bank & Donor Operations Platform  
**Audit Scope**: Complete Backend, Controllers, Middleware, Database, API, and Frontend  

---

## 1. Executive Summary & Security Posture

The application currently has solid fundamental security measures in place:
- Mass-assignment protection enforced on `User.php` (`role` is excluded from `$fillable`).
- Server-side role assignment enforced in `RegisteredUserController.php` (`$user->role = 'donor'`).
- Production debug mode verified (`APP_DEBUG=false` returns generic 404 without stack traces).
- Security headers enforced via custom `SecurityHeaders` middleware (`X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`).
- Rate limiting implemented on authentication endpoints (Login, Password Reset).
- Admin 2FA TOTP authentication implemented via `pragmarx/google2fa-laravel`.

However, several critical security & concurrency risks were identified during this line-by-line audit that must be hardened in **Phase 1** and subsequent phases.

---

## 2. Comprehensive Security Review Grid

| Vulnerability Vector | Severity | Existing Defense | Identified Weakness / Risk | Remediation Plan |
| :--- | :--- | :--- | :--- | :--- |
| **Insecure Direct Object References (IDOR)** | **HIGH** | `DonorMiddleware` checks `$user->role === 'donor'` | Donor routes accept `{id}` parameter in URL without checking if `$id === auth()->id()`. A malicious donor could tamper with `{id}` to view or update another donor's profile/appointment details. | Replace URL parameters with `auth()->user()->donor` or enforce Eloquent Policies (`AppointmentPolicy`, `DonorPolicy`). |
| **Inventory Allocation Race Condition** | **HIGH** | Single SQL query update in `BloodRequestAdminController` | Multiple simultaneous admin approvals for the same blood group could result in negative inventory balances or double-allocation. | Wrap inventory decrement/reservation in `DB::transaction()` with `lockForUpdate()` pessimistic row locking. |
| **Missing Form Request Validation** | **MEDIUM** | Inline `$request->validate([...])` in controllers | Validation rules are scattered across controller methods rather than isolated in dedicated Form Request classes. | Extract all request validation into `app/Http/Requests/...` classes. |
| **Sensitive Data Exposure (PHI)** | **MEDIUM** | Standard Eloquent JSON serialization | Donor phone numbers, DOB, and address are exposed in general API/Inertia responses without masking. | Create API Resources (`DonorResource`, `UserResource`) with field masking for unauthorized viewers. |
| **Granular RBAC Deficit** | **MEDIUM** | Binary role check (`admin` vs `donor`) | Every admin has full, unrestricted access to all operations (Settings, Deletions, Approvals). Lab technicians should not be able to delete users or modify settings. | Upgrade to Spatie Permission or granular policy permissions (`Phase 13`). |
| **Session Hardening** | **LOW** | Default session configuration | Session timeout is set to 120 minutes without automatic idle timeout for staff. | Implement automatic 15-minute idle logout for admin/staff accounts. |

---

## 3. Detailed Technical Breakdown of High-Risk Items

### IDOR Audit (`app/Http/Controllers/Donor/AppointmentController.php`)
```php
// Existing Code: Accepts $id from route parameter
public function show($id)
{
    $appointment = Appointment::findOrFail($id);
    return view('donor.appointments.show', compact('appointment'));
}
```
**Risk**: If donor User A visits `/donor/appointments/15` (which belongs to donor User B), User A can read User B's appointment notes and personal details.  
**Fix (Phase 1)**:
```php
public function show(Appointment $appointment)
{
    $this->authorize('view', $appointment);
    return view('donor.appointments.show', compact('appointment'));
}
```

### Concurrent Inventory Allocation Race Condition (`app/Http/Controllers/Admin/BloodRequestAdminController.php`)
```php
// Existing Code: Non-atomic stock check
$available = BloodInventory::where('blood_group_id', $groupId)->sum('units_available');
if ($available < $requestedUnits) {
    return back()->with('error', 'Insufficient blood stock');
}
// Time-of-check to time-of-use (TOCTOU) gap occurs here!
BloodInventory::where('blood_group_id', $groupId)->decrement('units_available', $requestedUnits);
```
**Risk**: If two admins approve blood requests for 5 units of O+ when stock is 6 units, both requests pass the `$available < $requestedUnits` check before decrementing, resulting in -4 available units!  
**Fix (Phase 1)**:
```php
DB::transaction(function () use ($groupId, $requestedUnits) {
    $inventory = BloodInventory::where('blood_group_id', $groupId)
        ->lockForUpdate()
        ->firstOrFail();

    if ($inventory->units_available < $requestedUnits) {
        throw new InsufficientStockException("Stock unavailable");
    }

    $inventory->decrement('units_available', $requestedUnits);
    $inventory->increment('units_requested', $requestedUnits);
});
```

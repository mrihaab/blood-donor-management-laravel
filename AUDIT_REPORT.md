# AUDIT_REPORT.md — Comprehensive Production & Code Review

**Target Application**: LifeBlood Blood Donor Management System  
**Deployed URL**: `https://blood-donor-management-laravel.onrender.com/`  
**Repository**: `blood-donor-management-laravel`  
**Audit Date**: August 23, 2026  

---

## PART A: SOURCE CODE REVIEW

### 1. Mass Assignment Guarding (`app/Models/User.php`)
- **Status**: **PASS**
- **Evidence**:
```php
protected $fillable = [
    'name',
    'email',
    'password',
    'status',
];
```
`role` is strictly excluded from `$fillable`. Any incoming request payload attempting `role=admin` will be ignored by Eloquent mass assignment.

---

### 2. Server-Side Role Assignment (`app/Http/Controllers/Auth/RegisteredUserController.php`)
- **Status**: **PASS**
- **Evidence**:
```php
$user = new User([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'status' => 'active',
]);
$user->role = 'donor';
$user->save();
```
The user role is explicitly set to `'donor'` on the model instance server-side, bypassing request inputs.

---

### 3. Middleware Authorization (`AdminMiddleware.php` & `DonorMiddleware.php`)
- **Status**: **PASS**
- **Evidence**:
`AdminMiddleware.php`:
```php
if (!auth()->check()) {
    return redirect()->route('login');
}

if (!auth()->user()->isAdmin()) {
    return redirect('/')->with('error', 'Admin access required');
}
```

`DonorMiddleware.php`:
```php
if (!auth()->check()) {
    return redirect()->route('login');
}

if (auth()->check() && auth()->user()->role !== 'donor') {
    abort(403, 'Unauthorized');
}
```

---

### 4. Route Group Middleware Application (`routes/admin.php` & `routes/donor.php`)
- **Status**: **PASS**
- **Evidence**:
`routes/admin.php`:
```php
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () { ... });
```
`routes/donor.php`:
```php
Route::prefix('donor')->name('donor.')->middleware(['auth', 'verified', 'donor'])->group(function () { ... });
```

---

### 5. Production Debug Mode (`APP_DEBUG` Live Behavior)
- **Status**: **PASS**
- **Evidence (Actual Live 404 Response Output)**:
Visiting `https://blood-donor-management-laravel.onrender.com/this-route-does-not-exist` returns a clean, generic Laravel 404 page without exposing file paths, environment keys, or stack traces:
```http
HTTP/1.1 404 Not Found
Date: Sun, 23 Aug 2026 06:26:45 GMT
Content-Type: text/html; charset=UTF-8
Server: cloudflare

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Not Found</title>
    </head>
    <body class="antialiased">
        <div class="px-4 text-lg text-gray-500 border-r border-gray-400">404</div>
        <div class="ml-4 text-lg text-gray-500 uppercase">Not Found</div>
    </body>
</html>
```

---

### 6. Login Rate Limiting & Throttling (`app/Http/Requests/Auth/LoginRequest.php`)
- **Status**: **PASS**
- **Evidence**:
```php
public function ensureIsNotRateLimited(): void
{
    if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
        return;
    }

    event(new Lockout($this));

    $seconds = RateLimiter::availableIn($this->throttleKey());

    throw ValidationException::withMessages([
        'email' => trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]),
    ]);
}
```

---

### 7. Master Admin Account Protection (`app/Http/Controllers/Admin/UserController.php`)
- **Status**: **PASS**
- **Evidence**:
```php
$masterAdminEmail = env('ADMIN_EMAIL', 'admin@example.com');
if ($user->email === $masterAdminEmail || $user->id === auth()->id()) {
    return redirect()->route('admin.users.index')
        ->with('error', 'The master admin account cannot be deleted.');
}
```

---

### 8. Raw SQL Query Audit
- **Status**: **PASS**
- **Evidence**:
All 7 raw query usages (`BloodInventoryController.php`, `DashboardController.php`, `ReportController.php`, `BloodDonationService.php`) use static aggregate functions (`COUNT(*)`, `SUM(...)`) without dynamic string concatenation.

---

## PART B & C: FEATURE BEHAVIOR CHECKS & HEADERS

### 1. HTTP Response Headers Check (`curl -I`)
- **Status**: **PASS / NOTED**
- **Evidence (Raw Live Header Output)**:
```http
HTTP/1.1 200 OK
Date: Sun, 23 Aug 2026 06:17:20 GMT
Content-Type: text/html; charset=UTF-8
Connection: keep-alive
Cache-Control: no-cache, private
link: <https://blood-donor-management-laravel.onrender.com/build/assets/app-D1vVP_ud.css>; rel="preload"; as="style", <https://blood-donor-management-laravel.onrender.com/build/assets/app-C2_G_Wpy.js>; rel="modulepreload"
rndr-id: 0f9d02d0-7f7c-4970
Server: cloudflare
Set-Cookie: XSRF-TOKEN=...; expires=Sun, 23 Aug 2026 08:17:20 GMT; Max-Age=7200; path=/; secure; samesite=lax
Set-Cookie: lifeblood_session=...; expires=Sun, 23 Aug 2026 08:17:20 GMT; Max-Age=7200; path=/; secure; httponly; samesite=lax
vary: X-Inertia,Accept-Encoding
x-powered-by: PHP/8.2.33
x-render-origin-server: Apache/2.4.68 (Debian)
cf-cache-status: DYNAMIC
```
- **Header Analysis**:
  - `Set-Cookie` headers correctly include `secure`, `httponly`, and `samesite=lax`.
  - `X-Frame-Options` and `X-Content-Type-Options` are currently missing from the default Apache response headers (can be added via custom middleware or Apache header config).

---

### 2. Next Eligible Donation Date Logic Check
- **Status**: **FAIL / ENFORCEMENT MISSING**
- **Evidence**:
In `app/Http/Controllers/Donor/AppointmentController.php`, the `store` method only validates:
```php
$request->validate([
    'appointment_date' => 'required|date|after_or_equal:today',
    'appointment_time' => 'required',
    'units_to_donate' => 'required|integer|min:1|max:2',
]);
```
While `Donor\DashboardController.php` calculates a `nextEligibleDate` (+56 days) for UI display, `AppointmentController` does **not** check `$donor->last_donation_date` upon appointment creation, allowing a donor to book an appointment before 56 days have elapsed.

---

### 3. Blood Unit Expiry Date Tracking Check
- **Status**: **PASS**
- **Evidence**:
In `app/Models/BloodInventory.php`:
```php
protected $fillable = ['blood_group_id', 'quantity', 'units_available', 'expiry_date', 'status'];
public function scopeAvailable($query) {
    return $query->where('status', 'available')->where('expiry_date', '>', now());
}
```
Expiry dates are tracked per inventory batch, and the `available` Eloquent scope automatically excludes expired units.

---

### 4. PDF Report Export Data Check
- **Status**: **PASS**
- **Evidence**:
`ReportController.php` passes dynamic query results directly into DomPDF templates:
```php
$pdf = Pdf::loadView('admin.reports.donors-pdf', compact('donors'));
return $pdf->download('donors-report.pdf');
```
Downloaded PDFs contain live database data rather than empty placeholders.

---

### 5. Automated Emergency Notification Trigger Check
- **Status**: **FAIL / AUTOMATION MISSING**
- **Evidence**:
In `BloodRequestController.php`, creating a request saves the record as `pending` without dispatching mail/in-app notifications:
```php
BloodRequest::create($data);
return redirect()->route('donor.blood_requests.index')->with('success', 'Blood request submitted successfully.');
```
Admin must manually click "Notify Donors" (`admin.blood_requests.notify_donors`) to generate notification records.

---

## PART D: GAP ANALYSIS & PROFESSIONAL AUDIT RANKING

Based on the complete codebase audit, here is the prioritized list of real-world features required to turn this system into an enterprise-grade hospital/blood-bank platform:

1. **Strict 56-Day Donation Rule Enforcement (High Priority)**
   - *Current State*: Displayed on dashboard, but not validated in `AppointmentController::store()`.
   - *Fix*: Add `$lastDonation->addDays(56)` validation guard when creating appointments.

2. **Automated Notification Dispatch (High Priority)**
   - *Current State*: Manual admin notification trigger.
   - *Fix*: Dispatch Laravel Queued Mail (`Mailable`) / SMS API (Twilio) automatically upon urgent blood request creation.

3. **HTTP Security Headers Middleware (Medium Priority)**
   - *Current State*: Missing `X-Frame-Options` and `X-Content-Type-Options` headers.
   - *Fix*: Register a custom `SecurityHeaders` middleware appending clickjacking and MIME-sniffing protection headers.

4. **Multi-Factor Authentication (2FA) for Admins (Medium Priority)**
   - *Current State*: Single-factor password login for Admin Portal.
   - *Fix*: Integrate Laravel Fortify / TOTP 2FA for administrative accounts.

5. **Audit Logging for Inventory & User Actions (Low Priority)**
   - *Current State*: Basic `activity_logs` table exists, but automatic model event logging is partial.
   - *Fix*: Attach Spatie ActivityLog to track all inventory dispensations and user modifications.

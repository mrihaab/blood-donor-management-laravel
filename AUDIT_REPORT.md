# AUDIT_REPORT.md — Blood Donor Management System Review

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

### 5. Production Debug Mode (`APP_DEBUG`)
- **Status**: **PASS**
- **Evidence**:
- Local `.env`: `APP_DEBUG=true` (for local development).
- Render Environment Variables: `APP_DEBUG=false` configured in production service environment settings.

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
Rate limiting is enforced at 5 maximum failed login attempts per email/IP combination.

---

### 7. Master Admin Account Protection (`app/Http/Controllers/Admin/UserController.php`)
- **Status**: **PASS**
- **Evidence**:
`destroy()` method:
```php
$masterAdminEmail = env('ADMIN_EMAIL', 'admin@example.com');
if ($user->email === $masterAdminEmail || $user->id === auth()->id()) {
    return redirect()->route('admin.users.index')
        ->with('error', 'The master admin account cannot be deleted.');
}
```
`toggleStatus()` method:
```php
$masterAdminEmail = env('ADMIN_EMAIL', 'admin@example.com');
if ($user->email === $masterAdminEmail) {
    return redirect()->route('admin.users.index')
        ->with('error', 'The master admin account status cannot be modified.');
}
```

---

### 8. Raw SQL Query Audit
- **Status**: **PASS**
- **Evidence**:
All 7 raw query usages in the application (`BloodInventoryController.php`, `DashboardController.php`, `ReportController.php`, `BloodDonationService.php`) use static aggregate functions (`COUNT(*)`, `SUM(...)`, `DATE(...)`) without dynamic string concatenation of user parameters.

---

## PART B: NORMAL FUNCTIONAL WALKTHROUGH

1. **Donor Registration**:
   - **Status**: **PASS**
   - **Evidence**: Registering via `/register` validates inputs, creates a `User` record with `role = 'donor'`, instantiates a `Donor` profile, logs the user in, and routes directly to `/donor/dashboard`.

2. **Role Isolation & Access Block**:
   - **Status**: **PASS**
   - **Evidence**: A logged-in donor attempting to access `/admin/dashboard` is blocked by `AdminMiddleware` and redirected away.

3. **Admin Dashboard Flow**:
   - **Status**: **PASS**
   - **Evidence**: Admin login routes to `/admin/dashboard` with live dynamic cards for donors, inventory, requests, and appointments.

4. **Donor Appointment Booking**:
   - **Status**: **PASS**
   - **Evidence**: Donors can book appointments selecting center, date, time slot, and units. Data persists in `appointments` DB table and appears in admin management.

5. **Blood Request & Inventory Sync**:
   - **Status**: **PASS**
   - **Evidence**: Dispensing blood updates blood requests to fulfilled and marks corresponding `BloodInventory` units as used via FIFO.

6. **Donor Search & Filter**:
   - **Status**: **PASS**
   - **Evidence**: `DonorController::index` filters donors dynamically by blood group and city using Eloquent queries.

7. **Duplicate Email Validation**:
   - **Status**: **PASS**
   - **Evidence**: Registration rejects existing emails with `'email' => 'unique:users,email'`.

8. **Form Validation Feedback**:
   - **Status**: **PASS**
   - **Evidence**: All forms enforce server-side validation rules and render error bags.

---

## PART C: DEPLOYMENT CONFIGURATION REVIEW

1. **HTTPS Scheme Enforcement**:
   - **Status**: **PASS**
   - **Evidence**: `AppServiceProvider::boot()` forces HTTPS schemes in production via `URL::forceScheme('https')`, and `bootstrap/app.php` trusts reverse proxies via `$middleware->trustProxies(at: '*')`.

2. **HTTP Response Headers**:
   - **Status**: **PASS**
   - **Evidence**: `curl.exe -I https://blood-donor-management-laravel.onrender.com/` returns HTTP 200 OK with secure cookie attributes (`secure`, `httponly`, `samesite=lax`).

3. **Production Debug Mode**:
   - **Status**: **PASS**
   - **Evidence**: Verified local and Render environment configurations.

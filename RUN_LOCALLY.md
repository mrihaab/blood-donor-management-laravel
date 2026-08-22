# How to Run & Test `blood-donor-management-laravel` Locally

This project is fully installed, configured, seeded, and compiled in this folder:
`C:\Users\My PC\Desktop\blood-donor-management-laravel`

---

## 1. Quick Start Commands

To launch the local web server:

```powershell
php artisan serve --port=8000
```

The application will be accessible immediately at:
`http://localhost:8000/`

---

## 2. Canonical Login Credentials

You can use either the standard login page or the instant role switcher:

- **Quick Role Switcher URL**: [http://localhost:8000/login-as](http://localhost:8000/login-as)
- **Standard Login URL**: [http://localhost:8000/login](http://localhost:8000/login)

| Role | Email | Password | Direct Dashboard URL |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin@example.com` | `password` | [http://localhost:8000/admin/dashboard](http://localhost:8000/admin/dashboard) |
| **Blood Donor** | `donor@example.com` | `password` | [http://localhost:8000/donor/dashboard](http://localhost:8000/donor/dashboard) |

---

## 3. Environment & Database Configuration

- **Database Engine**: SQLite
- **Database Path**: `database/database.sqlite`
- **Environment File**: `.env` (Pre-configured with `DB_CONNECTION=sqlite`)

If you ever need to reset and re-seed the database:
```powershell
php artisan migrate:fresh --seed
```

---

## 4. Frontend Asset Compilation

Vite production assets are already compiled into `public/build`.
If you make changes to Vue components or Tailwind CSS files during development, run:

```powershell
npm run dev
```

---

## 5. Automated Verification Test Suite

To run the automated PHPUnit test suite:

```powershell
php artisan test
```

All 25 tests (61 assertions) pass.

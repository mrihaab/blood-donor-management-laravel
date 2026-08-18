# LifeBlood - Blood Donor & Inventory Management System

A professional, full-featured Laravel web application designed to connect blood donors, healthcare providers, and administrators. Built with a modern hybrid stack combining **Inertia.js + Vue 3** for authentication & profile workflows and responsive **Blade templates with Tailwind CSS** for administrative and donor portals.

---

## 🚀 Key Features

### 🧍 Donor Portal
- **Donor Registration & Profile Management**: Register, update blood group, location, and contact information.
- **Donation Eligibility Tracker**: Automatic eligibility calculation based on past donation dates.
- **Appointment Scheduling**: Book, view, reschedule, and cancel blood donation appointments.
- **Donation History**: Detailed record of past donations and collection centers.
- **Emergency Blood Requests**: Request blood for family members or hospital emergencies.

### 🛡️ Admin Portal
- **Dashboard Overview**: Key statistics ($totalDonors$, $activeDonors$, $totalRequests$, $pendingRequests$, $totalDonations$) and real-time inventory overview.
- **Low-Stock Alerting**: Automatic notifications when blood stock drops below threshold levels.
- **Donor Management**: Search, view, edit, and manage registered donors.
- **Blood Inventory Management**: Manage units available per blood group ($A+$, $A-$, $B+$, $B-$, $AB+$, $AB-$, $O+$, $O-$) with expiry dates.
- **Blood Request Approval Workflow**: Review, approve, reject, or fulfill hospital/patient blood requests.
- **Donation Record Keeping**: Log new blood donations directly linked to donors and blood groups.
- **System Reports & PDF Export**: Export Donor reports, Donation logs, Inventory status, and Monthly statistics in PDF format via `barryvdh/laravel-dompdf`.
- **System Settings & User Management**: Configure low stock thresholds, blood groups, operational cities, and staff access.

### 🔐 Role-Based Quick Login
- Access `/login-as` to test role-based portals instantly without manually entering credentials.

---

## 🛠️ Stack & Architecture

- **Backend Framework**: Laravel 11.x (PHP 8.2+)
- **Database**: SQLite (Zero-config local database support)
- **Frontend Hybrid Architecture**:
  - **Inertia.js + Vue 3**: Authentication (Login, Register, Password Reset) and User Profile.
  - **Blade + Tailwind CSS**: Admin Panel & Donor Panel management views.
- **Asset Bundler**: Vite 5.x with Tailwind CSS 3.x
- **PDF Engine**: `barryvdh/laravel-dompdf`

---

## 🔑 Canonical Test Credentials

After seeding the database, you can log in using the following canonical accounts:

| Portal | Email | Password | Role |
| :--- | :--- | :--- | :--- |
| **Admin Portal** | `admin@example.com` | `password` | Administrator |
| **Donor Portal** | `donor@example.com` | `password` | Donor |

---

## ⚙️ Installation & Setup Guide

### 1. Prerequisites
- **PHP** 8.2 or higher (with `pdo_sqlite`, `fileinfo`, `gd`, `zip` extensions enabled)
- **Composer** 2.x
- **Node.js** 18+ and **npm**

### 2. Clone & Environment Setup
```bash
git clone https://github.com/mrihaab/blood-donor-management-laravel.git
cd blood-donor-management-laravel
cp .env.example .env
```

### 3. Install Dependencies
```bash
composer install
npm install
```

### 4. Database Setup & Seeding
```bash
touch database/database.sqlite
php artisan key:generate
php artisan migrate:fresh --seed
```

### 5. Compile Frontend Assets
```bash
npm run build
```

### 6. Run Application Server
```bash
php artisan serve
```
Visit `http://localhost:8000` in your web browser.

---

## 🧪 Testing

Run automated PHPUnit test suites:
```bash
php artisan test
```

---

## 📄 License

This project is open-source software licensed under the [MIT license](LICENSE).

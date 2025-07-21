# 🩸 BLOOD DONATION MANAGEMENT SYSTEM - COMPLETE TESTING GUIDE

## 🚀 **STARTING THE SYSTEM**

### Step 1: Start the Laravel Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
**Expected Output:** `Server running on [http://0.0.0.0:8000]`

### Step 2: Access the Application
- **Main URL:** http://localhost:8000
- **Quick Login Interface:** http://localhost:8000/login-as

---

## 🔐 **TESTING ALL FEATURES - STEP BY STEP**

### 🎯 **OPTION 1: Using Quick Login Interface (Recommended)**

1. **Go to:** http://localhost:8000/login-as
2. **You'll see two dropdown options:**
   - **Role:** Select "admin" or "donor"
   - **Email:** Select from pre-configured accounts

#### 📋 **Available Test Accounts:**

**👑 ADMIN ACCOUNTS:**
- `admin@bdms.com` (Role: admin)
- `admin@example.com` (Role: admin)
- `mrihaab6@gmail.com` (Role: admin)

**🩸 DONOR ACCOUNTS:**
- `donor@example.com` (Role: donor)
- `donor@gmail.com` (Role: donor)
- `70136997@student.uol.edu.pk` (Role: donor)

---

## 🧑‍💼 **TESTING ADMIN FEATURES**

### Step 1: Login as Admin
1. Go to http://localhost:8000/login-as
2. Select **Role:** `admin`
3. Select **Email:** `admin@bdms.com`
4. Click **Login**

### Step 2: Test Dashboard
✅ **Features to Test:**
- View total statistics (donors, requests, donations)
- Check blood inventory charts
- Verify recent activities log
- Test low-stock alerts

### Step 3: Test Donor Management
📍 **Navigation:** Admin Dashboard → Donors
✅ **Features to Test:**
- View all donors in table format
- Use search functionality
- Filter by blood group and city
- Click "View" on any donor to see profile
- Test "Block/Unblock" functionality
- Try "Add New Donor" button

### Step 4: Test Donation Records
📍 **Navigation:** Admin Dashboard → Donations
✅ **Features to Test:**
- View all donation records
- Filter by donor, date, blood group
- Add new donation entry
- Edit existing donation
- Test eligibility checker

### Step 5: Test Blood Requests
📍 **Navigation:** Admin Dashboard → Blood Requests
✅ **Features to Test:**
- View all blood requests
- Approve/reject requests
- Assign donors manually
- Send notifications to donors

### Step 6: Test Blood Inventory
📍 **Navigation:** Admin Dashboard → Inventory
✅ **Features to Test:**
- View current stock levels
- Update stock quantities
- Check low-stock alerts
- Test expiry date tracking

### Step 7: Test Reports & Export
📍 **Navigation:** Admin Dashboard → Reports
✅ **Features to Test:**
- Generate donor reports
- Export to CSV/PDF
- View monthly statistics
- Filter by date ranges

### Step 8: Test Notifications
📍 **Navigation:** Admin Dashboard → Notifications
✅ **Features to Test:**
- Send individual notifications
- Send bulk notifications to all donors
- View sent message history
- Test email functionality

### Step 9: Test User Management
📍 **Navigation:** Admin Dashboard → Users
✅ **Features to Test:**
- View all users (admins & donors)
- Create new admin accounts
- Edit user roles and status
- Change passwords

### Step 10: Test Settings
📍 **Navigation:** Admin Dashboard → Settings
✅ **Features to Test:**
- Update organization information
- Manage blood groups
- Edit city/region lists
- Configure system settings

---

## 🧑‍🦰 **TESTING DONOR FEATURES**

### Step 1: Login as Donor
1. Go to http://localhost:8000/login-as
2. Select **Role:** `donor`
3. Select **Email:** `donor@example.com`
4. Click **Login**

### Step 2: Test Donor Dashboard
✅ **Features to Test:**
- View personal donation overview
- Check next eligible donation date
- See blood group information
- Review notifications

### Step 3: Test Profile Management
📍 **Navigation:** Donor Dashboard → Profile
✅ **Features to Test:**
- View current profile information
- Edit personal details
- Change password
- Upload documents/ID

### Step 4: Test Appointment Booking
📍 **Navigation:** Donor Dashboard → Appointments
✅ **Features to Test:**
- Book new appointment
- View scheduled appointments
- Cancel appointments
- Check appointment history

### Step 5: Test Blood Requests
📍 **Navigation:** Donor Dashboard → Request Blood
✅ **Features to Test:**
- Submit blood request for patient
- Provide hospital information
- Track request status
- View request history

### Step 6: Test Donation History
📍 **Navigation:** Donor Dashboard → History
✅ **Features to Test:**
- View past donations
- Check donation dates
- See blood group confirmations
- Download donation certificates

---

## 🔧 **ADVANCED TESTING SCENARIOS**

### 1. **Workflow Testing:**
- Admin creates a blood request
- System notifies eligible donors
- Donor responds to request
- Admin approves and schedules donation
- Donation is recorded and inventory updated

### 2. **Notification Testing:**
- Send bulk email to all donors
- Test individual notifications
- Verify email delivery status

### 3. **Inventory Management:**
- Add new blood donation
- Check automatic inventory update
- Test low-stock alerts
- Verify expiry date warnings

### 4. **Reporting Testing:**
- Generate monthly reports
- Export data to CSV/PDF
- Filter reports by various criteria

---

## 🐛 **TROUBLESHOOTING**

### If you encounter issues:

1. **Database Connection Error:**
   ```bash
   php db_test.php
   ```

2. **Server Not Starting:**
   ```bash
   php artisan serve --port=8001
   ```

3. **Login Issues:**
   - Use the quick login interface at `/login-as`
   - Check if accounts exist in database

4. **Missing Data:**
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## 📊 **CURRENT SYSTEM STATUS**

✅ **Database:** MySQL - Connected (`new_blood_db`)
✅ **Users:** 10 total (3 admins, 7 donors)
✅ **Blood Groups:** 8 types (A+, A-, B+, B-, AB+, AB-, O+, O-)
✅ **Inventory:** 47 blood units available
✅ **All Features:** Implemented and functional

---

## 🎉 **SUCCESS INDICATORS**

You'll know the system is working correctly when you can:
- ✅ Login successfully using quick login
- ✅ Navigate between all admin/donor sections
- ✅ View data in tables and dashboards
- ✅ Create, edit, and delete records
- ✅ Generate reports and export data
- ✅ Send notifications
- ✅ Manage inventory and settings

---

## 📞 **NEXT STEPS**

Once testing is complete, you can:
1. Customize the organization settings
2. Add real user accounts
3. Configure email settings for production
4. Set up automated backups
5. Deploy to production server

**🎯 Start your testing at: http://localhost:8000/login-as**

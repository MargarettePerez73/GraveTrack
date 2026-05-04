# GraveTrack - Quick Start Guide

## ⚡ 5-Minute Setup

### Step 1: Install XAMPP
- Download XAMPP from https://www.apachefriends.org/
- Install with Apache, MySQL, and PHP components
- Start XAMPP Control Panel

### Step 2: Start Services
```
✓ Click "Start" on Apache
✓ Click "Start" on MySQL
```

### Step 3: Create Database
1. Open browser: `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Database name: `gravetrack_db`
4. Click "Create"
5. Click "Import" tab
6. Choose file: `database_setup.sql`
7. Click "Go"

### Step 4: Copy Project Files
```
Copy entire project folder to:
C:\xampp\htdocs\gravetrack\

Your structure should look like:
C:\xampp\htdocs\gravetrack\
├── Database/
│   └── db_connector.php
├── api/
│   ├── auth.php
│   ├── get_plots.php
│   └── ... (all other API files)
├── database_setup.sql
└── README.md
```

### Step 5: Test the Setup
Open browser and visit:
```
http://localhost/gravetrack/api/get_plots.php
```

You should see JSON response like:
```json
{
  "success": true,
  "data": [...]
}
```

## 🧪 Testing the APIs

### Option 1: Using Browser
Simply paste these URLs in your browser:

- Get all plots: `http://localhost/gravetrack/api/get_plots.php`
- Get vacant plots: `http://localhost/gravetrack/api/get_vacant_plots.php`
- Get vacancy stats: `http://localhost/gravetrack/api/get_vacancy_stats.php`

### Option 2: Using PHP CLI Test Script
```bash
cd C:\xampp\htdocs\gravetrack
php test_api.php
```

### Option 3: Using Postman or Insomnia
Import this collection:

**Login:**
```
POST http://localhost/gravetrack/api/auth.php
Content-Type: application/json

{
  "username": "testdummy1",
  "password": "12345"
}
```

**Get Plots:**
```
GET http://localhost/gravetrack/api/get_plots.php
```

## 👥 Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Treasurer | testdummy1 | 12345 |
| Engineer | engineer1 | 12345 |

## 🎯 Common Tasks

### Add a New Burial Record
```javascript
POST /api/save_burial_record.php

{
  "full_name": "Juan Dela Cruz",
  "date_of_death": "2026-05-01",
  "date_of_burial": "2026-05-03",
  "gender": "Male",
  "address": "Manila, Philippines",
  "plot_id": 4,
  "burial_type": "Single",
  "birth_date": "1950-01-01",
  "contact_person": "Maria Dela Cruz",
  "contact_number": "09123456789"
}
```

### Get Payment Summary (Treasurer only - Login first!)
```javascript
GET /api/get_payment_summary.php
```

### Process a Payment (Treasurer only)
```javascript
POST /api/process_payment.php

{
  "rental_id": 1,
  "amount": 2000,
  "payment_date": "2026-05-04",
  "deceased_name": "Maria Santos"
}
```

## 🔧 Troubleshooting

### "Database connection failed"
- ✓ Check if MySQL is running in XAMPP
- ✓ Verify database name is `gravetrack_db`
- ✓ Check credentials in `Database/db_connector.php`

### "404 Not Found"
- ✓ Verify files are in `C:\xampp\htdocs\gravetrack\`
- ✓ Check Apache is running
- ✓ Verify URL: `http://localhost/gravetrack/api/...`

### "Session not found" or "Unauthorized"
- ✓ Login first using `/api/auth.php`
- ✓ Ensure cookies are enabled
- ✓ Check if session is stored (some endpoints require login)

### CORS Errors (if using separate frontend)
- ✓ Ensure `.htaccess` is in the project root
- ✓ Enable `mod_headers` in Apache:
  1. Open `C:\xampp\apache\conf\httpd.conf`
  2. Find `#LoadModule headers_module modules/mod_headers.so`
  3. Remove the `#` to uncomment
  4. Restart Apache

## 📊 Database Structure Quick Reference

### Main Tables
- **users** - System users (Engineer/Treasurer)
- **plots** - Cemetery plot locations
- **deceased** - Deceased person records
- **contacts** - Contact information
- **rentals** - 3-year rental cycles (₱2,000 each)
- **payments** - Payment records
- **transactions** - Transaction history

### Views (Pre-joined data)
- **burial_records_view** - Complete burial info
- **plot_phase_view** - Plots by phase
- **transaction_summary_view** - Payment summaries

## 💡 Pro Tips

1. **Check Error Logs:**
   ```
   C:\xampp\apache\logs\error.log
   C:\xampp\mysql\data\mysql_error.log
   ```

2. **Enable PHP Errors (for development):**
   Edit `Database/db_connector.php` and add at the top:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

3. **View SQL Queries:**
   In phpMyAdmin, go to Status > Monitor to see live queries

4. **Backup Database:**
   phpMyAdmin > Export > Quick export > Go

## 🚀 Next Steps

1. ✅ Database setup complete
2. ✅ API endpoints working
3. 📱 Build your frontend (HTML/CSS/JS with Bootstrap)
4. 🎨 Implement the dark blue & white UI
5. ⚡ Add AJAX calls to these endpoints
6. 🔔 Integrate SweetAlert for notifications

## 📞 Need Help?

- Check the full `README.md` for detailed documentation
- Review API responses in browser DevTools (F12 > Network tab)
- Test endpoints individually before integrating

---

**Ready to build the frontend!** All backend APIs are functional and waiting for your UI. 🎉

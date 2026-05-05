# GraveTrack - Hostinger Deployment Guide

Complete guide for deploying GraveTrack Cemetery Management System to Hostinger hosting.

---

## 📋 Pre-Deployment Checklist

- ✅ PHP 7.4+ compatible hosting
- ✅ MySQL database support
- ✅ File manager or FTP access
- ✅ phpMyAdmin access

---

## 🚀 Step-by-Step Deployment to Hostinger

### Step 1: Prepare Your Files

1. **Download/Copy these files from your project:**
   ```
   /api/                    (all API files)
   /css/                    (style.css)
   /Database/               (db_connector.php)
   /includes/               (header.php, footer.php)
   /js/                     (app.js)
   /images/                 (if any)
   *.php files              (all PHP pages)
   .htaccess
   database_setup.sql
   ```

2. **Create a ZIP file** containing all these files (optional, for easier upload)

---

### Step 2: Upload Files to Hostinger

#### Option A: Using File Manager (Recommended)

1. **Login to Hostinger Control Panel**
   - Go to https://www.hostinger.com
   - Login with your credentials

2. **Navigate to File Manager**
   - Click on "Files" → "File Manager"
   - Navigate to `public_html` folder

3. **Upload Files**
   - Click "Upload" button
   - Upload your ZIP file OR upload folders individually
   - If uploaded ZIP, right-click and "Extract"

4. **Verify Structure**
   ```
   public_html/
   ├── api/
   ├── css/
   ├── Database/
   ├── includes/
   ├── js/
   ├── images/
   ├── index.php
   ├── login.php
   ├── dashboard.php
   ├── vacancy.php
   ├── burial_records.php
   ├── adding_burial_records.php
   ├── cemetery_map.php
   ├── payment_monitoring.php
   ├── .htaccess
   └── database_setup.sql
   ```

#### Option B: Using FTP Client (FileZilla)

1. **Get FTP Credentials from Hostinger**
   - Go to "Files" → "FTP Accounts"
   - Note: Hostname, Username, Password, Port (usually 21)

2. **Connect via FileZilla**
   - Download FileZilla: https://filezilla-project.org/
   - Enter FTP credentials
   - Connect

3. **Upload Files**
   - Drag and drop files from local (left) to remote `public_html` (right)

---

### Step 3: Create MySQL Database

1. **Access MySQL Databases**
   - In Hostinger control panel: "Databases" → "MySQL Databases"

2. **Create New Database**
   - Click "Create Database"
   - Database name: `u123456_gravetrack` (or your choice)
   - Click "Create"
   - **Note down:** Database name, username, password

3. **Access phpMyAdmin**
   - Click "Manage" next to your database
   - This opens phpMyAdmin

4. **Import Database Schema**
   - In phpMyAdmin, click your database name in left sidebar
   - Click "Import" tab
   - Click "Choose File" → select `database_setup.sql`
   - Click "Go" at bottom
   - Wait for "Import successful" message

5. **Verify Tables**
   - Check that all tables exist:
     - users
     - plots
     - deceased
     - contacts
     - rentals
     - payments
     - transactions
     - Views: burial_records_view, plot_phase_view, transaction_summary_view

---

### Step 4: Configure Database Connection

1. **Edit `Database/db_connector.php`**
   - In File Manager, navigate to `Database/db_connector.php`
   - Click "Edit"

2. **Update Database Credentials**
   ```php
   <?php
   class db_connector {
       // CHANGE THESE VALUES
       private $host = "localhost";              // Usually "localhost"
       private $db_name = "u123456_gravetrack";  // Your database name
       private $username = "u123456_admin";      // Your database username
       private $password = "your_password_here"; // Your database password
       private $conn;
       
       // ... rest of the code (don't change)
   ```

3. **Save Changes**

---

### Step 5: Update API Base URL

1. **Edit `js/app.js`**
   - In File Manager, navigate to `js/app.js`
   - Click "Edit"

2. **Update API Base URL**
   ```javascript
   // Change this line based on your setup:
   
   // If site is in root directory (example.com):
   const API_BASE_URL = '/api/';
   
   // If site is in subdirectory (example.com/gravetrack/):
   const API_BASE_URL = '/gravetrack/api/';
   ```

3. **Save Changes**

---

### Step 6: Configure .htaccess (If needed)

1. **Edit `.htaccess`**
   - If your site is in a subdirectory, update RewriteBase:

   ```apache
   # Change this line:
   RewriteBase /gravetrack/   # Replace with your subdirectory
   
   # If in root directory, use:
   RewriteBase /
   ```

2. **Check mod_rewrite**
   - Most Hostinger plans have mod_rewrite enabled
   - If you get 500 errors, temporarily rename `.htaccess` to `.htaccess.bak`

---

### Step 7: Set File Permissions

Ensure correct permissions for security:

```
Folders: 755
PHP files: 644
.htaccess: 644
```

In File Manager:
- Right-click each folder → "Permissions" → Set to 755
- Right-click each file → "Permissions" → Set to 644

---

### Step 8: Test Your Installation

1. **Access Your Site**
   - Open browser and navigate to:
     - `https://yourdomain.com/` (if in root)
     - `https://yourdomain.com/gravetrack/` (if in subfolder)

2. **You Should See:**
   - Login page redirecting from index.php

3. **Test Login**
   - Username: `testdummy1`
   - Password: `12345`
   - Role: Treasurer

4. **Test APIs Directly**
   - Visit: `https://yourdomain.com/api/get_plots.php`
   - Should return JSON data

5. **Test Each Page:**
   - ✅ Dashboard
   - ✅ Vacancy Monitoring
   - ✅ Burial Records
   - ✅ Add Burial Record
   - ✅ Cemetery Map
   - ✅ Payment Monitoring (Treasurer only)

---

## 🔧 Troubleshooting

### Error: "Database Connection Failed"

**Solution:**
1. Verify database credentials in `Database/db_connector.php`
2. Check database exists in phpMyAdmin
3. Ensure database user has all privileges
4. Try changing `$host` from `localhost` to `127.0.0.1`

---

### Error: "404 Not Found" for API calls

**Solution:**
1. Check `.htaccess` is uploaded
2. Verify `mod_rewrite` is enabled (contact Hostinger support)
3. Update `API_BASE_URL` in `js/app.js`
4. Check file paths are correct

---

### Error: "500 Internal Server Error"

**Solution:**
1. Check file permissions (folders 755, files 644)
2. Review `.htaccess` syntax
3. Check PHP error logs in Hostinger control panel
4. Temporarily rename `.htaccess` to test if it's the issue

---

### Error: "Session not working" / "Not authenticated"

**Solution:**
1. Check PHP session is enabled (it should be by default)
2. Ensure `session_start()` is in `api/auth.php`
3. Clear browser cookies and try again
4. Check `credentials: 'include'` in fetch calls

---

### CORS Errors (Cross-Origin Issues)

**Solution:**
1. Ensure `.htaccess` has CORS headers enabled
2. Check `Access-Control-Allow-Origin` header
3. If using subdomain, update allowed origins

---

### CSS/JS Not Loading

**Solution:**
1. Check file paths in HTML files
2. Verify files uploaded to correct folders
3. Hard refresh browser (Ctrl+F5 or Cmd+Shift+R)
4. Check browser console for 404 errors

---

## 🔒 Post-Deployment Security

### 1. Change Default Passwords

**Update default user passwords:**

```sql
-- Login to phpMyAdmin
-- Run this query:
UPDATE users 
SET password = 'your_new_password' 
WHERE username = 'testdummy1';

UPDATE users 
SET password = 'your_new_password' 
WHERE username = 'engineer1';
```

**⚠️ Important:** Implement password hashing before production!

```php
// In api/auth.php, use:
$hashed_password = password_hash('user_password', PASSWORD_DEFAULT);

// For verification:
if (password_verify($password, $user['password'])) {
    // Login successful
}
```

---

### 2. Remove Test Files

Delete these files after successful deployment:
- `test_api.php`
- `database_setup.sql` (keep a backup locally)
- `README.md` (optional)
- `DEPLOYMENT_GUIDE.md` (this file, optional)

---

### 3. Enable HTTPS

1. **In Hostinger Control Panel:**
   - Go to "Security" → "SSL"
   - Enable free SSL certificate
   - Wait 5-10 minutes for activation

2. **Force HTTPS (in .htaccess):**
   ```apache
   # Uncomment these lines:
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

---

### 4. Protect Sensitive Files

Ensure `.htaccess` blocks access to:
- Database connection file
- Configuration files
- SQL files

Already protected in provided `.htaccess`:
```apache
<FilesMatch "^(README\.md|\.htaccess|database_setup\.sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

### 5. Regular Backups

1. **Database Backup:**
   - phpMyAdmin → Export → Quick export → Go
   - Download SQL file
   - Schedule weekly backups

2. **File Backup:**
   - File Manager → Select all → Compress → Download ZIP
   - Or use Hostinger's automatic backup feature

---

## 🎯 Performance Optimization

### 1. Enable Caching

Add to `.htaccess`:
```apache
# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
</IfModule>
```

---

### 2. Enable Gzip Compression

Already in `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE text/html
</IfModule>
```

---

### 3. Optimize Database

In phpMyAdmin:
- Select your database
- Click "Operations"
- Scroll to "Table maintenance"
- Click "Optimize table"

---

## 📊 Monitoring

### Check Website Health

1. **Test Endpoints:**
   - `/api/get_plots.php` - Should return JSON
   - `/login.php` - Should show login page
   - `/dashboard.php` - Should redirect to login if not authenticated

2. **Check Error Logs:**
   - Hostinger Control Panel → "Advanced" → "Error Logs"
   - Review PHP errors

3. **Monitor Performance:**
   - Use Hostinger's built-in analytics
   - Check page load times

---

## 🆘 Support Resources

### Hostinger Resources
- **Support:** https://support.hostinger.com
- **Knowledge Base:** https://support.hostinger.com/en/collections
- **Live Chat:** Available 24/7 in control panel

### GraveTrack Resources
- **API Documentation:** `API_REFERENCE.md`
- **System Overview:** `PROJECT_SUMMARY.md`
- **Quick Start:** `QUICKSTART.md`

---

## ✅ Deployment Checklist

Before going live, verify:

- [ ] All files uploaded correctly
- [ ] Database imported successfully
- [ ] Database credentials configured
- [ ] API endpoints working (test in browser)
- [ ] Login functionality working
- [ ] All pages accessible
- [ ] Treasurer role restrictions working
- [ ] HTTPS enabled
- [ ] Default passwords changed
- [ ] Test files removed
- [ ] Backups configured
- [ ] Error logging enabled
- [ ] Performance optimized

---

## 🎉 You're Live!

Your GraveTrack Cemetery Management System is now deployed and ready for use!

**Access URLs:**
- **Production:** https://yourdomain.com/
- **API Test:** https://yourdomain.com/api/get_plots.php
- **phpMyAdmin:** Access via Hostinger control panel

**Default Users:**
- Treasurer: `testdummy1` / `12345`
- Engineer: `engineer1` / `12345`

**Remember to:**
1. Change default passwords immediately
2. Add your actual municipal workers as users
3. Configure regular backups
4. Monitor error logs

---

**Deployment Guide Version:** 1.0  
**Last Updated:** May 4, 2026  
**Platform:** Hostinger Shared/Business Hosting

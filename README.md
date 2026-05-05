# GraveTrack - Cemetery Vacancy Burial & Payment Monitoring System

A PHP-based backend system for managing cemetery plots, burial records, and payment monitoring for municipal workers.

## 🎯 Features

### Role-Based Access Control
- **Engineer Role**: Can view and manage vacant/occupied plots, burial records
- **Treasurer Role**: Can monitor payments (paid/unpaid), process payments with penalty calculation

### Core Functionality
- Cemetery plot vacancy monitoring
- Burial record management (CRUD operations)
- Payment tracking with automatic penalty calculation (25% for overdue)
- Interactive cemetery map with phase-based organization
- Transaction history tracking
- 3-year rental cycle management (₱2,000 per cycle)

## 📁 Project Structure

```
/code/
├── Database/
│   └── db_connector.php          # PDO database connection class
├── api/                           # RESTful API endpoints
│   ├── auth.php                   # Login/logout/session management
│   ├── get_plots.php              # Get all plots
│   ├── get_vacant_plots.php       # Get only vacant plots
│   ├── get_lot_details.php        # Get plot details with deceased records
│   ├── get_payment_summary.php    # Get payment overview (treasurer only)
│   ├── get_deceased_transactions.php  # Get transaction history for deceased
│   ├── save_burial_record.php     # Create new burial record
│   ├── update_burial_record.php   # Update existing burial record
│   ├── delete_burial_record.php   # Delete burial record
│   ├── process_payment.php        # Process payment with penalty calculation
│   ├── get_vacancy_stats.php      # Get vacancy statistics
│   ├── get_cemetery_map.php       # Get cemetery map data
│   └── get_rental_details.php     # Get rental payment details
└── database_setup.sql             # Database schema and sample data
```

## 🗄️ Database Schema

### Tables
- **users**: System users (Engineer, Treasurer)
- **plots**: Cemetery plot information
- **deceased**: Deceased person records
- **contacts**: Contact information for deceased
- **rentals**: 3-year rental periods
- **payments**: Payment records
- **transactions**: Transaction history

### Views
- **burial_records_view**: Complete burial records with contact and plot info
- **plot_phase_view**: Plots organized by cemetery phase
- **transaction_summary_view**: Payment summary with status

## 🚀 Setup Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 or higher
- MySQL/MariaDB

### Installation Steps

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import `database_setup.sql` file
   - This will create the `gravetrack_db` database with sample data

3. **Configure Database Connection**
   - Database credentials are in `Database/db_connector.php`
   - Default settings:
     ```php
     Host: localhost
     Database: gravetrack_db
     Username: root
     Password: (empty)
     ```

4. **Place Project Files**
   - Copy the entire project to `C:\xampp\htdocs\gravetrack\`
   - Or your XAMPP htdocs directory

5. **Test the Setup**
   - Open browser and navigate to: `http://localhost/gravetrack/api/auth.php`
   - Should return JSON response

## 🔐 Default Test Users

| Username | Password | Role | Email |
|----------|----------|------|-------|
| testdummy1 | 12345 | Treasurer | test@gmail.com |
| engineer1 | 12345 | Engineer | engineer@gmail.com |

## 📡 API Endpoints

### Authentication
- `POST /api/auth.php` - Login
- `GET /api/auth.php` - Check session
- `DELETE /api/auth.php` - Logout

### Plots & Vacancy
- `GET /api/get_plots.php` - Get all plots
- `GET /api/get_vacant_plots.php` - Get vacant plots for dropdown
- `GET /api/get_lot_details.php?plot_id={id}` - Get plot details
- `GET /api/get_vacancy_stats.php?block=&type=&status=` - Get vacancy statistics
- `GET /api/get_cemetery_map.php?search=&phase=` - Get cemetery map data

### Burial Records
- `POST /api/save_burial_record.php` - Create burial record
- `POST /api/update_burial_record.php` - Update burial record
- `POST /api/delete_burial_record.php` - Delete burial record

### Payments (Treasurer Only)
- `GET /api/get_payment_summary.php` - Get all payments
- `POST /api/get_deceased_transactions.php` - Get transactions by deceased name
- `GET /api/get_rental_details.php?deceased_id={id}` - Get rental details with penalties
- `POST /api/process_payment.php` - Process payment

## 💰 Payment Rules

### Standard Rental Cycle
- **Duration**: 3 years
- **Amount**: ₱2,000 per cycle
- **Payment Options**:
  - Full payment: ₱2,000
  - Split payment: ₱666 (year 1), ₱666 (year 2), ₱667 (year 3)

### Penalty
- **Rate**: 25% additional charge
- **Applied When**: Payment is not made by rental_end date
- **Example**: ₱2,000 + 25% = ₱2,500

## 🔄 Automatic Features

### Triggers
- When a burial record is inserted, the plot status automatically updates to "Occupied"
- When last burial record is deleted from a plot, status reverts to "Vacant"

### Rental Cycle
- Upon payment, a new 3-year rental cycle is automatically created
- Rental status is tracked (Paid, Unpaid, Overdue)

## 📊 Cemetery Phases

Plots are organized into phases based on block letters:

- **Phase 1**: Blocks A-I
- **Phase 2**: Blocks T-Z
- **Phase 3**: Block AA
- **Unassigned**: Other blocks

## 🔒 Security Features

- Session-based authentication
- Role-based access control (RBAC)
- SQL injection prevention using PDO prepared statements
- Transaction rollback on errors
- Password hashing ready (currently uses plain text for demo)

## ⚠️ Important Notes

1. **Password Security**: Currently using plain text passwords for demonstration. Implement password hashing before production:
   ```php
   // Use password_hash() and password_verify()
   $hashed = password_hash($password, PASSWORD_DEFAULT);
   ```

2. **CORS**: Add CORS headers if frontend is on different domain:
   ```php
   header('Access-Control-Allow-Origin: *');
   ```

3. **Error Logging**: Enable error logging for production:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 0);
   ini_set('log_errors', 1);
   ```

## 🎨 Frontend Integration

All API endpoints return JSON responses with the following structure:

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description"
}
```

## 📝 Sample API Calls

### Login
```javascript
fetch('http://localhost/gravetrack/api/auth.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    username: 'testdummy1',
    password: '12345'
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

### Get Vacant Plots
```javascript
fetch('http://localhost/gravetrack/api/get_vacant_plots.php')
  .then(res => res.json())
  .then(data => console.log(data));
```

### Save Burial Record
```javascript
fetch('http://localhost/gravetrack/api/save_burial_record.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    full_name: 'Juan Dela Cruz',
    date_of_death: '2026-05-01',
    date_of_burial: '2026-05-03',
    gender: 'Male',
    address: 'Manila',
    plot_id: 4,
    burial_type: 'Single',
    birth_date: '1950-01-01',
    contact_person: 'Maria Dela Cruz',
    contact_number: '09123456789'
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

## 🐛 Troubleshooting

### Database Connection Failed
- Check if MySQL is running in XAMPP
- Verify database credentials in `db_connector.php`
- Ensure `gravetrack_db` database exists

### 404 Not Found
- Check file paths and XAMPP htdocs location
- Verify Apache is running
- Check URL structure

### Session Issues
- Ensure `session_start()` is called
- Check PHP session settings in php.ini
- Clear browser cookies

## 📞 Support

For issues or questions about the backend system, refer to the cemetery.txt specification file.

---

**Built for Municipal Workers** | **Dark Blue & White Theme** | **Bootstrap 5 Ready**

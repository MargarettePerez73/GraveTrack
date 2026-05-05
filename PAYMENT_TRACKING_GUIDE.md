# Payment Status Tracking Implementation Guide

## Overview
This implementation adds automatic 3-year rental period tracking and payment status monitoring to the cemetery management system. The system automatically calculates payment status for occupied plots and displays it in the cemetery map.

## What's Been Added

### 1. Database Tables & Views

#### New Tables:
- **`payment_monitoring`** - Audit trail for payment status changes
- **`payment_penalties`** - Tracks overdue penalties (10% by default)

#### New Views:
- **`plot_payment_status_view`** - Detailed payment information per plot
- **`cemetery_plot_payment_view`** - Simplified view for cemetery map display

#### New Stored Procedures:
- **`update_payment_status()`** - Updates rental and penalty status (run periodically)
- **`get_plot_payment_summary(plot_id)`** - Gets payment details for a specific plot

### 2. Database Triggers

#### `after_deceased_insert`
Automatically creates a rental record when a burial is recorded:
- **rental_start**: Current date
- **rental_end**: Current date + 3 years
- **amount**: Set to 0.00 (update with actual fee)
- **status**: 'Unpaid'

### 3. API Updates

#### Updated `get_cemetery_map.php`
Now returns payment status for each plot:
```json
{
  "plot_id": 1,
  "block": "A",
  "section": "1",
  "lot": "1",
  "payment_status": "Paid|Partially Paid|Unpaid|Overdue|Overdue - Partial",
  "rental_end_date": "2029-05-05",
  "days_overdue": null
}
```

#### Updated `get_payment_summary.php`
Returns payment data for Treasurer view with columns:
- Plot Location
- Deceased Name
- Date of Burial
- Rental Period (start-end)
- Rental Amount
- Total Paid
- Amount Due
- Payment Status
- Days Overdue
- Contact Information

### 4. Frontend Updates

#### Cemetery Map (`cemetery_map.php`)
- **Treasurer View**: Now shows payment status colors:
  - **Green** (#10b981) - Fully Paid
  - **Orange** (#f59e0b) - Partially Paid
  - **Red** (#ef4444) - Unpaid
  - **Dark Red** (#991b1b) - Overdue

- **Enhanced Plot Details Modal**:
  - Shows rental period (3 years)
  - Displays current payment status
  - Provides "Record Payment" button for Treasurers
  - Shows rental end dates and days overdue

## Installation Steps

### Step 1: Run the SQL Update
Execute the SQL in `add_payment_status_tracking.sql`:

```bash
mysql -u root gravetrack_db < add_payment_status_tracking.sql
```

Or in phpMyAdmin:
1. Select your `gravetrack_db` database
2. Go to SQL tab
3. Copy and paste contents of `add_payment_status_tracking.sql`
4. Click Execute

### Step 2: Create Existing Rental Records
If you already have burial records, the SQL script will automatically create rental records for them. Verify by running:

```sql
SELECT * FROM rentals;
```

### Step 3: Update Rental Amounts
Set the 3-year rental fee in the `rentals` table:

```sql
UPDATE rentals SET amount = 5000.00 WHERE amount = 0.00;
```

Change `5000.00` to your actual 3-year rental fee.

### Step 4: Verify API Connections
Test the APIs are working:
- Open browser and visit: `http://localhost/api/get_cemetery_map.php`
- Verify payment_status field is present in response

## Payment Status Logic

### Status Determination:
```
Vacant: Plot has no burial records
Paid: Full rental amount paid AND rental period not expired
Partially Paid: Some payment made but less than full amount
Unpaid: No payment made and rental not expired
Overdue: Rental period expired with no payment
Overdue - Partial: Rental period expired with partial payment
```

### Example Timeline:
```
Date: 2026-05-05 (Burial Date)
Rental Period: 2026-05-05 to 2029-05-05
Status Transition:
  → Initial: Unpaid (no payment)
  → After payment of 2500: Partially Paid
  → After payment of 2500 more: Paid (5000 total)
  → After 2029-05-06: Overdue (if no payment made)
  → After partial late payment: Overdue - Partial
```

## Penalty System

### Automatic Penalty Application:
- Triggers 1 day after rental_end date if unpaid
- Default: 10% of rental amount (configurable)
- Applied automatically by `update_payment_status()` stored procedure

### Run Payment Status Update:
Schedule this to run daily/weekly via cron job:

```php
<?php
// payment_scheduler.php - call via cron
require_once 'Database/db_connector.php';
$database = new db_connector();
$db = $database->connect();
$db->exec("CALL update_payment_status();");
?>
```

Cron command:
```bash
0 0 * * * /usr/bin/php /var/www/html/payment_scheduler.php
```

## Recording Payments

### Add New Payment:
Use the existing `add_payment.php` API with:
```json
{
  "rental_id": 1,
  "amount": 2500.00,
  "payment_date": "2026-05-05"
}
```

Or use the UI at: `add_payment.php?plot_id=1`

## Monitoring & Reports

### View All Plot Payment Status:
```sql
SELECT * FROM plot_payment_status_view;
```

### View Overdue Plots:
```sql
SELECT * FROM plot_payment_status_view 
WHERE payment_status LIKE 'Overdue%';
```

### View Payment Audit Trail:
```sql
SELECT * FROM payment_monitoring 
ORDER BY last_checked DESC;
```

### View Penalties:
```sql
SELECT * FROM payment_penalties 
WHERE status = 'Active';
```

## Configuration

### Change Penalty Percentage:
Edit `add_payment_status_tracking.sql` and change:
```sql
penalty_percentage` decimal(5,2) DEFAULT 10.00
```

Then rerun the relevant sections.

### Change Rental Period (from 3 years):
Edit the trigger `after_deceased_insert`:
```sql
DATE_ADD(CURDATE(), INTERVAL 3 YEAR)  -- Change 3 to desired years
```

### Change Default Rental Amount:
```sql
UPDATE rentals SET amount = YOUR_AMOUNT WHERE plot_id = YOUR_PLOT;
```

## Troubleshooting

### Q: Payment status not showing in cemetery map?
A: 
1. Verify rental records exist: `SELECT * FROM rentals;`
2. Check API response includes payment_status field
3. Clear browser cache and reload

### Q: Overdue plots not showing as red?
A: 
1. Run `CALL update_payment_status();` to sync status
2. Check rental_end dates are in the past
3. Verify no payments exist for the rental

### Q: Need to manually update a plot's status?
A: 
```sql
UPDATE rentals SET status = 'Paid' WHERE rental_id = X;
```

## Database Views Reference

### `plot_payment_status_view` Columns:
- plot_id, block, section, lot
- deceased_id, full_name, date_of_burial
- rental_id, rental_start, rental_end
- rental_amount, total_paid, amount_remaining
- **payment_status** (Paid/Unpaid/Overdue/etc.)
- days_overdue

### `cemetery_plot_payment_view` Columns:
- plot_id, block, section, lot, plot_location
- plot_status (Vacant/Occupied)
- **payment_status**
- deceased_count

## Next Steps

1. ✅ Run the SQL update file
2. ✅ Set proper rental amounts
3. ✅ Test APIs return payment status
4. ✅ Test Treasurer view shows payment colors
5. 📋 Set up cron job for `update_payment_status()`
6. 📋 Train Treasurers on payment recording
7. 📋 Set up payment reminders for overdue plots

## Support

For issues or questions:
1. Check cemetery_map.php browser console for JS errors
2. Check API responses for SQL errors
3. Review MySQL error logs
4. Verify all foreign key constraints are satisfied

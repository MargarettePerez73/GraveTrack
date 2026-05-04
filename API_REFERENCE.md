# GraveTrack API Reference

Complete documentation for all backend endpoints.

---

## 📋 Table of Contents

1. [Authentication](#authentication)
2. [Plots & Vacancy](#plots--vacancy)
3. [Burial Records](#burial-records)
4. [Payments](#payments)
5. [Response Formats](#response-formats)
6. [Error Codes](#error-codes)

---

## Authentication

### Login
**Endpoint:** `POST /api/auth.php`

**Request Body:**
```json
{
  "username": "testdummy1",
  "password": "12345"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "username": "testdummy1",
    "email": "test@gmail.com",
    "role": "Treasurer"
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid username or password"
}
```

---

### Check Session
**Endpoint:** `GET /api/auth.php`

**Success Response (200):**
```json
{
  "success": true,
  "authenticated": true,
  "user": {
    "username": "testdummy1",
    "role": "Treasurer",
    "email": "test@gmail.com"
  }
}
```

---

### Logout
**Endpoint:** `DELETE /api/auth.php`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## Plots & Vacancy

### Get All Plots
**Endpoint:** `GET /api/get_plots.php`

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "plot_id": 1,
      "block": "A",
      "section": "1",
      "lot": "1",
      "type": "Single",
      "status": "Occupied",
      "date_added": "2026-04-29 06:32:12"
    }
  ]
}
```

---

### Get Vacant Plots
**Endpoint:** `GET /api/get_vacant_plots.php`

**Description:** Returns only vacant plots formatted for dropdown selection.

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "plot_id": 4,
      "label": "Block A, Section 1, Lot 2",
      "block": "A",
      "section": "1",
      "lot": "2",
      "type": "Single"
    }
  ]
}
```

---

### Get Lot Details
**Endpoint:** `GET /api/get_lot_details.php?plot_id={id}`

**Parameters:**
- `plot_id` (required) - Integer

**Success Response (200):**
```json
{
  "success": true,
  "plot": {
    "plot_id": 1,
    "block": "A",
    "section": "1",
    "lot": "1",
    "type": "Single",
    "status": "Occupied"
  },
  "deceased_records": [
    {
      "deceased_id": 1,
      "full_name": "Maria Santos",
      "birth_date": "2016-05-03",
      "date_of_death": "2024-01-10",
      "date_of_burial": "2024-01-15",
      "gender": "Female",
      "address": "Lipa City, Batangas",
      "burial_type": "Family Burial",
      "contact_person": "Pedro Santos",
      "contact_number": "09123456789"
    }
  ],
  "html": "<div class=\"plot-details\">...</div>"
}
```

---

### Get Vacancy Statistics
**Endpoint:** `GET /api/get_vacancy_stats.php`

**Query Parameters (all optional):**
- `block` - Filter by block (e.g., "A", "B")
- `type` - Filter by plot type (e.g., "Single", "Apartment")
- `status` - Filter by status (e.g., "Vacant", "Occupied")

**Example:** `/api/get_vacancy_stats.php?block=A&status=Vacant`

**Success Response (200):**
```json
{
  "success": true,
  "stats": {
    "total_plots": 7,
    "total_vacant": 4,
    "total_occupied": 3,
    "total_reserved": 0
  },
  "plots": [
    {
      "plot_id": 1,
      "block": "A",
      "section": "1",
      "lot": "1",
      "type": "Single",
      "status": "Occupied",
      "date_added": "2026-04-29 06:32:12"
    }
  ],
  "blocks": ["A", "B", "C"]
}
```

---

### Get Cemetery Map
**Endpoint:** `GET /api/get_cemetery_map.php`

**Query Parameters (all optional):**
- `search` - Search by deceased name, block, or lot
- `phase` - Filter by phase ("Phase 1", "Phase 2", "Phase 3")

**Example:** `/api/get_cemetery_map.php?phase=Phase%201`

**Success Response (200):**
```json
{
  "success": true,
  "plots": [
    {
      "plot_id": 1,
      "block": "A",
      "section": "1",
      "lot": "1",
      "type": "Single",
      "status": "Occupied",
      "phase": "Phase 1",
      "deceased_count": 1,
      "deceased_names": "Maria Santos"
    }
  ],
  "plotsByPhase": {
    "Phase 1": [...],
    "Phase 2": [...],
    "Phase 3": [...],
    "Unassigned": [...]
  }
}
```

**Phase Rules:**
- Phase 1: Blocks A-I
- Phase 2: Blocks T-Z
- Phase 3: Block AA
- Unassigned: All others

---

## Burial Records

### Save Burial Record
**Endpoint:** `POST /api/save_burial_record.php`

**Request Body:**
```json
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

**Required Fields:**
- `full_name`
- `date_of_death`
- `date_of_burial`
- `plot_id`

**Optional Fields:**
- `gender` (Male, Female, Other)
- `address`
- `burial_type`
- `birth_date`
- `contact_person`
- `contact_number`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Burial record saved successfully",
  "deceased_id": 24
}
```

**Automatic Actions:**
- Creates deceased record
- Creates contact record
- Updates plot status to "Occupied"
- Creates initial 3-year rental (₱2,000, Unpaid)
- Triggers `after_burial_insert` database trigger

---

### Update Burial Record
**Endpoint:** `POST /api/update_burial_record.php`

**Request Body:**
```json
{
  "deceased_id": 24,
  "full_name": "Juan Dela Cruz UPDATED",
  "date_of_death": "2026-05-01",
  "date_of_burial": "2026-05-03",
  "gender": "Male",
  "address": "Manila, Philippines",
  "burial_type": "Single",
  "birth_date": "1950-01-01",
  "contact_person": "Maria Dela Cruz",
  "contact_number": "09123456789"
}
```

**Required Fields:**
- `deceased_id`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Burial record updated successfully"
}
```

---

### Delete Burial Record
**Endpoint:** `POST /api/delete_burial_record.php`

**Request Body:**
```json
{
  "deceased_id": 24
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Burial record deleted successfully"
}
```

**Automatic Actions:**
- Deletes deceased record
- Cascade deletes contacts
- Cascade deletes rentals
- Cascade deletes payments
- Updates plot to "Vacant" if no other deceased remain

---

## Payments

### Get Payment Summary
**Endpoint:** `GET /api/get_payment_summary.php`

**Authorization:** Requires active session

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "transaction_id": 1,
      "Deceased Name": "Maria Santos",
      "Plot Location": "A - 1 - 1",
      "Date of Transaction": "2024-01-16",
      "Contact Person": "Pedro Santos",
      "Contact Number": "09123456789",
      "Amount": "5000.00",
      "Status": "Paid"
    }
  ]
}
```

**Status Values:**
- `Paid` - Payment received
- `Pending` - Payment initiated but not completed
- `Unpaid` - No payment record
- `Overdue` - Rental end date passed, no payment

---

### Get Deceased Transactions
**Endpoint:** `POST /api/get_deceased_transactions.php`

**Authorization:** Requires active session

**Request Body:**
```json
{
  "deceased_name": "Maria Santos"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "transaction_id": 1,
      "deceased_name": "Maria Santos",
      "plot_location": "A - 1 - 1",
      "transaction_date": "2024-01-16",
      "amount": "5000.00",
      "transaction_type": "Payment",
      "contact_person": "Pedro Santos",
      "contact_number": "09123456789",
      "rental_start": "2024-01-15",
      "rental_end": "2025-01-15",
      "rental_status": "Unpaid",
      "payment_date": "2024-01-16",
      "payment_status": "Paid"
    }
  ]
}
```

---

### Get Rental Details
**Endpoint:** `GET /api/get_rental_details.php?deceased_id={id}`

**Authorization:** Requires active session

**Parameters:**
- `deceased_id` (required) - Integer

**Success Response (200):**
```json
{
  "success": true,
  "rentals": [
    {
      "rental_id": 1,
      "rental_start": "2024-01-15",
      "rental_end": "2025-01-15",
      "amount": "5000.00",
      "rental_status": "Unpaid",
      "payment_id": 1,
      "payment_date": "2024-01-16",
      "payment_amount": "5000.00",
      "payment_status": "Paid",
      "days_overdue": -245,
      "penalty_applied": false,
      "penalty_amount": 0,
      "total_amount_due": 5000
    }
  ],
  "summary": {
    "total_paid": 5000,
    "total_due": 0,
    "total_amount": 5000
  }
}
```

**Penalty Calculation:**
- If `days_overdue > 0` and status is not "Paid"
- Penalty = 25% of rental amount
- `total_amount_due = amount * 1.25`

---

### Process Payment
**Endpoint:** `POST /api/process_payment.php`

**Authorization:** Requires Treasurer role

**Request Body:**
```json
{
  "rental_id": 1,
  "amount": 2000,
  "payment_date": "2026-05-04",
  "deceased_name": "Maria Santos"
}
```

**Required Fields:**
- `rental_id`
- `deceased_name`

**Optional Fields:**
- `amount` (defaults to rental amount + penalty if overdue)
- `payment_date` (defaults to today)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payment processed successfully",
  "payment_id": 3,
  "amount_paid": 2500
}
```

**Automatic Actions:**
- Creates payment record (status: "Paid")
- Updates rental status to "Paid"
- Creates transaction record
- Creates NEW 3-year rental period (Unpaid, ₱2,000)
- Applies 25% penalty if overdue

**Payment Calculation Logic:**
```php
if (today > rental_end && rental_status === 'Unpaid') {
    amount = amount * 1.25;  // 25% penalty
}
```

---

## Response Formats

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

---

## Error Codes

| HTTP Code | Meaning | Common Causes |
|-----------|---------|---------------|
| 200 | OK | Request successful |
| 400 | Bad Request | Missing required fields, invalid data format |
| 401 | Unauthorized | Not logged in, session expired |
| 403 | Forbidden | Insufficient permissions (e.g., non-Treasurer trying to process payment) |
| 404 | Not Found | Resource doesn't exist (plot_id, deceased_id not found) |
| 500 | Internal Server Error | Database error, server misconfiguration |

---

## Payment Business Rules

### 3-Year Rental Cycle
- **Standard Rate:** ₱2,000 per 3 years
- **Split Payment Option:**
  - Year 1: ₱666
  - Year 2: ₱666
  - Year 3: ₱667

### Penalties
- **Rate:** 25% of rental amount
- **Trigger:** Payment not received by `rental_end` date
- **Example:**
  - Original: ₱2,000
  - With Penalty: ₱2,500

### Rental Renewal
- When payment is processed, a new 3-year rental is automatically created
- New rental starts the day after previous rental ends
- New rental status: "Unpaid"
- New rental amount: ₱2,000 (no penalty until overdue)

---

## Session Management

### Session Variables Set on Login
```php
$_SESSION['user']      // username
$_SESSION['user_id']   // user ID
$_SESSION['role']      // Engineer or Treasurer
$_SESSION['email']     // user email
```

### Protected Endpoints (Session Required)
- `/api/get_payment_summary.php`
- `/api/get_deceased_transactions.php`
- `/api/get_rental_details.php`
- `/api/process_payment.php`

### Role-Specific Endpoints
- **Treasurer Only:**
  - `/api/process_payment.php`

---

## Database Views

These views are used by the API endpoints for optimized queries:

### burial_records_view
Pre-joined view of deceased + contacts + plots for complete burial records.

### plot_phase_view
Automatically calculates cemetery phase based on block letter.

### transaction_summary_view
Complete transaction summary with payment status calculation.

---

## Example Integration

### JavaScript/AJAX Example
```javascript
// Login
async function login() {
  const response = await fetch('http://localhost/gravetrack/api/auth.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',  // Important for session cookies!
    body: JSON.stringify({
      username: 'testdummy1',
      password: '12345'
    })
  });
  
  const data = await response.json();
  if (data.success) {
    console.log('Logged in as:', data.user.role);
  }
}

// Get vacant plots
async function getVacantPlots() {
  const response = await fetch('http://localhost/gravetrack/api/get_vacant_plots.php');
  const data = await response.json();
  
  if (data.success) {
    // Populate dropdown
    const select = document.getElementById('plot-select');
    data.data.forEach(plot => {
      const option = document.createElement('option');
      option.value = plot.plot_id;
      option.textContent = plot.label;
      select.appendChild(option);
    });
  }
}
```

---

## Notes

1. **CORS:** Enabled via `.htaccess` for cross-origin requests
2. **Sessions:** Use `credentials: 'include'` in fetch to maintain session
3. **Dates:** All dates in `YYYY-MM-DD` format
4. **Amounts:** Decimal values with 2 decimal places (e.g., 2000.00)
5. **Encoding:** All responses are UTF-8 JSON

---

**Last Updated:** May 4, 2026  
**API Version:** 1.0.0  
**Base URL:** `http://localhost/gravetrack/api/`

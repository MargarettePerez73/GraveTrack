<?php
session_start();
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login.'
    ]);
    exit;
}

/**
 * Map a row from the `payment_history` view to the shape expected by
 * payment_monitoring.php, cemetery_map.php, and dashboard.php.
 * Supports common column aliases (spaces, snake_case, etc.).
 */
function normalize_payment_summary_row(array $row): array
{
    $g = function (array $keys) use ($row) {
        foreach ($keys as $k) {
            if (array_key_exists($k, $row)) {
                return $row[$k];
            }
        }
        foreach ($row as $rk => $rv) {
            foreach ($keys as $k) {
                if (strcasecmp((string) $rk, (string) $k) === 0) {
                    return $rv;
                }
            }
        }
        return null;
    };

    $plotLocation   = $g(['Plot Location', 'plot_location']);
    $deceasedName   = $g(['Deceased Name', 'deceased_name', 'full_name', 'Full Name']);
    $status         = $g(['Status', 'payment_status', 'status']);
    $amount         = $g(['Amount', 'amount', 'amount_paid', 'Total Paid', 'total_paid']);
    $dateTx         = $g(['Date of Transaction', 'date_of_transaction', 'payment_date', 'transaction_date', 'last_checked', 'Last Payment Date']);
    $txId           = $g(['transaction_id', 'Transaction ID', 'payment_id', 'Payment ID', 'monitoring_id', 'Monitoring ID']);
    $contactPerson  = $g(['Contact Person', 'contact_person']);
    $contactNumber  = $g(['Contact Number', 'contact_number']);
    $dateOfBurial   = $g(['Date of Burial', 'date_of_burial']);
    $rentalId       = $g(['rental_id', 'Rental ID']);
    $rentalStart    = $g(['rental_start', 'Rental Start']);
    $rentalEnd      = $g(['rental_end', 'rental_end_date', 'Rental End']);
    $rentalAmount   = $g(['Rental Amount', 'rental_amount']);
    $totalPaid      = $g(['Total Paid', 'total_paid']);
    $amountDue      = $g(['Amount Due', 'amount_due']);
    $daysOverdue    = $g(['Days Overdue', 'days_overdue']);
    $orNumber       = $g(['or_number', 'OR Number', 'OR No.', 'or_no']);

    $out = $row;

    if ($plotLocation !== null) {
        $out['Plot Location'] = $plotLocation;
    }
    if ($deceasedName !== null) {
        $out['Deceased Name'] = $deceasedName;
    }
    if ($status !== null) {
        $out['Status'] = $status;
    }
    if ($amount !== null) {
        $out['Amount'] = $amount;
    }
    if ($dateTx !== null) {
        $out['Date of Transaction'] = $dateTx;
    }
    if ($txId !== null) {
        $out['transaction_id'] = $txId;
    }
    if ($contactPerson !== null) {
        $out['Contact Person'] = $contactPerson;
    }
    if ($contactNumber !== null) {
        $out['Contact Number'] = $contactNumber;
    }
    if ($dateOfBurial !== null) {
        $out['Date of Burial'] = $dateOfBurial;
    }
    if ($rentalId !== null) {
        $out['rental_id'] = $rentalId;
    }
    if ($rentalStart !== null) {
        $out['rental_start'] = $rentalStart;
    }
    if ($rentalEnd !== null) {
        $out['rental_end'] = $rentalEnd;
    }
    if ($rentalAmount !== null) {
        $out['Rental Amount'] = $rentalAmount;
    }
    if ($totalPaid !== null) {
        $out['Total Paid'] = $totalPaid;
    }
    if ($amountDue !== null) {
        $out['Amount Due'] = $amountDue;
    }
    if ($daysOverdue !== null) {
        $out['Days Overdue'] = $daysOverdue;
    }
    if ($orNumber !== null && $orNumber !== '') {
        $out['OR Number'] = $orNumber;
        $out['or_number'] = $orNumber;
    }

    return apply_paid_overdue_resolution($out);
}

/**
 * If the rental is fully paid, status must be Paid — not Overdue.
 * When the view still says Overdue (or days overdue > 0) but amounts show cleared,
 * use "Paid (was overdue)" so treasurers see it was settled after being overdue.
 */
function apply_paid_overdue_resolution(array $out): array
{
    $orig = isset($out['Status']) ? (string) $out['Status'] : '';

    $toFloat = function ($v): ?float {
        if ($v === null || $v === '') {
            return null;
        }

        return (float) $v;
    };

    $totalPaid = $toFloat($out['Total Paid'] ?? null);
    if ($totalPaid === null) {
        $totalPaid = $toFloat($out['total_paid'] ?? null);
    }

    $rentalAmt = $toFloat($out['Rental Amount'] ?? null);
    if ($rentalAmt === null) {
        $rentalAmt = $toFloat($out['rental_amount'] ?? null);
    }

    $amountDue = $toFloat($out['Amount Due'] ?? null);
    if ($amountDue === null) {
        $amountDue = $toFloat($out['amount_due'] ?? null);
    }

    $rowAmount = $toFloat($out['Amount'] ?? null);

    $fullyPaid = false;
    if ($rentalAmt !== null && $totalPaid !== null && $totalPaid >= $rentalAmt - 0.01) {
        $fullyPaid = true;
    }
    if ($amountDue !== null && $amountDue <= 0.01) {
        $fullyPaid = true;
    }
    // Single-line payment_history rows: only Amount + Rental Amount available
    if (!$fullyPaid && $rentalAmt !== null && $rowAmount !== null && $totalPaid === null && $rowAmount >= $rentalAmt - 0.01) {
        $fullyPaid = true;
    }

    if (!$fullyPaid) {
        return $out;
    }

    $wasOverdue = (stripos($orig, 'overdue') !== false);

    if (!$wasOverdue && isset($out['Days Overdue'])) {
        $d = $out['Days Overdue'];
        if ($d !== null && $d !== '' && (int) $d > 0) {
            $wasOverdue = true;
        }
    }

    if (!$wasOverdue && !empty($out['rental_end'])) {
        $end = strtotime((string) $out['rental_end']);
        if ($end && $end < strtotime('today')) {
            if (stripos($orig, 'overdue') !== false
                || stripos($orig, 'partial') !== false
                || $orig === 'Unpaid') {
                $wasOverdue = true;
            }
        }
    }

    $out['Status'] = $wasOverdue ? 'Paid (was overdue)' : 'Paid';

    return $out;
}

try {
    $database = new db_connector();
    $db = $database->connect();

    // Database view for payment monitoring UI.
    $stmt = $db->query('SELECT * FROM `payment_history`');
    $raw  = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $payments = [];
    foreach ($raw as $row) {
        $payments[] = normalize_payment_summary_row($row);
    }

    usort($payments, function ($a, $b) {
        $loc = strcmp((string) ($a['Plot Location'] ?? ''), (string) ($b['Plot Location'] ?? ''));
        if ($loc !== 0) {
            return $loc;
        }
        return strcmp((string) ($a['Deceased Name'] ?? ''), (string) ($b['Deceased Name'] ?? ''));
    });

    echo json_encode([
        'success' => true,
        'data' => $payments
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

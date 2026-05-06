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

if (($_SESSION['role'] ?? '') !== 'Treasurer') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Only Treasurer can view payment history.'
    ]);
    exit;
}

$deceasedId = isset($_GET['deceased_id']) ? (int) $_GET['deceased_id'] : 0;
if ($deceasedId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Valid deceased_id is required.'
    ]);
    exit;
}

try {
    $database = new db_connector();
    $db = $database->connect();

    $infoSql = "SELECT
                    d.full_name AS deceased_name,
                    CONCAT(p.block, ' - ', p.section, ' - ', p.lot) AS plot_location,
                    c.contact_person AS contact_person,
                    c.contact_number AS contact_number
                FROM deceased d
                LEFT JOIN plots p ON d.plot_id = p.plot_id
                LEFT JOIN contacts c ON d.deceased_id = c.deceased_id
                WHERE d.deceased_id = :deceased_id
                LIMIT 1";

    $infoStmt = $db->prepare($infoSql);
    $infoStmt->bindParam(':deceased_id', $deceasedId, PDO::PARAM_INT);
    $infoStmt->execute();
    $info = $infoStmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Deceased record not found.'
        ]);
        exit;
    }

    $paySql = "SELECT
                    pay.payment_id,
                    pay.rental_id,
                    pay.payment_date,
                    pay.amount,
                    pay.status,
                    r.rental_start,
                    r.rental_end,
                    r.amount AS rental_period_amount
                FROM payments pay
                INNER JOIN rentals r ON pay.rental_id = r.rental_id
                WHERE r.deceased_id = :deceased_id
                ORDER BY pay.payment_date ASC, pay.payment_id ASC";

    $payStmt = $db->prepare($paySql);
    $payStmt->bindParam(':deceased_id', $deceasedId, PDO::PARAM_INT);
    $payStmt->execute();
    $transactions = $payStmt->fetchAll(PDO::FETCH_ASSOC);

    $cumulativeByRental = [];
    foreach ($transactions as &$t) {
        $rid = (int) $t['rental_id'];
        $rentalAmt = (float) $t['rental_period_amount'];
        $paidThis = (float) $t['amount'];
        if (!isset($cumulativeByRental[$rid])) {
            $cumulativeByRental[$rid] = 0.0;
        }
        $cumulativeByRental[$rid] += $paidThis;
        $balance = $rentalAmt - $cumulativeByRental[$rid];
        if ($balance < 0) {
            $balance = 0.0;
        }
        $t['balance_remaining'] = round($balance, 2);
        if ($balance <= 0.00001) {
            $t['display_status'] = 'Paid';
        } else {
            $raw = $t['status'] ?? '';
            $t['display_status'] = ($raw === 'Paid') ? 'Partially Paid' : ($raw ?: 'Unpaid');
        }
    }
    unset($t);

    echo json_encode([
        'success' => true,
        'deceased_name' => $info['deceased_name'],
        'plot_location' => $info['plot_location'],
        'contact_person' => $info['contact_person'],
        'contact_number' => $info['contact_number'],
        'transactions' => $transactions
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

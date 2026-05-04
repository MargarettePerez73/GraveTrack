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

try {
    $deceased_id = isset($_GET['deceased_id']) ? intval($_GET['deceased_id']) : 0;

    if ($deceased_id === 0) {
        throw new Exception("Deceased ID is required");
    }

    $database = new db_connector();
    $db = $database->connect();

    // Get all rentals for this deceased
    $query = "SELECT
                r.rental_id,
                r.rental_start,
                r.rental_end,
                r.amount,
                r.status as rental_status,
                p.payment_id,
                p.payment_date,
                p.amount as payment_amount,
                p.status as payment_status,
                DATEDIFF(CURDATE(), r.rental_end) as days_overdue
              FROM rentals r
              LEFT JOIN payments p ON r.rental_id = p.rental_id
              WHERE r.deceased_id = :deceased_id
              ORDER BY r.rental_start DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':deceased_id', $deceased_id);
    $stmt->execute();

    $rentals = $stmt->fetchAll();

    // Calculate total amount due with penalties
    $total_due = 0;
    $total_paid = 0;

    foreach ($rentals as &$rental) {
        if ($rental['payment_status'] === 'Paid') {
            $total_paid += $rental['payment_amount'];
        } else {
            $amount = $rental['amount'];

            // Apply penalty if overdue
            if ($rental['days_overdue'] > 0) {
                $amount = $amount * 1.25; // 25% penalty
                $rental['penalty_applied'] = true;
                $rental['penalty_amount'] = $amount - $rental['amount'];
            } else {
                $rental['penalty_applied'] = false;
                $rental['penalty_amount'] = 0;
            }

            $rental['total_amount_due'] = $amount;
            $total_due += $amount;
        }
    }

    echo json_encode([
        'success' => true,
        'rentals' => $rentals,
        'summary' => [
            'total_paid' => $total_paid,
            'total_due' => $total_due,
            'total_amount' => $total_paid + $total_due
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

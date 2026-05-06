<?php
session_start();
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Treasurer') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Only Treasurer can process payments'
    ]);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['rental_id'])) {
        throw new Exception("Rental ID is required");
    }

    $database = new db_connector();
    $db = $database->connect();

    $db->beginTransaction();

    // Get rental details
    $rentalQuery = "SELECT * FROM rentals WHERE rental_id = :rental_id";
    $rentalStmt = $db->prepare($rentalQuery);
    $rentalStmt->bindParam(':rental_id', $data['rental_id']);
    $rentalStmt->execute();
    $rental = $rentalStmt->fetch();

    if (!$rental) {
        throw new Exception("Rental record not found");
    }

    $amount = isset($data['amount']) ? floatval($data['amount']) : $rental['amount'];
    $payment_date = isset($data['payment_date']) ? $data['payment_date'] : date('Y-m-d');

    // 25% penalty only after a 2-day grace period past rental_end (base renewal ₱2,000 → ₱500 penalty)
    $rental_end = new DateTime($rental['rental_end']);
    $grace_end  = (clone $rental_end)->modify('+2 days');
    $today      = new DateTime('today');

    if ($today > $grace_end && $rental['status'] === 'Unpaid') {
        $amount = $amount * 1.25;
    }

    // Insert payment record
    $paymentQuery = "INSERT INTO payments
                     (rental_id, payment_date, amount, status)
                     VALUES
                     (:rental_id, :payment_date, :amount, 'Paid')";

    $paymentStmt = $db->prepare($paymentQuery);
    $paymentStmt->bindParam(':rental_id', $data['rental_id']);
    $paymentStmt->bindParam(':payment_date', $payment_date);
    $paymentStmt->bindParam(':amount', $amount);
    $paymentStmt->execute();

    $payment_id = $db->lastInsertId();

    // Update rental status
    $updateRentalQuery = "UPDATE rentals SET status = 'Paid' WHERE rental_id = :rental_id";
    $updateRentalStmt = $db->prepare($updateRentalQuery);
    $updateRentalStmt->bindParam(':rental_id', $data['rental_id']);
    $updateRentalStmt->execute();

    // Create transaction record
    $transactionQuery = "INSERT INTO transactions
                         (name, transaction_date, amount, type, user_id, deceased_id)
                         VALUES
                         (:name, :transaction_date, :amount, 'Payment', :user_id, :deceased_id)";

    $transactionStmt = $db->prepare($transactionQuery);
    $transactionStmt->bindParam(':name', $data['deceased_name']);
    $transactionStmt->bindParam(':transaction_date', $payment_date);
    $transactionStmt->bindParam(':amount', $amount);
    $transactionStmt->bindParam(':user_id', $_SESSION['user_id']);
    $transactionStmt->bindParam(':deceased_id', $rental['deceased_id']);
    $transactionStmt->execute();

    // Create new rental period (3 years)
    $new_start = date('Y-m-d', strtotime($rental['rental_end'] . ' + 1 day'));
    $new_end = date('Y-m-d', strtotime($new_start . ' + 3 years'));

    $newRentalQuery = "INSERT INTO rentals
                       (deceased_id, plot_id, rental_start, rental_end, amount, status, processed_by)
                       VALUES
                       (:deceased_id, :plot_id, :rental_start, :rental_end, 2000.00, 'Unpaid', :processed_by)";

    $newRentalStmt = $db->prepare($newRentalQuery);
    $newRentalStmt->bindParam(':deceased_id', $rental['deceased_id']);
    $newRentalStmt->bindParam(':plot_id', $rental['plot_id']);
    $newRentalStmt->bindParam(':rental_start', $new_start);
    $newRentalStmt->bindParam(':rental_end', $new_end);
    $newRentalStmt->bindParam(':processed_by', $_SESSION['user_id']);
    $newRentalStmt->execute();

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully',
        'payment_id' => $payment_id,
        'amount_paid' => $amount
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

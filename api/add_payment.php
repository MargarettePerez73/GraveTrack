<?php
/**
 * Add Payment Transaction API
 * Records a payment for a rental period
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../Database/db_connector.php';

session_start();

// Check if user is authenticated and is Treasurer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Treasurer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$db = new db_connector();
$conn = $db->connect();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['rental_id', 'deceased_id', 'payment_date', 'amount'];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$rental_id = intval($input['rental_id']);
$deceased_id = intval($input['deceased_id']);
$payment_date = $input['payment_date'];
$amount = floatval($input['amount']);

try {
    // Verify rental exists and belongs to deceased
    $checkSql = "
        SELECT r.rental_id, r.amount, r.rental_start, r.rental_end
        FROM rentals r
        WHERE r.rental_id = :rental_id AND r.deceased_id = :deceased_id
    ";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':rental_id', $rental_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':deceased_id', $deceased_id, PDO::PARAM_INT);
    $checkStmt->execute();

    $rental = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        throw new Exception('Invalid rental period or deceased ID');
    }

    // Compute current total paid for this rental (before inserting this payment)
    $sumSql = "SELECT COALESCE(SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END), 0) AS total_paid
               FROM payments
               WHERE rental_id = :rental_id";
    $sumStmt = $conn->prepare($sumSql);
    $sumStmt->bindParam(':rental_id', $rental_id, PDO::PARAM_INT);
    $sumStmt->execute();
    $sumRow = $sumStmt->fetch(PDO::FETCH_ASSOC);
    $totalPaidBefore = floatval($sumRow['total_paid'] ?? 0);

    // Apply 25% overdue penalty based on rental_end date.
    // Penalized target = rental amount + 25% of rental amount.
    $rentalAmount = floatval($rental['amount']);
    $isOverdue = strtotime($rental['rental_end']) < strtotime(date('Y-m-d'));
    $penaltyAmount = $isOverdue ? ($rentalAmount * 0.25) : 0.0;
    $requiredTotal = $rentalAmount + $penaltyAmount;
    $remainingNeeded = max(0, $requiredTotal - $totalPaidBefore);
    if ($remainingNeeded > 0 && $amount > $remainingNeeded) {
        // Clamp overpayment so system does not accidentally exceed required total.
        $amount = $remainingNeeded;
    }

    // Insert payment record
    $insertSql = "
        INSERT INTO payments (
            rental_id,
            payment_date,
            amount,
            status
        ) VALUES (
            :rental_id,
            :payment_date,
            :amount,
            'Paid'
        )
    ";

    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bindParam(':rental_id', $rental_id, PDO::PARAM_INT);
    $insertStmt->bindParam(':payment_date', $payment_date, PDO::PARAM_STR);
    $insertStmt->bindParam(':amount', $amount, PDO::PARAM_STR);

    if (!$insertStmt->execute()) {
        throw new Exception('Failed to insert payment record');
    }

    $payment_id = $conn->lastInsertId();

    // Update rental status based on new cumulative amount (includes overdue penalty if applicable)
    $newTotalPaid = $totalPaidBefore + $amount;
    $newStatus = ($newTotalPaid + 0.0001 >= $requiredTotal) ? 'Paid' : 'Unpaid';
    $updateSql = "UPDATE rentals SET status = :status WHERE rental_id = :rental_id";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindParam(':status', $newStatus, PDO::PARAM_STR);
    $updateStmt->bindParam(':rental_id', $rental_id, PDO::PARAM_INT);
    $updateStmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Payment recorded successfully',
        'payment_id' => $payment_id,
        'penalty_applied' => $isOverdue,
        'required_total' => round($requiredTotal, 2),
        'total_paid' => round($newTotalPaid, 2),
        'remaining_balance' => round(max(0, $requiredTotal - $newTotalPaid), 2)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

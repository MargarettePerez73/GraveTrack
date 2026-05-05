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
$conn = $db->getConnection();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['rental_id', 'deceased_id', 'payment_date', 'amount', 'payment_method'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$rental_id = intval($input['rental_id']);
$deceased_id = intval($input['deceased_id']);
$payment_date = $input['payment_date'];
$amount = floatval($input['amount']);
$payment_method = trim($input['payment_method']);
$reference_number = isset($input['reference_number']) ? trim($input['reference_number']) : null;
$notes = isset($input['notes']) ? trim($input['notes']) : null;

try {
    $conn->beginTransaction();

    // Verify rental exists and belongs to deceased
    $checkSql = "
        SELECT r.rental_id, r.amount, r.rental_start, r.rental_end
        FROM rental r
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

    // Check if payment already exists for this rental
    $existingSql = "SELECT payment_id FROM payments WHERE rental_id = :rental_id";
    $existingStmt = $conn->prepare($existingSql);
    $existingStmt->bindParam(':rental_id', $rental_id, PDO::PARAM_INT);
    $existingStmt->execute();

    if ($existingStmt->fetch()) {
        throw new Exception('Payment already recorded for this rental period');
    }

    // Insert payment record
    $insertSql = "
        INSERT INTO payments (
            rental_id,
            payment_date,
            amount,
            payment_method,
            reference_number,
            notes,
            recorded_by
        ) VALUES (
            :rental_id,
            :payment_date,
            :amount,
            :payment_method,
            :reference_number,
            :notes,
            :recorded_by
        )
    ";

    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bindParam(':rental_id', $rental_id, PDO::PARAM_INT);
    $insertStmt->bindParam(':payment_date', $payment_date, PDO::PARAM_STR);
    $insertStmt->bindParam(':amount', $amount, PDO::PARAM_STR);
    $insertStmt->bindParam(':payment_method', $payment_method, PDO::PARAM_STR);
    $insertStmt->bindParam(':reference_number', $reference_number, PDO::PARAM_STR);
    $insertStmt->bindParam(':notes', $notes, PDO::PARAM_STR);
    $insertStmt->bindParam(':recorded_by', $_SESSION['user_id'], PDO::PARAM_INT);

    if (!$insertStmt->execute()) {
        throw new Exception('Failed to insert payment record');
    }

    $payment_id = $conn->lastInsertId();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment recorded successfully',
        'payment_id' => $payment_id
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

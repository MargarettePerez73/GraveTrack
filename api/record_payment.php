<?php
/**
 * Record payment from Payment Monitoring UI (plot_id + form fields).
 */
session_start();
header('Content-Type: application/json');

require_once '../Database/db_connector.php';
require_once __DIR__ . '/payment_record_service.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Treasurer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$plot_id = isset($input['plot_id']) ? (int) $input['plot_id'] : 0;
$payment_date = $input['payment_date'] ?? $input['date'] ?? null;
$amount = isset($input['amount']) ? (float) $input['amount'] : null;
$or_number = isset($input['or_number']) ? trim((string) $input['or_number']) : '';
$paid_by = isset($input['paid_by']) ? trim((string) $input['paid_by']) : '';

if ($plot_id <= 0 || !$payment_date || $amount === null || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'plot_id, date, and amount are required']);
    exit;
}

$db = new db_connector();
$conn = $db->connect();

try {
    $findSql = 'SELECT r.rental_id, r.deceased_id, r.plot_id
                FROM rentals r
                INNER JOIN deceased d ON d.deceased_id = r.deceased_id
                WHERE d.plot_id = :plot_id
                ORDER BY r.rental_end DESC
                LIMIT 1';
    $findStmt = $conn->prepare($findSql);
    $findStmt->bindParam(':plot_id', $plot_id, PDO::PARAM_INT);
    $findStmt->execute();
    $row = $findStmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception('No rental found for this plot. Add a rental record first.');
    }

    $result = gravetrack_record_rental_payment($conn, [
        'rental_id'    => (int) $row['rental_id'],
        'deceased_id'  => (int) $row['deceased_id'],
        'payment_date' => $payment_date,
        'amount'       => $amount,
        'or_number'    => $or_number,
        'paid_by'      => $paid_by,
        'plot_id'      => $plot_id,
    ]);

    echo json_encode([
        'success'    => true,
        'message'    => 'Payment recorded successfully',
        'payment_id' => $result['payment_id'],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}

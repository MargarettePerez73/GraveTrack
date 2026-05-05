<?php
/**
 * Add Payment Transaction API
 * Records a payment for a rental period (payments + transactions mirror with OR / paid by).
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../Database/db_connector.php';
require_once __DIR__ . '/payment_record_service.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Treasurer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$required = ['rental_id', 'deceased_id', 'payment_date', 'amount'];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$db = new db_connector();
$conn = $db->connect();

try {
    $result = gravetrack_record_rental_payment($conn, [
        'rental_id'    => (int) $input['rental_id'],
        'deceased_id'  => (int) $input['deceased_id'],
        'payment_date' => $input['payment_date'],
        'amount'       => (float) $input['amount'],
        'or_number'    => $input['or_number'] ?? '',
        'paid_by'      => $input['paid_by'] ?? '',
        'plot_id'      => isset($input['plot_id']) ? (int) $input['plot_id'] : null,
    ]);

    echo json_encode([
        'success'    => true,
        'message'    => 'Payment recorded successfully',
        'payment_id' => $result['payment_id'],
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}

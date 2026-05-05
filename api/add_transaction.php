<?php
session_start();
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Treasurer') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Only Treasurer can add transactions'
    ]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['deceased_id']) || !isset($input['amount']) || empty($input['transaction_date'])) {
        throw new Exception('Required fields: deceased_id, amount, transaction_date');
    }

    $database = new db_connector();
    $db = $database->connect();

    $query = "INSERT INTO transactions 
              (transaction_date, or_number, paid_by, amount, type, user_id, deceased_id";
    
    $params = [
        ':transaction_date' => $input['transaction_date'],
        ':or_number' => $input['or_number'] ?? null,
        ':paid_by' => $input['paid_by'] ?? null,
        ':amount' => floatval($input['amount']),
        ':type' => 'Payment',
        ':user_id' => $_SESSION['user_id'],
        ':deceased_id' => intval($input['deceased_id'])
    ];

    // Add optional plot_id
    if (isset($input['plot_id'])) {
        $query .= ", plot_id";
        $params[':plot_id'] = intval($input['plot_id']);
    } else {
        $query .= ", plot_id";
        $params[':plot_id'] = null;
    }

    $query .= ") VALUES (:transaction_date, :or_number, :paid_by, :amount, :type, :user_id, :deceased_id, :plot_id)";

    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    $transaction_id = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Transaction recorded successfully',
        'transaction_id' => $transaction_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

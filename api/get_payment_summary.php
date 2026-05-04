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
    $database = new db_connector();
    $db = $database->connect();

    $query = "SELECT
                transaction_id,
                `Deceased Name`,
                `Plot Location`,
                `Date of Transaction`,
                `Contact Person`,
                `Contact Number`,
                `Amount`,
                `Status`
              FROM transaction_summary_view
              ORDER BY `Date of Transaction` DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $payments = $stmt->fetchAll();

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
?>

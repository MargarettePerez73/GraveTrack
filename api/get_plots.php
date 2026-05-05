<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $database = new db_connector();
    $db = $database->connect();

    $query = "SELECT plot_id, block, section, lot, type, status, date_added FROM plots ORDER BY block, section, lot";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $plots = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $plots
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

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
    $data = json_decode(file_get_contents('php://input'), true);
    $deceased_name = isset($data['deceased_name']) ? trim($data['deceased_name']) : '';

    if (empty($deceased_name)) {
        throw new Exception("Deceased name is required");
    }

    $database = new db_connector();
    $db = $database->connect();

    $query = "SELECT
                t.transaction_id,
                d.full_name as deceased_name,
                CONCAT(p.block, ' - ', p.section, ' - ', p.lot) as plot_location,
                t.transaction_date,
                t.amount,
                t.type as transaction_type,
                c.contact_person,
                c.contact_number,
                r.rental_start,
                r.rental_end,
                r.status as rental_status,
                pay.payment_date,
                pay.status as payment_status
              FROM transactions t
              LEFT JOIN deceased d ON t.deceased_id = d.deceased_id
              LEFT JOIN plots p ON d.plot_id = p.plot_id
              LEFT JOIN contacts c ON d.deceased_id = c.deceased_id
              LEFT JOIN rentals r ON d.deceased_id = r.deceased_id
              LEFT JOIN payments pay ON r.rental_id = pay.rental_id
              WHERE d.full_name LIKE :deceased_name
              ORDER BY t.transaction_date DESC";

    $stmt = $db->prepare($query);
    $searchTerm = '%' . $deceased_name . '%';
    $stmt->bindParam(':deceased_name', $searchTerm);
    $stmt->execute();

    $transactions = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $transactions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

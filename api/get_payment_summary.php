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

    // Get payment summary for all occupied plots
    $query = "SELECT
                CONCAT(p.block, ' - ', p.section, ' - ', p.lot) as 'Plot Location',
                d.full_name as 'Deceased Name',
                d.date_of_burial as 'Date of Burial',
                MAX(pay.payment_id) as transaction_id,
                MAX(pay.payment_date) as 'Date of Transaction',
                r.rental_id,
                r.rental_start,
                r.rental_end,
                r.amount as 'Rental Amount',
                COALESCE(SUM(CASE WHEN pay.status = 'Paid' THEN pay.amount ELSE 0 END), 0) as 'Amount',
                COALESCE(SUM(CASE WHEN pay.status = 'Paid' THEN pay.amount ELSE 0 END), 0) as 'Total Paid',
                r.amount - COALESCE(SUM(CASE WHEN pay.status = 'Paid' THEN pay.amount ELSE 0 END), 0) as 'Amount Due',
                CASE 
                    WHEN p.status = 'Vacant' THEN 'Vacant'
                    WHEN r.rental_id IS NULL THEN 'No Rental'
                    WHEN COALESCE(SUM(CASE WHEN pay.status = 'Paid' THEN pay.amount ELSE 0 END), 0) >= r.amount AND r.rental_end >= CURDATE() THEN 'Paid'
                    WHEN COALESCE(SUM(CASE WHEN pay.status = 'Paid' THEN pay.amount ELSE 0 END), 0) > 0 AND COALESCE(SUM(CASE WHEN pay.status = 'Paid' THEN pay.amount ELSE 0 END), 0) < r.amount THEN 'Partially Paid'
                    WHEN r.rental_end < CURDATE() AND COALESCE(SUM(CASE WHEN pay.status = 'Paid' THEN pay.amount ELSE 0 END), 0) = 0 THEN 'Overdue'
                    WHEN r.rental_end < CURDATE() THEN 'Overdue - Partial'
                    ELSE 'Unpaid'
                END as 'Status',
                DATEDIFF(CURDATE(), r.rental_end) as 'Days Overdue',
                c.contact_person as 'Contact Person',
                c.contact_number as 'Contact Number'
              FROM plots p
              LEFT JOIN deceased d ON p.plot_id = d.plot_id
              LEFT JOIN contacts c ON d.deceased_id = c.deceased_id
              LEFT JOIN rentals r ON d.deceased_id = r.deceased_id
              LEFT JOIN payments pay ON r.rental_id = pay.rental_id
              WHERE p.status = 'Occupied' OR (p.status IS NOT NULL AND d.deceased_id IS NOT NULL)
              GROUP BY p.plot_id, d.deceased_id, r.rental_id
              ORDER BY r.rental_end ASC, p.block, p.section, p.lot";

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

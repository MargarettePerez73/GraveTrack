<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $database = new db_connector();
    $db = $database->connect();

    // Allow selection of:
    // - Vacant plots
    // - Occupied plots that still have capacity (< 5 deceased)
    $query = "SELECT
                p.plot_id,
                CONCAT('Block ', p.block, ', Section ', p.section, ', Lot ', p.lot) as label,
                p.block,
                p.section,
                p.lot,
                p.type,
                p.status,
                COUNT(d.deceased_id) AS deceased_count
              FROM plots p
              LEFT JOIN deceased d ON p.plot_id = d.plot_id
              WHERE p.status IN ('Vacant','Occupied')
              GROUP BY p.plot_id
              HAVING (p.status = 'Vacant' OR (p.status = 'Occupied' AND COUNT(d.deceased_id) < 5))
              ORDER BY p.block, p.section, p.lot";

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

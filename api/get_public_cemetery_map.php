<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $database = new db_connector();
    $db = $database->connect();

    $query = "SELECT
                p.plot_id,
                p.block,
                p.section,
                p.lot,
                p.type,
                p.status,
                p.date_added,
                CASE
                    WHEN p.block = 'AA' THEN 'Phase 3'
                    WHEN p.block REGEXP '^[A-I]$' THEN 'Phase 1'
                    WHEN p.block REGEXP '^[T-Z]$' THEN 'Phase 2'
                    ELSE 'Unassigned'
                END as phase,
                COUNT(d.deceased_id) as deceased_count,
                GROUP_CONCAT(DISTINCT d.full_name ORDER BY d.date_of_burial DESC SEPARATOR ', ') as deceased_names
              FROM plots p
              LEFT JOIN deceased d ON p.plot_id = d.plot_id
              GROUP BY p.plot_id
              ORDER BY 
                FIELD(phase, 'Phase 3', 'Phase 2', 'Phase 1', 'Unassigned'),
                p.block, p.section, p.lot";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $plots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'plots' => $plots,
        'count' => count($plots)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


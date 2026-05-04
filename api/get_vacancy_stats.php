<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $database = new db_connector();
    $db = $database->connect();

    // Get filters from query params
    $block = isset($_GET['block']) ? $_GET['block'] : '';
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    // Build WHERE clause
    $where = [];
    $params = [];

    if (!empty($block)) {
        $where[] = "block = :block";
        $params[':block'] = $block;
    }
    if (!empty($type)) {
        $where[] = "type = :type";
        $params[':type'] = $type;
    }
    if (!empty($status)) {
        $where[] = "status = :status";
        $params[':status'] = $status;
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get statistics
    $statsQuery = "SELECT
                    COUNT(*) as total_plots,
                    SUM(CASE WHEN status = 'Vacant' THEN 1 ELSE 0 END) as total_vacant,
                    SUM(CASE WHEN status = 'Occupied' THEN 1 ELSE 0 END) as total_occupied,
                    SUM(CASE WHEN status = 'Reserved' THEN 1 ELSE 0 END) as total_reserved
                   FROM plots
                   $whereClause";

    $statsStmt = $db->prepare($statsQuery);
    foreach ($params as $key => $value) {
        $statsStmt->bindValue($key, $value);
    }
    $statsStmt->execute();
    $stats = $statsStmt->fetch();

    // Get plot list
    $plotsQuery = "SELECT
                    plot_id,
                    block,
                    section,
                    lot,
                    type,
                    status,
                    date_added
                   FROM plots
                   $whereClause
                   ORDER BY block, section, lot";

    $plotsStmt = $db->prepare($plotsQuery);
    foreach ($params as $key => $value) {
        $plotsStmt->bindValue($key, $value);
    }
    $plotsStmt->execute();
    $plots = $plotsStmt->fetchAll();

    // Get unique blocks for filter dropdown
    $blocksQuery = "SELECT DISTINCT block FROM plots ORDER BY block";
    $blocksStmt = $db->prepare($blocksQuery);
    $blocksStmt->execute();
    $blocks = $blocksStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'plots' => $plots,
        'blocks' => $blocks
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

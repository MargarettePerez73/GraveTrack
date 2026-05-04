<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $database = new db_connector();
    $db = $database->connect();

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $phase = isset($_GET['phase']) ? trim($_GET['phase']) : '';

    // Build WHERE clause for phase filter
    $where = [];
    $params = [];

    if (!empty($phase)) {
        switch ($phase) {
            case 'Phase 1':
                $where[] = "block REGEXP '^[A-I]'";
                break;
            case 'Phase 2':
                $where[] = "block REGEXP '^[T-Z]'";
                break;
            case 'Phase 3':
                $where[] = "block = 'AA'";
                break;
        }
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get plots with deceased count and names
    $query = "SELECT
                p.plot_id,
                p.block,
                p.section,
                p.lot,
                p.type,
                p.status,
                p.date_added,
                CASE
                    WHEN p.block REGEXP '^[A-I]' THEN 'Phase 1'
                    WHEN p.block REGEXP '^[T-Z]' THEN 'Phase 2'
                    WHEN p.block = 'AA' THEN 'Phase 3'
                    ELSE 'Unassigned'
                END as phase,
                COUNT(d.deceased_id) as deceased_count,
                GROUP_CONCAT(d.full_name SEPARATOR ', ') as deceased_names
              FROM plots p
              LEFT JOIN deceased d ON p.plot_id = d.plot_id
              $whereClause
              GROUP BY p.plot_id
              ORDER BY p.block, p.section, p.lot";

    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $plots = $stmt->fetchAll();

    // Filter by search if provided
    if (!empty($search)) {
        $plots = array_filter($plots, function($plot) use ($search) {
            $searchLower = strtolower($search);
            return strpos(strtolower($plot['block']), $searchLower) !== false ||
                   strpos(strtolower($plot['lot']), $searchLower) !== false ||
                   strpos(strtolower($plot['deceased_names'] ?? ''), $searchLower) !== false;
        });
        $plots = array_values($plots); // Re-index array
    }

    // Group by phase
    $plotsByPhase = [
        'Phase 1' => [],
        'Phase 2' => [],
        'Phase 3' => [],
        'Unassigned' => []
    ];

    foreach ($plots as $plot) {
        $plotsByPhase[$plot['phase']][] = $plot;
    }

    echo json_encode([
        'success' => true,
        'plots' => $plots,
        'plotsByPhase' => $plotsByPhase
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

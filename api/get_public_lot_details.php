<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $database = new db_connector();
    $db = $database->connect();

    $plot_id = $_GET['plot_id'] ?? 0;
    if (!$plot_id) {
        throw new Exception('Plot ID required');
    }

    $query = "SELECT 
                p.plot_id, p.block, p.section, p.lot, p.type, p.status, p.date_added, p.phase,
                GROUP_CONCAT(DISTINCT d.full_name ORDER BY d.date_of_burial DESC SEPARATOR ', ') as deceased_names,
                d.deceased_id, d.full_name, d.birth_date, d.date_of_death, d.date_of_burial
              FROM plots p 
              LEFT JOIN deceased d ON p.plot_id = d.plot_id 
              WHERE p.plot_id = :plot_id
              GROUP BY p.plot_id, d.deceased_id
              ORDER BY d.date_of_burial DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':plot_id', $plot_id, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        echo json_encode(['success' => false, 'message' => 'Plot not found']);
        exit;
    }

    // Extract plot info (first row)
    $plot = $results[0];
    unset($plot['deceased_id'], $plot['full_name'], $plot['date_of_death'], $plot['date_of_burial']);

    // Extract deceased array
    $deceased = array_filter($results, function($row) {
        return !empty($row['deceased_id']);
    });

    echo json_encode([
        'success' => true,
        'plot' => $plot,
        'deceased' => $deceased
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


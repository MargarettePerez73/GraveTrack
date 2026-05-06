<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $plot_id = isset($_GET['plot_id']) ? intval($_GET['plot_id']) : 0;

    if ($plot_id === 0) {
        throw new Exception("Plot ID is required");
    }

    $database = new db_connector();
    $db = $database->connect();

    // Get plot information
    $plotQuery = "SELECT *, 
        CASE
            WHEN block = 'AA' THEN 'Phase 3'
            WHEN block REGEXP '^[A-I]$' THEN 'Phase 1'
            WHEN block REGEXP '^[T-Z]$' THEN 'Phase 2'
            ELSE 'Unassigned'
        END as phase
        FROM plots WHERE plot_id = :plot_id";
    $plotStmt = $db->prepare($plotQuery);
    $plotStmt->bindParam(':plot_id', $plot_id);
    $plotStmt->execute();
    $plot = $plotStmt->fetch(PDO::FETCH_ASSOC);

    if (!$plot) {
        throw new Exception("Plot not found");
    }

    // Get all deceased records for this plot - only name, DOB, DOD for public
    $deceasedQuery = 
    "SELECT
        d.deceased_id,
        d.full_name,
        d.birth_date,
        d.date_of_death,
        d.date_of_burial
    FROM deceased d
    WHERE d.plot_id = :plot_id
    ORDER BY d.date_of_burial DESC";

    $deceasedStmt = $db->prepare($deceasedQuery);
    $deceasedStmt->bindParam(':plot_id', $plot_id);
    $deceasedStmt->execute();
    $deceased_records = $deceasedStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'plot' => $plot,
        'deceased_records' => $deceased_records
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['deceased_id'])) {
        throw new Exception("Deceased ID is required");
    }

    $database = new db_connector();
    $db = $database->connect();

    $db->beginTransaction();

    // Get plot_id before deleting
    $getPlotQuery = "SELECT plot_id FROM deceased WHERE deceased_id = :deceased_id";
    $getPlotStmt = $db->prepare($getPlotQuery);
    $getPlotStmt->bindParam(':deceased_id', $data['deceased_id']);
    $getPlotStmt->execute();
    $deceased = $getPlotStmt->fetch();
    $plot_id = $deceased['plot_id'];

    // Delete deceased record (cascade will handle contacts, rentals, payments)
    $deleteQuery = "DELETE FROM deceased WHERE deceased_id = :deceased_id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':deceased_id', $data['deceased_id']);
    $deleteStmt->execute();

    // Check if there are any other deceased in this plot
    $checkQuery = "SELECT COUNT(*) as count FROM deceased WHERE plot_id = :plot_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':plot_id', $plot_id);
    $checkStmt->execute();
    $result = $checkStmt->fetch();

    // If no more deceased, mark plot as vacant
    if ($result['count'] == 0) {
        $updatePlotQuery = "UPDATE plots SET status = 'Vacant' WHERE plot_id = :plot_id";
        $updatePlotStmt = $db->prepare($updatePlotQuery);
        $updatePlotStmt->bindParam(':plot_id', $plot_id);
        $updatePlotStmt->execute();
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Burial record deleted successfully'
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

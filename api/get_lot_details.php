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
    $plotQuery = "SELECT * FROM plots WHERE plot_id = :plot_id";
    $plotStmt = $db->prepare($plotQuery);
    $plotStmt->bindParam(':plot_id', $plot_id);
    $plotStmt->execute();
    $plot = $plotStmt->fetch();

    if (!$plot) {
        throw new Exception("Plot not found");
    }

    // Get all deceased records for this plot
    $deceasedQuery = "SELECT
                        d.deceased_id,
                        d.full_name,
                        d.date_of_birth as birth_date,
                        d.date_of_death,
                        d.date_of_burial,
                        d.gender,
                        d.address,
                        d.burial_type,
                        c.contact_person,
                        c.contact_number
                      FROM deceased d
                      LEFT JOIN contacts c ON d.deceased_id = c.deceased_id
                      WHERE d.plot_id = :plot_id
                      ORDER BY d.date_of_burial DESC";

    $deceasedStmt = $db->prepare($deceasedQuery);
    $deceasedStmt->bindParam(':plot_id', $plot_id);
    $deceasedStmt->execute();
    $deceased_records = $deceasedStmt->fetchAll();

    // Generate HTML for modal
    $html = '<div class="plot-details">';
    $html .= '<h4>Plot Information</h4>';
    $html .= '<p><strong>Block:</strong> ' . htmlspecialchars($plot['block']) . '</p>';
    $html .= '<p><strong>Section:</strong> ' . htmlspecialchars($plot['section']) . '</p>';
    $html .= '<p><strong>Lot:</strong> ' . htmlspecialchars($plot['lot']) . '</p>';
    $html .= '<p><strong>Type:</strong> ' . htmlspecialchars($plot['type']) . '</p>';
    $html .= '<p><strong>Status:</strong> <span class="badge badge-' .
             ($plot['status'] === 'Vacant' ? 'success' : 'danger') . '">' .
             htmlspecialchars($plot['status']) . '</span></p>';

    if (count($deceased_records) > 0) {
        $html .= '<hr><h4>Deceased Records (' . count($deceased_records) . ')</h4>';
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-sm">';
        $html .= '<thead><tr><th>Name</th><th>Date of Death</th><th>Contact Person</th><th>Contact Number</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($deceased_records as $record) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($record['full_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($record['date_of_death']) . '</td>';
            $html .= '<td>' . htmlspecialchars($record['contact_person'] ?? 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($record['contact_number'] ?? 'N/A') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
    }
    $html .= '</div>';

    echo json_encode([
        'success' => true,
        'plot' => $plot,
        'deceased_records' => $deceased_records,
        'html' => $html
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

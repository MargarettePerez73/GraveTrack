<?php
/**
 * Live Search API - Search deceased by name (Public - No Auth Required)
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../Database/db_connector.php';

$db = new db_connector();
$conn = $db->connect();

// Get search term
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($searchTerm) < 2) {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

try {
    $sql = "
        SELECT
            d.deceased_id,
            d.full_name,
            d.birth_date,
            d.date_of_death,
            d.date_of_burial,
            p.plot_id,
            p.block,
            p.section,
            p.lot,
            p.phase
        FROM deceased d
        INNER JOIN plots p ON d.plot_id = p.plot_id
        WHERE d.full_name LIKE :search
        ORDER BY d.full_name ASC
        LIMIT 10
    ";

    $stmt = $conn->prepare($sql);
    $searchParam = '%' . $searchTerm . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'results' => $results
    ]);

} catch (Exception $e) {
    error_log('Search API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Search error: ' . $e->getMessage()
    ]);
}
?>

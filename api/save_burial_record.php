<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $required = ['full_name', 'date_of_death', 'date_of_burial', 'plot_id'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . " is required");
        }
    }

    $database = new db_connector();
    $db = $database->connect();

    // Start transaction
    $db->beginTransaction();

    // Insert deceased record
    $deceasedQuery = "INSERT INTO deceased
                      (full_name, date_of_death, date_of_burial, gender, address, plot_id, burial_type, birth_date)
                      VALUES
                      (:full_name, :date_of_death, :date_of_burial, :gender, :address, :plot_id, :burial_type, :birth_date)";

    $deceasedStmt = $db->prepare($deceasedQuery);
    $deceasedStmt->bindParam(':full_name', $data['full_name']);
    $deceasedStmt->bindParam(':date_of_death', $data['date_of_death']);
    $deceasedStmt->bindParam(':date_of_burial', $data['date_of_burial']);
    $deceasedStmt->bindParam(':gender', $data['gender']);
    $deceasedStmt->bindParam(':address', $data['address']);
    $deceasedStmt->bindParam(':plot_id', $data['plot_id']);
    $deceasedStmt->bindParam(':burial_type', $data['burial_type']);
    $deceasedStmt->bindParam(':birth_date', $data['birth_date']);
    $deceasedStmt->execute();

    $deceased_id = $db->lastInsertId();

    // Insert contact record if provided
    if (!empty($data['contact_person']) || !empty($data['contact_number'])) {
        $contactQuery = "INSERT INTO contacts
                         (deceased_id, contact_person, contact_number)
                         VALUES
                         (:deceased_id, :contact_person, :contact_number)";

        $contactStmt = $db->prepare($contactQuery);
        $contactStmt->bindParam(':deceased_id', $deceased_id);
        $contactStmt->bindParam(':contact_person', $data['contact_person']);
        $contactStmt->bindParam(':contact_number', $data['contact_number']);
        $contactStmt->execute();
    }

    // Update plot status (trigger will also do this, but we do it explicitly too)
    $updatePlotQuery = "UPDATE plots SET status = 'Occupied' WHERE plot_id = :plot_id";
    $updatePlotStmt = $db->prepare($updatePlotQuery);
    $updatePlotStmt->bindParam(':plot_id', $data['plot_id']);
    $updatePlotStmt->execute();

// Create initial rental record ONLY if none exists for this burial date (prevents duplicates)
    $rental_start = $data['date_of_burial'];
    $rental_end = date('Y-m-d', strtotime($rental_start . ' + 3 years'));
    $rental_amount = 2000.00;

    // CHECK if rental already exists for this deceased + start date
    $checkRental = $db->prepare("SELECT COUNT(*) FROM rentals WHERE deceased_id = :deceased_id AND rental_start = :rental_start");
    $checkRental->bindParam(':deceased_id', $deceased_id);
    $checkRental->bindParam(':rental_start', $rental_start);
    $checkRental->execute();
    
    if ($checkRental->fetchColumn() == 0) {
        $rentalQuery = "INSERT INTO rentals
                        (deceased_id, plot_id, rental_start, rental_end, amount, status)
                        VALUES
                        (:deceased_id, :plot_id, :rental_start, :rental_end, :amount, 'Unpaid')";

        $rentalStmt = $db->prepare($rentalQuery);
        $rentalStmt->bindParam(':deceased_id', $deceased_id);
        $rentalStmt->bindParam(':plot_id', $data['plot_id']);
        $rentalStmt->bindParam(':rental_start', $rental_start);
        $rentalStmt->bindParam(':rental_end', $rental_end);
        $rentalStmt->bindParam(':amount', $rental_amount);
        $rentalStmt->execute();
    }

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Burial record saved successfully',
        'deceased_id' => $deceased_id
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

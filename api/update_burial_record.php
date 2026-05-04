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

    // Update deceased record
    $deceasedQuery = "UPDATE deceased SET
                      full_name = :full_name,
                      date_of_death = :date_of_death,
                      date_of_burial = :date_of_burial,
                      gender = :gender,
                      address = :address,
                      burial_type = :burial_type,
                      birth_date = :birth_date
                      WHERE deceased_id = :deceased_id";

    $deceasedStmt = $db->prepare($deceasedQuery);
    $deceasedStmt->bindParam(':deceased_id', $data['deceased_id']);
    $deceasedStmt->bindParam(':full_name', $data['full_name']);
    $deceasedStmt->bindParam(':date_of_death', $data['date_of_death']);
    $deceasedStmt->bindParam(':date_of_burial', $data['date_of_burial']);
    $deceasedStmt->bindParam(':gender', $data['gender']);
    $deceasedStmt->bindParam(':address', $data['address']);
    $deceasedStmt->bindParam(':burial_type', $data['burial_type']);
    $deceasedStmt->bindParam(':birth_date', $data['birth_date']);
    $deceasedStmt->execute();

    // Update or insert contact
    $checkContactQuery = "SELECT contact_id FROM contacts WHERE deceased_id = :deceased_id";
    $checkStmt = $db->prepare($checkContactQuery);
    $checkStmt->bindParam(':deceased_id', $data['deceased_id']);
    $checkStmt->execute();
    $existingContact = $checkStmt->fetch();

    if ($existingContact) {
        $contactQuery = "UPDATE contacts SET
                         contact_person = :contact_person,
                         contact_number = :contact_number
                         WHERE deceased_id = :deceased_id";
    } else {
        $contactQuery = "INSERT INTO contacts
                         (deceased_id, contact_person, contact_number)
                         VALUES
                         (:deceased_id, :contact_person, :contact_number)";
    }

    $contactStmt = $db->prepare($contactQuery);
    $contactStmt->bindParam(':deceased_id', $data['deceased_id']);
    $contactStmt->bindParam(':contact_person', $data['contact_person']);
    $contactStmt->bindParam(':contact_number', $data['contact_number']);
    $contactStmt->execute();

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Burial record updated successfully'
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

<?php
/**
 * GraveTrack API Test Suite
 * Simple command-line PHP script to test all API endpoints
 *
 * Usage: php test_api.php
 */

$BASE_URL = 'http://localhost/gravetrack/api/';

echo "=== GraveTrack API Test Suite ===\n\n";

// Helper function to make API calls
function testEndpoint($method, $endpoint, $data = null, $description = '') {
    global $BASE_URL;

    echo "Testing: $description\n";
    echo "Endpoint: $method $endpoint\n";

    $ch = curl_init($BASE_URL . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Status: $httpCode\n";
    echo "Response: " . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n";
    echo str_repeat('-', 80) . "\n\n";

    return json_decode($response, true);
}

// 1. Test Authentication
echo "\n=== AUTHENTICATION TESTS ===\n\n";

testEndpoint('POST', 'auth.php', [
    'username' => 'testdummy1',
    'password' => '12345'
], 'Login as Treasurer');

testEndpoint('GET', 'auth.php', null, 'Check Session Status');

// 2. Test Plots API
echo "\n=== PLOTS & VACANCY TESTS ===\n\n";

testEndpoint('GET', 'get_plots.php', null, 'Get All Plots');
testEndpoint('GET', 'get_vacant_plots.php', null, 'Get Vacant Plots Only');
testEndpoint('GET', 'get_lot_details.php?plot_id=1', null, 'Get Lot Details for Plot ID 1');
testEndpoint('GET', 'get_vacancy_stats.php', null, 'Get Vacancy Statistics');
testEndpoint('GET', 'get_cemetery_map.php', null, 'Get Cemetery Map Data');
testEndpoint('GET', 'get_cemetery_map.php?phase=Phase%201', null, 'Get Cemetery Map - Phase 1 Only');

// 3. Test Burial Records API
echo "\n=== BURIAL RECORDS TESTS ===\n\n";

$newBurialData = [
    'full_name' => 'API Test Person',
    'date_of_death' => '2026-05-01',
    'date_of_burial' => '2026-05-03',
    'gender' => 'Male',
    'address' => 'Test Address, Batangas',
    'plot_id' => 4,
    'burial_type' => 'Single',
    'birth_date' => '1950-01-01',
    'contact_person' => 'Test Contact Person',
    'contact_number' => '09123456789'
];

$result = testEndpoint('POST', 'save_burial_record.php', $newBurialData, 'Create New Burial Record');
$deceased_id = $result['deceased_id'] ?? null;

if ($deceased_id) {
    // Test update
    $updateData = $newBurialData;
    $updateData['deceased_id'] = $deceased_id;
    $updateData['full_name'] = 'API Test Person UPDATED';

    testEndpoint('POST', 'update_burial_record.php', $updateData, 'Update Burial Record');

    // Test delete (commented out to preserve data)
    // testEndpoint('POST', 'delete_burial_record.php', ['deceased_id' => $deceased_id], 'Delete Burial Record');
}

// 4. Test Payment APIs (Treasurer only)
echo "\n=== PAYMENT MONITORING TESTS ===\n\n";

testEndpoint('GET', 'get_payment_summary.php', null, 'Get Payment Summary');

testEndpoint('POST', 'get_deceased_transactions.php', [
    'deceased_name' => 'Maria Santos'
], 'Get Deceased Transactions by Name');

testEndpoint('GET', 'get_rental_details.php?deceased_id=1', null, 'Get Rental Details for Deceased ID 1');

// Test payment processing (commented out to avoid actual payment creation)
/*
testEndpoint('POST', 'process_payment.php', [
    'rental_id' => 1,
    'amount' => 2000,
    'payment_date' => date('Y-m-d'),
    'deceased_name' => 'Maria Santos'
], 'Process Payment for Rental ID 1');
*/

// 5. Test Logout
echo "\n=== LOGOUT TEST ===\n\n";
testEndpoint('DELETE', 'auth.php', null, 'Logout');
testEndpoint('GET', 'auth.php', null, 'Verify Session Destroyed');

echo "\n=== All Tests Completed ===\n";
?>

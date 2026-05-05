<?php
session_start();
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Login
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['username']) || empty($data['password'])) {
            throw new Exception("Username and password are required");
        }

        $database = new db_connector();
        $db = $database->connect();

        $query = "SELECT user_id, username, email, role FROM users
                  WHERE username = :username AND password = :password";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->execute();

        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Invalid username or password");
        }

        // Set session
        $_SESSION['user'] = $user['username'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

} elseif ($method === 'GET') {
    // Check session
    if (isset($_SESSION['user'])) {
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user' => [
                'username' => $_SESSION['user'],
                'role' => $_SESSION['role'],
                'email' => $_SESSION['email']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'authenticated' => false
        ]);
    }

} elseif ($method === 'DELETE') {
    // Logout
    session_destroy();
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}
?>

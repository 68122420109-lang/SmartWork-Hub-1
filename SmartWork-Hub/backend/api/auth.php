<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['action']) && $data['action'] === 'login') {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            echo json_encode(['success' => true, 'role' => $user['role']]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    } else if (isset($data['action']) && $data['action'] === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
    }
} else if ($method === 'GET') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode(['authenticated' => true, 'role' => $_SESSION['role']]);
    } else {
        echo json_encode(['authenticated' => false]);
    }
}
?>

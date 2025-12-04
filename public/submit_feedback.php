<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Database connection
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=smartbus', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!$input['subject'] || !$input['message'] || !$input['type']) {
        throw new Exception('Missing required fields');
    }
    
    // Insert feedback
    $sql = "INSERT INTO feedback (user_id, subject, message, type, rating, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 'pending', NOW(), NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        2, // Default user ID for testing
        $input['subject'],
        $input['message'],
        $input['type'],
        $input['rating'] ?? null
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Feedback submitted successfully',
        'id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
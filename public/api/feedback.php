<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=smartbus', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($method === 'POST') {
        // Create feedback
        if (!$input['subject'] || !$input['message'] || !$input['type']) {
            throw new Exception('Missing required fields');
        }
        
        $sql = "INSERT INTO feedback (user_id, subject, message, type, rating, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            2, // Default user ID
            $input['subject'],
            $input['message'],
            $input['type'],
            $input['rating'] ?? null
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Feedback created successfully',
            'id' => $pdo->lastInsertId()
        ]);
        
    } elseif ($method === 'GET') {
        // Get feedback with optional type filter
        $type = $_GET['type'] ?? null;
        
        if ($type && $type !== 'all') {
            $sql = "SELECT * FROM feedback WHERE type = ? ORDER BY created_at DESC LIMIT 50";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$type]);
        } else {
            $sql = "SELECT * FROM feedback ORDER BY created_at DESC LIMIT 50";
            $stmt = $pdo->query($sql);
        }
        
        $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $feedback]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
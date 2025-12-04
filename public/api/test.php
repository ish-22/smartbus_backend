<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'status' => 'PHP API is working',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => 'Direct PHP (not Laravel)'
]);
?>
<?php
require_once "config.php";

if (isset($_GET['username'])) {
    $username = trim($_GET['username']);
    
    if (empty($username)) {
        echo json_encode(['status' => 'empty']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'taken']);
    } else {
        echo json_encode(['status' => 'available']);
    }
    exit;
}
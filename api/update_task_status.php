<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (empty($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$taskId = $data['id'] ?? null;
$newStatus = $data['status'] ?? null;

$allowedStatuses = ['todo', 'doing', 'done'];

if ($taskId && in_array($newStatus, $allowedStatuses)) {
    try {
        require_once "../config.php";
        
        // تغییر وضعیت تسک بر اساس آیدی مربوطه در دیتابیس
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $taskId]);
        
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database Error"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid Data"]);
}
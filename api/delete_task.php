<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$taskId = $data['id'] ?? null;

if (!$taskId) {
    echo json_encode(["success" => false, "message" => "شناسه تسک نامعتبر است."]);
    exit;
}

try {
    require_once "../config.php";

    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "خطای دیتابیس: " . $e->getMessage()]);
}
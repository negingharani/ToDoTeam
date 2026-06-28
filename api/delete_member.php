<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once "../config.php";

if (empty($_SESSION['user_id']) || empty($_SESSION['project_id']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(["success" => false, "message" => "عدم دسترسی کافی"]);
    exit;
}

$project_id = $_SESSION['project_id'];
$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');

if (empty($username)) {
    echo json_encode(["success" => false, "message" => "نام کاربری نامعتبر است."]);
    exit;
}

try {
    $stmt_u = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt_u->execute([$username]);
    $user = $stmt_u->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "کاربر مورد نظر یافت نشد."]);
        exit;
    }

    $user_id = $user['id'];

    // Delete relation row from target project
    $stmt_del = $pdo->prepare("DELETE FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt_del->execute([$project_id, $user_id]);

    echo json_encode(["success" => true, "message" => "کاربر با موفقیت از پروژه حذف شد."]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "خطا در فرآیند حذف از دیتابیس."]);
}
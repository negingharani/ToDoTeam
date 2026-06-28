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
$position = trim($data['position'] ?? '');
$role     = trim($data['role'] ?? 'member');

if (empty($username) || empty($position)) {
    echo json_encode(["success" => false, "message" => "وارد کردن اطلاعات فیلد پوزیشن الزامی است."]);
    exit;
}

try {
    // Find target user ID from username
    $stmt_u = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt_u->execute([$username]);
    $user = $stmt_u->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "کاربر مورد نظر یافت نشد."]);
        exit;
    }

    $user_id = $user['id'];

    // Update relationship information inside project scope
    $stmt_update = $pdo->prepare("UPDATE project_members SET position = ?, role = ? WHERE project_id = ? AND user_id = ?");
    $stmt_update->execute([$position, $role, $project_id, $user_id]);

    echo json_encode(["success" => true, "message" => "اطلاعات با موفقیت ویرایش شد."]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "خطایی در به‌روزرسانی پایگاه داده به وجود آمد."]);
}
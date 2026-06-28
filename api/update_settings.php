<?php
// api/update_settings.php
session_start();
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) exit;

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

// به‌روزرسانی نام و ایمیل (بدون تغییر نام کاربری طبق خواسته شما)
$sql = "UPDATE users SET full_name = ?, email = ?";
$params = [$data['full_name'], $data['email']];

// در صورت ورود پسورد جدید
if (!empty($data['password'])) {
    $sql .= ", password = ?";
    $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
}

$sql .= " WHERE id = ?";
$params[] = $user_id;

$stmt = $pdo->prepare($sql);
if ($stmt->execute($params)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'خطا در ذخیره اطلاعات']);
}
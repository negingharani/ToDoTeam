<?php
// api/settings.php
session_start();
require_once "../config.php"; // مسیر کانفیگ دیتابیس

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// دریافت اطلاعات کاربر
$stmt = $pdo->prepare("SELECT full_name, email, username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// دریافت پروژه‌های کاربر
// اصلاح کوئری برای جلوگیری از تکرار
$stmt_proj = $pdo->prepare("
    SELECT DISTINCT p.id, p.name 
    FROM projects p 
    LEFT JOIN project_members pm ON p.id = pm.project_id 
    WHERE p.owner_id = ? OR pm.user_id = ?
");
$stmt_proj->execute([$user_id, $user_id]);
$projects = $stmt_proj->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'user' => $user,
    'projects' => $projects,
    'current_project_id' => $_SESSION['project_id']
]);
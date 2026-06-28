<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once "../config.php";

if (empty($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "عدم دسترسی"]);
    exit;
}

$project_id = $_SESSION['project_id'] ?? 0;

if ($project_id <= 0) {
    echo json_encode([]);
    exit;
}

try {
    // اصلاح کوئری: با استفاده از CASE، اگر کاربر مالک پروژه بود، نقش او را به صورت متنی 'owner' رد می‌کنیم تا جاوااسکریپت آن را به عنوان عضو تیم رندر نکند
    $query = "SELECT 
                u.full_name AS name, 
                u.username, 
                CASE WHEN p.owner_id = u.id THEN 'manager.png' ELSE u.avatar END AS avatar, 
                CASE WHEN p.owner_id = u.id THEN 'owner' ELSE pm.role END AS role, 
                pm.position 
              FROM project_members pm
              JOIN users u ON pm.user_id = u.id
              JOIN projects p ON pm.project_id = p.id
              WHERE pm.project_id = ?
              ORDER BY u.full_name ASC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$project_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($members);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "خطا در دیتابیس"]);
}
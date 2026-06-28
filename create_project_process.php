<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: create_project.php");
    exit;
}

$user_id      = $_SESSION['user_id'];
$project_name = trim($_POST['project_name'] ?? '');
$type         = $_POST['type'] ?? 'personal';
$description  = trim($_POST['description'] ?? '');

$_SESSION['old_inputs'] = [
    'project_name' => $project_name,
    'type'         => $type,
    'description'  => $description
];

if (empty($project_name)) {
    $_SESSION['error'] = "وارد کردن نام پروژه الزامی است.";
    header("Location: create_project.php");
    exit;
}

// [حالت سوم شما] مچ‌گیری حواسی: آیا پروژه با این نام از قبل برای این کاربر وجود دارد؟
$stmt = $pdo->prepare("SELECT id FROM projects WHERE name = ? AND owner_id = ? LIMIT 1");
$stmt->execute([$project_name, $user_id]);
$existing_project = $stmt->fetch();

if ($existing_project) {
    // پروژه از قبل وجود داشته، دیتابیس را شلوغ نمی‌کنیم و مستقیم می‌رویم داشبورد
    unset($_SESSION['old_inputs']);
    header("Location: dashboard.php?project_id=" . $existing_project['id']);
    exit;
}

// [حالت دوم شما] کاربر لاگین است و پروژه جدید می‌سازد (هر چندتا که دلش بخواهد با یک ایمیل)
$stmt = $pdo->prepare("
    INSERT INTO projects(name, type, description, owner_id)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$project_name, $type, $description, $user_id]);
$project_id = $pdo->lastInsertId();

// تخصیص دسترسی مالک به پروژه
$stmt = $pdo->prepare("
    INSERT INTO project_members(project_id, user_id, role)
    VALUES (?, ?, 'owner')
");
$stmt->execute([$project_id, $user_id]);

unset($_SESSION['old_inputs']);
header("Location: dashboard.php?project_id=" . $project_id);
exit;
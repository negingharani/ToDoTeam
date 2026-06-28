<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once "../config.php";

if (empty($_SESSION['user_id']) || empty($_SESSION['project_id'])) {
    echo json_encode(["success" => false, "message" => "عدم دسترسی"]);
    exit;
}

$project_id = $_SESSION['project_id'];
$data = json_decode(file_get_contents("php://input"), true);

$full_name = trim($data['full_name'] ?? '');
$username  = trim($data['username'] ?? '');
$email     = strtolower(trim($data['email'] ?? ''));
$position  = trim($data['position'] ?? '');
$password  = $data['password'] ?? '';
$role      = trim($data['role'] ?? 'member'); 

if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($position)) {
    echo json_encode(["success" => false, "message" => "پر کردن تمامی فیلدها الزامی است."]);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(["success" => false, "message" => "رمز عبور باید حداقل ۸ کاراکتر باشد."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if username is already taken inside this specific project
    $stmt = $pdo->prepare("
        SELECT u.id FROM users u
        JOIN project_members pm ON u.id = pm.user_id
        WHERE u.username = ? AND pm.project_id = ? LIMIT 1
    ");
    $stmt->execute([$username, $project_id]);
    if ($stmt->fetch()) {
        echo json_encode(["success" => false, "message" => "این نام کاربری قبلاً در این پروژه استفاده شده است."]);
        exit;
    }

    // Check globally if user exists via email address
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = $user['id'];
    } else {
        // Build new user with hashed password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$full_name, $username, $email, $hashed_password]);
        $user_id = $pdo->lastInsertId();
    }

    // Attach user to current project with defined dynamic role and position
    $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role, position) VALUES (?, ?, ?, ?)");
    $stmt->execute([$project_id, $user_id, $role, $position]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "کاربر با موفقیت به پروژه اضافه شد."]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["success" => false, "message" => "خطایی در پایگاه داده رخ داده است."]);
}
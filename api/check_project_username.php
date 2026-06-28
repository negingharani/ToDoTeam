<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json; charset=UTF-8");

require_once "../config.php";

// دریافت نام کاربری ارسالی و آیدی پروژه از سشن
$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$project_id = isset($_SESSION['project_id']) ? (int)$_SESSION['project_id'] : 0;

if (empty($username)) {
    echo json_encode(['status' => 'empty']);
    exit;
}

if ($project_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'پروژه مشخص نیست.']);
    exit;
}

try {
    /*
       این کوئری بررسی می‌کند آیا کاربری با این نام کاربری، 
       قبلاً در جدول project_members برای این پروژه خاص ثبت شده است یا خیر.
    */
    $query = "
        SELECT pm.id 
        FROM project_members pm
        JOIN users u ON pm.user_id = u.id
        WHERE u.username = ? AND pm.project_id = ?
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $project_id]);

    if ($stmt->fetch()) {
        // این نام کاربری قبلاً عضو این پروژه شده است
        echo json_encode(['status' => 'taken']);
    } else {
        // نام کاربری در این پروژه وجود ندارد و آزاد است
        echo json_encode(['status' => 'available']);
    }
    exit;

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
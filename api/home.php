<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['user_id']) || !isset($_SESSION['project_id'])) {
    echo json_encode(["success" => false, "message" => "خطای دسترسی"]);
    exit;
}

require_once "../config.php";
$project_id = (int)$_SESSION['project_id'];

try {
    // ۱. آمار کلی وضعیت تسک‌ها برای نمودار دایره‌ای
    $stmt_status = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM tasks 
        WHERE project_id = ? 
        GROUP BY status
    ");
    $stmt_status->execute([$project_id]);
    $status_rows = $stmt_status->fetchAll(PDO::FETCH_ASSOC);

    $todo_count = 0;
    $doing_count = 0;
    $done_count = 0;

    foreach ($status_rows as $row) {
        if ($row['status'] === 'todo') $todo_count = (int)$row['count'];
        if ($row['status'] === 'doing') $doing_count = (int)$row['count'];
        if ($row['status'] === 'done') $done_count = (int)$row['count'];
    }

    // ۲. تعداد کل اعضا و دریافت لیست اعضا برای نمایش دایره‌ای آواتارها
    $stmt_members = $pdo->prepare("
        SELECT u.full_name, u.username, u.avatar 
        FROM project_members pm
        JOIN users u ON pm.user_id = u.id
        WHERE pm.project_id = ?
    ");
    $stmt_members->execute([$project_id]);
    $members_list = $stmt_members->fetchAll(PDO::FETCH_ASSOC);
    $total_members = count($members_list);

    // ۳. آمار تسک‌های انجام شده توسط هر کاربر برای نمودار ستونی (گسسته)
    $stmt_user_tasks = $pdo->prepare("
        SELECT u.full_name, COUNT(t.id) as total_done
        FROM users u
        JOIN tasks t ON t.assigned_to = u.id
        WHERE t.project_id = ? AND t.status = 'done'
        GROUP BY u.id
    ");
    $stmt_user_tasks->execute([$project_id]);
    $user_tasks_rows = $stmt_user_tasks->fetchAll(PDO::FETCH_ASSOC);

    $chart_user_labels = [];
    $chart_user_data = [];
    foreach ($user_tasks_rows as $row) {
        $chart_user_labels[] = $row['full_name'];
        $chart_user_data[] = (int)$row['total_done'];
    }

    // خروجی نهایی ساختار یافته
    echo json_encode([
        "active_tasks" => $todo_count + $doing_count,
        "members" => $total_members,
        "done_tasks" => $done_count,
        "task_breakdown" => [
            "todo" => $todo_count,
            "doing" => $doing_count,
            "done" => $done_count
        ],
        "members_avatars" => $members_list,
        "users_chart" => [
            "labels" => $chart_user_labels,
            "data" => $chart_user_data
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
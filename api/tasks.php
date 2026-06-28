<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (empty($_SESSION['user_id']) || empty($_SESSION['project_id'])) {
    echo json_encode([]);
    exit;
}

function gregorian_to_jalali($gy, $gm, $gd) {
    $g_days = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $j_days = [0, 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    $gy = (int)$gy; $gm = (int)$gm; $gd = (int)$gd;
    $days = 0;
    for ($i = 1; $i < $gm; $i++) $days += $g_days[$i];
    if ($gm > 2 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0))) $days++;
    $days += $gd;
    $jy = $gy - 621;
    if ($days >= 79) {
        $days -= 79; $jm = 1;
        while ($jm <= 12 && $days >= $j_days[$jm]) { $days -= $j_days[$jm]; $jm++; }
        $jd = $days + 1;
    } else {
        $jy -= 1; $days += 286; $jm = 1;
        while ($jm <= 12 && $days >= $j_days[$jm]) { $days -= $j_days[$jm]; $jm++; }
        $jd = $days + 1;
    }
    return [$jy, $jm, $jd];
}

$project_id = $_SESSION['project_id'];

try {
    require_once "../config.php";
    
    $stmt = $pdo->prepare("
        SELECT t.id, t.title, u.username AS user, t.due_date, t.status, t.priority, t.description
        FROM tasks t
        LEFT JOIN users u ON t.assigned_to = u.id
        WHERE t.project_id = ? 
        ORDER BY t.id DESC
    ");
    $stmt->execute([$project_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tasks as &$task) {
        if (!empty($task['due_date'])) {
            $parts = explode('-', $task['due_date']);
            if (count($parts) === 3) {
                $j_date = gregorian_to_jalali((int)$parts[0], (int)$parts[1], (int)$parts[2]);
                $task['due_date'] = sprintf("%04d/%02d/%02d", $j_date[0], $j_date[1], $j_date[2]);
            }
        }
    }
    unset($task);
    
    echo json_encode($tasks);
} catch (PDOException $e) {
    echo json_encode([]);
}
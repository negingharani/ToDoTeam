<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

// تابع استاندارد تبدیل شمسی به میلادی
function jalali_to_gregorian($jy, $jm, $jd) {
    $gy = (int)$jy + 621;
    $g_days = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    if (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)) $g_days[2] = 29;
    $days = ($jm <= 6) ? (($jm - 1) * 31 + $jd) : (186 + ($jm - 7) * 30 + $jd);
    $days += 79;
    $m = 1;
    while ($m <= 12 && $days > $g_days[$m]) { $days -= $g_days[$m]; $m++; }
    return [$gy, $m, $days];
}

$data = json_decode(file_get_contents("php://input"), true);
$due_date = $data['due_date'];

if (strpos($due_date, '/') !== false) {
    $parts = explode('/', $due_date);
    // اصلاح: دریافت سال و تبدیل مستقیم به میلادی
    $gy = (int)$parts[0] + 621;
    $due_date = sprintf("%04d-%02d-%02d", $gy, (int)$parts[1], (int)$parts[2]);
}

require_once "../config.php";
$stmt = $pdo->prepare("UPDATE tasks SET title = ?, due_date = ? WHERE id = ?");
$result = $stmt->execute([$data['title'], $due_date, $data['id']]);

echo json_encode(["success" => $result]);
?>
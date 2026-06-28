<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'manager') {
    echo json_encode(["success" => false, "message" => "سطح دسترسی غیرمجاز است."]);
    exit;
}

// تابع تبدیل تاریخ شمسی (جلالی) به میلادی (گرگورین)
function jalali_to_gregorian($jy, $jm, $jd) {
    $jy += 1595;
    $days = -355668 + (365 * $jy) + ((int)($jy / 33) * 8) + (int)((($jy % 33) + 3) / 4) + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
    $jy = 400 * (int)($days / 146097);
    $days %= 146097;
    if ($days > 36524) {
        $days--;
        $jy += 100 * (int)($days / 36524);
        $days %= 36524;
        if ($days >= 365) $days++;
    }
    $jy += 4 * (int)($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += (int)(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $gd = $days + 1;
    $sal_g = [0, 31, (($jy % 4 == 0 && $jy % 100 != 0) || $jy % 400 == 0) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    for ($gm = 0; $gm < 13 && $gd > $sal_g[$gm]; $gm++) $gd -= $sal_g[$gm];
    return array($jy, $gm, $gd);
}

$data = json_decode(file_get_contents("php://input"), true);
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$username = trim($data['user'] ?? ''); 
$priority = trim($data['priority'] ?? 'medium');
$dueDate = !empty($data['due_date']) ? $data['due_date'] : null;

// پردازش و تبدیل تاریخ شمسی به میلادی برای دیتابیس
if ($dueDate) {
    // فرض بر این است که تاریخ به صورت YYYY-MM-DD ارسال می‌شود
    $parts = explode('-', $dueDate);
    if (count($parts) === 3) {
        $g_date = jalali_to_gregorian((int)$parts[0], (int)$parts[1], (int)$parts[2]);
        // تبدیل به فرمت استاندارد YYYY-MM-DD میلادی
        $dueDate = sprintf("%04d-%02d-%02d", $g_date[0], $g_date[1], $g_date[2]);
    }
}

$creatorId = $_SESSION['user_id']; 
$projectId = $_SESSION['project_id'] ?? 0;

if (!empty($title) && !empty($username)) {
    try {
        require_once "../config.php";
        
        // پیدا کردن آی‌دی عددی معتبر بر اساس نام کاربری
        $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmtUser->execute([$username]);
        $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        if (!$userRow) {
            echo json_encode(["success" => false, "message" => "کاربری با این نام کاربری در سیستم یافت نشد."]);
            exit;
        }
        
        $userId = (int)$userRow['id'];
        
        // ثبت نهایی تسک کاملاً هماهنگ با تمام فیلدهای دیتابیس شما
        $stmt = $pdo->prepare("
            INSERT INTO tasks (project_id, title, description, assigned_to, created_by, status, priority, due_date) 
            VALUES (?, ?, ?, ?, ?, 'todo', ?, ?)
        ");
        $stmt->execute([$projectId, $title, $description, $userId, $creatorId, $priority, $dueDate]);
        
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "خطای دیتابیس: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "لطفاً تمامی فیلدهای الزامی (عنوان تسک و مسئول انجام) را پر کنید."]);
}
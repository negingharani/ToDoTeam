<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit;
}

$full_name    = trim($_POST['full_name']);
$username     = trim($_POST['username']);
$email        = strtolower(trim($_POST['email'])); 
$password_raw = $_POST['password'];
$project_name = trim($_POST['project_name']);
$type         = $_POST['type'];
$description  = trim($_POST['description']);

// ذخیره موقت مقادیر ورودی در سشن برای جلوگیری از پاک شدن فرم در صورت خطا
$_SESSION['old_inputs'] = [
    'full_name'    => $full_name,
    'username'     => $username,
    'email'        => $_POST['email'], // ایمیل اصلی کاربر بدون strtolower برای نمایش مجدد
    'project_name' => $project_name,
    'type'         => $type,
    'description'  => $description
];

// ۱. ولیدیشن خالی نبودن فیلدها سمت سرور
if (empty($full_name) || empty($username) || empty($email) || empty($password_raw) || empty($project_name)) {
    $_SESSION['error'] = "پر کردن فیلدهای ستاره‌دار الزامی است.";
    header("Location: register.php");
    exit;
}

// ۲. ولیدیشن فرمت ایمیل سمت سرور
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "فرمت ایمیل وارد شده صحیح نیست.";
    header("Location: register.php");
    exit;
}

// ۳. ولیدیشن طول پسورد سمت سرور
if (strlen($password_raw) < 8) {
    $_SESSION['error'] = "رمز عبور باید حداقل ۸ کاراکتر باشد.";
    header("Location: register.php");
    exit;
}

// ۴. بررسی تکراری نبودن نام کاربری سمت سرور (امنیت نهایی)
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "این نام کاربری قبلاً ثبت شده است.";
    header("Location: register.php");
    exit;
}

// ۵. بررسی ایمیل و فرآیند ساخت کاربر و پروژه
$password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $_SESSION['error'] = "این ایمیل قبلاً ثبت شده است. لطفاً از ایمیل دیگری استفاده کنید.";
    header("Location: register.php");
    exit;
} else {
    $stmt = $pdo->prepare("
        INSERT INTO users(full_name, username, email, password, role)
        VALUES (?, ?, ?, ?, 'manager')
    ");
    $stmt->execute([$full_name, $username, $email, $password_hashed]);
    $user_id = $pdo->lastInsertId();
}

// ساخت پروژه جدید
$stmt = $pdo->prepare("
    INSERT INTO projects(name, type, description, owner_id)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$project_name, $type, $description, $user_id]);
$project_id = $pdo->lastInsertId();

// عضویت در پروژه
$stmt = $pdo->prepare("
    INSERT INTO project_members(project_id, user_id, role)
    VALUES (?, ?, 'owner')
");
$stmt->execute([$project_id, $user_id]);

// چون ثبت نام موفق بود، داده‌های قدیمی سشن را پاک می‌کنیم
unset($_SESSION['old_inputs']);

// لاگین و هدایت به پیشخوان
$_SESSION['user_id'] = $user_id;
$_SESSION['role']    = 'manager';
$_SESSION['name']    = $full_name;

header("Location: dashboard.php");
exit;
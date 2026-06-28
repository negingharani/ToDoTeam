<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

$_SESSION['old_email'] = $email;

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "فیلدها نباید خالی باشند";
    header("Location: login.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "فرمت ایمیل وارد شده صحیح نیست";
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error'] = "ایمیل یا رمز عبور اشتباه است";
    header("Location: login.php");
    exit;
}

unset($_SESSION['old_email']);

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
$_SESSION['name']    = $user['full_name'];

header("Location: projects.php");
exit;
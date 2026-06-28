<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

date_default_timezone_set('Asia/Tehran');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: forgot_password.php");
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$_SESSION['old_email'] = $email;

if (empty($email)) {
    $_SESSION['error'] = "فیلد ایمیل نباید خالی باشد";
    header("Location: forgot_password.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "فرمت ایمیل وارد شده صحیح نیست";
    header("Location: forgot_password.php");
    exit;
}

$stmt = $pdo->prepare("SELECT email FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "این ایمیل در سیستم ثبت نشده است";
    header("Location: forgot_password.php");
    exit;
}

$token  = bin2hex(random_bytes(32));
$expire = date("Y-m-d H:i:s", time() + 3600); 

$update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expire = ? WHERE email = ?");
$update->execute([$token, $expire, $email]);

$link_reset = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/reset_password.php?token=$token";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'example@gmail.com';
    $mail->Password   = 'qwgq xwit lekj wbhs'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('example@gmail.com', 'ToDo Team');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'بازیابی رمز عبور ToDo Team';
    $mail->Body = "
        <div style='font-family:Tahoma, sans-serif; direction:rtl; max-width:500px; margin:0 auto; padding:20px; border:1px solid #ddd; border-radius:10px; text-align:center;'>
            <h2 style='color:#764ba2;'>📝 بازیابی رمز عبور ToDo Team</h2>
            <p>با سلام، درخواست بازیابی رمز عبور شما ثبت شد.</p>
            <p>برای تعیین رمز عبور جدید، روی دکمه زیر کلیک کنید:</p>
            <p style='margin: 25px 0;'><a href='$link_reset' style='display:inline-block; padding:12px 25px; background:linear-gradient(135deg, #667eea, #764ba2); color:white; text-decoration:none; border-radius:30px; font-weight:bold;'>تغییر رمز عبور</a></p>
            <p style='margin-top:20px; font-size:12px; color:#999;'>این لینک فقط تا ۱ ساعت آینده معتبر است.</p>
        </div>
    ";

    $mail->send();
    unset($_SESSION['old_email']);
    $_SESSION['success'] = "لینک بازیابی با موفقیت به جیمیل شما ارسال شد.";
    header("Location: login.php");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "خطا در ارسال ایمیل: {$mail->ErrorInfo}";
    header("Location: forgot_password.php");
    exit;
}
?>
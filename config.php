<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$db   = "todoteam";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// کلیدهای ثابت برای انکریپشن (تغییر ندهید تا پیام‌های قبلی خراب نشوند)
define('ENCRYPTION_KEY', 'p@ssW0rd_S3cr3t_K3y_For_ToDo_Team_2026!'); 
define('ENCRYPTION_SALT', 'mY_S@lt_Sh0uld_B3!');

// تابع رمزگذاری پیام (مطابق با نام‌گذاری داخل messages.php)
function encrypt_chat_message($pureText) {
    $ciphering = "AES-256-CBC";
    $options = 0;
    
    $iv_length = openssl_cipher_iv_length($ciphering);
    $encryption_iv = substr(hash('sha256', ENCRYPTION_SALT), 0, $iv_length);
    $encryption_key = hash('sha256', ENCRYPTION_KEY);
    
    return openssl_encrypt($pureText, $ciphering, $encryption_key, $options, $encryption_iv);
}

// تابع رمزگشایی پیام (مطابق با نام‌گذاری داخل messages.php)
function decrypt_chat_message($encryptedText) {
    $ciphering = "AES-256-CBC";
    $options = 0;
    
    $iv_length = openssl_cipher_iv_length($ciphering);
    $decryption_iv = substr(hash('sha256', ENCRYPTION_SALT), 0, $iv_length);
    $decryption_key = hash('sha256', ENCRYPTION_KEY);
    
    return openssl_decrypt($encryptedText, $ciphering, $decryption_key, $options, $decryption_iv);
}
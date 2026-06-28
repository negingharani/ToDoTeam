<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json; charset=UTF-8");
require_once "../config.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['project_id'])) {
    echo json_encode(["success" => false, "message" => "عدم احراز هویت یا پروژه نامشخص"]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$project_id = (int)$_SESSION['project_id'];

// -------------------------------------------------------------
// ۱. بخش دریافت پیام‌ها (GET Request)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' || isset($_GET['fetch'])) {
    $target_receiver = isset($_GET['receiver_id']) && (int)$_GET['receiver_id'] > 0 ? (int)$_GET['receiver_id'] : 0;
    
    try {
        if ($target_receiver > 0) {
            // گفتگوهای خصوصی بین کاربر فعلی و شخص انتخاب شده
            $query = "
                SELECT m.id, m.user_id, m.message, m.is_encrypted, m.created_at, u.full_name, u.avatar 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.project_id = ? 
                  AND ((m.user_id = ? AND m.receiver_id = ?) OR (m.user_id = ? AND m.receiver_id = ?))
                ORDER BY m.id ASC
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$project_id, $user_id, $target_receiver, $target_receiver, $user_id]);
        } else {
            // چت‌روم عمومی پروژه
            $query = "
                SELECT m.id, m.user_id, m.message, m.is_encrypted, m.created_at, u.full_name, u.avatar 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.project_id = ? AND (m.receiver_id IS NULL OR m.receiver_id = 0)
                ORDER BY m.id ASC
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$project_id]);
        }
        
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $processed_messages = [];
        
        foreach ($messages as $msg) {
            $plain_text = $msg['message'];
            
            // باز کردن قفل پیام در صورت وجود لایه امنیتی
            if ((int)$msg['is_encrypted'] === 1 && function_exists('decrypt_chat_message')) {
                $plain_text = decrypt_chat_message($msg['message']);
            }
            
            $processed_messages[] = [
                "id" => $msg['id'],
                "is_me" => ((int)$msg['user_id'] === $user_id),
                "sender_name" => $msg['full_name'],
                "avatar" => !empty($msg['avatar']) ? "image/" . $msg['avatar'] : "image/user.png",
                "message" => $plain_text, // فیلترینگ تگ‌ها قبلاً در زمان ذخیره انجام شده است
                "time" => date("H:i", strtotime($msg['created_at']))
            ];
        }
        
        // دریافت لیست اعضا برای سایدبار
        $stmt_members = $pdo->prepare("
            SELECT u.id, u.full_name, u.username, u.avatar, pm.role
            FROM project_members pm
            JOIN users u ON pm.user_id = u.id
            WHERE pm.project_id = ? AND u.id != ?
            ORDER BY u.full_name ASC
        ");
        $stmt_members->execute([$project_id, $user_id]);
        $project_members = $stmt_members->fetchAll(PDO::FETCH_ASSOC);
        
        // دریافت اطلاعات مالک اصلی پروژه
        $stmt_owner = $pdo->prepare("
            SELECT u.id, u.full_name, u.username, u.avatar, 'manager' as role
            FROM projects p
            JOIN users u ON p.owner_id = u.id
            WHERE p.id = ? AND u.id != ?
        ");
        $stmt_owner->execute([$project_id, $user_id]);
        $owner_data = $stmt_owner->fetch(PDO::FETCH_ASSOC);
        
        $final_sidebar_users = [];
        if ($owner_data) {
            $final_sidebar_users[] = $owner_data;
        }
        foreach ($project_members as $mb) {
            if ($owner_data && $owner_data['id'] == $mb['id']) continue;
            $final_sidebar_users[] = $mb;
        }

        echo json_encode([
            "success" => true,
            "messages" => $processed_messages,
            "sidebar_users" => $final_sidebar_users
        ]);
        exit;
        
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "خطا در بارگذاری: " . $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// ۲. بخش ارسال پیام جدید (POST Request)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $raw_message = isset($input['message']) ? trim($input['message']) : '';
    $receiver_id = isset($input['receiver_id']) && (int)$input['receiver_id'] > 0 ? (int)$input['receiver_id'] : null;
    
    if ($raw_message === '') {
        echo json_encode(["success" => false, "message" => "متن پیام خالی است"]);
        exit;
    }
    
    // امنیت اولیه در برابر تزریق کدهای مخرب HTML / XSS
    $safe_message = htmlspecialchars($raw_message, ENT_QUOTES, 'UTF-8');
    
    $final_message = $safe_message;
    $is_encrypted = 0;
    
    if (function_exists('encrypt_chat_message')) {
        $final_message = encrypt_chat_message($safe_message);
        $is_encrypted = 1;
    }
    
    try {
        $stmt_insert = $pdo->prepare("
            INSERT INTO messages (project_id, user_id, receiver_id, message, is_encrypted, created_at) 
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $success = $stmt_insert->execute([$project_id, $user_id, $receiver_id, $final_message, $is_encrypted]);
        
        echo json_encode(["success" => $success]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "خطا در ارسال: " . $e->getMessage()]);
        exit;
    }
}
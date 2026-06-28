<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['project_id']) && (int)$_GET['project_id'] > 0) {
    $_SESSION['project_id'] = (int)$_GET['project_id'];
}

$current_project_id = $_SESSION['project_id'] ?? 0;

if ($current_project_id <= 0) {
    header("Location: projects.php"); 
    exit;
}

$role = 'member'; 

try {
    $stmt_owner = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND owner_id = ? LIMIT 1");
    $stmt_owner->execute([$current_project_id, $user_id]);
    
    if ($stmt_owner->fetch()) {
        $role = 'manager';
    } else {
        $stmt_member = $pdo->prepare("SELECT role FROM project_members WHERE project_id = ? AND user_id = ? LIMIT 1");
        $stmt_member->execute([$current_project_id, $user_id]);
        $member_data = $stmt_member->fetch(PDO::FETCH_ASSOC);
        
        if ($member_data) {
            $role = $member_data['role'];
        } else {
            die("خطای دسترسی: شما عضو این پروژه نیستید.");
        }
    }

    $_SESSION['role'] = $role;
    $_SESSION['project_id'] = $current_project_id;

} catch (PDOException $e) {
    die("خطا در بررسی سطح دسترسی: " . $e->getMessage());
}

$logged_in_user_avatar = '';
$logged_in_user_name = '';
$logged_in_username = '';
try {
    $stmt_curr_user = $pdo->prepare("SELECT username, full_name, avatar FROM users WHERE id = ? LIMIT 1");
    $stmt_curr_user->execute([$user_id]);
    $curr_user_data = $stmt_curr_user->fetch(PDO::FETCH_ASSOC);
    if ($curr_user_data) {
        $logged_in_user_name = $curr_user_data['full_name'];
        $logged_in_username = $curr_user_data['username'];
        if (!empty($curr_user_data['avatar'])) {
            $logged_in_user_avatar = trim($curr_user_data['avatar']);
        }
    }
} catch (PDOException $e) { }

$default_sidebar_avatar = ($role === 'manager') ? 'image/manager.png' : 'image/user.png';
$final_sidebar_avatar_path = (!empty($logged_in_user_avatar)) ? 'image/' . $logged_in_user_avatar : $default_sidebar_avatar;


$users_list = [];
try {
    $query = "SELECT u.id, u.username, u.full_name FROM project_members pm 
              JOIN users u ON pm.user_id = u.id 
              WHERE pm.project_id = ? 
              ORDER BY u.full_name ASC";
    $stmt_users = $pdo->prepare($query);
    $stmt_users->execute([$current_project_id]);
    $users_list = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ToDo Team | داشبورد</title>
    <link rel="shortcut icon" href="image/fav.png" type="image/x-icon">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-purple: #764ba2;
            --primary-blue: #667eea;
            --gradient-main: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --bg-light: #ffffff;
            --ring-track: #f0f2f5;
            --task-bg: #eceff3;
        }

        .global-loader-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background-color: var(--bg-light);
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            z-index: 99999;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }

        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 50px;
        }

        .logo-wrapper {
            position: relative;
            width: 170px;
            height: 170px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loader-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 9px solid var(--ring-track);
            border-top: 9px solid var(--primary-blue);
            border-right: 9px solid var(--primary-purple);
            border-radius: 50%;
            animation: spin-loader 1.2s linear infinite;
        }

        .logo-mock {
            position: relative;
            width: 95px;
            height: 95px;
            border: 14px solid var(--primary-purple); 
            border-left: transparent; 
            border-radius: 0 50px 50px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            left: 10px;
        }

        .logo-mock::before {
            content: '';
            position: absolute;
            left: -14px;
            top: -14px;
            bottom: -14px;
            width: 14px; 
            background: var(--primary-blue);
            border-radius: 6px;
        }

        .logo-checkmark {
            position: absolute;
            width: 36px;
            height: 18px;
            border-left: 9px solid var(--primary-blue);
            border-bottom: 9px solid var(--primary-blue);
            transform: rotate(-45deg) translate(5px, -8px);
            filter: drop-shadow(0 4px 10px rgba(102, 126, 234, 0.3));
        }

        .todo-loader-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
            width: 280px;
        }

        .todo-item-loader {
            display: flex;
            align-items: center;
            gap: 14px;
            opacity: 0.35;
            transform: translateY(5px);
            transition: all 0.25s ease-in-out;
        }

        .checkbox-mock {
            width: 22px;
            height: 22px;
            border: 2px solid var(--primary-blue); 
            border-radius: 6px;
            position: relative;
            transition: all 0.2s ease;
            flex-shrink: 0;
            background-color: transparent;
        }

        .text-line {
            height: 6px; 
            background: var(--task-bg);
            border-radius: 3px;
            flex-grow: 1;
            position: relative;
            overflow: hidden;
        }

        .text-line::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: var(--gradient-main);
            transition: width 0.3s ease;
        }

        .todo-item-loader.active {
            opacity: 1;
            transform: translateY(0);
        }

        .todo-item-loader.active .checkbox-mock {
            background: var(--gradient-main);
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .todo-item-loader.active .checkbox-mock::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 3px;
            width: 5px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0; 
            transform: rotate(45deg);
        }

        .todo-item-loader.active .text-line::after {
            width: 100%;
        }

        @keyframes spin-loader {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .global-loader-backdrop.fade-out {
            opacity: 0;
            visibility: hidden;
        }

        .task-item {
            background: #fff; padding: 12px; margin-bottom: 10px; border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-right: 4px solid #764ba2;
            position: relative; padding-left: 45px !important;
        }
        .task-item[draggable="true"] { cursor: grab; }
        .task-item.dragging { opacity: 0.5; cursor: grabbing; }

        .task-edit-btn {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            background: #f1f5f9; color: #4a5568; border: none; width: 28px; height: 28px;
            border-radius: 50%; display: flex; justify-content: center; align-items: center;
            cursor: pointer; transition: all 0.2s ease; font-size: 0.85rem; z-index: 10;
        }
        .task-edit-btn:hover {
            background: #764ba2; color: #fff; transform: translateY(-50%) scale(1.1);
        }
        
        .float-btn {
            position: fixed; bottom: 30px; left: 30px; width: 60px; height: 60px;
            background: #764ba2; color: #fff; border-radius: 50%; display: flex;
            justify-content: center; align-items: center; font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(118, 75, 162, 0.3); cursor: pointer; z-index: 999;
            transition: 0.3s; display: none;
        }
        .float-btn:hover { transform: scale(1.1); background: #667eea; }

        .modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: none; justify-content: center;
            align-items: center; z-index: 1000;
        }
        .modal-content {
            background: #fff; padding: 30px; border-radius: 24px; width: 480px;
            max-width: 90%; text-align: right; box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .modal-content h3 { color: #2d3748; margin-bottom: 20px; font-size: 1.5rem; font-weight: 700; }
        .modal-content label { display: block; text-align: right; margin: 12px 0 6px; color: #4a5568; font-weight: 600; font-size: 0.85rem; }
        .modal-content input, .modal-content select, .modal-content textarea {
            width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 14px;
            transition: all 0.25s ease; font-family: inherit; background-color: #f8fafc; color: #334155; box-sizing: border-box;
        }
        .modal-content input:focus, .modal-content select:focus, .modal-content textarea:focus {
            outline: none; background-color: #fff; border-color: #764ba2; box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1);
        }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .btn-cancel { background: #e2e8f0; color: #4a5568; border: none; padding: 10px 20px; border-radius: 30px; cursor: pointer; font-weight: 600; }
        .btn-save { background: #764ba2; color: #fff; border: none; padding: 10px 24px; border-radius: 30px; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .btn-save:hover { background: #667eea; }
        .btn-delete { background: #e53e3e; color: #fff; border: none; padding: 10px 20px; border-radius: 30px; cursor: pointer; font-weight: 600; margin-left: auto; }
        .btn-delete:hover { background: #c53030; }
        
        .modal-alert-error {
            background-color: #fff5f5; color: #e53e3e; border: 1px solid #fed7d7;
            padding: 14px; margin: 15px 0; border-radius: 12px; text-align: right;
            font-size: 13.5px; font-weight: 500; line-height: 1.6; display: none;
        }

        .members-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 20px;
        }
        .member-card {
            background: #fff; border-radius: 16px; padding: 20px; text-align: center; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; 
            transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; position: relative;
        }
        .member-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .member-avatar {
            width: 75px; height: 75px; border-radius: 50%; object-fit: cover; margin: 0 hover 12px; border: 3px solid #764ba2; background: #f7fafc;
        }
        .member-name { font-size: 1.05rem; font-weight: 700; color: #2d3748; margin-bottom: 4px; }
        .member-position { font-size: 0.85rem; color: #718096; font-weight: 500; margin-bottom: 8px; }
        .member-badge-role { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .role-owner { background: #feebc8; color: #c05621; }
        .role-manager { background: #fed7d7; color: #9b2c2c; }
        .role-member { background: #e2e8f0; color: #4a5568; }

        .username-status { font-size: 0.8rem; font-weight: 600; margin-top: 5px; display: none; }
        .suggest-container {
            background: #f7fafc; border: 1px dashed #764ba2; border-radius: 8px;
            padding: 10px 14px; margin-top: 8px; display: none; align-items: center;
            justify-content: space-between; font-size: 0.85rem;
        }
        .suggest-btn {
            background: #764ba2; color: white; border: none; padding: 4px 10px;
            border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-family: inherit;
        }

        .user-sidebar-badge {
            margin-top: auto; padding: 0px 0; display: flex; justify-content: center; align-items: center; border-top: 1px solid #e2e8f0;
        }
        .user-sidebar-badge.active{
            background-color: transparent;
        }
        .user-sidebar-badge img {
            width: 46px; height: 46px; border-radius: 50%; object-fit: cover; border: 2px solid #764ba2; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.08); transition: transform 0.2s; cursor: pointer;
        }
        .user-sidebar-badge img:hover { transform: scale(1.08); }

        .chat-container { display: flex; height: calc(100vh - 100px); background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .chat-sidebar { width: 280px; background: #f8fafc; border-left: 1px solid #e2e8f0; display: flex; flex-direction: column; }
        .chat-sidebar-header { padding: 15px; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #2d3748; }
        .chat-user-list { flex: 1; overflow-y: auto; }
        .chat-user-item { display: flex; align-items: center; padding: 12px 15px; gap: 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: 0.2s; }
        .chat-user-item:hover, .chat-user-item.active { background: #f1f5f9; }
        .chat-user-item img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; }
        .chat-user-info { display: flex; flex-direction: column; flex: 1; }
        .chat-user-name { font-size: 0.9rem; font-weight: 600; color: #334155; text-align: right; }
        .chat-user-role { font-size: 0.75rem; color: #94a3b8; text-align: right; }
        
        .chat-main { flex: 1; display: flex; flex-direction: column; background: #fafafa; }
        .chat-header { padding: 15px 20px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px; font-weight: bold; color: #2d3748; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
        
        .chat-bubble { max-width: 65%; display: flex; flex-direction: column; gap: 4px; }
        .chat-bubble.me { align-self: flex-start; }
        .chat-bubble.other { align-self: flex-end; }
        
        .chat-msg-wrapper { display: flex; gap: 10px; align-items: flex-end; }
        .chat-bubble.me .chat-msg-wrapper { flex-direction: row; }
        .chat-bubble.other .chat-msg-wrapper { flex-direction: row-reverse; }
        
        .chat-bubble img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
        .chat-text-box { padding: 10px 14px; border-radius: 14px; font-size: 0.9rem; line-height: 1.5; word-break: break-word; }
        
        .chat-bubble.me .chat-text-box { background: #764ba2; color: #fff; border-bottom-right-radius: 2px; }
        .chat-bubble.other .chat-text-box { background: #e2e8f0; color: #1e293b; border-bottom-left-radius: 2px; }
        
        .chat-meta { font-size: 0.75rem; color: #94a3b8; display: flex; gap: 6px; }
        .chat-bubble.me .chat-meta { justify-content: flex-start; }
        .chat-bubble.other .chat-meta { justify-content: flex-end; }
        
        .chat-footer { padding: 15px 20px; background: #fff; border-top: 1px solid #e2e8f0; display: flex; gap: 12px; align-items: center; }
        .chat-input { flex: 1; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-family: inherit; font-size: 0.9rem; resize: none; outline: none; height: 22px; max-height: 100px; }
        .chat-input:focus { border-color: #764ba2; }
        .chat-send-btn { background: #764ba2; color: #fff; border: none; width: 45px; height: 45px; border-radius: 12px; cursor: pointer; display: flex; justify-content: center; align-items: center; transition: 0.2s; font-size: 1.1rem; }
        .chat-send-btn:hover { background: #667eea; }
    </style>
<link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
</head>
<body>

<div class="global-loader-backdrop" id="globalPageLoader">
    <div class="loading-container">
        <div class="logo-wrapper">
            <div class="loader-ring"></div>
            <div class="logo-mock">
                <div class="logo-checkmark"></div>
            </div>
        </div>
        <ul class="todo-loader-list">
            <li class="todo-item-loader" id="loaderTask1"><div class="checkbox-mock"></div><div class="text-line"></div></li>
            <li class="todo-item-loader" id="loaderTask2"><div class="checkbox-mock"></div><div class="text-line"></div></li>
            <li class="todo-item-loader" id="loaderTask3"><div class="checkbox-mock"></div><div class="text-line"></div></li>
        </ul>
    </div>
</div>

<script>
    window.userRole = "<?php echo $role; ?>";
    window.currentUsername = "<?php echo htmlspecialchars($logged_in_username); ?>";

    document.addEventListener("DOMContentLoaded", () => {
        const loaderTasks = [
            document.getElementById('loaderTask1'),
            document.getElementById('loaderTask2'),
            document.getElementById('loaderTask3')
        ];
        loaderTasks.forEach((task, index) => {
            if(task) {
                setTimeout(() => {
                    task.classList.add('active');
                }, (index + 1) * 350); 
            }
        });
    });

    window.addEventListener("load", () => {
        const mainLoader = document.getElementById('globalPageLoader');
        if (mainLoader) {
            setTimeout(() => {
                mainLoader.classList.add('fade-out');
            }, 1200);
        }
    });
</script>

<div class="app-container">
    <aside class="sidebar">
        <div class="logo"></div>
        <nav class="nav-menu">
            <div class="nav-item ni active" data-section="home" title="خانه"><i class="fas fa-home"></i></div>
            <div class="nav-item ni" data-section="tasks" title="تسک‌ها"><i class="fas fa-list-check"></i></div>
            <div class="nav-item ni" data-section="members" title="اعضا"><i class="fas fa-users"></i></div>
            <div class="nav-item ni" data-section="messages" title="پیام‌ها"><i class="fas fa-envelope"></i></div>
            <div class="nav-item ni logout-item" id="logoutBtn" title="خروج"><i class="fas fa-sign-out-alt"></i></div>
        </nav>
        
        <div class="nav-item user-sidebar-badge" data-section="settings">
            <img src="<?php echo $final_sidebar_avatar_path; ?>" 
                 alt="Profile" 
                 title="<?php echo htmlspecialchars($logged_in_user_name); ?>"
                 onerror="this.src='<?php echo $default_sidebar_avatar; ?>'">
        </div>
    </aside>

    <main class="main-content" id="mainContent">
        <div class="loading">در حال بارگذاری...</div>
    </main>
</div>

<?php if ($role === 'manager'): ?>
<div class="float-btn" id="openModalBtn" title="افزودن">
    <i class="fas fa-plus"></i>
</div>

<div class="modal" id="taskModal">
    <div class="modal-content">
        <h3><i class="fas fa-plus-circle" style="color: #764ba2;"></i> تعریف تسک جدید</h3>
        <div class="modal-alert-error" id="task-js-error"></div>

        <form id="createTaskForm">
            <label>عنوان تسک *</label>
            <input type="text" id="taskTitle" required>
            
            <label>توضیحات تسک</label>
            <textarea id="taskDescription" rows="3"></textarea>

            <label>مسئول انجام *</label>
            <select id="taskAssignee" required>
                <option value=""> مجری تسک </option>
                <?php foreach ($users_list as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['username']); ?>">
                        <?php echo htmlspecialchars($user['full_name']) . " (" . htmlspecialchars($user['username']) . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>تاریخ پایان</label>
            <input type="text" id="taskDueDate" class="pdate-picker" readonly>

            <label>اولویت تسک *</label>
            <select id="taskPriority" required>
                <option value="medium">متوسط (Medium)</option>
                <option value="low">کم (Low)</option>
                <option value="high">زیاد (High)</option>
            </select>
            
            <div class="modal-actions">
                <button type="button" class="btn-cancel" id="closeModalBtn">انصراف</button>
                <button type="submit" class="btn-save">ثبت تسک</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="editTaskModal">
    <div class="modal-content">
        <h3><i class="fas fa-edit" style="color: #764ba2;"></i> ویرایش تسک</h3>
        <div class="modal-alert-error" id="edit-task-js-error"></div>

        <form id="editTaskForm">
            <input type="hidden" id="editTaskId">
            
            <label>عنوان تسک *</label>
            <input type="text" id="editTaskTitle" required>
            
            <label>توضیحات تسک</label>
            <textarea id="editTaskDescription" rows="3"></textarea>

            <label>مسئول انجام *</label>
            <select id="editTaskAssignee" required>
                <option value=""> مجری تسک </option>
                <?php foreach ($users_list as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['username']); ?>">
                        <?php echo htmlspecialchars($user['full_name']) . " (" . htmlspecialchars($user['username']) . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>تاریخ پایان</label>
            <input type="text" id="editTaskDueDate" class="pdate-picker" readonly>

            <label>اولویت تسک *</label>
            <select id="editTaskPriority" required>
                <option value="medium">متوسط (Medium)</option>
                <option value="low">کم (Low)</option>
                <option value="high">زیاد (High)</option>
            </select>
            
            <div class="modal-actions">
                <button type="button" class="btn-delete" id="deleteTaskBtn">حذف تسک</button>
                <button type="button" class="btn-cancel" id="closeEditTaskModalBtn">انصراف</button>
                <button type="submit" class="btn-save">ذخیره تغییرات</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="memberModal">
    <div class="modal-content">
        <h3><i class="fas fa-user-plus" style="color: #764ba2;"></i> افزودن عضو جدید به این پروژه</h3>
        <div class="modal-alert-error" id="member-js-error"></div>

        <form id="createMemberForm">
            <label>نام کامل عضو *</label>
            <input type="text" id="memberFullName">

            <label>نام کاربری *</label>
            <input type="text" id="memberUsername">
            <div id="memberUsernameStatus" class="username-status"></div>

            <label>ایمیل *</label>
            <input type="email" id="memberEmail" placeholder="مثلاً: example@email.com">

            <label>عنوان شغلی *</label>
            <input type="text" id="memberPosition" placeholder="مثلاً: Front-End Developer">

            <label>رمز عبور (حداقل ۸ کاراکتر) *</label>
            <div style="position: relative; width: 100%;">
                <input type="password" id="memberPassword" placeholder="********" style="padding-left: 45px;">
                <span id="toggleMemberPass" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; user-select: none; font-size: 1.2rem;">🙈</span>
            </div>
            
            <div id="memberPasswordSuggest" class="suggest-container">
                <span>پیشنهاد رمز امن: <strong id="memberSuggestedPass" style="font-family: monospace; letter-spacing: 1px; color:#2d3748;"></strong></span>
                <button type="button" id="memberUseSuggestBtn" class="suggest-btn">استفاده</button>
            </div>

            <label>نقش کارمند *</label>
            <select id="memberRole" required>
                <option value="member">عضو تیم (member)</option>
                <option value="manager">مدیر پروژه (manager)</option>
            </select>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" id="closeMemberModalBtn">انصراف</button>
                <button type="submit" class="btn-save">ثبت و افزودن</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="editMemberModal">
    <div class="modal-content">
        <h3><i class="fas fa-user-edit" style="color: #764ba2;"></i> ویرایش اطلاعات عضو</h3>
        <div class="modal-alert-error" id="edit-member-js-error"></div>
        <form id="editMemberForm">
            <input type="hidden" id="editMemberUsernameHidden">
            <label>نام کامل</label>
            <input type="text" id="editMemberFullName" readonly style="background-color: #e2e8f0; cursor: not-allowed;">
            
            <label>عنوان شغلی / پوزیشن *</label>
            <input type="text" id="editMemberPosition" placeholder="مثلاً: UI/UX Designer">

            <label>نقش پروژه *</label>
            <select id="editMemberRole">
                <option value="member">عضو تیم (member)</option>
                <option value="manager">مدیر پروژه (manager)</option>
            </select>

            <div class="modal-actions">
                <button type="button" class="btn-delete" id="deleteMemberBtn">حذف کاربر</button>
                <button type="button" class="btn-cancel" id="closeEditMemberModalBtn">انصراف</button>
                <button type="submit" class="btn-save">ذخیره تغییرات</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="js/dashboard.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
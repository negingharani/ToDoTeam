<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php'; 

date_default_timezone_set('Asia/Tehran');

$error = '';
$valid_token = false;
$token = '';

if (isset($_GET['token']) && !empty(trim($_GET['token']))) {
    $token = trim($_GET['token']);
    
    $stmt = $pdo->prepare("SELECT email FROM users WHERE reset_token = ? AND reset_expire > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $valid_token = true;
    } else {
        $error = "این لینک بازیابی نامعتبر است یا زمان ۱ ساعته آن تمام شده است";
    }
} else {
    $error = "دسترسی غیرمجاز! توکنی پیدا نشد";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && $valid_token) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($password) || empty($confirm_password)) {
        $error = "لطفاً تمامی فیلدها را پر کنید";
    } elseif (strlen($password) < 4) { 
        $error = "رمز عبور باید حداقل ۴ کاراکتر باشد";
    } elseif ($password !== $confirm_password) {
        $error = "رمز عبور و تکرار آن با هم برابر نیستند";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expire = NULL WHERE reset_token = ?");
        
        if ($update->execute([$hashed_password, $token])) {
            $_SESSION['success'] = "رمز عبور با موفقیت تغییر کرد! اکنون می‌توانید وارد شوید.";
            header("Location: login.php");
            exit;
        } else {
            $error = "مشکلی در ذخیره رمز جدید رخ داد";
        }
    }
}
?>
<!doctype html>
<html lang="fa">
<head>
<meta charset="utf-8">
<title>ToDo Team | تعیین رمز عبور جدید</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/login_register.css">
<link rel="shortcut icon" href="image/fav.png" type="image/x-icon">
</head>
<body>

<div class="box">
    <h1>تعیین رمز جدید</h1>
    <hr>
    
    <div style="color: #4a5568; font-size: 0.85rem; margin-bottom: 25px; text-align: center;">
        لطفاً رمز عبور جدید خود را وارد و تایید کنید.
    </div>

    <?php if($error): ?>
        <div class="alert alert-error" id="session-error">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-error" id="js-error" style="display: none;"></div>

    <?php if($valid_token): ?>
        <form method="post" action="" id="resetForm">
            
            <p>🔒 رمز عبور جدید</p>
            <div class="password-container">
                <input type="password" name="password" id="password" class="every" placeholder="********">
                <span id="togglePass" style="cursor: pointer;">🙈</span>
            </div>

            <p>🔒 تکرار رمز عبور جدید</p>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" class="every" placeholder="********">
                <span id="togglePassConfirm" style="cursor: pointer; position: absolute; left: 16px; top: 50%; transform: translateY(-50%); opacity: 0.6; font-size: 1.2rem;">🙈</span>
            </div>

            <div style="margin-top: 25px;">
                <button type="submit" class="btn">ثبت رمز عبور و ورود</button>
            </div>
        </form>
    <?php endif; ?>

    <hr>
    <a href="login.php" class="linked" style="font-weight: bold;">← بازگشت به صفحه ورود</a>
</div>

<script>
const passInput = document.getElementById("password");
const confirmInput = document.getElementById("confirm_password");
const jsError = document.getElementById("js-error");
const sessionError = document.getElementById("session-error");
const resetForm = document.getElementById("resetForm");

function hideErrors() {
    jsError.style.display = "none";
    if (sessionError) {
        sessionError.style.display = "none";
    }
}

if(passInput) {
    passInput.focus();

    passInput.addEventListener("input", hideErrors);
    confirmInput.addEventListener("input", hideErrors);

    resetForm.addEventListener("submit", (e) => {
        let passValue = passInput.value.trim();
        let confirmValue = confirmInput.value.trim();

        if (passValue === "" || confirmValue === "") {
            e.preventDefault();
            jsError.textContent = "فیلدها نباید خالی باشند";
            jsError.style.display = "block";
            return;
        }

        if (passValue.length < 4) {
            e.preventDefault();
            jsError.textContent = "رمز عبور باید حداقل ۴ کاراکتر باشد";
            jsError.style.display = "block";
            return;
        }

        if (passValue !== confirmValue) {
            e.preventDefault();
            jsError.textContent = "رمز عبور و تکرار آن با هم برابر نیستند";
            jsError.style.display = "block";
            return;
        }
    });

    const toggle = document.getElementById("togglePass");
    toggle.addEventListener("click", () => {
        if (passInput.type === "password") {
            passInput.type = "text";
            toggle.textContent = "🙉";
        } else {
            passInput.type = "password";
            toggle.textContent = "🙈";
        }
    });

    const toggleConfirm = document.getElementById("togglePassConfirm");
    toggleConfirm.addEventListener("click", () => {
        if (confirmInput.type === "password") {
            confirmInput.type = "text";
            toggleConfirm.textContent = "🙉";
        } else {
            confirmInput.type = "password";
            toggleConfirm.textContent = "🙈";
        }
    });
}
</script>

</body>
</html>
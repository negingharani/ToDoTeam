<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$old_email = isset($_SESSION['old_email']) ? $_SESSION['old_email'] : '';
unset($_SESSION['old_email']); 
?>
<!doctype html>
<html lang="fa">
<head>
<meta charset="utf-8">
<title>ToDo Team | بازیابی رمز عبور</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/login_register.css">
<link rel="shortcut icon" href="image/fav.png" type="image/x-icon">
</head>
<body>

<div class="box">
    <h1>بازیابی رمز عبور</h1>
    <hr>
    
    <div style="color: #4a5568; font-size: 0.85rem; margin-bottom: 25px; text-align: center;">
        ایمیل خود را وارد کنید تا لینک بازیابی برای شما ارسال شود.
    </div>

    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-error" id="session-error">
            <?php 
                echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); 
                unset($_SESSION['error']); 
            ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-error" id="js-error" style="display: none;"></div>

    <form method="post" action="forgot_password_process.php" id="forgotForm">
        <p>📧 ایمیل</p>
        <input type="text" name="email" id="email" class="every" placeholder="example@email.com" value="<?= htmlspecialchars($old_email, ENT_QUOTES, 'UTF-8') ?>">
        
        <div style="margin-top: 25px;">
            <button type="submit" class="btn">ارسال لینک بازیابی</button>
        </div>
    </form>

    <hr>
    <a href="login.php" class="linked" style="font-weight: bold;">← بازگشت به صفحه ورود</a>
</div>

<script>
const emailInput = document.getElementById("email");
const jsError = document.getElementById("js-error");
const sessionError = document.getElementById("session-error");
const forgotForm = document.getElementById("forgotForm");
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function hideErrors() {
    jsError.style.display = "none";
    if (sessionError) {
        sessionError.style.display = "none";
    }
}

emailInput.addEventListener("input", () => {
    let emailValue = emailInput.value.trim();
    if (emailValue !== "" && emailRegex.test(emailValue)) {
        hideErrors();
    }
});

forgotForm.addEventListener("submit", (e) => {
    let emailValue = emailInput.value.trim();
    
    if (emailValue === "") {
        e.preventDefault();
        jsError.textContent = "فیلد ایمیل نباید خالی باشد";
        jsError.style.display = "block";
        emailInput.focus();
        return;
    }
    
    if (!emailRegex.test(emailValue)) {
        e.preventDefault();
        jsError.textContent = "فرمت ایمیل وارد شده صحیح نیست";
        jsError.style.display = "block";
        emailInput.focus();
        return;
    }
});

emailInput.focus();
</script>

</body>
</html>
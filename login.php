<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$old_email = isset($_SESSION['old_email']) ? $_SESSION['old_email'] : '';
unset($_SESSION['old_email']); 
?>

<!doctype html>
<html lang="fa">
<head>
<meta charset="utf-8">
<title>Todo Team | ورود</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/login_register.css">
</head>
<body>

<div class="box">

    <h1>ورود</h1>
    <hr>

    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-error" id="session-error">
            <?php 
                echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); 
                unset($_SESSION['error']); 
            ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-error" id="js-error" style="display: none;"></div>

    <form action="login_process.php" method="post" name="myform" id="loginForm">

        <p>📧 ایمیل</p>
        <input type="text" name="email" id="email" class="every" placeholder="example@email.com" value="<?= htmlspecialchars($old_email, ENT_QUOTES, 'UTF-8') ?>">

        <p>🔒 رمز عبور</p>
        <div class="password-container">
            <input type="password" name="password" id="password" class="every" placeholder="********">
            <span id="togglePass" style="cursor: pointer;">🙈</span>
        </div>

        <input class="btn" type="submit" value="ورود">

        <hr>

        <a class="linked" href="#">فراموشی رمز</a>
    </form>

</div>

<script>
const emailInput = document.getElementById("email");
const passInput = document.getElementById("password");
const jsError = document.getElementById("js-error");
const sessionError = document.getElementById("session-error");
const loginForm = document.getElementById("loginForm");

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


passInput.addEventListener("input", () => {
    let passValue = passInput.value.trim();
    
    if (passValue !== "") {
        hideErrors();
    }
});


loginForm.addEventListener("submit", (e) => {
    let emailValue = emailInput.value.trim();
    let passValue = passInput.value.trim();
    

    if (emailValue === "" || passValue === "") {
        e.preventDefault(); 
        jsError.textContent = "فیلدها نباید خالی باشند";
        jsError.style.display = "block";
        
        if (emailValue === "") emailInput.focus();
        else passInput.focus();
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

emailInput.focus();
</script>

</body>
</html>
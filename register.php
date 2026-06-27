<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// دریافت داده‌های قدیمی در صورت وجود خطا
$old = $_SESSION['old_inputs'] ?? [];
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>Todo Team | ساخت پروژه</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/login_register.css">

    <style>
        body { padding: 40px 20px; }
        .box { width: 600px; max-width: 95%; text-align: right; }
        .header { text-align: center; margin-bottom: 25px; }
        .project-icon {
            width: 75px; height: 75px; border-radius: 20px;
            background: rgba(118, 75, 162, 0.1); color: #764ba2;
            display: flex; justify-content: center; align-items: center;
            font-size: 2rem; margin: 0 auto 15px;
        }
        .header h1 { margin-bottom: 8px; color: #2d3748; }
        .section-title {
            color: #764ba2; font-weight: 700; font-size: 1rem; margin: 25px 0 15px;
            padding-bottom: 8px; border-bottom: 2px solid rgba(118, 75, 162, 0.2);
        }
        .row { display: flex; gap: 15px; }
        .row .input-group { flex: 1; }
        textarea { min-height: 120px; font-family: inherit; resize: vertical; }
        select {
            background-color: #f8fafc; cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: left 16px center; background-size: 16px;
        }
        select:focus { background-color: #fff; }
        .footer-note { margin-top: 20px; text-align: center; color: #718096; font-size: .85rem; }

        /* استایل بازخورد نام کاربری */
        .username-status { font-size: 0.8rem; font-weight: 600; margin-top: 5px; display: none; }
        .status-available { color: #38a169; }
        .status-taken { color: #e53e3e; }

        /* باکس پیشنهاد پسورد */
        .suggest-container {
            background: #f7fafc; border: 1px dashed #764ba2; border-radius: 8px;
            padding: 10px 14px; margin-top: 8px; display: none; align-items: center;
            justify-content: space-between; font-size: 0.85rem;
        }
        .suggest-btn {
            background: #764ba2; color: white; border: none; padding: 4px 10px;
            border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-family: inherit;
        }
        .suggest-btn:hover { background: #667eea; }

        @media(max-width: 768px) {
            .row { flex-direction: column; gap: 0; }
            .box { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<div class="box">
    <div class="header">
        <div class="project-icon"><i class="fas fa-folder-plus"></i></div>
        <h1>ساخت پروژه جدید</h1>
    </div>
    <hr>

    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-error" id="session-error">
            <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <div class="alert alert-error" id="js-error" style="display: none;"></div>

    <form action="register_process.php" method="POST" id="registerForm">
        <div class="section-title">
            <i class="fas fa-user-shield" style="margin-left: 5px;"></i> اطلاعات مالک پروژه
        </div>

        <div class="input-group">
            <p>نام کامل *</p>
            <input type="text" name="full_name" id="fullName" class="every" placeholder="مثال: نگین احمدی" value="<?= htmlspecialchars($old['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="row">
            <div class="input-group">
                <p>نام کاربری *</p>
                <input type="text" name="username" id="username" class="every" placeholder="مثال: negin_dev" value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <div id="usernameStatus" class="username-status"></div>
            </div>

            <div class="input-group">
                <p>ایمیل *</p>
                <input type="text" name="email" id="email" class="every" placeholder="example@email.com" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>

        <div class="input-group">
            <p>رمز عبور *</p>
            <div class="password-container" style="position: relative; width: 100%;">
                <input type="password" name="password" id="password" class="every" placeholder="حداقل ۸ کاراکتر" style="width: 100%; padding-left: 45px;">
                <span id="togglePass" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; user-select: none; font-size: 1.2rem;">🙈</span>
            </div>
            
            <div id="passwordSuggest" class="suggest-container">
                <span>پیشنهاد رمز امن: <strong id="suggestedPass" style="font-family: monospace; letter-spacing: 1px; color:#2d3748;"></strong></span>
                <button type="button" id="useSuggestBtn" class="suggest-btn">استفاده از این رمز</button>
            </div>
        </div>

        <div class="section-title">
            <i class="fas fa-project-diagram" style="margin-left: 5px;"></i> اطلاعات پروژه
        </div>

        <div class="input-group">
            <p>نام پروژه *</p>
            <input type="text" name="project_name" id="projectName" class="every" placeholder="مثال: فروشگاه اینترنتی پوشاک" value="<?= htmlspecialchars($old['project_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="input-group">
            <p>نوع پروژه</p>
            <select name="type" class="every">
                <option value="personal" <?= (isset($old['type']) && $old['type'] === 'personal') ? 'selected' : '' ?>>شخصی</option>
                <option value="org" <?= (isset($old['type']) && $old['type'] === 'org') ? 'selected' : '' ?>>سازمانی</option>
            </select>
        </div>

        <div class="input-group">
            <p>توضیحات پروژه</p>
            <textarea name="description" class="every" placeholder="هدف، توضیحات و جزئیات پروژه را وارد کنید..."><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="div-btn" style="margin-top: 25px;">
            <button class="btn" type="submit">ایجاد پروژه و ثبت‌نام</button>
        </div>
    </form>

    <div class="footer-note">
        پس از ایجاد پروژه، شما به عنوان <strong style="color: #764ba2;">Owner</strong> (مالک) شناخته خواهید شد.
    </div>
</div>

<script>
const registerForm = document.getElementById("registerForm");
const fullNameInput = document.getElementById("fullName");
const usernameInput = document.getElementById("username");
const emailInput = document.getElementById("email");
const passInput = document.getElementById("password");
const projectNameInput = document.getElementById("projectName");

const jsError = document.getElementById("js-error");
const sessionError = document.getElementById("session-error");
const usernameStatus = document.getElementById("usernameStatus");

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
let isUsernameValid = usernameInput.value.trim() !== ""; 

function hideErrors() {
    jsError.style.display = "none";
    if (sessionError) sessionError.style.display = "none";
}

let typingTimer;
usernameInput.addEventListener("input", () => {
    hideErrors();
    clearTimeout(typingTimer);
    let usernameValue = usernameInput.value.trim();

    if(usernameValue === "") {
        usernameStatus.style.display = "none";
        isUsernameValid = false;
        return;
    }

    typingTimer = setTimeout(() => {
        fetch(`check_username.php?username=${encodeURIComponent(usernameValue)}`)
            .then(res => res.json())
            .then(data => {
                usernameStatus.style.display = "block";
                if(data.status === 'available') {
                    usernameStatus.className = "username-status status-available";
                    usernameStatus.textContent = "✓ این نام کاربری آزاد است";
                    isUsernameValid = true;
                } else if(data.status === 'taken') {
                    usernameStatus.className = "username-status status-taken";
                    usernameStatus.textContent = "✕ این نام کاربری قبلاً انتخاب شده است";
                    isUsernameValid = false;
                }
            }).catch(() => { isUsernameValid = true; }); 
    }, 400); 
});

const passwordSuggest = document.getElementById("passwordSuggest");
const suggestedPassStr = document.getElementById("suggestedPass");
const useSuggestBtn = document.getElementById("useSuggestBtn");
const toggle = document.getElementById("togglePass");

function generateSecurePassword() {
    const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
    let password = "";
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return password;
}

passInput.addEventListener("focus", () => {
    if(passInput.value.length < 8) {
        const randomPass = generateSecurePassword();
        suggestedPassStr.textContent = randomPass;
        passwordSuggest.style.display = "flex";
    }
});

useSuggestBtn.addEventListener("click", () => {
    passInput.type = "text"; 
    passInput.value = suggestedPassStr.textContent;
    toggle.textContent = "🙉"; // باز شدن چشم میمون هنگام انتخاب رمز پیشنهادی
    passwordSuggest.style.display = "none";
    hideErrors();
});

passInput.addEventListener("input", () => {
    hideErrors();
    if(passInput.value.length >= 8) {
        passwordSuggest.style.display = "none";
    }
});

// منطق دکمه میمون برای نمایش/مخفی کردن رمز
toggle.addEventListener("click", () => {
    if (passInput.type === "password") {
        passInput.type = "text";
        toggle.textContent = "🙉";
    } else {
        passInput.type = "password";
        toggle.textContent = "🙈";
    }
});

fullNameInput.addEventListener("input", hideErrors);
emailInput.addEventListener("input", hideErrors);
projectNameInput.addEventListener("input", hideErrors);

registerForm.addEventListener("submit", (e) => {
    let fName = fullNameInput.value.trim();
    let uName = usernameInput.value.trim();
    let emailValue = emailInput.value.trim();
    let passValue = passInput.value.trim();
    let pName = projectNameInput.value.trim();

    if (fName === "" || uName === "" || emailValue === "" || passValue === "" || pName === "") {
        e.preventDefault();
        jsError.textContent = "لطفاً تمام فیلدهای ستاره‌دار (*) را پر کنید.";
        jsError.style.display = "block";
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    if (!emailRegex.test(emailValue)) {
        e.preventDefault();
        jsError.textContent = "فرمت ایمیل وارد شده صحیح نیست.";
        jsError.style.display = "block";
        emailInput.focus();
        return;
    }

    if (passValue.length < 8) {
        e.preventDefault();
        jsError.textContent = "رمز عبور نباید کمتر از ۸ کاراکتر باشد.";
        jsError.style.display = "block";
        passInput.focus();
        return;
    }

    if (!isUsernameValid) {
        e.preventDefault();
        jsError.textContent = "لطفاً یک نام کاربری دیگر انتخاب کنید. این نام قبلاً گرفته شده است.";
        jsError.style.display = "block";
        usernameInput.focus();
        return;
    }
});
</script>
</body>
</html>
<?php
// پاک کردن ورودی‌های قدیمی سشن پس از لود شدن کامل صفحه تا فرم در رفرش‌های بعدی خالی شود
unset($_SESSION['old_inputs']);
?>
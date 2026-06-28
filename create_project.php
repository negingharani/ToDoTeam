<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// امنیت: اگر کاربر لاگین نبود باید اول لاگین کند
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$old = $_SESSION['old_inputs'] ?? [];
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>Todo Team | ساخت پروژه جدید</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/login_register.css">
    <style>
        body { padding: 40px 20px; }
        .box { width: 550px; max-width: 95%; text-align: right; }
        .header { text-align: center; margin-bottom: 25px; }
        .project-icon {
            width: 75px; height: 75px; border-radius: 20px;
            background: rgba(118, 75, 162, 0.1); color: #764ba2;
            display: flex; justify-content: center; align-items: center;
            font-size: 2rem; margin: 0 auto 15px;
        }
        .header h1 { margin-bottom: 8px; color: #2d3748; }
        .section-title {
            color: #764ba2; font-weight: 700; font-size: 1rem; margin: 20px 0 15px;
            padding-bottom: 8px; border-bottom: 2px solid rgba(118, 75, 162, 0.2);
        }
        textarea { min-height: 120px; font-family: inherit; resize: vertical; }
        select {
            background-color: #f8fafc; cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: left 16px center; background-size: 16px;
        }
        .footer-note { margin-top: 20px; text-align: center; color: #718096; font-size: .85rem; }
    </style>
</head>
<body>

<div class="box">
    <div class="header">
        <div class="project-icon"><i class="fas fa-folder-plus"></i></div>
        <h1>ساخت پروژه جدید</h1>
        <p style="font-size:0.9rem; color:#4a5568;">خوش آمدید <strong><?= htmlspecialchars($_SESSION['name']) ?></strong> عزیز</p>
    </div>
    <hr>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" id="session-error">
            <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <div class="alert alert-error" id="js-error" style="display: none;"></div>

    <form action="create_project_process.php" method="POST" id="projectForm">
        <div class="section-title">
            <i class="fas fa-project-diagram" style="margin-left: 5px;"></i> جزئیات پروژه جدید
        </div>

        <div class="input-group">
            <p>نام پروژه *</p>
            <input type="text" name="project_name" id="projectName" class="every" placeholder="مثال: اپلیکیشن موبایل فروشگاهی" value="<?= htmlspecialchars($old['project_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
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
            <textarea name="description" class="every" placeholder="هدف و جزئیات پروژه خود را وارد کنید..."><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="div-btn" style="margin-top: 25px;">
            <button class="btn" type="submit">ایجاد پروژه و ورود</button>
        </div>
    </form>
</div>

<script>
const projectForm = document.getElementById("projectForm");
const projectNameInput = document.getElementById("projectName");
const jsError = document.getElementById("js-error");

projectForm.addEventListener("submit", (e) => {
    if (projectNameInput.value.trim() === "") {
        e.preventDefault();
        jsError.textContent = "لطفاً نام پروژه را وارد کنید.";
        jsError.style.display = "block";
    }
});
</script>
</body>
</html>
<?php unset($_SESSION['old_inputs']); ?>
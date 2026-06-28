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

$stmt = $pdo->prepare("
    SELECT * FROM projects
    WHERE owner_id = ?
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>پروژه‌های من</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="css/projects.css">
</head>
<body>

<div class="container">

    <div class="header">
        <h1><i class="far fa-folder"></i> پروژه‌های من</h1>
        <a class="btn-create" href="register.php">
            <i class="fas fa-plus"></i> پروژه جدید
        </a>
    </div>

    <?php if (count($projects) === 0): ?>
        <div class="empty">
            <div class="empty-icon"><i class="far fa-folder-open"></i></div>
            <p>هنوز هیچ پروژه‌ای ثبت نشده است.</p>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($projects as $project): ?>
                <div class="card">
                    <div>
                        <h3><?= htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        
                        <?php if (($project['type'] ?? '') === 'org'): ?>
                            <span class="project-type">
                                <i class="far fa-building"></i> سازمانی
                            </span>
                        <?php else: ?>
                            <span class="project-type">
                                <i class="far fa-user"></i> شخصی
                            </span>
                        <?php endif; ?>

                        <p>
                            <?= nl2br(htmlspecialchars($project['description'] ?? 'توضیحاتی اضافه نشده است.', ENT_QUOTES, 'UTF-8')) ?>
                        </p>
                    </div>

                    <a class="btn-enter" href="dashboard.php?project_id=<?= $project['id'] ?>">
                        ورود به پروژه <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
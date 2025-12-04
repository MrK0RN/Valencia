<?php
$currentUser = Auth::user();
$pageTitle = $pageTitle ?? 'Админ-панель';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Админ-панель</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Session::generateCsrfToken()); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div class="admin-content">
            <header class="admin-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                </div>
                <div class="header-right">
                    <span class="user-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($currentUser->name ?? 'Администратор'); ?>
                    </span>
                    <a href="/admin/logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Выход
                    </a>
                </div>
            </header>
            <main class="admin-main">


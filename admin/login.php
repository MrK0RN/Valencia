<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Session.php';

Session::start();

// Если уже авторизован, перенаправляем
if (Auth::check() && Auth::isAdmin()) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // Проверка CSRF токена
    if (!Session::validateCsrfToken($csrfToken)) {
        $error = 'Ошибка безопасности. Пожалуйста, попробуйте снова.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $user = Auth::attempt($email, $password);
        if ($user && Auth::isAdmin()) {
            header('Location: /admin/index.php');
            exit;
        } else {
            $error = 'Неверный email или пароль, либо недостаточно прав';
        }
    }
}

$csrfToken = Session::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1><i class="fas fa-lock"></i> Вход в админ-панель</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Войти</button>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; font-size: 12px; color: #666;">
                <strong>Демо-доступ:</strong><br>
                Email: <code>admin@demo.com</code><br>
                Password: <code>demo123</code>
            </div>
        </div>
    </div>
</body>
</html>


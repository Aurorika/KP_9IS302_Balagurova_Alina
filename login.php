<?php
/*// 1. Подключаем настройки БД (создан на прошлом занятии 14.01)
try {
    require 'db.php';  // теперь ошибка будет перехвачена
} catch (PDOException $e) {
    $errorMsg = "Ошибка подключения к БД: " . $e->getMessage();
}*/

// 1. Старт сессии — ВСЕГДА на первой строке
session_start();
// Если пользователь уже авторизован, отправляем в профиль
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}


require 'db.php';

$errorMsg = '';

// 2. Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if (empty($email) || empty($pass)) {
        $errorMsg = "Заполните все поля!";
    } else {
        // Поиск пользователя по email
        $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Сверка пароля с хешем
        if ($user && password_verify($pass, $user['password_hash'])) {
            // Успех: сохраняем данные в сессию
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Перенаправление в закрытый раздел
            header("Location: profile.php");
            exit;
        } else {
            $errorMsg = "Неверный логин или пароль.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в систему</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Авторизация</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Войти</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="register.php">Нет аккаунта? Зарегистрироваться</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
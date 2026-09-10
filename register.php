
<?php
// 1. Подключаем настройки БД (создан на прошлом занятии 14.01)
try {
    require '../db.php';;  // теперь ошибка будет перехвачена
} catch (PDOException $e) {
    $errorMsg = "Ошибка подключения к БД: " . $e->getMessage();
}


// 2. Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Очистка данных
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $passConfirm = $_POST['password_confirm'];

    // 3. Валидация (Проверки)
    if (empty($email) || empty($pass)) {
        $errorMsg = "Заполните все поля!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Некорректный формат Email!";
    } elseif ($pass !== $passConfirm) {
        $errorMsg = "Пароли не совпадают!";
    } else {
        // 4. Если ошибок нет — ХЕШИРУЕМ и СОХРАНЯЕМ
        
        // Генерируем безопасный хеш (bcrypt)
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Готовим SQL-запрос (Защита от SQL Injection)
        $sql = "INSERT INTO users (email, password_hash, role) VALUES (:email, :hash, 'client')";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':email' => $email,
                ':hash' => $hash
            ]);
            $successMsg = "Регистрация успешна! <a href='login.php'>Войти</a>";
        } catch (PDOException $e) {
            // Код 23000 означает нарушение уникальности (дубликат email)
            if ($e->getCode() == 23000) {
                $errorMsg = "Такой email уже зарегистрирован.";
            } else {
                $errorMsg = "Ошибка БД: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Регистрация</h4>
                </div>
                <div class="card-body">
                    
                    <!-- Блок вывода сообщений -->
                    <?php if($errorMsg): ?>
                        <div class="alert alert-danger"><?= $errorMsg ?></div>
                    <?php endif; ?>
                    
                    <?php if($successMsg): ?>
                        <div class="alert alert-success"><?= $successMsg ?></div>
                    <?php else: ?>

                    <!-- Сама форма -->
                    <form method="POST" action="register.php">
                        <div class="mb-3">
                            <label class="form-label">Email адрес</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Подтверждение пароля</label>
                            <input type="password" name="password_confirm" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="login.php">Уже есть аккаунт? Войти</a>
                    </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
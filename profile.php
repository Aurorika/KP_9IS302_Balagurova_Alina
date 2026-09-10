<?php
// 1. Старт сессии и проверка авторизации
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Подключение к БД
require 'db.php';

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// 3. Получаем данные текущего пользователя
$stmt = $pdo->prepare("SELECT email, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$currentRole = $currentUser['role'];
$currentEmail = $currentUser['email'];

// 4. Обработка смены роли (только для администратора)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role']) && $currentRole === 'admin') {
    $targetId = (int)$_POST['user_id'];
    $newRole = $_POST['new_role'];
    
    if ($targetId === $userId) {
        $message = "Вы не можете изменить свою собственную роль.";
        $messageType = 'warning';
    } else {
        // Проверяем текущую роль целевого пользователя
        $checkStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $checkStmt->execute([$targetId]);
        $targetUser = $checkStmt->fetch();
        
        if (!$targetUser) {
            $message = "Пользователь не найден.";
            $messageType = 'danger';
        } elseif ($targetUser['role'] === 'admin' && $newRole === 'client') {
            // ЗАПРЕТ: нельзя понизить администратора до клиента
            $message = "Нельзя понизить администратора до клиента.";
            $messageType = 'danger';
        } else {
            // Обновление роли
            $updateStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($updateStmt->execute([$newRole, $targetId])) {
                $message = "Роль пользователя успешно обновлена.";
                $messageType = 'success';
            } else {
                $message = "Ошибка при обновлении роли.";
                $messageType = 'danger';
            }
        }
    }
}

// 5. Если администратор — получаем список всех пользователей
$allUsers = [];
if ($currentRole === 'admin') {
    $allStmt = $pdo->query("SELECT id, email, role FROM users ORDER BY id");
    $allUsers = $allStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Карточка профиля -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">👤 Мой профиль</h4>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
                </div>
                <div class="card-body">
                    <p><strong>Email:</strong> <?= htmlspecialchars($currentEmail) ?></p>
                    <p><strong>Роль:</strong> 
                        <span class="badge <?= $currentRole === 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                            <?= htmlspecialchars($currentRole) ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Блок сообщений -->
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Если администратор — панель управления пользователями -->
            <?php if ($currentRole === 'admin' && !empty($allUsers)): ?>
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">👥 Управление пользователями</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Текущая роль</th>
                                        <th>Изменить роль</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allUsers as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                                                    <?= htmlspecialchars($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['role'] === 'admin'): ?>
                                                    <!-- Для администраторов форма смены скрыта -->
                                                    <span class="text-muted">Нельзя изменить</span>
                                                <?php else: ?>
                                                    <form method="POST" class="row g-2">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <div class="col-auto">
                                                            <select name="new_role" class="form-select form-select-sm">
                                                                <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>Клиент</option>
                                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-auto">
                                                            <button type="submit" name="change_role" class="btn btn-sm btn-primary"
                                                                    <?= $user['id'] == $userId ? 'disabled' : '' ?>>
                                                                Обновить
                                                            </button>
                                                            <?php if ($user['id'] == $userId): ?>
                                                                <small class="text-muted">(себя нельзя)</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
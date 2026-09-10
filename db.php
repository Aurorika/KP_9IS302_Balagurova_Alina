<?php
/*
 * Файл конфигурации базы данных
 * Используется паттерн PDO (PHP Data Objects)
 */

// 1. НАСТРОЙКИ (Измените на свои данные из Beget!)
$host = 'localhost'; // На Beget хост ВСЕГДА localhost
$db   = 'v95292zf_km'; // Имя базы данных (из панели MySQL)
$user = 'v95292zf_km'; // Имя пользователя (часто совпадает с именем БД)
$pass = 'HXUaY7MNiP1G'; // Пароль, который вы задали при создании БД
$charset = 'utf8mb4'; // Кодировка (поддерживает эмодзи и все языки)

// 2. DSN (Data Source Name) - строка подключения
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// 3. ОПЦИИ PDO (Критически важны для удобства и безопасности)
$options = [
    // Выбрасывать исключения при ошибках (чтобы видеть Fatal Error, а не белый экран)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // По умолчанию получать данные как ассоциативный массив ( ['email' => '...'] )
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // Отключить эмуляцию подготовленных выражений (Повышает безопасность против SQLi)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 4. ПОПЫТКА ПОДКЛЮЧЕНИЯ
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Если мы здесь — значит подключение прошло успешно.
    // Переменная $pdo теперь содержит объект подключения.
    
} catch (\PDOException $e) {
    // 5. ОБРАБОТКА ОШИБКИ
    // В реальном проекте мы бы писали ошибку в лог-файл
    // Для обучения выводим ошибку на экран:
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
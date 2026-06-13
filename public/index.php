<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config;
use App\Database;

// Load environment variables from .env (if present)
Config::load();

// ── Connect to the database and fetch demo data ──────────────────────────────
$errorMessage = null;
$users        = [];

try {
    $pdo   = Database::getConnection();
    $stmt  = $pdo->query('SELECT id, name, email, created_at FROM users ORDER BY id DESC LIMIT 10');
    $users = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

$appName = htmlspecialchars($_ENV['APP_NAME'] ?? 'Hostland Demo', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $appName ?></title>
    <style>
        body  { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th    { background: #f4f4f4; }
        .error { color: #c00; background: #fee; padding: 12px; border-radius: 4px; }
        .ok    { color: #060; background: #efe; padding: 12px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1><?= $appName ?></h1>

    <?php if ($errorMessage): ?>
        <p class="error">⚠ Ошибка подключения к БД: <?= $errorMessage ?></p>
    <?php else: ?>
        <p class="ok">✔ Подключение к MySQL установлено успешно.</p>

        <?php if ($users): ?>
            <h2>Последние пользователи</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Имя</th><th>E-mail</th><th>Дата регистрации</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td><?= (int) $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name'],  ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Таблица пользователей пуста. Запустите миграции и добавьте тестовые данные.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>

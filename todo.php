<?php
// TODO лист с записью и чтением из MySQL

if (is_file(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

function configValue(string $key, ?string $default = null): ?string
{
    if (defined($key)) {
        $value = constant($key);
        if (is_scalar($value)) {
            $value = (string) $value;
            if ($value !== '') {
                return $value;
            }
        }
    }

    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    return $default;
}

$host     = configValue('DB_HOST', 'localhost');
$db       = configValue('DB_NAME');
$user     = configValue('DB_USER');
$pass     = configValue('DB_PASS');
$charset  = 'utf8mb4';

if ($db === null || $user === null || $pass === null) {
    throw new RuntimeException("Ошибка конфигурации БД: задайте DB_NAME, DB_USER и DB_PASS в config.php или переменных окружения.");
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Создание таблицы, если не существует
    $pdo->exec("CREATE TABLE IF NOT EXISTS todos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Получение всех задач
    $stmt = $pdo->query("SELECT * FROM todos ORDER BY created_at DESC");
    $todos = $stmt->fetchAll();
    
    // Обработка добавления новой задачи
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task'])) {
        $task = trim($_POST['task']);
        if (!empty($task)) {
            $stmt = $pdo->prepare("INSERT INTO todos (task) VALUES (?)");
            $stmt->execute([$task]);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    
    // Обработка удаления задачи
    if (isset($_GET['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
} catch (RuntimeException $e) {
    die($e->getMessage());
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}
?>
<!DOCTYPE html><html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TODO лист</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        h1 { color: #333; }
        .todo-form { display: flex; gap: 10px; margin-bottom: 30px; }
        input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        .todo-list { list-style: none; padding: 0; }
        .todo-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px; 
            border-bottom: 1px solid #eee; 
            background: #f9f9f9;
        }
        .todo-item:last-child { border-bottom: none; }
        .todo-text { word-break: break-all; }
        .delete-btn { 
            padding: 5px 10px; 
            background: #f44336; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 12px;
        }
        .delete-btn:hover { background: #d32f2f; }
    </style>
</head>
<body>
    <h1> TODO лист</h1>
    
<form method="POST" class="todo-form">
    <input type="text" name="task" placeholder="Введите новую задачу..." required>
    <button type="submit">Добавить</button>
</form>

<ul class="todo-list">
    <?php foreach ($todos as $todo): ?>
        <li class="todo-item">
            <span class="todo-text"><?= htmlspecialchars($todo['task']) ?></span>
            <a href="?delete=<?= $todo['id'] ?>" class="delete-btn" onclick="return confirm('Удалить задачу?');">Удалить</a>
        </li>
    <?php endforeach; ?>
</ul></body>
</html>

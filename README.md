# Hostland-test

Учебный проект: автоматическое развёртывание PHP + MySQL приложения на shared-хостинге [Hostland](https://hostland.ru) с помощью **GitHub Actions** и **SFTP**.

---

## Структура проекта

```
.github/
  workflows/
    deploy.yml          # GitHub Actions: сборка и деплой
public/
  index.php             # Точка входа (фронт-контроллер)
  .htaccess             # Настройки Apache / mod_rewrite
src/
  Config.php            # Загрузка .env без сторонних зависимостей
  Database.php          # Singleton PDO-подключение к MySQL
sql/
  migrations.sql        # Идемпотентные SQL-миграции
composer.json           # PHP-зависимости (PSR-4 автозагрузка)
.env.example            # Шаблон переменных окружения
```

---

## Как работает деплой

При каждом `push` в ветку `main` GitHub Actions выполняет три шага:

1. **Сборка** — устанавливает PHP-зависимости через Composer (`composer install --no-dev`).
2. **SFTP-выгрузка** — синхронизирует файлы проекта с удалённой директорией через `lftp` (зеркалирование с удалением устаревших файлов).
3. **Миграции** — подключается к серверу по SSH и применяет `sql/migrations.sql` к базе данных MySQL.

```
push → main
  │
  ├─ actions/checkout          # Клонирование кода
  ├─ shivammathur/setup-php    # PHP 8.2 + PDO MySQL
  ├─ composer install          # Установка зависимостей
  ├─ lftp mirror (SFTP)        # Загрузка файлов на хостинг
  └─ appleboy/ssh-action       # Запуск SQL-миграций на сервере
```

---

## Настройка

### 1. Скопируйте `.env.example` → `.env`

```bash
cp .env.example .env
```

Заполните `.env` реальными значениями. **Никогда не коммитьте `.env`** — он добавлен в `.gitignore`.

### 2. Добавьте секреты в GitHub

Перейдите в **Settings → Secrets and variables → Actions** вашего репозитория и создайте следующие секреты:

| Секрет | Описание |
|---|---|
| `SFTP_HOST` | Hostname SFTP-сервера (например `ftp.hostland.ru`) |
| `SFTP_PORT` | Порт SFTP (обычно `22`) |
| `SFTP_USER` | Логин SFTP |
| `SFTP_PASS` | Пароль SFTP |
| `SFTP_REMOTE_PATH` | Абсолютный путь на сервере (например `/home/user/public_html`) |
| `SSH_HOST` | Hostname SSH-сервера (часто совпадает с `SFTP_HOST`) |
| `SSH_PORT` | Порт SSH (обычно `22`) |
| `SSH_USER` | Логин SSH |
| `SSH_PASS` | Пароль SSH |
| `DB_HOST` | MySQL хост (обычно `localhost` на shared-хостинге) |
| `DB_PORT` | MySQL порт (обычно `3306`) |
| `DB_NAME` | Имя базы данных |
| `DB_USER` | Пользователь БД |
| `DB_PASS` | Пароль пользователя БД |

### 3. Сделайте первый коммит в `main`

```bash
git add .
git commit -m "feat: initial project with GitHub Actions deploy"
git push origin main
```

GitHub Actions автоматически запустит workflow. Статус выполнения можно отслеживать во вкладке **Actions** репозитория.

---

## Локальный запуск

```bash
# Установить зависимости
composer install

# Создать .env из шаблона и заполнить
cp .env.example .env

# Запустить встроенный веб-сервер PHP
php -S localhost:8080 -t public/
```

Откройте [http://localhost:8080](http://localhost:8080) в браузере.

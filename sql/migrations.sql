-- =============================================================================
-- migrations.sql — initial database schema
--
-- Run once on the target MySQL server.
-- The GitHub Actions workflow executes this file automatically via SSH on
-- every push to main.  All statements use IF NOT EXISTS / IF EXISTS so the
-- script is safe to re-run (idempotent).
-- =============================================================================

-- Create the users table
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed a demo row (ignored on subsequent runs thanks to INSERT IGNORE)
INSERT IGNORE INTO `users` (`name`, `email`) VALUES
    ('Тест Пользователь', 'test@example.com');

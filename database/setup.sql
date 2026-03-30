DROP DATABASE IF EXISTS `login_app`;
CREATE DATABASE `login_app` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `login_app`;

CREATE TABLE `users` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100)    NOT NULL,
    `surname`       VARCHAR(100)    NOT NULL,
    `login_name`    VARCHAR(100)    NOT NULL,
    `phone_number`  VARCHAR(20)     NOT NULL,
    `email_address` VARCHAR(255)    NOT NULL,
    `password`      VARCHAR(255)    NOT NULL,
    `role`          ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_login_name` (`login_name`),
    UNIQUE KEY `uq_email_address` (`email_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_attempts` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED    NULL,
    `login_name`    VARCHAR(100)    NOT NULL,
    `ip_address`    VARCHAR(45)     NOT NULL,
    `user_agent`    VARCHAR(512)    NOT NULL,
    `attempted_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_login_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fixtures
-- Passwords are Argon2id hashes of 'secret123'
INSERT INTO `users` (`name`, `surname`, `login_name`, `phone_number`, `email_address`, `password`, `role`) VALUES
    ('John',  'Smith',   'jsmith',   '+44 7700 900123',   'john.smith@example.com',   '$argon2id$v=19$m=65536,t=4,p=1$YUtxZUVKWnM0OE9CbkVjVw$XCKZswjrpUVnxvXZkw7BS8JmgMHX0mYKBSxvZUkfKPk', 'admin'),
    ('Marie', 'Dupont',  'mdupont',  '+33 6 12 34 56 78', 'marie.dupont@example.com', '$argon2id$v=19$m=65536,t=4,p=1$YUtxZUVKWnM0OE9CbkVjVw$XCKZswjrpUVnxvXZkw7BS8JmgMHX0mYKBSxvZUkfKPk', 'user'),
    ('Karel', 'Novak',   'knovak',   '+420 601 234 567',  'karel.novak@example.com',  '$argon2id$v=19$m=65536,t=4,p=1$YUtxZUVKWnM0OE9CbkVjVw$XCKZswjrpUVnxvXZkw7BS8JmgMHX0mYKBSxvZUkfKPk', 'user');

-- ====================================================
--  AUTH MIGRATION — adds Users table
--  Run this AFTER database.sql
-- ====================================================

USE osi_inspector;

DROP TABLE IF EXISTS Users;

CREATE TABLE Users (
    UserID       INT PRIMARY KEY AUTO_INCREMENT,
    Username     VARCHAR(50)  UNIQUE NOT NULL,
    Email        VARCHAR(100) UNIQUE NOT NULL,
    Password     VARCHAR(255) NOT NULL,
    FullName     VARCHAR(100),
    Role         VARCHAR(20)  DEFAULT 'User',
    LastLogin    TIMESTAMP NULL,
    LastIP       VARCHAR(45),
    CreatedAt    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_username ON Users(Username);
CREATE INDEX idx_user_email    ON Users(Email);

-- Default users are auto-created on first page load by config.php
-- Default credentials:
--   admin / admin123
--   demo  / demo123

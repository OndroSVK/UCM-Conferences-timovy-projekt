-- UCM Conferences: empty MariaDB databases and an application user.
-- Tables are NOT created here; they are created only by the Laravel migrations
-- (php artisan migrate). See docs/prepojenie-databazy.md.
--
-- Run as a MariaDB administrator, e.g.:  mariadb -u root -p < create-database.sql
-- Replace CHANGE_ME with a password of your own first. Never commit a real password.

CREATE DATABASE IF NOT EXISTS ucm_conferences
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Separate database for automated tests: the tests wipe it on every run.
CREATE DATABASE IF NOT EXISTS ucm_conferences_test
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- '%' allows a connection from another container / host; use 'localhost' when the
-- application runs on the same machine as the database.
CREATE USER IF NOT EXISTS 'ucm'@'%' IDENTIFIED BY 'CHANGE_ME';

GRANT ALL PRIVILEGES ON ucm_conferences.* TO 'ucm'@'%';
GRANT ALL PRIVILEGES ON ucm_conferences_test.* TO 'ucm'@'%';

FLUSH PRIVILEGES;

<?php
declare(strict_types=1);

function mis_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $storage = __DIR__ . '/storage';
    if (!is_dir($storage) && !mkdir($storage, 0770, true) && !is_dir($storage)) {
        throw new RuntimeException('無法建立資料儲存目錄。');
    }

    $pdo = new PDO('sqlite:' . $storage . '/mis.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    mis_migrate($pdo);
    return $pdo;
}

function mis_migrate(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        display_name TEXT NOT NULL,
        role TEXT NOT NULL CHECK (role IN (\'employee\', \'admin\')),
        active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS employees (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER UNIQUE,
        employee_no TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        department TEXT NOT NULL DEFAULT \'\',
        title TEXT NOT NULL DEFAULT \'\',
        email TEXT NOT NULL DEFAULT \'\',
        status TEXT NOT NULL DEFAULT \'active\' CHECK (status IN (\'active\', \'inactive\')),
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS feedback (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        reference_no TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        category TEXT NOT NULL,
        message TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT \'new\' CHECK (status IN (\'new\', \'processing\', \'closed\')),
        ip_hash TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action TEXT NOT NULL,
        target_type TEXT NOT NULL DEFAULT \'\',
        target_id TEXT NOT NULL DEFAULT \'\',
        details TEXT NOT NULL DEFAULT \'{}\',
        ip_address TEXT NOT NULL,
        created_at TEXT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )');

    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, display_name, role, active, created_at, updated_at) VALUES (:username, :password_hash, :display_name, :role, 1, :created_at, :updated_at)');
        $seeds = array(
            array('admin', 'admin1234', '系統管理者', 'admin'),
            array('employee', 'employee1234', '測試員工', 'employee'),
        );
        foreach ($seeds as $seed) {
            $stmt->execute(array(':username' => $seed[0], ':password_hash' => password_hash($seed[1], PASSWORD_DEFAULT), ':display_name' => $seed[2], ':role' => $seed[3], ':created_at' => $now, ':updated_at' => $now));
        }
        $employeeUserId = (int) $pdo->query("SELECT id FROM users WHERE username = 'employee'")->fetchColumn();
        $employeeStmt = $pdo->prepare('INSERT INTO employees (user_id, employee_no, name, department, title, email, status, created_at, updated_at) VALUES (:user_id, :employee_no, :name, :department, :title, :email, \'active\', :created_at, :updated_at)');
        $employeeStmt->execute(array(':user_id' => $employeeUserId, ':employee_no' => 'EMP001', ':name' => '測試員工', ':department' => '營運部', ':title' => '專員', ':email' => '', ':created_at' => $now, ':updated_at' => $now));
    }
}

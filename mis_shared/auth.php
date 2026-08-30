<?php
declare(strict_types=1);

function mis_current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function mis_enforce_session_timeout(): void
{
    $user = mis_current_user();
    if (!$user) {
        return;
    }
    $config = mis_load_config();
    $timeout = isset($config['session_timeout']) ? max(300, (int) $config['session_timeout']) : 1800;
    $lastActivity = isset($_SESSION['last_activity']) ? (int) $_SESSION['last_activity'] : time();
    if (time() - $lastActivity > $timeout) {
        mis_logout();
        mis_flash('error', '登入已逾時，請重新登入。');
        return;
    }
    $_SESSION['last_activity'] = time();
}

function mis_attempt_login(string $username, string $password): bool
{
    $stmt = mis_db()->prepare('SELECT id, username, password_hash, display_name, role FROM users WHERE username = :username AND active = 1 LIMIT 1');
    $stmt->execute(array(':username' => $username));
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        mis_record_login_failure();
        mis_audit('login_failed', 'user', $username);
        return false;
    }
    unset($_SESSION['login_failures']);
    session_regenerate_id(true);
    $_SESSION['user'] = array('id' => (int) $user['id'], 'username' => $user['username'], 'display_name' => $user['display_name'], 'role' => $user['role']);
    $_SESSION['last_activity'] = time();
    mis_audit('login_success', 'user', (string) $user['id']);
    return true;
}

function mis_login_rate_limited(): bool
{
    $now = time();
    $failures = isset($_SESSION['login_failures']) && is_array($_SESSION['login_failures']) ? $_SESSION['login_failures'] : array();
    $failures = array_values(array_filter($failures, function ($timestamp) use ($now) {
        return is_int($timestamp) && $timestamp > $now - 300;
    }));
    $_SESSION['login_failures'] = $failures;
    return count($failures) >= 5;
}

function mis_record_login_failure(): void
{
    if (!isset($_SESSION['login_failures']) || !is_array($_SESSION['login_failures'])) {
        $_SESSION['login_failures'] = array();
    }
    $_SESSION['login_failures'][] = time();
    $_SESSION['login_failures'] = array_slice($_SESSION['login_failures'], -5);
}

function mis_logout(): void
{
    unset($_SESSION['user'], $_SESSION['last_activity']);
    session_regenerate_id(true);
}

function mis_require_login(?string $role = null): void
{
    $user = mis_current_user();
    if (!$user) {
        $target = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : mis_base_url('mis_hr/');
        header('Location: ' . mis_base_url('mis_shared/login.php') . '?next=' . rawurlencode($target));
        exit;
    }
    if ($role !== null && $user['role'] !== $role) {
        http_response_code(403);
        mis_render_header('權限不足', 'system');
        echo '<main class="panel narrow"><h1>權限不足</h1><p>你沒有執行此操作的權限。</p></main>';
        mis_render_footer();
        exit;
    }
}

function mis_safe_next($value): string
{
    $default = mis_base_url('mis_hr/');
    if (!is_string($value) || $value === '' || strpos($value, '//') === 0 || strpos($value, mis_base_url()) !== 0) {
        return $default;
    }
    return $value;
}

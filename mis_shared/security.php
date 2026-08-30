<?php
declare(strict_types=1);

function mis_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function mis_base_url(string $path = ''): string
{
    $config = mis_load_config();
    $base = rtrim(isset($config['base_path']) ? (string) $config['base_path'] : '', '/');
    return $base . '/' . ltrim($path, '/');
}

function mis_csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function mis_verify_csrf($token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function mis_post_string(string $key, int $maxLength, bool $required = true): string
{
    $value = isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
    if ($required && $value === '') {
        throw new InvalidArgumentException('請完整填寫必填欄位。');
    }
    if (mb_strlen($value, 'UTF-8') > $maxLength) {
        throw new InvalidArgumentException('輸入內容超過允許長度。');
    }
    return $value;
}

function mis_json_response(bool $success, string $message, int $status = 200, array $extra = array()): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(array('success' => $success, 'message' => $message), $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function mis_client_ip(): string
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function mis_audit(string $action, string $targetType = '', string $targetId = '', array $details = array()): void
{
    try {
        $user = mis_current_user();
        $stmt = mis_db()->prepare('INSERT INTO audit_logs (user_id, action, target_type, target_id, details, ip_address, created_at) VALUES (:user_id, :action, :target_type, :target_id, :details, :ip, :created_at)');
        $stmt->execute(array(
            ':user_id' => $user ? (int) $user['id'] : null,
            ':action' => $action,
            ':target_type' => $targetType,
            ':target_id' => $targetId,
            ':details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':ip' => mis_client_ip(),
            ':created_at' => date('Y-m-d H:i:s'),
        ));
    } catch (Throwable $error) {
        error_log('MIS audit failure: ' . $error->getMessage());
    }
}

function mis_flash(string $type, string $message): void
{
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function mis_take_flash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

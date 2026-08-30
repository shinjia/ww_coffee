<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/mis_shared/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mis_json_response(false, '僅接受 POST 請求。', 405);
}

try {
    if (!mis_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        throw new RuntimeException('頁面已逾時，請重新整理後再送出。');
    }
    $honeypot = mis_post_string('company', 100, false);
    $openedAt = isset($_POST['opened_at']) && is_string($_POST['opened_at']) && ctype_digit($_POST['opened_at']) ? (int) $_POST['opened_at'] : 0;
    if ($honeypot !== '' || $openedAt < 1 || time() - $openedAt < 2 || time() - $openedAt > 86400) {
        throw new InvalidArgumentException('無法確認表單有效性，請重新整理後再試。');
    }

    $lastSubmit = isset($_SESSION['last_feedback_at']) ? (int) $_SESSION['last_feedback_at'] : 0;
    $limit = isset(mis_load_config()['feedback_rate_limit_seconds']) ? (int) mis_load_config()['feedback_rate_limit_seconds'] : 30;
    if ($lastSubmit > 0 && time() - $lastSubmit < max(10, $limit)) {
        throw new InvalidArgumentException('送出過於頻繁，請稍候再試。');
    }

    $name = mis_post_string('name', 60);
    $email = mis_post_string('email', 254);
    $category = mis_post_string('category', 30);
    $message = mis_post_string('message', 2000);
    $consent = isset($_POST['consent']) && $_POST['consent'] === '1';
    $allowedCategories = array('service', 'product', 'environment', 'other');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Email 格式不正確。');
    }
    if (!in_array($category, $allowedCategories, true)) {
        throw new InvalidArgumentException('請選擇有效的意見類別。');
    }
    if (mb_strlen($message, 'UTF-8') < 10) {
        throw new InvalidArgumentException('留言內容請至少輸入 10 個字。');
    }
    if (!$consent) {
        throw new InvalidArgumentException('請先同意聯絡資料使用說明。');
    }

    $now = date('Y-m-d H:i:s');
    $reference = 'FB' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    $ipHash = hash('sha256', mis_client_ip() . '|' . date('Y-m'));
    $stmt = mis_db()->prepare('INSERT INTO feedback (reference_no, name, email, category, message, status, ip_hash, created_at, updated_at) VALUES (:reference_no, :name, :email, :category, :message, \'new\', :ip_hash, :created_at, :updated_at)');
    $stmt->execute(array(':reference_no' => $reference, ':name' => $name, ':email' => $email, ':category' => $category, ':message' => $message, ':ip_hash' => $ipHash, ':created_at' => $now, ':updated_at' => $now));
    $_SESSION['last_feedback_at'] = time();
    mis_audit('feedback_created', 'feedback', $reference, array('category' => $category));
    mis_json_response(true, '意見已送出，謝謝你的回饋。', 201, array('reference' => $reference));
} catch (InvalidArgumentException $exception) {
    mis_json_response(false, $exception->getMessage(), 422);
} catch (RuntimeException $exception) {
    mis_json_response(false, $exception->getMessage(), 400);
} catch (Throwable $exception) {
    error_log('Feedback submission failure: ' . $exception->getMessage());
    mis_json_response(false, '目前無法送出，請稍後再試。', 500);
}

<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !mis_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
    http_response_code(400);
    exit('無效的請求。');
}
if (mis_current_user()) {
    mis_audit('logout', 'user', (string) mis_current_user()['id']);
}
mis_logout();
mis_flash('success', '你已安全登出。');
header('Location: ' . mis_base_url('mis_shared/login.php'));
exit;

<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

if (mis_current_user()) {
    header('Location: ' . mis_safe_next(isset($_GET['next']) ? $_GET['next'] : null));
    exit;
}

$error = '';
$next = mis_safe_next(isset($_GET['next']) ? $_GET['next'] : (isset($_POST['next']) ? $_POST['next'] : null));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!mis_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
            throw new RuntimeException('頁面已逾時，請重新整理後再試。');
        }
        $username = mis_post_string('username', 50);
        $password = mis_post_string('password', 200);
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $username)) {
            throw new InvalidArgumentException('帳號格式不正確。');
        }
        if (mis_login_rate_limited()) {
            throw new RuntimeException('登入失敗次數過多，請於 5 分鐘後再試。');
        }
        if (!mis_attempt_login($username, $password)) {
            usleep(350000);
            throw new InvalidArgumentException('帳號或密碼不正確。');
        }
        header('Location: ' . $next);
        exit;
    } catch (Throwable $exception) {
        $error = $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : '登入失敗，請稍後再試。';
    }
}

mis_render_header('員工登入');
?>
<main class="auth-wrap">
  <section class="panel narrow">
    <p class="eyebrow">STAFF ACCESS</p>
    <h1>員工登入</h1>
    <p class="muted">請使用公司核發的帳號登入內部系統。</p>
    <?php if ($error !== ''): ?><div class="message error" role="alert"><?= mis_e($error) ?></div><?php endif; ?>
    <form method="post" class="stack-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= mis_e(mis_csrf_token()) ?>">
      <input type="hidden" name="next" value="<?= mis_e($next) ?>">
      <label>帳號<input name="username" type="text" maxlength="50" autocomplete="username" required></label>
      <label>密碼<input name="password" type="password" maxlength="200" autocomplete="current-password" required></label>
      <button class="primary-button" type="submit">登入</button>
    </form>
    <?php if (mis_load_config()['environment'] === 'development'): ?>
      <aside class="dev-note"><strong>開發測試帳號</strong><br>管理者：admin / admin1234<br>員工：employee / employee1234</aside>
    <?php endif; ?>
  </section>
</main>
<?php mis_render_footer(); ?>

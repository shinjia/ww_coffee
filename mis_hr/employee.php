<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/mis_shared/bootstrap.php';
mis_require_login('admin');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!mis_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
            throw new RuntimeException('頁面已逾時，請重新整理後再試。');
        }
        $employeeNo = strtoupper(mis_post_string('employee_no', 20));
        $name = mis_post_string('name', 60);
        $department = mis_post_string('department', 60);
        $title = mis_post_string('title', 60);
        $email = mis_post_string('email', 254, false);
        $username = strtolower(mis_post_string('username', 50));
        $role = mis_post_string('role', 20);

        if (!preg_match('/^[A-Z0-9-]+$/', $employeeNo)) {
            throw new InvalidArgumentException('員工編號只能使用英文大寫、數字及連字號。');
        }
        if (!preg_match('/^[a-z0-9._-]+$/', $username)) {
            throw new InvalidArgumentException('帳號只能使用小寫英文、數字、句點、底線及連字號。');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email 格式不正確。');
        }
        if (!in_array($role, array('employee', 'admin'), true)) {
            throw new InvalidArgumentException('角色設定不正確。');
        }

        $pdo = mis_db();
        $pdo->beginTransaction();
        $now = date('Y-m-d H:i:s');
        $password = $username . '1234';
        $userStmt = $pdo->prepare('INSERT INTO users (username, password_hash, display_name, role, active, created_at, updated_at) VALUES (:username, :password_hash, :display_name, :role, 1, :created_at, :updated_at)');
        $userStmt->execute(array(':username' => $username, ':password_hash' => password_hash($password, PASSWORD_DEFAULT), ':display_name' => $name, ':role' => $role, ':created_at' => $now, ':updated_at' => $now));
        $userId = (int) $pdo->lastInsertId();
        $employeeStmt = $pdo->prepare('INSERT INTO employees (user_id, employee_no, name, department, title, email, status, created_at, updated_at) VALUES (:user_id, :employee_no, :name, :department, :title, :email, \'active\', :created_at, :updated_at)');
        $employeeStmt->execute(array(':user_id' => $userId, ':employee_no' => $employeeNo, ':name' => $name, ':department' => $department, ':title' => $title, ':email' => $email, ':created_at' => $now, ':updated_at' => $now));
        $pdo->commit();
        mis_audit('employee_created', 'employee', (string) $pdo->lastInsertId(), array('employee_no' => $employeeNo, 'username' => $username, 'role' => $role));
        mis_flash('success', '員工已新增。開發初始密碼為「' . $username . '1234」。');
        header('Location: ' . mis_base_url('mis_hr/'));
        exit;
    } catch (PDOException $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = strpos($exception->getMessage(), 'UNIQUE') !== false ? '員工編號或帳號已存在。' : '資料儲存失敗，請稍後再試。';
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : '新增失敗，請稍後再試。';
    }
}

mis_render_header('新增員工', 'hr');
?>
<main class="app-main">
  <header class="page-head"><div><p class="eyebrow">HUMAN RESOURCES</p><h1>新增員工</h1><p>建立員工資料及可登入的系統帳號。</p></div><a class="secondary-button" href="<?= mis_e(mis_base_url('mis_hr/')) ?>">返回名冊</a></header>
  <section class="panel">
    <?php if ($error !== ''): ?><div class="message error" role="alert"><?= mis_e($error) ?></div><?php endif; ?>
    <form method="post" class="form-grid" novalidate>
      <input type="hidden" name="csrf_token" value="<?= mis_e(mis_csrf_token()) ?>">
      <label class="field">員工編號<input name="employee_no" maxlength="20" pattern="[A-Za-z0-9-]+" required value="<?= mis_e(isset($_POST['employee_no']) ? $_POST['employee_no'] : '') ?>"></label>
      <label class="field">姓名<input name="name" maxlength="60" required value="<?= mis_e(isset($_POST['name']) ? $_POST['name'] : '') ?>"></label>
      <label class="field">部門<input name="department" maxlength="60" required value="<?= mis_e(isset($_POST['department']) ? $_POST['department'] : '') ?>"></label>
      <label class="field">職稱<input name="title" maxlength="60" required value="<?= mis_e(isset($_POST['title']) ? $_POST['title'] : '') ?>"></label>
      <label class="field">Email（選填）<input name="email" type="email" maxlength="254" value="<?= mis_e(isset($_POST['email']) ? $_POST['email'] : '') ?>"></label>
      <label class="field">登入帳號<input name="username" maxlength="50" pattern="[a-z0-9._-]+" required value="<?= mis_e(isset($_POST['username']) ? $_POST['username'] : '') ?>"><small>開發初始密碼為帳號加 1234。</small></label>
      <label class="field">角色<select name="role"><option value="employee">員工</option><option value="admin">管理者</option></select></label>
      <div class="span-2"><button class="primary-button" type="submit">建立員工與帳號</button></div>
    </form>
  </section>
</main>
<?php mis_render_footer(); ?>

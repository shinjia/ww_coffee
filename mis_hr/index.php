<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/mis_shared/bootstrap.php';
mis_require_login();

$user = mis_current_user();
$employees = mis_db()->query('SELECT e.*, u.username, u.role, u.active FROM employees e LEFT JOIN users u ON u.id = e.user_id ORDER BY e.employee_no')->fetchAll();
$activeCount = 0;
foreach ($employees as $employee) {
    if ($employee['status'] === 'active') {
        $activeCount++;
    }
}

mis_render_header('人事管理', 'hr');
?>
<main class="app-main">
  <header class="page-head">
    <div><p class="eyebrow">HUMAN RESOURCES</p><h1>人事管理</h1><p>員工基本資料與系統帳號管理。</p></div>
    <?php if ($user['role'] === 'admin'): ?><a class="primary-button" href="<?= mis_e(mis_base_url('mis_hr/employee.php')) ?>">新增員工</a><?php endif; ?>
  </header>

  <section class="summary-grid" aria-label="人事資料摘要">
    <div class="summary-card"><span>員工總數</span><strong><?= count($employees) ?></strong></div>
    <div class="summary-card"><span>在職</span><strong><?= $activeCount ?></strong></div>
    <div class="summary-card"><span>停用</span><strong><?= count($employees) - $activeCount ?></strong></div>
  </section>

  <section class="panel">
    <h2>員工名冊</h2>
    <?php if (!$employees): ?>
      <p class="muted">目前沒有員工資料。</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>員工編號</th><th>姓名</th><th>部門／職稱</th><th>聯絡信箱</th><th>帳號／角色</th><th>狀態</th></tr></thead>
          <tbody>
          <?php foreach ($employees as $employee): ?>
            <tr>
              <td><?= mis_e($employee['employee_no']) ?></td>
              <td><?= mis_e($employee['name']) ?></td>
              <td><?= mis_e($employee['department']) ?><br><span class="muted"><?= mis_e($employee['title']) ?></span></td>
              <td><?= $employee['email'] !== '' ? mis_e($employee['email']) : '—' ?></td>
              <td><?= $employee['username'] ? mis_e($employee['username']) . ' / ' . mis_e($employee['role']) : '未建立' ?></td>
              <td><span class="badge <?= mis_e($employee['status']) ?>"><?= $employee['status'] === 'active' ? '在職' : '停用' ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php mis_render_footer(); ?>

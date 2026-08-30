<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/mis_shared/bootstrap.php';
mis_require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!mis_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
            throw new RuntimeException('頁面已逾時，請重新整理後再試。');
        }
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = mis_post_string('status', 20);
        if (!$id || !in_array($status, array('new', 'processing', 'closed'), true)) {
            throw new InvalidArgumentException('留言或狀態資料不正確。');
        }
        $stmt = mis_db()->prepare('UPDATE feedback SET status = :status, updated_at = :updated_at WHERE id = :id');
        $stmt->execute(array(':status' => $status, ':updated_at' => date('Y-m-d H:i:s'), ':id' => $id));
        if ($stmt->rowCount() !== 1) {
            throw new InvalidArgumentException('找不到指定留言。');
        }
        mis_audit('feedback_status_updated', 'feedback', (string) $id, array('status' => $status));
        mis_flash('success', '留言狀態已更新。');
    } catch (Throwable $exception) {
        mis_flash('error', $exception instanceof InvalidArgumentException || $exception instanceof RuntimeException ? $exception->getMessage() : '狀態更新失敗。');
    }
    header('Location: ' . mis_base_url('mis_feedback/admin.php'));
    exit;
}

$feedback = mis_db()->query('SELECT * FROM feedback ORDER BY created_at DESC, id DESC LIMIT 200')->fetchAll();
$counts = array('new' => 0, 'processing' => 0, 'closed' => 0);
foreach ($feedback as $item) {
    if (isset($counts[$item['status']])) {
        $counts[$item['status']]++;
    }
}
$categoryLabels = array('service' => '服務建議', 'product' => '產品／咖啡', 'environment' => '環境設施', 'other' => '其他');
$statusLabels = array('new' => '新留言', 'processing' => '處理中', 'closed' => '已結案');

mis_render_header('客戶意見管理', 'feedback');
?>
<main class="app-main">
  <header class="page-head"><div><p class="eyebrow">CUSTOMER FEEDBACK</p><h1>客戶意見管理</h1><p>檢視外部留言並更新處理狀態，最多顯示最新 200 筆。</p></div><a class="secondary-button" href="<?= mis_e(mis_base_url('mis_feedback/')) ?>" target="_blank" rel="noopener">開啟留言頁</a></header>
  <section class="summary-grid" aria-label="留言摘要">
    <div class="summary-card"><span>新留言</span><strong><?= $counts['new'] ?></strong></div>
    <div class="summary-card"><span>處理中</span><strong><?= $counts['processing'] ?></strong></div>
    <div class="summary-card"><span>已結案</span><strong><?= $counts['closed'] ?></strong></div>
  </section>
  <section class="panel">
    <h2>留言清單</h2>
    <?php if (!$feedback): ?><p class="muted">目前沒有客戶留言。</p><?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>編號／時間</th><th>客戶</th><th>類別／內容</th><th>狀態</th></tr></thead>
      <tbody><?php foreach ($feedback as $item): ?><tr>
        <td><?= mis_e($item['reference_no']) ?><br><span class="muted"><?= mis_e($item['created_at']) ?></span></td>
        <td><?= mis_e($item['name']) ?><br><a href="mailto:<?= mis_e($item['email']) ?>"><?= mis_e($item['email']) ?></a></td>
        <td><span class="badge"><?= mis_e(isset($categoryLabels[$item['category']]) ? $categoryLabels[$item['category']] : $item['category']) ?></span><br><?= nl2br(mis_e($item['message'])) ?></td>
        <td>
          <span class="badge <?= mis_e($item['status']) ?>"><?= mis_e($statusLabels[$item['status']]) ?></span>
          <form method="post" class="inline-form" data-confirm="確定更新這筆留言的狀態？">
            <input type="hidden" name="csrf_token" value="<?= mis_e(mis_csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
            <select name="status" aria-label="更新 <?= mis_e($item['reference_no']) ?> 狀態"><?php foreach ($statusLabels as $value => $label): ?><option value="<?= mis_e($value) ?>"<?= $item['status'] === $value ? ' selected' : '' ?>><?= mis_e($label) ?></option><?php endforeach; ?></select>
            <button class="secondary-button" type="submit">更新</button>
          </form>
        </td>
      </tr><?php endforeach; ?></tbody>
    </table></div>
    <?php endif; ?>
  </section>
</main>
<?php mis_render_footer(); ?>

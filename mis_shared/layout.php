<?php
declare(strict_types=1);

function mis_render_header(string $title, string $section = ''): void
{
    $user = mis_current_user();
    $isPublic = $section === 'public';
    $appName = isset(mis_load_config()['app_name']) ? (string) mis_load_config()['app_name'] : '資訊系統';
    ?><!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title><?= mis_e($title) ?>｜<?= mis_e($appName) ?></title>
  <link rel="stylesheet" href="<?= mis_e(mis_base_url('mis_shared/assets/internal.css?v=2026083001')) ?>">
</head>
<body>
<header class="app-header">
  <a class="app-brand" href="<?= mis_e($isPublic ? mis_base_url('web/') : mis_base_url('mis_hr/')) ?>"><span>WW</span><strong><?= $isPublic ? '木窗咖啡' : '木窗資訊系統' ?></strong></a>
  <nav aria-label="子系統選單">
    <?php if ($isPublic): ?>
      <a href="<?= mis_e(mis_base_url('web/')) ?>">返回品牌官網</a>
      <a href="<?= mis_e(mis_base_url('mis_shared/login.php')) ?>">員工登入</a>
    <?php elseif ($user): ?>
      <a<?= $section === 'hr' ? ' aria-current="page"' : '' ?> href="<?= mis_e(mis_base_url('mis_hr/')) ?>">人事管理</a>
      <a<?= $section === 'feedback' ? ' aria-current="page"' : '' ?> href="<?= mis_e(mis_base_url('mis_feedback/admin.php')) ?>">客戶意見</a>
      <span class="user-label"><?= mis_e($user['display_name']) ?>（<?= mis_e($user['role']) ?>）</span>
      <form action="<?= mis_e(mis_base_url('mis_shared/logout.php')) ?>" method="post"><input type="hidden" name="csrf_token" value="<?= mis_e(mis_csrf_token()) ?>"><button class="link-button" type="submit">登出</button></form>
    <?php else: ?>
      <a href="<?= mis_e(mis_base_url('mis_feedback/')) ?>">客戶留言</a>
      <a href="<?= mis_e(mis_base_url('mis_shared/login.php')) ?>">員工登入</a>
    <?php endif; ?>
  </nav>
</header>
<?php $flash = mis_take_flash(); if ($flash): ?>
  <div class="flash <?= mis_e($flash['type']) ?>" role="status"><?= mis_e($flash['message']) ?></div>
<?php endif; ?>
<?php
}

function mis_render_footer(bool $public = false): void
{
    ?>
<footer class="app-footer">木窗咖啡 WW Coffee<?= $public ? '' : ' · 內部資訊系統' ?></footer>
<script src="<?= mis_e(mis_base_url('mis_shared/assets/internal.js?v=2026083001')) ?>" defer></script>
</body>
</html><?php
}

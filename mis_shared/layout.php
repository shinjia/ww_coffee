<?php
declare(strict_types=1);

function mis_render_header(string $title, string $section = '', array $extraStyles = array()): void
{
    $user = mis_current_user();
    $isPublic = $section === 'public';
    $config = mis_load_config();
    $appName = isset($config['app_name']) ? (string) $config['app_name'] : '資訊系統';
    $isDevelopment = isset($config['environment']) && $config['environment'] === 'development';
    ?><!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title><?= mis_e($title) ?>｜<?= mis_e($appName) ?></title>
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='16' fill='%2334483a'/%3E%3C/svg%3E">
  <link rel="stylesheet" href="<?= mis_e(mis_base_url('mis_shared/assets/internal.css?v=2026083001')) ?>">
  <?php foreach ($extraStyles as $style): ?><link rel="stylesheet" href="<?= mis_e(mis_base_url((string) $style)) ?>"><?php endforeach; ?>
</head>
<body>
<header class="app-header">
  <a class="app-brand" href="<?= mis_e($isPublic ? mis_base_url('web/') : mis_base_url()) ?>"><span>WW</span><strong><?= $isPublic ? '木窗咖啡' : '木窗資訊系統' ?></strong></a>
  <nav aria-label="子系統選單">
    <?php if ($isPublic): ?>
      <a href="<?= mis_e(mis_base_url('web/')) ?>">返回品牌官網</a>
      <a href="<?= mis_e(mis_base_url('mis_shared/login.php')) ?>">員工登入</a>
    <?php elseif ($user): ?>
      <a href="<?= mis_e(mis_base_url()) ?>">系統目錄</a>
      <a<?= $section === 'hr' ? ' aria-current="page"' : '' ?> href="<?= mis_e(mis_base_url('mis_hr/')) ?>">人事管理</a>
      <a<?= $section === 'feedback' ? ' aria-current="page"' : '' ?> href="<?= mis_e(mis_base_url('mis_feedback/admin.php')) ?>">客戶意見</a>
      <a<?= $section === 'shop' ? ' aria-current="page"' : '' ?> href="<?= mis_e(mis_base_url('mis_shop/admin.php')) ?>">商品點餐</a>
      <a href="<?= mis_e(mis_base_url('mis_shop/analytics.php')) ?>">推薦分析</a>
      <a href="<?= mis_e(mis_base_url('mis_shop/ai-lab.php')) ?>">AI 推薦實驗</a>
      <?php if ($user['role'] === 'admin' && $isDevelopment): ?><a href="<?= mis_e(mis_base_url('mis_shop/generator.php')) ?>">虛擬資料</a><?php endif; ?>
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

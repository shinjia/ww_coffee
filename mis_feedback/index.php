<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/mis_shared/bootstrap.php';
mis_render_header('客戶意見', 'public');
?>
<main class="auth-wrap">
  <section class="panel narrow">
    <p class="eyebrow">CUSTOMER FEEDBACK</p>
    <h1>告訴我們你的想法</h1>
    <p class="muted">你的建議會幫助木窗咖啡做得更好。標示「必填」的欄位請完整填寫。</p>
    <form id="feedback-form" class="stack-form" action="<?= mis_e(mis_base_url('mis_feedback/submit.php')) ?>" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= mis_e(mis_csrf_token()) ?>">
      <input type="hidden" name="opened_at" value="<?= time() ?>">
      <label class="honeypot" aria-hidden="true">公司名稱<input name="company" type="text" tabindex="-1" autocomplete="off"></label>
      <label>姓名（必填）<input name="name" maxlength="60" autocomplete="name" required></label>
      <label>Email（必填）<input name="email" type="email" maxlength="254" inputmode="email" autocomplete="email" required></label>
      <label>意見類別（必填）
        <select name="category" required>
          <option value="">請選擇</option><option value="service">服務建議</option><option value="product">產品／咖啡</option><option value="environment">環境設施</option><option value="other">其他</option>
        </select>
      </label>
      <label>留言內容（必填）<textarea name="message" minlength="10" maxlength="2000" required placeholder="請至少輸入 10 個字"></textarea></label>
      <label class="consent"><input name="consent" type="checkbox" value="1" required><span>我同意木窗咖啡為回覆本次意見而使用上述聯絡資料。</span></label>
      <button class="primary-button" type="submit"><span>送出意見</span></button>
      <p class="form-status" id="feedback-status" role="status" aria-live="polite"></p>
    </form>
  </section>
</main>
<script src="<?= mis_e(mis_base_url('mis_feedback/feedback.js?v=2026083001')) ?>" defer></script>
<?php mis_render_footer(true); ?>

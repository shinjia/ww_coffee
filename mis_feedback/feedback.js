(() => {
  'use strict';
  const form = document.querySelector('#feedback-form');
  const status = document.querySelector('#feedback-status');
  if (!form || !status || typeof window.fetch !== 'function') return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    status.className = 'form-status';
    status.textContent = '';
    if (!form.checkValidity()) {
      form.reportValidity();
      status.textContent = '請完整填寫必填欄位並檢查格式。';
      status.classList.add('error');
      return;
    }

    const button = form.querySelector('button[type="submit"]');
    const buttonText = button.querySelector('span');
    button.disabled = true;
    buttonText.textContent = '送出中…';
    status.textContent = '正在送出意見，請稍候…';

    try {
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      const result = await response.json().catch(() => ({ success: false, message: '伺服器回應格式不正確。' }));
      if (!response.ok || !result.success) throw new Error(result.message || '送出失敗。');
      status.textContent = `${result.message} 查詢編號：${result.reference}`;
      status.classList.add('success');
      form.reset();
    } catch (error) {
      status.textContent = error instanceof Error ? error.message : '目前無法送出，請稍後再試。';
      status.classList.add('error');
    } finally {
      button.disabled = false;
      buttonText.textContent = '送出意見';
    }
  });
})();

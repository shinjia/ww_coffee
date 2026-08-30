(() => {
  'use strict';

  const menuButton = document.querySelector('.menu-toggle');
  const navigation = document.querySelector('.site-nav');
  const status = document.querySelector('#form-status');
  const form = document.querySelector('#subscribe-form');

  const closeMenu = () => {
    if (!menuButton || !navigation) return;
    menuButton.setAttribute('aria-expanded', 'false');
    navigation.classList.remove('is-open');
  };

  if (menuButton && navigation) {
    menuButton.addEventListener('click', () => {
      const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
      menuButton.setAttribute('aria-expanded', String(!isOpen));
      navigation.classList.toggle('is-open', !isOpen);
    });
    navigation.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeMenu();
    });
  }

  const revealItems = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealItems.forEach((item) => observer.observe(item));
  } else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
  }

  if (form && status) {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const emailInput = form.elements.email;
      const submitButton = form.querySelector('button[type="submit"]');
      const email = typeof emailInput.value === 'string' ? emailInput.value.trim() : '';
      status.className = 'form-status';

      if (!email) {
        status.textContent = '請輸入 Email。';
        status.classList.add('error');
        emailInput.focus();
        return;
      }
      if (!emailInput.validity.valid || email.length > 254) {
        status.textContent = 'Email 格式似乎不正確，請再檢查一次。';
        status.classList.add('error');
        emailInput.focus();
        return;
      }

      submitButton.disabled = true;
      status.textContent = '處理中…';
      try {
        await new Promise((resolve) => window.setTimeout(resolve, 650));
        status.textContent = '謝謝你！訂閱功能將於正式開站時啟用。';
        status.classList.add('success');
        form.reset();
      } catch (error) {
        status.textContent = '目前無法送出，請稍後再試。';
        status.classList.add('error');
        console.error('Subscription error:', error);
      } finally {
        submitButton.disabled = false;
      }
    });
  }

  const year = document.querySelector('#year');
  if (year) year.textContent = String(new Date().getFullYear());
})();

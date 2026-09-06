(() => {
  const cart = new Map();
  const dialog = document.querySelector('[data-cart-dialog]');
  const itemsBox = document.querySelector('[data-cart-items]');
  const count = document.querySelector('[data-cart-count]');
  const total = document.querySelector('[data-cart-total]');
  const json = document.querySelector('[data-cart-json]');
  const render = () => {
    const rows = [...cart.values()];
    count.textContent = String(rows.reduce((n, row) => n + row.quantity, 0));
    const sum = rows.reduce((n, row) => n + row.price * row.quantity, 0);
    total.textContent = `NT$${sum}`;
    json.value = JSON.stringify(rows);
    if (!rows.length) { itemsBox.innerHTML = '<p class="muted">尚未加入商品。</p>'; return; }
    itemsBox.innerHTML = rows.map(row => `<div class="cart-row"><div><strong>${escapeHtml(row.name)}</strong><br><small>NT$${row.price}</small></div><input type="number" min="1" max="10" value="${row.quantity}" data-cart-qty="${row.id}" aria-label="${escapeHtml(row.name)}數量"><button type="button" class="secondary-button" data-cart-remove="${row.id}">移除</button></div>`).join('');
  };
  const escapeHtml = value => String(value).replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
  document.addEventListener('click', event => {
    const add = event.target.closest('[data-add]');
    if (add) { const id = Number(add.dataset.id); const old = cart.get(id); cart.set(id, {id, name:add.dataset.name, price:Number(add.dataset.price), quantity:Math.min(10,(old?.quantity||0)+1), temperature:add.dataset.temperature === 'hot' ? 'hot' : '', sweetness:''}); render(); add.textContent='已加入'; setTimeout(()=>add.textContent='加入購物車',700); }
    if (event.target.closest('[data-cart-open]')) { render(); dialog.showModal(); }
    const remove = event.target.closest('[data-cart-remove]'); if (remove) { cart.delete(Number(remove.dataset.cartRemove)); render(); }
    const filter = event.target.closest('[data-filter]'); if (filter) { document.querySelectorAll('[data-filter]').forEach(b=>b.classList.toggle('active',b===filter)); applyFilter(); }
  });
  document.addEventListener('change', event => { if (event.target.matches('[data-cart-qty]')) { const row=cart.get(Number(event.target.dataset.cartQty)); if(row){row.quantity=Math.max(1,Math.min(10,Number(event.target.value)||1));render();} } });
  const applyFilter=()=>{const active=document.querySelector('[data-filter].active')?.dataset.filter||'all';const term=(document.querySelector('[data-search]')?.value||'').trim().toLowerCase();document.querySelectorAll('[data-product]').forEach(card=>card.classList.toggle('hidden',(active!=='all'&&card.dataset.category!==active)||(term&&!card.dataset.search.toLowerCase().includes(term))));};
  document.querySelector('[data-search]')?.addEventListener('input',applyFilter);
  document.querySelector('[data-order-form]')?.addEventListener('submit',async event=>{event.preventDefault();const status=event.currentTarget.querySelector('[data-order-status]');const button=event.currentTarget.querySelector('[data-submit-order]');if(!cart.size){status.textContent='請先加入商品。';return;}button.disabled=true;status.textContent='訂單送出中…';try{const response=await fetch(document.body.dataset.orderUrl,{method:'POST',body:new FormData(event.currentTarget),headers:{Accept:'application/json'}});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'送出失敗');status.textContent=`完成！訂單編號 ${result.reference}，總額 NT$${result.total}`;cart.clear();render();}catch(error){console.error(error);status.textContent=error.message||'目前無法送出，請稍後再試。';}finally{button.disabled=false;}});
  render();
})();


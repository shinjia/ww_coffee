const products = JSON.parse(document.querySelector('#product-data').textContent).filter(product => product.stock > 0);
const status = document.querySelector('[data-ai-status]');
const results = document.querySelector('[data-ai-results]');
let transformerModule;
let extractor;
const embed = async texts => (await extractor(texts, { pooling: 'mean', normalize: true })).tolist();
const render = items => { results.innerHTML = items.map(product => `<article><img src="assets/images/${product.image}" alt="商品分類照片"><div><h3>${product.name}</h3><p>${product.reason || `語意相似度 ${(product.score * 100).toFixed(1)}%`}</p><strong>NT$${product.price}</strong></div></article>`).join(''); };
document.querySelector('[data-ai-form]').addEventListener('submit', async event => {
  event.preventDefault();
  const query = document.querySelector('[data-ai-query]').value.trim();
  const mode = document.querySelector('[data-ai-mode]').value;
  const button = event.currentTarget.querySelector('button');
  if (!query) { status.textContent = '請輸入需求。'; return; }
  button.disabled = true; results.innerHTML = '';
  try {
    if (mode === 'hybrid') {
      status.textContent = '正在計算會員與匿名訂單訊號…';
      const response = await fetch(document.body.dataset.hybridUrl, { headers: { Accept: 'application/json' } });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message);
      render(data.items);
      status.textContent = data.customer_based ? '已結合會員偏好與訂單熱門度。' : '尚未登入，改用商品內容與訂單熱門度。';
    } else if (mode === 'combo') {
      const found = query.match(/([0-9]{2,4})\s*元?/);
      const budget = found ? Number(found[1]) : 250;
      let best = [];
      for (let first = 0; first < products.length; first += 1) {
        for (let second = first + 1; second < products.length; second += 1) {
          const sum = products[first].price + products[second].price;
          if (sum <= budget && (!best.length || sum > best[0].price + best[1].price)) best = [products[first], products[second]];
        }
      }
      if (!best.length) best = [products.filter(product => product.price <= budget).sort((a, b) => b.price - a.price)[0]].filter(Boolean);
      render(best.map(product => ({ ...product, reason: `組合預算 NT$${budget}，目前有庫存` })));
      status.textContent = `已從文字抽取預算 NT$${budget} 並搜尋可行組合；仍請自行確認過敏原。`;
    } else {
      status.textContent = extractor ? '正在計算相似度…' : '正在下載並載入模型，首次執行可能需要較久…';
      transformerModule = transformerModule || await import('https://cdn.jsdelivr.net/npm/@huggingface/transformers@3.8.1');
      transformerModule.env.allowLocalModels = false;
      extractor = extractor || await transformerModule.pipeline('feature-extraction', 'Xenova/all-MiniLM-L6-v2', { dtype: 'q8' });
      const vectors = await embed([query, ...products.map(product => product.text)]);
      const queryVector = vectors[0];
      render(products.map((product, index) => ({ ...product, score: queryVector.reduce((sum, value, position) => sum + value * vectors[index + 1][position], 0) })).sort((a, b) => b.score - a.score).slice(0, 4));
      status.textContent = '推薦完成；分數僅表示文字向量相似度，不代表營養或過敏安全。';
    }
  } catch (error) { console.error(error); status.textContent = error.message || '推薦失敗；一般商品目錄仍可使用。'; }
  finally { button.disabled = false; }
});

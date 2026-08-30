const TRANSFORMERS_CDN = 'https://cdn.jsdelivr.net/npm/@huggingface/transformers@3.8.1?v=2026083001';
const TASK = 'sentiment-analysis';

const models = {
  'Xenova/bert-base-multilingual-uncased-sentiment': {
    name: '多語言五星評價模型', languages: '中文、英文及多語言', purpose: '將評論分類為 1–5 星', feature: '適合比較中文、英文與混合文字', limitation: '星級不是單純正負面；諷刺與混合情緒可能誤判'
  },
  'Xenova/distilbert-base-uncased-finetuned-sst-2-english': {
    name: 'DistilBERT 英文情緒模型', languages: '英文', purpose: '英文正面／負面二分類', feature: '模型較小，適合入門比較', limitation: '訓練任務以英文為主，中文輸出不應視為可靠'
  }
};

const samples = {
  '全中文': ['咖啡香氣很棒，店員也很親切。','咖啡很好喝，但是等了半個小時。','今天的拿鐵普通，沒有特別喜歡或討厭。','環境很漂亮，可是音樂真的太大聲了。','價格不便宜，但我應該還會再來。'],
  'English': ['The coffee was wonderful and the staff made my day.','Great espresso, but the service was painfully slow.','It was fine, nothing memorable.','I would never order this drink again.','Not bad at all — I was pleasantly surprised.'],
  '中英混合': ['Coffee 很好喝，但是 service 太慢。','今天的 latte 很順口，love it!','環境很 chill，但座位真的不太舒服。','The staff 很親切，but the coffee was cold.','這杯 espresso is not terrible, just average.']
};

const elements = {
  model: document.querySelector('#model-select'), device: document.querySelector('#device-select'), modelCard: document.querySelector('#model-card'),
  load: document.querySelector('#load-button'), run: document.querySelector('#run-button'), input: document.querySelector('#input-text'), count: document.querySelector('#char-count'),
  status: document.querySelector('#status-text'), progressText: document.querySelector('#progress-text'), progress: document.querySelector('#progress'), fileStatus: document.querySelector('#file-status'),
  action: document.querySelector('#action-status'), empty: document.querySelector('#result-empty'), content: document.querySelector('#result-content'),
  label: document.querySelector('#result-label'), score: document.querySelector('#result-score'), scores: document.querySelector('#score-list'), raw: document.querySelector('#raw-output'), samples: document.querySelector('#sample-groups')
};

let classifier = null;
let loadedKey = '';
let transformersModule = null;
let loadingSequence = 0;

function setAction(message, type = '') {
  elements.action.textContent = message;
  elements.action.className = `action-status${type ? ` ${type}` : ''}`;
}

function renderModelCard() {
  const model = models[elements.model.value];
  elements.modelCard.innerHTML = `<dl><dt>Model ID</dt><dd>${escapeHtml(elements.model.value)}</dd><dt>語言</dt><dd>${escapeHtml(model.languages)}</dd><dt>用途</dt><dd>${escapeHtml(model.purpose)}</dd><dt>特色</dt><dd>${escapeHtml(model.feature)}</dd><dt>限制</dt><dd>${escapeHtml(model.limitation)}</dd></dl>`;
}

function escapeHtml(value) {
  const span = document.createElement('span');
  span.textContent = String(value);
  return span.innerHTML;
}

function resetModel() {
  classifier = null;
  loadedKey = '';
  loadingSequence += 1;
  elements.run.disabled = true;
  elements.load.disabled = false;
  elements.status.textContent = '模型已變更，請載入新模型';
  elements.progress.value = 0;
  elements.progressText.textContent = '0%';
  elements.fileStatus.textContent = '原本的 Pipeline 不會用於新模型。';
  setAction('');
  renderModelCard();
}

function updateProgress(data) {
  if (!data || typeof data !== 'object') return;
  const file = typeof data.file === 'string' ? data.file.split('/').pop() : '';
  const percent = Number.isFinite(data.progress) ? Math.max(0, Math.min(100, data.progress)) : null;
  if (data.status === 'progress' && percent !== null) {
    elements.status.textContent = '模型下載中';
    elements.progress.value = percent;
    elements.progressText.textContent = `${percent.toFixed(0)}%`;
  } else if (data.status === 'initiate') {
    elements.status.textContent = '準備模型檔案';
  } else if (data.status === 'done') {
    elements.status.textContent = '載入模型中';
  }
  if (file) elements.fileStatus.textContent = `目前檔案：${file}`;
}

async function loadModel() {
  const sequence = ++loadingSequence;
  const modelId = elements.model.value;
  const device = elements.device.value;
  const key = `${modelId}|${device}`;
  elements.load.disabled = true;
  elements.run.disabled = true;
  elements.status.textContent = '準備 Transformers.js';
  elements.fileStatus.textContent = '正在連線至 CDN 與 Hugging Face Hub…';
  elements.progress.value = 0;
  elements.progressText.textContent = '0%';
  setAction('模型載入中，請勿關閉頁面。');

  try {
    if (device === 'webgpu' && !navigator.gpu) throw new Error('此瀏覽器或裝置不支援 WebGPU，請改用 WASM。');
    if (!transformersModule) transformersModule = await import(TRANSFORMERS_CDN);
    const options = { dtype: 'q8', progress_callback: updateProgress };
    if (device === 'webgpu') options.device = 'webgpu';
    const nextClassifier = await transformersModule.pipeline(TASK, modelId, options);
    if (sequence !== loadingSequence || modelId !== elements.model.value || device !== elements.device.value) return;
    classifier = nextClassifier;
    loadedKey = key;
    elements.status.textContent = '模型載入完成';
    elements.progress.value = 100;
    elements.progressText.textContent = '100%';
    elements.fileStatus.textContent = '可以開始測試；再次載入時瀏覽器通常會使用快取。';
    elements.run.disabled = false;
    setAction('模型已就緒。', 'success');
  } catch (error) {
    console.error('Model loading failed:', error);
    classifier = null;
    loadedKey = '';
    elements.status.textContent = '模型載入失敗';
    elements.fileStatus.textContent = error instanceof Error ? error.message : '未知錯誤';
    setAction('模型載入失敗。請檢查網路、記憶體，或更換模型／執行方式。', 'error');
  } finally {
    if (sequence === loadingSequence) elements.load.disabled = false;
  }
}

function readableLabel(label) {
  const normalized = String(label).toUpperCase();
  if (normalized === 'POSITIVE') return '正面';
  if (normalized === 'NEGATIVE') return '負面';
  const star = normalized.match(/([1-5])/);
  return star ? `${star[1]} 星評價` : label;
}

function normalizeResults(output) {
  const list = Array.isArray(output) && Array.isArray(output[0]) ? output[0] : output;
  return Array.isArray(list) ? [...list].sort((a, b) => Number(b.score) - Number(a.score)) : [];
}

function renderResult(output) {
  const results = normalizeResults(output);
  if (!results.length) throw new Error('模型沒有回傳可辨識的分類結果。');
  const top = results[0];
  elements.label.textContent = readableLabel(top.label);
  elements.score.textContent = `信心度 ${(Number(top.score) * 100).toFixed(1)}%`;
  elements.scores.replaceChildren(...results.map((item) => {
    const row = document.createElement('div');
    row.className = 'score-row';
    const label = document.createElement('span'); label.textContent = readableLabel(item.label);
    const bar = document.createElement('span'); bar.className = 'score-bar';
    const fill = document.createElement('i'); fill.style.width = `${Math.max(0, Math.min(100, Number(item.score) * 100))}%`; bar.append(fill);
    const score = document.createElement('strong'); score.textContent = `${(Number(item.score) * 100).toFixed(1)}%`;
    row.append(label, bar, score); return row;
  }));
  elements.raw.textContent = JSON.stringify(output, null, 2);
  elements.empty.hidden = true;
  elements.content.hidden = false;
}

async function runAnalysis() {
  const text = elements.input.value.trim();
  const currentKey = `${elements.model.value}|${elements.device.value}`;
  if (!text) { setAction('請先輸入要分析的文字。', 'error'); elements.input.focus(); return; }
  if (text.length > 500) { setAction('輸入文字不可超過 500 字。', 'error'); return; }
  if (!classifier || loadedKey !== currentKey) { setAction('請先載入目前選擇的模型。', 'error'); return; }
  elements.run.disabled = true;
  elements.load.disabled = true;
  setAction('AI 執行中…');
  try {
    const output = await classifier(text, { top_k: null });
    renderResult(output);
    setAction('分析完成。', 'success');
  } catch (error) {
    console.error('Inference failed:', error);
    setAction('分析失敗。文字可能過長，或裝置記憶體不足；請縮短文字後再試。', 'error');
  } finally {
    elements.run.disabled = !classifier || loadedKey !== currentKey;
    elements.load.disabled = false;
  }
}

function renderSamples() {
  Object.entries(samples).forEach(([group, items]) => {
    const section = document.createElement('section'); section.className = 'sample-group';
    const heading = document.createElement('h3'); heading.textContent = group;
    const list = document.createElement('div'); list.className = 'sample-list';
    items.forEach((text) => {
      const button = document.createElement('button'); button.type = 'button'; button.className = 'sample-button'; button.textContent = text;
      button.addEventListener('click', () => { elements.input.value = text; elements.input.dispatchEvent(new Event('input')); document.querySelector('#experiment').scrollIntoView({ behavior: 'smooth' }); elements.input.focus(); });
      list.append(button);
    });
    section.append(heading, list); elements.samples.append(section);
  });
}

elements.model.addEventListener('change', resetModel);
elements.device.addEventListener('change', resetModel);
elements.load.addEventListener('click', loadModel);
elements.run.addEventListener('click', runAnalysis);
elements.input.addEventListener('input', () => { elements.count.textContent = `${elements.input.value.length} / 500`; });

if (!navigator.gpu) elements.device.querySelector('option[value="webgpu"]').disabled = true;
renderModelCard();
renderSamples();

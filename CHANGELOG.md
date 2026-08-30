# 修改紀錄

本文件記錄木窗咖啡 WW Coffee 對外網站的重要變更。日期採 `YYYY-MM-DD`，版本依語意化版本格式記錄。

## [0.6.0] - 2026-08-30

### 新增

- 新增獨立 Sentence Similarity 頁面，以 `Xenova/all-MiniLM-L6-v2` 產生 Embedding 並計算 cosine similarity。
- 新增獨立 Feature Extraction / Embedding 頁面，顯示 384 維向量摘要與完整 Raw JSON。

### 變更

- 文字 AI 實驗室與系統入口說明由十類更新為十二類。
- 新增 Embedding 與相似度結果不得偽造的維護規範。

### 驗證

- 新增頁面與專用 JavaScript 可由本機網站讀取，CSS／JS 連結均含 `?v=20260830xx`。
- 兩頁皆具輸入檢查及載入中、完成、錯誤狀態。
- Chrome 實測 Sentence Similarity 為 0.8097；Feature Extraction 輸出 384 維正規化向量，L2 norm 為 1.000000。

## [0.5.0] - 2026-08-30

### 變更

- 將品牌前台 `index.html` 與 `assets/` 移至 `web/`。
- 將根目錄 `index.html` 改為網站與子系統清單入口。
- 修正品牌官網、客戶留言及 AI Lab 的跨資料夾返回連結。

### 新增

- 新增六個入口：品牌官網、客戶意見留言、文字 AI 實驗室、員工登入、人事管理及客戶意見管理。
- 清單入口以「對外」、「內部」、「實驗」標示使用範圍。
- 新增 `web/assets/css/portal.css?v=2026083001`。

### 驗證

- 根入口、品牌官網、各子系統與品牌靜態資源均回傳 HTTP 200。
- 舊 `/assets/` 路徑回傳 HTTP 404，跨資料夾連結正確。
- 390px 寬度無橫向溢出，入口頁 Console 無錯誤。

## [0.4.0] - 2026-08-30

### 新增

- 將 Lab 入口與 Sentiment Analysis 拆成 `index.html`、`sentiment.html` 及專用 `sentiment.js`。
- 新增 Zero-shot、NER、QA、Summarization、Text Generation、EN→ZH、ZH→EN、Fill-Mask、Text2Text 九個獨立 HTML 與專用 JS。
- 新增共用 `runtime.js`，只負責 Transformers.js 載入、進度、完成、錯誤及 Raw JSON 狀態。
- 每個頁面提供符合 Pipeline 的專用輸入、結果與限制說明。

### 變更

- Lab 共用 CSS 更新為 `?v=2026083003`。
- 明訂一種應用一個獨立 HTML 與專用 JS，入口頁不混合實驗功能。

### 驗證

- 入口與十個實驗頁 HTTP／腳本載入正常，390px 寬度皆無橫向溢出。
- Text2Text 範例可正確帶入指令與文字，所有頁面無初始 Console 錯誤。
- QA q8 模型首次下載與真實推論成功，答案為 `a free dessert`，信心度 54.0%。

## [0.3.0] - 2026-08-30

### 新增

- 建立 `lab_hugging` 原生 HTML、CSS、JavaScript 文字 AI 教學網站。
- 建立 Sentiment Analysis 實驗，固定 Transformers.js 3.8.1，提供多語五星與英文正負模型。
- 加入 WASM／WebGPU 選項、模型下載進度、切換保護、真實推論、易讀結果、分數圖表與 Raw JSON。
- 加入中文、英文及中英混合共 15 組測試案例與教學觀察題。
- 建立 `lab_hugging/hugging.md`，記錄外部來源、模型研究、使用方式及限制。

### 修正

- 修正分析完成後空白提示仍占據結果區的 `hidden` 顯示問題；Lab CSS 更新為 `?v=2026083002`。

### 驗證

- 英文 q8 模型首次下載及 WASM 推論成功；正面案例輸出 POSITIVE 99.99%。
- 模型切換會停用舊 Pipeline，第二次快取載入約 0.9 秒。
- 390px 手機寬度無橫向溢出，案例互動與 Raw JSON 正常。

## [0.2.0] - 2026-08-30

### 新增

- 建立 `mis_shared` 共用 PHP 7.4 核心，包含 JSON 設定、PDO SQLite、Session 登入、角色權限、CSRF、安全標頭、登入失敗限制與稽核紀錄。
- 建立 `mis_hr` 人事管理子系統，提供員工名冊、摘要及管理者新增員工／帳號功能。
- 建立 `mis_feedback` 對外客戶意見表單與內部留言管理介面。
- 客戶留言加入雙層輸入驗證、蜜罐、開啟時間檢查、頻率限制、個資同意及非同步狀態。
- 建立 `mis_hr/hr.md` 與 `mis_feedback/feedback.md` 子系統開發文件。
- 在品牌官網導覽加入客戶意見入口。

### 安全

- 密碼以 `password_hash()` 儲存，Session 登入後更新 ID 並設定 30 分鐘閒置逾時。
- 加入 CSP、X-Frame-Options、nosniff、Referrer-Policy、Permissions-Policy 與 no-store。
- 使用 `.htaccess` 阻擋設定 JSON 與 SQLite 直接下載，Apache 實測回傳 403。

### 驗證

- 所有 PHP 檔案通過 PHP 7.4 語法檢查。
- 實測未登入導向、兩種角色權限、新增員工、留言驗證、留言成功送出及靜態資源載入。
- 測試建立的員工與留言資料已清除。

## [0.1.3] - 2026-08-30

### 修正

- 縮小首頁及各區塊主要標題，避免桌機與手機版面產生過多換行。
- 加入中文標題不任意斷字的規則，並針對窄螢幕提供更小字級。
- CSS 快取版本更新為 `?v=2026083002`。

## [0.1.2] - 2026-08-30

### 修正

- 將所有 CSS／JavaScript 快取參數由錯誤的 `?=20260830xx` 修正為 `?v=20260830xx`。

## [0.1.1] - 2026-08-30

### 新增

- 新增 `CHANGELOG.md`，集中記錄各版本的重要修改。
- 將變更紀錄維護要求納入專案文件與後續開發流程。

## [0.1.0] - 2026-08-30

### 新增

- 建立原生 HTML、CSS、JavaScript 的響應式單頁網站。
- 加入桌機與手機導覽、品牌故事、咖啡選品、沖煮理念、來店資訊及頁尾。
- 加入訂閱表單的空值、Email 格式與長度檢查，以及處理中、完成與錯誤狀態。
- 加入捲動進場效果及 `prefers-reduced-motion` 支援。
- 使用 OpenAI ImageGen 生成主視覺，轉為本地 WebP 資產。
- 建立 `README.md`、`AGENTS.md`、`HANDOFF.md` 專案文件。

### 驗證

- 確認首頁、CSS、JavaScript 與 WebP 圖片皆可由本機 HTTP 伺服器正常載入。
- 確認不存在的資源正確回傳 404。
- 確認 HTML ID 無重複，CSS 與 JavaScript 基本結構正確。

### 已知限制

- 訂閱功能尚未串接後端，目前不傳送或儲存 Email。
- 尚未取得正式店址、營業時間、社群連結、菜單及價格。
- 尚待於已連接 Chrome 瀏覽器擴充功能的環境完成視覺驗收。

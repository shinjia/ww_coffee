# AGENTS.md

本文件為後續 AI／Codex 開發木窗咖啡網站時必須遵守的規範。

## 開始與完成流程

1. 修改前完整閱讀 `README.md`、`AGENTS.md`、`HANDOFF.md`、`CHANGELOG.md`。
2. 先盤點現有功能與檔案，只做需求範圍內的變更。
3. 完成後測試主要流程、手機版面、Console、404 與外部資源。
4. 每次修改後同步更新上述四份文件，確保內容符合實際狀態。
5. `CHANGELOG.md` 記錄對使用者、部署或後續開發有意義的變更，不記錄無關的操作細節。

## 開發規範

- 使用原生 HTML、CSS、JavaScript；無明確必要性不得增加框架或套件。
- HTML、CSS、JavaScript 分離，保持檔案結構簡單。
- 採 Responsive Web Design，避免文字、圖片、按鈕溢出或遮擋。
- 以 Chrome 為主要測試瀏覽器，兼顧 Edge、Safari 等現代瀏覽器。
- 所有使用者輸入需驗證空值、格式、長度及異常資料。
- 所有非同步操作需提供載入中、完成與錯誤狀態，並以 `try/catch/finally` 保護流程。
- 不得將密碼、API Key、Token 或敏感資訊放入前端。
- 使用外部 CDN、API、模型或套件前，記錄來源、版本、用途及失敗處理。
- 修改既有功能時避免大幅重構，優先維持相容性。
- 所有 HTML 內連結的 `.css`、`.js` URL 必須有 `?v=20260830xx`；每次該檔案更新，`xx` 加一。

## 現有設計原則

- 色彩：燕麥米、墨黑、木質棕、沉靜綠。
- 風格：安靜、溫暖、有留白的當代咖啡品牌。
- 未取得真實店址、電話、社群、菜單與售價前，不可虛構資訊。
- 品牌前台固定放在 `web/`；專案根目錄 `index.html` 只作網站與各子系統的清單入口。
- 主視覺 `web/assets/images/ww-coffee-hero.webp` 是專案本地資產，不應改為不穩定的外部圖片 URL。
- 新增可供使用者直接進入的子系統時，需同步更新根目錄清單頁，並明確標示「對外」、「內部」或「實驗」。
- 主要標題需使用可適應視窗的字級與 `word-break: keep-all`，避免中文字詞被擠成過多行或溢出。

## MIS 開發規範

- 子系統命名為 `mis_xxx`，且必須在該目錄保留 `xxx.md`，記錄用途、角色權限、資料、流程、限制與快取版本。
- 共用設定、SQLite、驗證、安全函式與資產集中在 `mis_shared`，子系統不得複製另一套共用核心。
- 後端維持 PHP 7.4 相容，不使用 PHP 8 專屬語法；資料存放使用 SQLite、JSON 或必要的文字檔，不使用 MySQL。
- 所有 SQL 必須使用 PDO prepared statements；啟用 foreign keys，寫入流程需要交易時必須使用 transaction。
- 密碼只允許以 `password_hash()` 儲存，禁止保存或記錄明文。帳號加 `1234` 僅限 development 環境。
- 所有狀態變更必須在後端做角色、CSRF、格式、長度與允許值驗證；不得只依賴前端限制。
- 管理與人資頁正式部署時必須限制為內網或 VPN；`mis_shared/config`、`mis_shared/storage` 及備份不得公開存取。
- 客戶留言等公開表單必須具備 CSRF、防機器人／頻率限制、個資告知、成功與錯誤狀態，不得將後端例外細節回傳給使用者。
- 管理操作、登入成功／失敗及公開資料新增應寫入 `audit_logs`，但不得記錄密碼、Session ID 或完整敏感內容。
- MIS 共用與子系統 CSS／JS 同樣使用 `?v=20260830xx`，每個更新檔案獨立遞增。

## Browser AI Lab 規範

- `lab_hugging` 使用原生 HTML、CSS、JavaScript，不建立 npm、打包或框架流程。
- Transformers.js 固定為 `3.8.1`；更換版本前須查核官方文件、模型相容性並更新來源與測試紀錄。
- 推論結果必須來自真實模型，不得寫死答案、偽造信心度或在失敗時顯示假結果。
- 模型切換後必須停用舊 Pipeline，重新載入成功前不得執行。
- 模型下載、載入、推論、完成及失敗狀態必須顯示於畫面，完整錯誤保留於 Console。
- 新增模型或 Pipeline 時，須在 `lab_hugging/hugging.md` 記錄 Model ID、Task、語言、大小、Transformers.js／WASM／WebGPU 與實測狀態。
- 每一種 Pipeline 必須使用獨立 HTML 與專用 JS；`index.html` 只作入口，不得把多種應用混在同一個實驗頁。
- 允許 `runtime.js` 共用模型載入與狀態處理，但各任務的欄位、參數與結果轉換必須留在該任務專用 JS。
- Sentence Similarity 必須以模型產生的 Embedding 計算 cosine similarity；Feature Extraction 必須呈現真實向量維度與數值，不得以固定分數或範例向量代替。

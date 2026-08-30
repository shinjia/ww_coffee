# HANDOFF.md

## 目前完成狀態

- 已將木窗咖啡響應式品牌官網移至 `web/`。
- 根目錄 `index.html` 已改為品牌官網與各子系統的清單入口頁。
- 已完成桌機與手機導覽、品牌主視覺、故事、咖啡選品、沖煮理念、來店資訊及頁尾。
- 已完成訂閱表單的空值、Email 格式與長度檢查，以及處理中、完成、錯誤狀態。
- CSS 快取版本目前為 `?v=2026083002`，JS 為 `?v=2026083001`。
- 目前無外部 CDN、API 或第三方套件。
- 已建立 `mis_shared` 共用核心、`mis_hr` 人事管理及 `mis_feedback` 客戶意見留言子系統。
- 已建立 `lab_hugging` 入口與十二個各自獨立的 Browser AI 文字實驗，包含 Sentence Similarity 與 Feature Extraction / Embedding。
- 已建立獨立 `lab_hugging_python`，包含 12 個 Server AI 頁面、專用 JS、Flask API、模型管理器及 12 個 Python 任務模組；原 `lab_hugging` 未修改。
- 已建立 `CHANGELOG.md`，目前網站版本為 `0.7.0`。

## 重要決策

- 專案原始目錄為空，採用最小化的原生三層結構建立。
- 未提供實際營業資料，因此以「籌備中」呈現，不建立假地址或假聯絡方式。
- 訂閱流程目前只做前端互動示意，不收集資料。
- 主視覺由 OpenAI 內建 ImageGen 生成、轉為 WebP 並保存為本地檔案 `web/assets/images/ww-coffee-hero.webp`；約 68 KB。
- PHP 環境為 7.4.33；共用資料使用 PDO SQLite，設定使用 JSON。
- 帳號、員工、留言及稽核共用同一 SQLite；密碼只保存雜湊。
- `mis_shared/config` 與 `mis_shared/storage` 使用 `.htaccess` 阻擋網站直接存取。
- 公開留言入口已連結至品牌官網；人資與留言管理共用登入狀態。
- Browser AI 固定使用 Transformers.js 3.8.1；外部來源為 jsDelivr 與 Hugging Face Hub，不使用 API Key。
- Lab 每種 Pipeline 使用獨立 HTML 與專用 JS，僅共用 `runtime.js` 的下載、進度與錯誤處理。
- Sentence Similarity 與 Feature Extraction 共用 `Xenova/all-MiniLM-L6-v2`；前者計算兩個正規化 Embedding 的 cosine similarity，後者呈現 384 維向量。
- 根目錄入口列出品牌官網、客戶留言、AI Lab、員工登入、人事管理及客戶意見管理，並區分對外／內部／實驗。
- Python Lab 採 Lazy Load + Process 記憶體快取；API 回傳 `load_ms`、`inference_ms`、`total_ms` 與 `model_cached`。

## 已知問題

- 尚未取得正式 Logo、品牌規範、店址、營業時間、社群連結、菜單、價格與咖啡豆實際資料。
- 訂閱尚未串接後端或電子報服務。
- 目前環境未連接 Chrome 瀏覽器擴充功能，仍需在可啟動 Chrome 的環境進行完整跨瀏覽器人工視覺驗收。
- 登入失敗限制目前以 Session 記錄；正式環境仍需增加 Web Server／WAF 層的 IP 流量限制。
- 尚未實作初次登入強制改密碼、忘記密碼、員工編輯／停用、留言搜尋／分頁／通知及資料保存期限清理。
- 正式部署前必須設定公司內網／VPN 存取限制、移除開發預設密碼並完成隱私權政策。
- 多語模型、WebGPU、Chrome、Edge、Safari 與手機實機尚待完整驗證。
- 已建立 Python 3.11.9 的 `.venv`，但 Flask、Transformers、PyTorch 等套件尚未安裝；API 測試與真實模型推論尚待執行。

## 已完成檢查

- 本機 PHP HTTP 伺服器首頁、CSS、JavaScript、WebP 圖片皆回傳 200。
- 不存在的測試路徑正確回傳 404。
- HTML 共 16 個 ID，未發現重複。
- JavaScript 與 CSS 基本結構檢查通過；環境無 Node.js，未執行 `node --check`。
- 所有 PHP 檔案通過 PHP 7.4 `php -l` 語法檢查。
- 實測未登入導向、管理者登入、員工名冊、管理者新增員工、一般員工 403 權限阻擋均正常。
- 客戶留言錯誤 Email 回傳 422，有效留言回傳 201、成功訊息與查詢編號；測試資料已清除。
- Apache 實測設定 JSON 與 SQLite 均回傳 403；MIS CSS／JS 資源均回傳 200。
- CSP、Session Cookie、安全標頭、CSRF、登入失敗限制及 no-store 已加入。
- Lab 英文 q8 模型已完成首次下載、真實推論、模型切換停用與第二次 Browser Cache 載入測試。
- Lab 15 個案例渲染與案例帶入正常；390px 寬度無橫向溢出。
- Lab Console 僅有 CDN 未提供 content-length 的非阻斷警告；未發現程式錯誤。
- 十二個獨立實驗頁均可載入；新增頁面已完成靜態資源、輸入驗證與快取參數檢查。
- QA q8 模型真實推論成功，從 Context 擷取 `a free dessert`；其餘新增模型待逐一下載實測。
- `all-MiniLM-L6-v2` q8 真實推論成功：相近句 cosine similarity 為 0.8097，Embedding 為 384 維且 L2 norm 為 1.000000。
- 根入口與六個清單連結均可存取；品牌 CSS、JS、WebP 皆回傳 200，舊 `/assets/` 路徑回傳 404。
- 系統入口在 390px 寬度無橫向溢出，六張卡片完整且 Console 無錯誤。
- Python Lab 入口與 12 個任務頁皆回傳 HTTP 200，390px 無橫向溢出；API 未啟動時前端能顯示明確錯誤，404 測試正常。

## 下一步

1. 確認正式內網／VPN、網域、`base_path` 與資料庫備份位置。
2. 實作初次登入強制改密碼、密碼重設及帳號停用流程。
3. 補齊員工編輯／搜尋與留言搜尋／分頁／通知／保存期限清理。
4. 完成個資告知、隱私權政策及跨瀏覽器人工驗收。
5. 取得品牌正式資料並完成對外網站的正式營業內容與 SEO。

## 修改紀錄

- 2026-08-31：新增獨立 Python Server AI 實驗室，保留原有 Transformers.js 全部功能。
- 2026-08-30：新增 Sentence Similarity 與 Feature Extraction / Embedding，文字 AI 實驗室擴充為十二類。
- 2026-08-30：品牌前台移至 `web/`，根首頁改為各子系統清單入口。
- 2026-08-30：將每種 Browser AI 應用拆成獨立 HTML 與專用 JS，完成十個實驗入口與頁面。
- 2026-08-30：建立 `lab_hugging` 文字 AI 實驗室與情緒分析實驗。
- 2026-08-30：建立 MIS 共用核心、人事管理與對外客戶意見留言子系統。
- 2026-08-30：縮小主要標題並改善中英文標題換行，CSS 快取版本更新至 `02`。
- 2026-08-30：修正 CSS／JS 快取參數格式為 `?v=20260830xx`。
- 2026-08-30：新增 `CHANGELOG.md`，並將其納入文件同步流程。
- 2026-08-30：建立第一版網站與三份專案文件。

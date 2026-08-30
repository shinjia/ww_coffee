# 木窗咖啡 WW Coffee

木窗咖啡（Wood Window Coffee）對外品牌網站。第一版為純 HTML、CSS、JavaScript 的響應式單頁網站，不需安裝套件或建置工具。

## 使用與安裝

1. 將本專案放在 Apache 可存取的網站目錄。
2. 啟動 XAMPP Apache。
3. 瀏覽 `http://localhost/ww_coffee/` 進入系統清單目錄頁。

品牌前台網址為 `http://localhost/ww_coffee/web/`。建議一律以 HTTP 伺服器測試，避免直接使用 `file://`。

## 功能

- 響應式桌機與手機版面
- 品牌故事、咖啡選品、沖煮理念及來店資訊
- 手機導覽選單與鍵盤 Escape 關閉
- 捲動進場效果，支援 `prefers-reduced-motion`
- Email 格式與空值檢查、處理中／完成／錯誤狀態
- 無外部 CDN、API、套件或前端敏感資訊
- 對外客戶意見表單，以及登入後的留言處理介面
- 共用員工登入、人事名冊與管理者新增員工功能
- `lab_hugging` 十二種獨立 Browser AI 文字教學實驗

訂閱功能目前為前端示意，不會傳送或儲存 Email；後端服務接妥前會清楚告知使用者。

## 檔案結構

```text
ww_coffee/
├─ index.html                     # 系統清單入口
├─ web/                           # 對外品牌前台
│  ├─ index.html
│  └─ assets/
│     ├─ css/style.css、portal.css
│     ├─ js/main.js
│     └─ images/ww-coffee-hero.webp
├─ mis_shared/
│  ├─ config/app.json
│  ├─ assets/
│  ├─ storage/
│  └─ 共用 PHP 驗證、資料庫、安全及版面檔案
├─ mis_hr/
│  ├─ index.php
│  ├─ employee.php
│  └─ hr.md
├─ mis_feedback/
│  ├─ index.php
│  ├─ submit.php
│  ├─ admin.php
│  ├─ feedback.js
│  └─ feedback.md
├─ lab_hugging/
│  ├─ index.html
│  ├─ sentiment.html、zero-shot.html、ner.html、qa.html
│  ├─ summarization.html、generation.html
│  ├─ translation-en-zh.html、translation-zh-en.html
│  ├─ fill-mask.html、text2text.html
│  ├─ sentence-similarity.html、feature-extraction.html
│  ├─ css/style.css
│  ├─ js/runtime.js 與各實驗專用 JS
│  └─ hugging.md
├─ README.md
├─ AGENTS.md
├─ HANDOFF.md
└─ CHANGELOG.md
```

版本與重要修改記錄請參閱 `CHANGELOG.md`。

## 內部系統與客戶意見

- 系統清單：`http://localhost/ww_coffee/`
- 品牌官網：`http://localhost/ww_coffee/web/`
- 客戶意見：`http://localhost/ww_coffee/mis_feedback/`
- 員工登入：`http://localhost/ww_coffee/mis_shared/login.php`
- 人事管理：`http://localhost/ww_coffee/mis_hr/`
- 留言管理：`http://localhost/ww_coffee/mis_feedback/admin.php`

首次開啟 PHP 頁面時會自動建立 `mis_shared/storage/mis.sqlite` 與資料表。開發測試帳號：

- 管理者：`admin` / `admin1234`
- 員工：`employee` / `employee1234`

上述密碼僅供隔離的 development 環境使用。正式部署前必須改為安全密碼及初次登入強制改密碼，並將 `mis_shared/config/app.json` 的 `environment` 改為正式環境設定。

目前設定的 `base_path` 為 `/ww_coffee`；若部署目錄不同，需同步修改 `mis_shared/config/app.json`。Apache 必須允許 `.htaccess`，確保 `config` 與 `storage` 不能由網站下載。正式環境另應限制 `mis_hr` 與管理頁只能透過公司內網或 VPN 存取。

## 快取版本

品牌前台 CSS 使用 `?v=2026083002`，JavaScript 使用 `?v=2026083001`；系統入口 `portal.css` 使用 `?v=2026083001`。每次修改對應檔案時，將末兩碼數字加一並同步修改引用該檔案的 HTML。

MIS 共用 CSS／JS 與留言 JS 目前使用 `?v=2026083001`；各檔案分別遞增版本。

## 文字 AI 實驗室

開啟 `http://localhost/ww_coffee/lab_hugging/`，再進入十二個獨立 Browser AI 文字實驗。新增的 Sentence Similarity 會比較兩個 Embedding 的 cosine similarity；Feature Extraction / Embedding 會顯示 384 維向量摘要與完整 JSON。首次載入各模型需要網路並下載 ONNX 模型；後續通常會使用 Browser Cache。詳細模型、來源及測試紀錄請參閱 `lab_hugging/hugging.md`。

Lab CSS 為 `?v=2026083003`，共用與各實驗 JS 為 `?v=2026083001`。

## 圖片來源

`web/assets/images/ww-coffee-hero.webp` 由 OpenAI 內建 ImageGen 於 2026-08-30 生成並轉為 WebP，用途為網站主視覺；不依賴外部載入。

## 瀏覽器

以最新版 Chrome 為主要目標，並採用現代 Edge、Safari 支援的標準 HTML/CSS/JavaScript。若瀏覽器不支援 IntersectionObserver，內容會直接顯示。

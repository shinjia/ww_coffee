# 木窗咖啡 WW Coffee

木窗咖啡（Wood Window Coffee）對外品牌網站。第一版為純 HTML、CSS、JavaScript 的響應式單頁網站，不需安裝套件或建置工具。

## 使用與安裝

1. 將本專案放在 Apache 可存取的網站目錄。
2. 啟動 XAMPP Apache。
3. 瀏覽 `http://localhost/<專案目錄>/` 進入系統清單目錄頁。

品牌前台網址為 `http://localhost/<專案目錄>/web/`。建議一律以 HTTP 伺服器測試，避免直接使用 `file://`。

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
- `lab_hugging_python` 十二種獨立 Python Server AI 文字教學實驗
- 根目錄入口依「網站對外」、「內部管理」、「Lab 實驗」分成三區，列出商品會員、商品管理、虛擬資料及 AI 推薦等全部可用入口
- 教學商品目錄、QR Code 點餐、成品庫存、顧客會員與個人化推薦
- development 限定的虛擬員工、意見留言、會員、訂單與推薦互動資料產生器

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
├─ mis_shop/                     # 教學商品、點餐與推薦子系統
│  ├─ index.php、member.php、ai-lab.php
│  ├─ submit-order.php、admin.php、generator.php
│  ├─ assets/                    # CSS、JS、本地 QR 程式與七張 WebP 商品照
│  └─ shop.md
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
├─ lab_hugging_python/            # 獨立 Python Server AI 實驗室
│  ├─ 入口與 12 個任務 HTML
│  ├─ css/、js/
│  ├─ api/app.py、model_manager.py、requirements.txt
│  ├─ api/tasks/                  # 12 個任務專用 Python 模組
│  └─ hugging_python.md
├─ README.md
├─ AGENTS.md
├─ HANDOFF.md
└─ CHANGELOG.md
```

版本與重要修改記錄請參閱 `CHANGELOG.md`。

## 內部系統與客戶意見

- 系統清單：`http://localhost/<專案目錄>/`
- 品牌官網：`http://localhost/<專案目錄>/web/`
- 客戶意見：`http://localhost/<專案目錄>/mis_feedback/`
- 員工登入：`http://localhost/<專案目錄>/mis_shared/login.php`
- 人事管理：`http://localhost/<專案目錄>/mis_hr/`
- 留言管理：`http://localhost/<專案目錄>/mis_feedback/admin.php`
- 商品與 QR 點餐：`http://localhost/<專案目錄>/mis_shop/`
- 會員中心：`http://localhost/<專案目錄>/mis_shop/member.php`
- AI 語意推薦：`http://localhost/<專案目錄>/mis_shop/ai-lab.php`
- 商品點餐管理：`http://localhost/<專案目錄>/mis_shop/admin.php`
- 虛擬資料產生器：`http://localhost/<專案目錄>/mis_shop/generator.php`（僅 development 管理者）
- 推薦分析與評估：`http://localhost/<專案目錄>/mis_shop/analytics.php`（需員工登入）

首次開啟 PHP 頁面時會自動建立 `mis_shared/storage/mis.sqlite` 與資料表。開發測試帳號：

- 管理者：`admin` / `admin123`
- 員工：`employee` / `employee123`

上述密碼僅供隔離的 development 環境使用。正式部署前必須改為安全密碼及初次登入強制改密碼，並將 `mis_shared/config/app.json` 的 `environment` 改為正式環境設定。

`base_path` 預設為 `auto`，後台會依目前 PHP 頁面的 URL 自動辨識專案根路徑，因此整個目錄改名或搬到其他層級後不必修改連結。若伺服器有特殊反向代理路徑，才在 `mis_shared/config/app.json` 明確填入路徑。Apache 必須允許 `.htaccess`，確保 `config` 與 `storage` 不能由網站下載。正式環境另應限制 `mis_hr` 與管理頁只能透過公司內網或 VPN 存取。

共用後台導覽會直接讀取 `app.json` 的 `environment` 判斷是否顯示 development 專用功能，不依賴額外環境函式。

## 商品、QR 點餐、會員與 AI 推薦規畫

`mis_shop` 已完成開發階段 1 至 10。它定位為教學用網頁，以適量功能示範商品目錄、QR Code 點餐、成品庫存、會員資料、個人化推薦、真實 Embedding 語意搜尋、虛擬資料產生，以及推薦與營運分析，不延伸至原料、配方、供應商、採購、線上金流、電子發票或外送平台。

### 示範商品與價格

下列皆為課程示範資料，不代表木窗咖啡正式菜單或售價；實作時須在公開頁面清楚標示。

| 分類 | 示範商品 | 價格 |
| --- | --- | ---: |
| 咖啡 | 美式咖啡、拿鐵、卡布奇諾、摩卡、單品手沖 | NT$80、NT$110、NT$110、NT$125、NT$150 |
| 茶飲 | 紅茶、烏龍茶、鮮奶茶 | NT$65、NT$70、NT$95 |
| 非咖啡 | 巧克力歐蕾、抹茶歐蕾 | NT$105、NT$115 |
| 點心 | 原味司康、巧克力餅乾、磅蛋糕、乳酪蛋糕 | NT$65、NT$50、NT$75、NT$120 |
| 輕食 | 火腿起司吐司 | NT$100 |
| 咖啡商品 | 綜合濾掛咖啡盒、精選咖啡豆 | NT$320、NT$480 |
| 紀念品 | 木窗咖啡馬克杯、品牌帆布袋、木質杯墊 | NT$380、NT$290、NT$120 |

### 商品與 AI 可用資料

每項商品預計保存商品編號、名稱、分類、說明、圖片、價格、成品庫存、排序及上架／售罄／停售狀態，並保留可供篩選及 AI 推薦使用的結構化資訊：冰熱選項、咖啡因程度、甜度感受、風味、口感、價格區間、飲食標籤、過敏原、適用情境與可搭配商品。過敏原屬強制排除條件，不可只當推薦權重；資料不足時須提示顧客向店員確認，不得由 AI 猜測。

### 對外功能

- 響應式商品目錄、分類篩選、搜尋、商品詳情、庫存與售罄狀態。
- 購物車、內用／外帶、桌號或取餐名稱、訂單總額與送出結果。
- 桌號及外帶入口 QR Code；QR Code 只攜帶經後端驗證的入口或桌號識別，不包含會員 ID、Email、價格或權限資訊。
- 訂單狀態顯示：已收到、製作中、可取餐、已完成、已取消。
- 免登入也能點餐；會員登入後才提供收藏、歷史訂單、再次點餐與個人化推薦。

### 成品庫存與訂單

庫存只管理可直接販售的杯數、份數、片數、盒數、包數或個數，不建立原料、配方或 BOM。系統保留初始設定、售出扣減、取消回補及人工調整四類異動；每筆異動記錄原因、數量、操作者與時間。建立訂單與扣庫存必須在同一資料庫 transaction 中完成，並重新由後端取得商品價格、上架狀態與庫存，避免價格竄改、重複送單及超賣。訂單明細保存下單當時的商品名稱、選項與價格快照，日後調價不得改變歷史訂單。

### 會員功能

顧客會員與既有員工帳號分開保存，避免公開會員取得 MIS 權限。第一版包含 Email 註冊／登入、顯示名稱、修改密碼、歷史訂單、再次點餐、收藏、口味與飲食偏好、推薦同意、清除推薦紀錄及停用帳號。只收集功能必要資料，不預設收集電話、地址、生日或性別；密碼只保存 `password_hash()` 雜湊。

會員可主動設定常選分類、咖啡因、風味、口感、冰熱、甜度、價格範圍、飲食限制與使用情境。取得明確同意後，才可將查看、收藏、購物車、完成購買、重複購買、搜尋及推薦回饋用於個人化；應提供停止個人化、查看用途、清除紀錄與資料保存期限說明。

### 內部教學管理

內部介面維持四個主要區域：商品管理、成品庫存與異動、訂單看板、桌號／QR Code 管理。現有 `employee` 可查看及處理訂單與一般庫存操作；`admin` 可管理商品、價格、上下架、庫存調整、桌號及報表。所有權限與狀態變更均須在後端驗證並寫入 `audit_logs`。

development／Lab 的「虛擬資料產生器」可分別指定員工、意見留言、會員、訂單、推薦互動、日期範圍及亂數種子，整批寫入共用 SQLite 的關聯資料表。員工登入帳號由批次自動產生，會員帳號為完整 Email；development 的員工與會員密碼固定為「帳號＋123」，資料庫只保存 `password_hash()` 雜湊。訂單或推薦互動需要至少一位會員。

每次產生都需建立獨立批次編號，資料加上 `is_synthetic` 或等效來源標記，保存產生參數、亂數種子、建立者、筆數、開始／完成時間及錯誤狀態。相同種子與設定應能重現相同分布，方便課程比較推薦演算法。產生器應支援均勻、熱門商品偏斜、時段偏好、會員口味群組、冷啟動、新品、售罄及異常重複訂單等教學情境。

產生器必須遵守以下安全界線：

- 只允許 `admin` 使用，且只能在明確啟用的 development／Lab 環境執行；production 預設拒絕。
- 使用明顯虛構的姓名與保留測試網域 Email，不使用或仿造真實顧客個資，也不呼叫外部寄信、通知、付款或第三方服務。
- 設定單次及資料總量上限，顯示處理中、完成、部分失敗與錯誤狀態，避免大量同步寫入拖垮教學環境。
- 寫入前檢查商品、桌號與狀態，必要時使用 transaction；訂單金額與明細依資料庫商品價格計算，不任意產生矛盾資料。
- 預設不得消耗正式成品庫存；需要示範庫存變化時，必須使用獨立選項並產生相應庫存異動。
- 支援依批次查看統計、匯出參數及清除該批次。清除前需再次確認，只能刪除帶有同一虛擬批次標記的資料，並依 foreign key 關聯安全移除，不得清空正式資料表。
- 所有產生及清除操作均寫入 `audit_logs`，但不記錄密碼、Session ID 或不必要的完整資料內容。

### AI 教學方向

- 以偏好、預算、庫存及過敏原建立可解釋的規則式推薦基準。
- 以商品標籤和會員偏好進行內容式推薦及餐點搭配。
- 使用 Embedding 與 cosine similarity 實作自然語言語意搜尋。
- 從自然語言抽取冰熱、甜度、咖啡因、風味、預算及飲食限制。
- 以匿名互動與訂單資料示範協同過濾及混合推薦。
- 示範新會員冷啟動、熱門偏誤、多樣性、新品探索與推薦解釋。
- 依人數、預算、偏好與庫存產生組合，但商品、價格、過敏原及庫存必須取自資料庫。
- 示範對話式點餐；AI 只能協助建立購物車，顧客確認前不得送出訂單。
- 以歷史教學資料進行熱門商品、常見搭配、時段趨勢、成品需求預測及異常重複訂單偵測。
- 以匿名 A/B Test 比較規則式、內容式及混合推薦的點擊率、加入購物車率、購買率、接受率與不感興趣比例。
- 商品文案、多語菜單與過敏原問答均須有人工作業或結構化資料驗證，不能直接信任生成內容。

推薦畫面應顯示「為什麼推薦給我」，並提供喜歡、不感興趣、不要再推薦與修改偏好。建議保存推薦方法、候選結果、顯示時間及匿名回饋，以便課程實際評估推薦品質，而不是只展示模型輸出。

### 開發階段

1. **已完成｜需求與資料設計**：示範菜單、價格、AI 標籤、桌號、角色、訂單狀態及資料關聯。
2. **已完成｜商品目錄與管理**：20 項商品、七張分類照片、價格、庫存、上下架及響應式目錄。
3. **已完成｜QR Code 點餐與訂單**：桌號驗證、購物車、後端計價、價格快照、狀態及員工看板。
4. **已完成｜成品庫存**：售出扣減、取消回補、人工調整、異動紀錄、transaction 及超賣防護。
5. **已完成｜會員基礎功能**：獨立顧客帳號、偏好、收藏、歷史訂單、推薦同意及紀錄清除。
6. **已完成｜推薦基準版**：訪客熱門／有庫存推薦，以及會員偏好、收藏、預算與過敏原規則推薦。
7. **已完成｜AI 擴充課程**：Transformers.js `3.8.1` 搭配 `Xenova/all-MiniLM-L6-v2` q8 的真實 Embedding 語意搜尋；協同過濾與混合推薦的資料結構及虛擬事件已備妥，完整比較介面留待第 9 階段。
8. **已完成｜實驗資料產生器**：可重現的虛擬會員、偏好、訂單與推薦互動批次，具數量上限、種子、批次統計及安全清除。
9. **已完成｜分析與評估**：可切換正式／虛擬資料，提供推薦方法曝光、點擊率、接受率、不感興趣、多樣性、熱門商品、近 14 日趨勢、七日平均需求預測、冷啟動與異常訂單候選。
10. **已完成｜整合驗收**：完成 PHP／JavaScript、SQLite、HTTP、權限、安全標頭、404／405、訂單與庫存、會員、虛擬資料、QR Code、AI 降級，以及 Chrome／Edge 桌機與 390px 行動版驗收。此 Windows 環境沒有 Safari，已完成標準相容性檢視，但 Safari 實機仍屬部署後的外部驗收限制。

每一階段都應可獨立展示與驗收；AI 功能失敗時仍須保留一般商品瀏覽、購物車與點餐能力。

## 快取版本

品牌前台 CSS 使用 `?v=2026083002`，JavaScript 使用 `?v=2026083001`；系統入口 `portal.css` 與分類版面 `portal-groups.css` 使用 `?v=2026083001`。每次修改對應檔案時，將末兩碼數字加一並同步修改引用該檔案的 HTML。

MIS 共用 CSS／JS 與留言 JS 目前使用 `?v=2026083001`；各檔案分別遞增版本。

## 文字 AI 實驗室

開啟 `http://localhost/<專案目錄>/lab_hugging/`，再進入十二個獨立 Browser AI 文字實驗。新增的 Sentence Similarity 會比較兩個 Embedding 的 cosine similarity；Feature Extraction / Embedding 會顯示 384 維向量摘要與完整 JSON。首次載入各模型需要網路並下載 ONNX 模型；後續通常會使用 Browser Cache。詳細模型、來源及測試紀錄請參閱 `lab_hugging/hugging.md`。

Lab CSS 為 `?v=2026083003`，共用與各實驗 JS 為 `?v=2026083001`。

## Python Server AI 實驗室

開啟 `http://localhost/<專案目錄>/lab_hugging_python/`。此目錄不取代原 Browser AI；前端透過 `fetch()` 呼叫 `http://127.0.0.1:5000/api/v1`，模型首次呼叫時載入並留在 Python Process 重用。建議另裝 Python 3.11，再依 `lab_hugging_python/hugging_python.md` 建立虛擬環境、安裝固定版本套件並啟動 Flask API。

Python Lab CSS、共用 API JS 與各任務 JS 初版均為 `?v=2026083001`。

## 圖片來源

`web/assets/images/ww-coffee-hero.webp` 由 OpenAI 內建 ImageGen 於 2026-08-30 生成並轉為 WebP，用途為網站主視覺；不依賴外部載入。

`mis_shop/assets/images/` 的咖啡、茶飲、非咖啡、點心、輕食、咖啡商品與紀念品七張分類照片由 OpenAI 內建 ImageGen 於 2026-09-06 生成，轉為 900px WebP；用途為課程商品目錄，不依賴外部圖片 URL。

## 瀏覽器

以最新版 Chrome 為主要目標，並採用現代 Edge、Safari 支援的標準 HTML/CSS/JavaScript。若瀏覽器不支援 IntersectionObserver，內容會直接顯示。

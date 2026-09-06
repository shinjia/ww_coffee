# mis_shop 商品、QR 點餐、會員與 AI 推薦

## 用途與完成範圍

教學用商品與點餐子系統，已完成階段 1 至 10：20 項含價格商品、七張分類照片、公開目錄、購物車、QR Code 桌號點餐、訂單、成品庫存、會員、偏好與收藏、規則／內容／混合推薦、真實 Embedding 語意搜尋、development 限定的虛擬資料產生器、分析評估及整合驗收。商品及價格均為課程示範，不代表正式菜單。

不包含原料、配方、供應商、採購、金流、電子發票與外送平台。分析頁提供教學用 A/B 指標、趨勢、簡單預測、冷啟動、異常與多樣性；虛擬資料預設排除，不得解讀為真實營運成效。

## 入口

- `index.php`：公開商品目錄、推薦與購物車。
- `member.php`：顧客會員註冊、登入、偏好、收藏與訂單。
- `ai-lab.php`：Transformers.js Embedding 語意搜尋。
- `admin.php`：員工訂單看板；管理者商品、價格、庫存與 QR Code。
- `generator.php`：僅 development 管理者可使用；整批建立員工、意見留言、會員、訂單與推薦互動。
- `analytics.php`：登入後使用的推薦與營運分析評估。

## 權限與安全

- 訪客可瀏覽及點餐，會員功能不強迫登入。
- 顧客帳號使用獨立 `shop_customers`，不能登入員工 MIS。
- `employee` 可處理訂單；`admin` 可管理商品、庫存及虛擬資料。
- 後端重算價格、驗證桌號／狀態／庫存，訂單與扣庫存使用 transaction。
- 取消訂單只回補一次；歷史明細保存商品名稱與價格快照。
- 過敏原由結構化欄位強制處理，語意分數不代表過敏安全。
- 虛擬資料使用 `example.invalid`、`is_synthetic` 與批次 ID，不寄信、不付款、不扣正式庫存；只能依批次清除。員工與會員密碼為帳號加 `123`，只保存雜湊。

## AI 與外部來源

- 語意搜尋固定使用 Transformers.js `3.8.1` 及 `Xenova/all-MiniLM-L6-v2` q8，來源為 jsDelivr 與 Hugging Face Hub。
- QR Code 使用 npm `qrcodejs` `1.0.0`，已保存為本地 `assets/qrcode.min.js`，執行時不依賴 CDN。
- 外部資源載入失敗時顯示明確狀態；一般目錄、規則推薦、網址及點餐仍可使用。
- 七張分類商品照由 OpenAI 內建 ImageGen 於 2026-09-06 生成，轉為 900px WebP，無外部圖片相依。

## 資料

使用共用 SQLite 的 `shop_categories`、`shop_products`、`shop_tables`、`shop_customers`、`shop_preferences`、`shop_favorites`、`shop_orders`、`shop_order_items`、`shop_stock_movements`、`shop_recommendation_events` 與 `shop_synthetic_batches`。

## 快取版本

- `shop.css`、`shop-responsive.css`、`shop.js`、`admin-qr.js`、`qrcode.min.js`：`?v=2026083001`。
- `ai-recommend.js`：`?v=2026083002`。
- `analytics.css`：`?v=2026083001`。

## 驗收結果

- PHP 7.4 與全部 JavaScript 語法通過；SQLite migration、20 項 seed 與 foreign keys 正常。
- 訂單扣庫存、取消回補、會員、虛擬批次建立與依批次清除均完成實測，測試資料已移除。
- Chrome 與 Edge 於 1440×900、390×844 均無水平溢出或 Console 錯誤；20 項商品、預算推薦、五個 QR Code、管理與分析頁正常。
- 靜態缺檔回傳 404、訂單端點 GET 回傳 405、未登入產生器導向登入；CSP、nosniff、frame protection 及 no-store 標頭存在。
- 此 Windows 環境無 Safari；已採用現代標準並檢視相容性，Safari 實機需在具備該瀏覽器的外部環境複驗。

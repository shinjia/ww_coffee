# lab_hugging 文字 AI 實驗室

## 目的

使用原生 HTML、CSS、JavaScript 與 Hugging Face Transformers.js，建立可直接在瀏覽器執行、可觀察與比較的文字 NLP 教學實驗。

## 實驗與獨立檔案

| Lab | 任務 | Pipeline | HTML | 專用 JS |
| --- | --- | --- | --- | --- |
| 01 | 情緒分析 | `sentiment-analysis` | `sentiment.html` | `sentiment.js` |
| 02 | 零樣本分類 | `zero-shot-classification` | `zero-shot.html` | `zero-shot.js` |
| 03 | 命名實體辨識 | `token-classification` | `ner.html` | `ner.js` |
| 04 | 擷取式問答 | `question-answering` | `qa.html` | `qa.js` |
| 05 | 文字摘要 | `summarization` | `summarization.html` | `summarization.js` |
| 06 | 文字生成 | `text-generation` | `generation.html` | `generation.js` |
| 07 | 英翻中 | `translation` | `translation-en-zh.html` | `translation-en-zh.js` |
| 08 | 中翻英 | `translation` | `translation-zh-en.html` | `translation-zh-en.js` |
| 09 | 遮罩填詞 | `fill-mask` | `fill-mask.html` | `fill-mask.js` |
| 10 | Text2Text | `text2text-generation` | `text2text.html` | `text2text.js` |
| 11 | Sentence Similarity | `feature-extraction` + cosine similarity | `sentence-similarity.html` | `sentence-similarity.js` |
| 12 | Feature Extraction / Embedding | `feature-extraction` | `feature-extraction.html` | `feature-extraction.js` |

`index.html` 只作實驗入口。`runtime.js` 只共用 CDN 載入、進度、狀態及 Raw JSON，不混合各任務的輸入與結果邏輯。

## 外部來源

- Transformers.js `3.8.1`
- CDN：`https://cdn.jsdelivr.net/npm/@huggingface/transformers@3.8.1`
- 用途：在瀏覽器載入 Pipeline、Tokenizer、ONNX Runtime 與模型推論功能。
- 模型來源：Hugging Face Hub。
- CDN 或模型載入失敗時，畫面會顯示錯誤；Console 保留完整錯誤。

## 模型研究紀錄

### 各獨立實驗模型

| 任務 | Model ID | 語言／用途 | Browser 狀態 |
| --- | --- | --- | --- |
| Zero-shot | `Xenova/mobilebert-uncased-mnli` | 英文自訂標籤分類 | 頁面完成，模型待實測 |
| NER | `Xenova/bert-base-NER` | 英文人名／組織／地點 | 頁面完成，模型待實測 |
| QA | `Xenova/distilbert-base-cased-distilled-squad` | 英文擷取式問答 | WASM q8 實測成功 |
| Summarization | `Xenova/distilbart-cnn-6-6` | 英文摘要，模型較大 | 頁面完成，模型待實測 |
| Generation | `Xenova/distilgpt2` | 英文續寫 | 頁面完成，模型待實測 |
| EN → ZH | `Xenova/opus-mt-en-zh` | 英翻中 | 頁面完成，模型待實測 |
| ZH → EN | `Xenova/opus-mt-zh-en` | 中翻英 | 頁面完成，模型待實測 |
| Fill-Mask | `Xenova/bert-base-multilingual-cased` | 多語遮罩預測 | 頁面完成，模型待實測 |
| Text2Text | `Xenova/flan-t5-small` | 英文指令式文字轉換 | 頁面完成，模型待實測 |
| Sentence Similarity | `Xenova/all-MiniLM-L6-v2` | 英文句向量與 cosine similarity | WASM q8 實測成功 |
| Feature Extraction / Embedding | `Xenova/all-MiniLM-L6-v2` | 英文文字轉 384 維正規化向量 | WASM q8 實測成功 |

所有模型均已查核 Hugging Face 模型頁含 Transformers.js 使用方式或官方 Pipeline 範例，且具 ONNX 權重。生成式模型採 q8；Text2Text 採 q4 以降低負擔。實際檔案大小依模型與量化檔案而異。

### Xenova/all-MiniLM-L6-v2

- Task：Feature Extraction / Sentence Embedding
- Languages：以英文為主，其他語言結果不應視為可靠
- Output：mean pooling、normalize 後為 384 維向量
- Sentence Similarity：以兩句文字的真實模型向量計算 cosine similarity
- Transformers.js：模型頁提供 `feature-extraction` Pipeline 用法，具 ONNX q8 權重
- Browser Tested：2026-08-30 於 Codex 內建 Chrome 完成真實推論
- WASM：q8 模型下載、Browser Cache 再載入與推論成功
- WebGPU：本頁目前未提供選項
- 備註：相似度是模型估計，不可當作客觀或人工審核結論。

### Xenova/bert-base-multilingual-uncased-sentiment

- Task：Sentiment Analysis / Text Classification
- Languages：中文、英文及多語言
- Approx. Size：依瀏覽器取得的 q8 ONNX 檔案為準
- Transformers.js：具 ONNX 權重，供 Transformers.js 使用
- Browser Tested：多語模型尚未完成真實模型下載驗證
- WASM：已納入載入流程，多語模型待驗證
- WebGPU：已納入可選流程，待支援裝置驗證
- 中文：五星分類，待實測
- 英文：五星分類，待實測
- 中英混合：待實測
- 備註：輸出尺度為 1–5 星，不是單純正負二分類。

### Xenova/distilbert-base-uncased-finetuned-sst-2-english

- Task：Sentiment Analysis / Text Classification
- Languages：英文
- Approx. Size：模型庫包含多種 ONNX 權重，頁面指定 q8
- Transformers.js：官方模型頁提供 Transformers.js 使用方式
- Browser Tested：2026-08-30 於 Codex 內建瀏覽器完成
- WASM：q8 模型首次下載、載入與真實推論成功
- WebGPU：已納入可選流程，待支援裝置驗證
- 中文：不建議，模型以英文任務為主
- 英文：正面／負面二分類，已以正面案例完成實測
- 中英混合：輸出不可視為可靠
- 備註：適合與多語模型比較語言及分類尺度差異。

## 使用方式

1. 由 HTTP 伺服器開啟 `http://localhost/ww_coffee/lab_hugging/`。
2. 從入口選擇一個獨立實驗。
3. 按「載入模型」，等待進度顯示完成。
4. 填寫該任務專用輸入，再執行 AI。

首次使用需從 jsDelivr 與 Hugging Face Hub 下載外部檔案，後續通常使用 Browser Cache。請勿直接使用 `file://` 開啟。

## 快取版本

- 共用 `css/style.css?v=2026083003`
- `js/runtime.js?v=2026083001`
- 各實驗專用 JS：`?v=2026083001`
- Transformers.js CDN 查詢版本：`?v=2026083001`

## 已知限制與下一步

- 已完成英文模型首次下載、真實推論、模型切換停用舊 Pipeline，以及第二次快取載入（約 0.9 秒）。
- QA 模型已完成首次下載與真實推論，正確從 Context 擷取 `a free dessert`，信心度 54.0%。
- 十二個獨立實驗頁均完成載入與專用欄位檢查；Sentiment、QA、Sentence Similarity 與 Feature Extraction 已完成真實推論，其餘模型尚待逐一下載驗證。
- 已完成 390px 手機寬度無橫向溢出檢查；尚未在已連接 Chrome／Edge 的環境驗證 WebGPU 與手機實機記憶體。
- Console 有一則 jsDelivr 未提供 content-length 的 Transformers.js 警告，函式庫會自動擴充緩衝區，不影響推論。
- Safari 尚未驗證，WebGPU 選項會依 `navigator.gpu` 偵測。
- 下一步為逐一完成其餘模型的首次下載、真實推論與 Chrome／Edge／Safari 人工驗收。

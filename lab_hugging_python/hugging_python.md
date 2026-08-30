# Python Server AI 文字實驗室

本目錄是獨立的 Server AI 教學環境，不取代或修改 `../lab_hugging/`。原生 HTML／CSS／JS 以 `fetch()` 呼叫 Flask；模型第一次請求才載入，之後留在 Python Process 記憶體重用。

## 安裝與啟動

建議 Python 3.11。目前已建立 Python 3.11.9 的 `.venv`；套件尚未安裝，需先執行下列 `pip install` 指令。系統原有 Python 3.8.5 不用於此專案。

```powershell
cd D:\xampp\htdocs\ww_coffee\lab_hugging_python
C:\Path\To\Python311\python.exe -m venv .venv
.\.venv\Scripts\python.exe -m pip install --upgrade pip
.\.venv\Scripts\python.exe -m pip install -r api\requirements.txt
.\.venv\Scripts\python.exe api\app.py
```

開啟 `http://localhost/ww_coffee/lab_hugging_python/`。API 位於 `http://127.0.0.1:5000/api/v1`，健康檢查為 `/health`。首次模型下載需要網路，快取放在 `api/storage/models/`。

## 任務

12 個 Endpoint：`sentiment`、`zero-shot`、`ner`、`qa`、`summarize`、`generate`、`translate-en-zh`、`translate-zh-en`、`fill-mask`、`text2text`、`similarity`、`embedding`。每個任務均有獨立 HTML、JS 與 `api/tasks/*.py`。

模型來源與用途記錄於各頁及 Python 模組；套件版本固定在 `api/requirements.txt`。使用 Hugging Face Transformers、PyTorch、Sentence Transformers、Flask、SentencePiece 與 Sacremoses。載入失敗時 API 回傳一般化錯誤，完整例外只留 Server Log。

## 安全與效能

- 前後端皆驗證空值、型別、長度與允許範圍；Request Body 上限 64 KB。
- CORS 僅允許 `config.json` 設定的 localhost Origin，不含任何 API Key 或 Token。
- API 回傳 `model_cached`、`load_ms`、`inference_ms`、`total_ms`。
- Model Manager 使用每模型 Lock，避免同一模型首次請求時重複載入。
- 正式部署前需增加 HTTPS、反向代理、驗證、Rate Limit、Process Manager 與監控。

## 版本與測試

- CSS、共用 API JS 與任務 JS：`?v=2026083001`。
- 測試：`.\.venv\Scripts\python.exe -m unittest discover api\tests`
- 已確認 `.venv` 使用 Python 3.11.9；Flask、Transformers、PyTorch 與 Sentence Transformers 尚未安裝，因此 API 測試與真實模型推論尚未執行成功。

import time
from common import ApiInputError, require_text

MODEL = "bert-base-multilingual-cased"

def run(payload, manager):
    text = require_text(payload, max_length=1500)
    if text.count("[MASK]") != 1:
        raise ApiInputError("文字必須包含且只能包含一個 [MASK]。")
    pipe, cached, load_ms = manager.get_pipeline("fill-mask", MODEL)
    started = time.perf_counter()
    result = pipe(text, top_k=5)
    return result, {"task": "fill-mask", "model": MODEL, "model_cached": cached, "load_ms": round(load_ms, 2), "inference_ms": round((time.perf_counter()-started)*1000, 2)}

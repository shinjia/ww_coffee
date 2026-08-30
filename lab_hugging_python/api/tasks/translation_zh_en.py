import time
from common import require_text

MODEL = "Helsinki-NLP/opus-mt-zh-en"

def run(payload, manager):
    text = require_text(payload, max_length=2500)
    pipe, cached, load_ms = manager.get_pipeline("translation", MODEL)
    started = time.perf_counter()
    result = pipe(text, max_new_tokens=160)
    return result, {"task": "translate-zh-en", "model": MODEL, "model_cached": cached, "load_ms": round(load_ms, 2), "inference_ms": round((time.perf_counter()-started)*1000, 2)}

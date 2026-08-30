import time
from common import require_text

MODEL = "distilbert-base-uncased-finetuned-sst-2-english"

def run(payload, manager):
    text = require_text(payload, max_length=1000)
    pipe, cached, load_ms = manager.get_pipeline("sentiment-analysis", MODEL)
    started = time.perf_counter()
    result = pipe(text, top_k=None)
    return result, {"task": "sentiment", "model": MODEL, "model_cached": cached, "load_ms": round(load_ms, 2), "inference_ms": round((time.perf_counter()-started)*1000, 2)}

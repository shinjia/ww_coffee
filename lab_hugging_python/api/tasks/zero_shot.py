import time
from common import require_list, require_text

MODEL = "typeform/distilbert-base-uncased-mnli"

def run(payload, manager):
    text = require_text(payload, max_length=1500)
    labels = require_list(payload, "labels", 2, 10)
    pipe, cached, load_ms = manager.get_pipeline("zero-shot-classification", MODEL)
    started = time.perf_counter()
    result = pipe(text, labels)
    return result, {"task": "zero-shot", "model": MODEL, "model_cached": cached, "load_ms": round(load_ms, 2), "inference_ms": round((time.perf_counter()-started)*1000, 2)}

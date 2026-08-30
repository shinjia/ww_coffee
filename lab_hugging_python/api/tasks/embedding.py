import time
from common import require_text
MODEL="sentence-transformers/all-MiniLM-L6-v2"
def run(payload,manager):
 text=require_text(payload,max_length=1500);model,cached,load=manager.get_sentence_model(MODEL);started=time.perf_counter();vector=model.encode([text],normalize_embeddings=True)[0].tolist();return {"embedding":vector,"dimensions":len(vector)},{"task":"embedding","model":MODEL,"model_cached":cached,"load_ms":round(load,2),"inference_ms":round((time.perf_counter()-started)*1000,2)}

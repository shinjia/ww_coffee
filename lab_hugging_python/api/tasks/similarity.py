import time
from common import require_text
MODEL="sentence-transformers/all-MiniLM-L6-v2"
def run(payload,manager):
 a=require_text(payload,"sentence_a",1000);b=require_text(payload,"sentence_b",1000);model,cached,load=manager.get_sentence_model(MODEL);started=time.perf_counter();vectors=model.encode([a,b],normalize_embeddings=True);data={"similarity":float(vectors[0].dot(vectors[1])),"dimensions":int(len(vectors[0]))};return data,{"task":"similarity","model":MODEL,"model_cached":cached,"load_ms":round(load,2),"inference_ms":round((time.perf_counter()-started)*1000,2)}

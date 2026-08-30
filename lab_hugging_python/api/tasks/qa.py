import time
from common import require_text
MODEL="distilbert-base-cased-distilled-squad"
def run(payload,manager):
 context=require_text(payload,"context",5000);question=require_text(payload,"question",500);pipe,cached,load=manager.get_pipeline("question-answering",MODEL);started=time.perf_counter();data=pipe(question=question,context=context);return data,{"task":"qa","model":MODEL,"model_cached":cached,"load_ms":round(load,2),"inference_ms":round((time.perf_counter()-started)*1000,2)}

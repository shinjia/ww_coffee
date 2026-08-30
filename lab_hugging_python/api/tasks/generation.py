import time
from common import bounded_int,require_text
MODEL="distilgpt2"
def run(payload,manager):
 text=require_text(payload,max_length=1500);tokens=bounded_int(payload,"max_new_tokens",40,5,100);pipe,cached,load=manager.get_pipeline("text-generation",MODEL);started=time.perf_counter();data=pipe(text,max_new_tokens=tokens,do_sample=True,temperature=.8,pad_token_id=50256);return data,{"task":"generate","model":MODEL,"model_cached":cached,"load_ms":round(load,2),"inference_ms":round((time.perf_counter()-started)*1000,2)}

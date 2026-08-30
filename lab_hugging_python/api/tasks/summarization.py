import time
from common import require_text
MODEL="sshleifer/distilbart-cnn-6-6"
def run(payload,manager):
 text=require_text(payload,max_length=6000);pipe,cached,load=manager.get_pipeline("summarization",MODEL);started=time.perf_counter();data=pipe(text,max_new_tokens=100,truncation=True);return data,{"task":"summarize","model":MODEL,"model_cached":cached,"load_ms":round(load,2),"inference_ms":round((time.perf_counter()-started)*1000,2)}

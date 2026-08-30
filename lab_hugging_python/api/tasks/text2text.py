import time
from common import require_text
MODEL="google/flan-t5-small"
def run(payload,manager):
 instruction=require_text(payload,"instruction",300);text=require_text(payload,"text",2500);pipe,cached,load=manager.get_pipeline("text2text-generation",MODEL);started=time.perf_counter();data=pipe("%s: %s"%(instruction,text),max_new_tokens=120);return data,{"task":"text2text","model":MODEL,"model_cached":cached,"load_ms":round(load,2),"inference_ms":round((time.perf_counter()-started)*1000,2)}

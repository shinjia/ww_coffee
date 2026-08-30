import time
from common import require_text
MODEL="dslim/bert-base-NER"
def run(payload,manager):
 text=require_text(payload,max_length=2000);pipe,cached,load=manager.get_pipeline("token-classification",MODEL,aggregation_strategy="simple");started=time.perf_counter();data=pipe(text);return data,{"task":"ner","model":MODEL,"model_cached":cached,"load_ms":round(load,2),"inference_ms":round((time.perf_counter()-started)*1000,2)}

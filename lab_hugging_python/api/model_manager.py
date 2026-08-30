import threading,time
from sentence_transformers import SentenceTransformer
from transformers import pipeline
class ModelManager:
 def __init__(self,cache_dir):self.cache_dir=cache_dir;self._models={};self._locks={};self._guard=threading.Lock()
 def _lock(self,key):
  with self._guard:return self._locks.setdefault(key,threading.Lock())
 def get_pipeline(self,task,model,**options):
  key="pipeline:%s:%s"%(task,model)
  with self._lock(key):
   if key in self._models:return self._models[key],True,0.0
   started=time.perf_counter();instance=pipeline(task,model=model,model_kwargs={"cache_dir":self.cache_dir},**options);elapsed=(time.perf_counter()-started)*1000;self._models[key]=instance;return instance,False,elapsed
 def get_sentence_model(self,model):
  key="sentence:%s"%model
  with self._lock(key):
   if key in self._models:return self._models[key],True,0.0
   started=time.perf_counter();instance=SentenceTransformer(model,cache_folder=self.cache_dir);elapsed=(time.perf_counter()-started)*1000;self._models[key]=instance;return instance,False,elapsed
 def status(self):return sorted(self._models.keys())

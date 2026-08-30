import json,os,time
from flask import Flask,jsonify,request
from common import ApiInputError
from model_manager import ModelManager
from tasks import TASKS
BASE_DIR=os.path.dirname(os.path.abspath(__file__))
with open(os.path.join(BASE_DIR,"config.json"),encoding="utf-8") as f:CONFIG=json.load(f)
manager=ModelManager(os.path.join(BASE_DIR,CONFIG["model_cache_dir"]));os.makedirs(manager.cache_dir,exist_ok=True)
app=Flask(__name__);app.config["MAX_CONTENT_LENGTH"]=65536
def allowed(origin):return not origin or any(origin==x or origin.startswith(x+":") for x in CONFIG["allowed_origins"])
@app.after_request
def headers(response):
 origin=request.headers.get("Origin","")
 if origin and allowed(origin):response.headers["Access-Control-Allow-Origin"]=origin;response.headers["Vary"]="Origin";response.headers["Access-Control-Allow-Headers"]="Content-Type";response.headers["Access-Control-Allow-Methods"]="GET, POST, OPTIONS"
 response.headers["X-Content-Type-Options"]="nosniff";response.headers["Cache-Control"]="no-store";return response
@app.before_request
def origin_check():
 if request.method=="OPTIONS":return("",204)
 if not allowed(request.headers.get("Origin","")):return jsonify({"ok":False,"error":{"code":"ORIGIN_DENIED","message":"不允許的來源。"}}),403
@app.get("/api/v1/health")
def health():return jsonify({"ok":True,"data":{"service":"WW Coffee Python AI","loaded_models":manager.status()}})
@app.post("/api/v1/<task_name>")
def execute(task_name):
 handler=TASKS.get(task_name)
 if not handler:return jsonify({"ok":False,"error":{"code":"NOT_FOUND","message":"找不到指定任務。"}}),404
 payload=request.get_json(silent=True)
 if not isinstance(payload,dict):return jsonify({"ok":False,"error":{"code":"INVALID_JSON","message":"請傳送 JSON 物件。"}}),400
 started=time.perf_counter()
 try:
  data,meta=handler(payload,manager);meta["total_ms"]=round((time.perf_counter()-started)*1000,2);return jsonify({"ok":True,"data":data,"meta":meta})
 except ApiInputError as error:return jsonify({"ok":False,"error":{"code":"INVALID_INPUT","message":str(error)}}),422
 except Exception:app.logger.exception("AI task failed: %s",task_name);return jsonify({"ok":False,"error":{"code":"INFERENCE_FAILED","message":"模型執行失敗，請稍後再試或縮短輸入。"}}),500
if __name__=="__main__":app.run(host=CONFIG["host"],port=int(CONFIG["port"]),debug=bool(CONFIG["debug"]),threaded=True)

class ApiInputError(ValueError): pass
def require_text(payload,key="text",max_length=2000):
 value=payload.get(key)
 if not isinstance(value,str) or not value.strip(): raise ApiInputError("欄位 %s 不可為空。"%key)
 value=value.strip()
 if len(value)>max_length: raise ApiInputError("欄位 %s 不可超過 %d 字。"%(key,max_length))
 return value
def require_list(payload,key,minimum=1,maximum=20):
 value=payload.get(key)
 if not isinstance(value,list): raise ApiInputError("欄位 %s 必須是陣列。"%key)
 value=[str(x).strip() for x in value if str(x).strip()]
 if not minimum<=len(value)<=maximum: raise ApiInputError("欄位 %s 數量錯誤。"%key)
 return value
def bounded_int(payload,key,default,minimum,maximum):
 try:value=int(payload.get(key,default))
 except(TypeError,ValueError):raise ApiInputError("欄位 %s 必須是整數。"%key)
 if not minimum<=value<=maximum:raise ApiInputError("欄位 %s 超出允許範圍。"%key)
 return value

import os,sys,unittest
from unittest.mock import patch
API_DIR=os.path.dirname(os.path.dirname(os.path.abspath(__file__)));sys.path.insert(0,API_DIR)
import app as api_app
class ApiTest(unittest.TestCase):
 def setUp(self):self.client=api_app.app.test_client()
 def test_health(self):self.assertEqual(self.client.get('/api/v1/health').status_code,200)
 def test_invalid_json(self):self.assertEqual(self.client.post('/api/v1/sentiment',data='bad',content_type='application/json').status_code,400)
 def test_unknown_task(self):self.assertEqual(self.client.post('/api/v1/unknown',json={}).status_code,404)
 def test_mock_inference(self):
  meta={"task":"sentiment","model":"test","model_cached":True,"load_ms":0,"inference_ms":1}
  with patch.dict(api_app.TASKS,{"sentiment":lambda payload,manager:([{"label":"POSITIVE","score":.99}],meta)}):response=self.client.post('/api/v1/sentiment',json={"text":"Good."})
  self.assertEqual(response.status_code,200);self.assertTrue(response.get_json()['ok'])
if __name__=='__main__':unittest.main()

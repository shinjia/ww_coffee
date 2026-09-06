<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/mis_shared/bootstrap.php';
require_once __DIR__ . '/lib.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { mis_json_response(false, '僅接受 GET 請求。', 405); }
try {
    $products=shop_products();$customer=shop_customer();$personal=shop_recommendations($customer,$products,count($products));
    $popularity=array();foreach(mis_db()->query("SELECT oi.product_id,SUM(oi.quantity) qty FROM shop_order_items oi JOIN shop_orders o ON o.id=oi.order_id WHERE o.status IN ('ready','completed') GROUP BY oi.product_id")->fetchAll() as $row){$popularity[(int)$row['product_id']]=(int)$row['qty'];}
    $max=$popularity?max($popularity):1;$ranked=array();
    foreach($personal as $p){if((int)$p['stock']<1){continue;}$content=max(0,(float)$p['_score']);$collaborative=isset($popularity[(int)$p['id']])?$popularity[(int)$p['id']]/max(1,$max):0;$score=$content+($collaborative*3);$ranked[]=array('id'=>(int)$p['id'],'name'=>$p['name'],'price'=>(int)$p['price'],'image'=>$p['image_path'],'score'=>$score,'reason'=>$p['_reason'].($collaborative>0?'，並參考匿名完成訂單的熱門程度':''));}
    usort($ranked,function($a,$b){return $b['score']<=>$a['score'];});mis_json_response(true,'混合推薦完成。',200,array('items'=>array_slice($ranked,0,4),'customer_based'=>$customer!==null));
}catch(Throwable $e){error_log('Hybrid recommendation failure: '.$e->getMessage());mis_json_response(false,'目前無法產生混合推薦。',500);}


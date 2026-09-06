<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/mis_shared/bootstrap.php';
require_once __DIR__ . '/lib.php';
mis_require_login('admin');
$config = mis_load_config();
if (($config['environment'] ?? 'production') !== 'development' || empty($config['shop_synthetic_generator_enabled'])) {
    http_response_code(403); mis_render_header('功能未啟用', 'shop');
    echo '<main class="panel"><h1>虛擬資料產生器未啟用</h1></main>'; mis_render_footer(); exit;
}
function generator_int(string $key, int $min, int $max): int {
    $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
    if ($value === false || $value === null || $value < $min || $value > $max) throw new InvalidArgumentException($key . ' 超過允許範圍。');
    return (int) $value;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!mis_verify_csrf($_POST['csrf_token'] ?? null)) throw new RuntimeException('頁面已逾時。');
        $action = mis_post_string('action', 20); $pdo = mis_db(); $user = mis_current_user();
        if ($action === 'generate') {
            $ec = generator_int('employee_count', 0, 100); $fc = generator_int('feedback_count', 0, 1000);
            $cc = generator_int('customer_count', 0, 200); $oc = generator_int('order_count', 0, 1000);
            $rc = generator_int('event_count', 0, 5000); $days = generator_int('days', 1, 365); $seed = generator_int('seed', 1, 2147483646);
            if (($oc || $rc) && !$cc) throw new InvalidArgumentException('產生訂單或推薦互動時，會員數至少為 1。');
            if (!$ec && !$fc && !$cc && !$oc && !$rc) throw new InvalidArgumentException('至少指定一種虛擬資料。');
            mt_srand($seed); $code = 'SYN' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4)); $now = date('Y-m-d H:i:s');
            $params = json_encode(array('employee_count'=>$ec,'feedback_count'=>$fc,'customer_count'=>$cc,'order_count'=>$oc,'event_count'=>$rc,'days'=>$days), JSON_UNESCAPED_UNICODE);
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO shop_synthetic_batches (batch_code,seed,parameters,employee_count,feedback_count,customer_count,order_count,event_count,status,created_by,created_at) VALUES (?,?,?,?,?,?,?,?,\'running\',?,?)');
            $stmt->execute(array($code,$seed,$params,$ec,$fc,$cc,$oc,$rc,$user['id'],$now)); $bid = (int)$pdo->lastInsertId();

            $names = array('教學店員','虛擬吧檯員','測試外場員','課程人員'); $deps = array('門市營運','吧檯','顧客服務','教學示範');
            $us = $pdo->prepare('INSERT INTO users (username,password_hash,display_name,role,active,is_synthetic,synthetic_batch_id,created_at,updated_at) VALUES (?,?,?,\'employee\',1,1,?,?,?)');
            $es = $pdo->prepare('INSERT INTO employees (user_id,employee_no,name,department,title,email,status,is_synthetic,synthetic_batch_id,created_at,updated_at) VALUES (?,?,?,?,?,?,\'active\',1,?,?,?)');
            for ($i=1; $i<=$ec; $i++) {
                $account='syn'.$bid.'e'.str_pad((string)$i,3,'0',STR_PAD_LEFT); $name=$names[$i%count($names)].$i; $created=date('Y-m-d H:i:s',time()-mt_rand(0,$days*86400));
                $us->execute(array($account,password_hash($account.'123',PASSWORD_DEFAULT),$name,$bid,$created,$created)); $uid=(int)$pdo->lastInsertId();
                $no='V'.str_pad((string)$bid,6,'0',STR_PAD_LEFT).'-'.str_pad((string)$i,3,'0',STR_PAD_LEFT);
                $es->execute(array($uid,$no,$name,$deps[$i%count($deps)],'教學人員',$account.'@example.invalid',$bid,$created,$created));
            }

            $fn=array('虛擬顧客','教學訪客','測試顧客','課程學員'); $cats=array('service','product','environment','other'); $states=array('new','processing','closed');
            $msgs=array('服務流程清楚，作為課程示範資料。','希望增加更多低咖啡因選項。','商品目錄容易閱讀。','這是一筆虛擬意見留言。');
            $fs=$pdo->prepare('INSERT INTO feedback (reference_no,name,email,category,message,status,ip_hash,is_synthetic,synthetic_batch_id,created_at,updated_at) VALUES (?,?,?,?,?,?,?,1,?,?,?)');
            for ($i=1; $i<=$fc; $i++) { $created=date('Y-m-d H:i:s',time()-mt_rand(0,$days*86400)); $fs->execute(array('FBSYN'.$bid.str_pad((string)$i,5,'0',STR_PAD_LEFT),$fn[$i%4].$i,'feedback.'.$bid.'.'.$i.'@example.invalid',$cats[$i%4],$msgs[$i%4],$states[$i%3],hash('sha256','synthetic-'.$bid.'-'.$i),$bid,$created,$created)); }

            $mn=array('測試會員','咖啡學員','茶飲學員','下午茶學員'); $fl=array('果香','花香','堅果','巧克力','茶香'); $tx=array('清爽','滑順','濃郁','酥脆'); $all=array('','奶類','蛋','堅果'); $cids=array();
            $cs=$pdo->prepare('INSERT INTO shop_customers (email,password_hash,display_name,active,personalization_consent,is_synthetic,synthetic_batch_id,created_at,updated_at) VALUES (?,?,?,1,1,1,?,?,?)');
            $ps=$pdo->prepare('INSERT INTO shop_preferences (customer_id,categories,caffeine,flavors,texture,temperature,sweetness,budget,avoid_allergens,occasions,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            for ($i=1; $i<=$cc; $i++) { $created=date('Y-m-d H:i:s',time()-mt_rand(0,$days*86400)); $email='syn'.$bid.'m'.$i.'@example.invalid'; $cs->execute(array($email,password_hash($email.'123',PASSWORD_DEFAULT),$mn[$i%4].$i,$bid,$created,$created)); $cid=(int)$pdo->lastInsertId(); $cids[]=$cid; $ps->execute(array($cid,$i%2?'咖啡,點心':'茶飲,非咖啡',array('any','low','medium','high')[$i%4],$fl[$i%5],$tx[$i%4],$i%3===0?'hot':'any','any',array(100,150,200,500)[$i%4],$all[$i%4],array('早餐','提神','下午茶','放鬆')[$i%4],$created)); }

            $products=$pdo->query('SELECT id,name,price FROM shop_products WHERE active=1')->fetchAll(); $statuses=array('received','preparing','ready','completed','completed','cancelled');
            $os=$pdo->prepare('INSERT INTO shop_orders (reference_no,customer_id,table_code,service_type,pickup_name,total,status,is_synthetic,synthetic_batch_id,created_at,updated_at) VALUES (?,?,?,?,?,?,?,1,?,?,?)');
            $is=$pdo->prepare('INSERT INTO shop_order_items (order_id,product_id,product_name,unit_price,quantity,line_total) VALUES (?,?,?,?,?,?)');
            for ($i=1; $i<=$oc; $i++) { $items=array(); $total=0; for($j=0;$j<mt_rand(1,3);$j++){ $p=$products[mt_rand(0,count($products)-1)];$q=mt_rand(1,2);$items[]=array($p,$q);$total+=(int)$p['price']*$q; } $created=date('Y-m-d H:i:s',time()-mt_rand(0,$days*86400)); $os->execute(array($code.'-'.str_pad((string)$i,5,'0',STR_PAD_LEFT),$cids[mt_rand(0,count($cids)-1)],'T0'.mt_rand(1,4),mt_rand(0,3)?'dine_in':'takeaway','虛擬顧客',$total,$statuses[mt_rand(0,count($statuses)-1)],$bid,$created,$created)); $oid=(int)$pdo->lastInsertId(); foreach($items as $row)$is->execute(array($oid,$row[0]['id'],$row[0]['name'],$row[0]['price'],$row[1],(int)$row[0]['price']*$row[1])); }
            $rs=$pdo->prepare('INSERT INTO shop_recommendation_events (customer_id,product_id,method,event_type,reason,is_synthetic,synthetic_batch_id,created_at) VALUES (?,?,?,?,?,1,?,?)'); $methods=array('rule','content','semantic','hybrid');$events=array('shown','clicked','favorite','accepted','not_interested');
            for($i=0;$i<$rc;$i++){ $p=$products[mt_rand(0,count($products)-1)];$rs->execute(array($cids[mt_rand(0,count($cids)-1)],$p['id'],$methods[mt_rand(0,3)],$events[mt_rand(0,4)],'虛擬推薦回饋',$bid,date('Y-m-d H:i:s',time()-mt_rand(0,$days*86400)))); }
            $pdo->prepare('UPDATE shop_synthetic_batches SET status=\'completed\' WHERE id=?')->execute(array($bid)); $pdo->commit();
            mis_audit('shop_synthetic_generated','shop_synthetic_batch',(string)$bid,array('employees'=>$ec,'feedback'=>$fc,'customers'=>$cc,'orders'=>$oc,'events'=>$rc,'seed'=>$seed)); mis_flash('success','批次 '.$code.' 已建立；員工與會員密碼為帳號加 123。');
        } elseif ($action === 'delete') {
            $bid=generator_int('batch_id',1,2147483646);$stmt=$pdo->prepare('SELECT batch_code FROM shop_synthetic_batches WHERE id=?');$stmt->execute(array($bid));$code=$stmt->fetchColumn();if($code===false)throw new InvalidArgumentException('找不到批次。');
            $pdo->beginTransaction(); foreach(array('shop_recommendation_events','shop_orders','shop_customers','feedback','employees','users') as $table)$pdo->prepare('DELETE FROM '.$table.' WHERE synthetic_batch_id=? AND is_synthetic=1')->execute(array($bid)); $pdo->prepare('DELETE FROM shop_synthetic_batches WHERE id=?')->execute(array($bid));$pdo->commit();
            mis_audit('shop_synthetic_deleted','shop_synthetic_batch',(string)$bid,array('batch_code'=>$code));mis_flash('success','此批次虛擬資料已安全清除。');
        }
    } catch(Throwable $e) { if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();mis_flash('error',($e instanceof InvalidArgumentException||$e instanceof RuntimeException)?$e->getMessage():'產生器執行失敗。'); }
    header('Location: '.mis_base_url('mis_shop/generator.php'));exit;
}
$batches=mis_db()->query('SELECT b.*,u.display_name FROM shop_synthetic_batches b LEFT JOIN users u ON u.id=b.created_by ORDER BY b.id DESC LIMIT 30')->fetchAll();mis_render_header('虛擬資料產生器','shop');
?>
<main class="app-main"><header class="page-head"><div><p class="eyebrow">DEVELOPMENT LAB</p><h1>虛擬資料產生器</h1><p>整批建立員工、意見留言、會員、訂單及推薦互動；不扣正式庫存、不寄信、不付款。</p></div><a class="secondary-button" href="<?=mis_e(mis_base_url('mis_shop/admin.php'))?>">返回商品後台</a></header>
<section class="panel"><h2>建立新批次</h2><p>各數量可設為 0。員工及會員密碼固定為「帳號＋123」；會員帳號是完整 Email。</p><form method="post" class="form-grid" data-confirm="確定產生這批虛擬資料？"><input type="hidden" name="csrf_token" value="<?=mis_e(mis_csrf_token())?>"><input type="hidden" name="action" value="generate">
<label>員工數（0–100）<input type="number" name="employee_count" min="0" max="100" value="10" required></label><label>意見留言數（0–1000）<input type="number" name="feedback_count" min="0" max="1000" value="50" required></label><label>會員數（0–200）<input type="number" name="customer_count" min="0" max="200" value="20" required></label><label>訂單數（0–1000）<input type="number" name="order_count" min="0" max="1000" value="100" required></label><label>推薦互動數（0–5000）<input type="number" name="event_count" min="0" max="5000" value="300" required></label><label>日期範圍（最近天數）<input type="number" name="days" min="1" max="365" value="60" required></label><label>亂數種子<input type="number" name="seed" min="1" max="2147483646" value="20260906" required></label><div><button class="primary-button">整批產生資料</button></div></form></section>
<section class="panel"><h2>批次紀錄</h2><?php if(!$batches):?><p class="muted">尚無虛擬批次。</p><?php else:?><div class="table-wrap"><table><thead><tr><th>批次</th><th>員工／留言／會員／訂單／互動</th><th>建立資訊</th><th>清除</th></tr></thead><tbody><?php foreach($batches as $b):?><tr><td><?=mis_e($b['batch_code'])?><br><span class="badge"><?=mis_e($b['status'])?></span></td><td><?=(int)$b['employee_count']?>／<?=(int)$b['feedback_count']?>／<?=(int)$b['customer_count']?>／<?=(int)$b['order_count']?>／<?=(int)$b['event_count']?></td><td><?=mis_e($b['display_name']?:'未知')?>／<?=mis_e($b['created_at'])?></td><td><form method="post" data-confirm="只會刪除此批次的虛擬資料，確定繼續？"><input type="hidden" name="csrf_token" value="<?=mis_e(mis_csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="batch_id" value="<?=(int)$b['id']?>"><button class="secondary-button">清除此批次</button></form></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></section></main><?php mis_render_footer();?>

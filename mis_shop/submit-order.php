<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/mis_shared/bootstrap.php';
require_once __DIR__ . '/lib.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { mis_json_response(false, '僅接受 POST 請求。', 405); }
try {
    if (!mis_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) { throw new RuntimeException('頁面已逾時，請重新整理。'); }
    $last = isset($_SESSION['shop_last_order']) ? (int) $_SESSION['shop_last_order'] : 0;
    if (time() - $last < max(2, (int) mis_load_config()['shop_order_rate_limit_seconds'])) { throw new InvalidArgumentException('送出過於頻繁，請稍候再試。'); }
    $tableCode = mis_post_string('table_code', 20);
    $serviceType = mis_post_string('service_type', 20);
    $pickupName = mis_post_string('pickup_name', 40);
    $cartRaw = mis_post_string('cart_json', 12000);
    if (!shop_valid_table($tableCode) || !in_array($serviceType, array('dine_in','takeaway'), true)) { throw new InvalidArgumentException('桌號或點餐方式不正確。'); }
    $cart = json_decode($cartRaw, true);
    if (!is_array($cart) || count($cart) < 1 || count($cart) > 30) { throw new InvalidArgumentException('購物車內容不正確。'); }
    $pdo = mis_db(); $pdo->beginTransaction();
    $items = array(); $total = 0;
    $productStmt = $pdo->prepare('SELECT id, name, price, stock, active, temperature FROM shop_products WHERE id = :id');
    foreach ($cart as $row) {
        $id = isset($row['id']) ? filter_var($row['id'], FILTER_VALIDATE_INT) : false;
        $qty = isset($row['quantity']) ? filter_var($row['quantity'], FILTER_VALIDATE_INT) : false;
        $temperature = isset($row['temperature']) && is_string($row['temperature']) ? $row['temperature'] : '';
        $sweetness = isset($row['sweetness']) && is_string($row['sweetness']) ? $row['sweetness'] : '';
        if (!$id || !$qty || $qty < 1 || $qty > 10 || mb_strlen($temperature) > 10 || mb_strlen($sweetness) > 10) { throw new InvalidArgumentException('商品數量或選項不正確。'); }
        $productStmt->execute(array(':id' => $id)); $product = $productStmt->fetch();
        if (!$product || !(int) $product['active'] || (int) $product['stock'] < $qty) { throw new InvalidArgumentException('部分商品已售罄或庫存不足，請重新選擇。'); }
        $line = (int) $product['price'] * $qty; $total += $line;
        $items[] = array($product, $qty, $temperature, $sweetness, $line);
    }
    $now = date('Y-m-d H:i:s'); $reference = shop_reference(); $customer = shop_customer();
    $orderStmt = $pdo->prepare('INSERT INTO shop_orders (reference_no, customer_id, table_code, service_type, pickup_name, total, status, created_at, updated_at) VALUES (:ref,:customer,:table,:service,:name,:total,\'received\',:created,:updated)');
    $orderStmt->execute(array(':ref'=>$reference,':customer'=>$customer ? $customer['id'] : null,':table'=>$tableCode,':service'=>$serviceType,':name'=>$pickupName,':total'=>$total,':created'=>$now,':updated'=>$now));
    $orderId = (int) $pdo->lastInsertId();
    $itemStmt = $pdo->prepare('INSERT INTO shop_order_items (order_id, product_id, product_name, unit_price, quantity, temperature, sweetness, line_total) VALUES (?,?,?,?,?,?,?,?)');
    $stockStmt = $pdo->prepare('UPDATE shop_products SET stock = stock - :qty, updated_at = :now WHERE id = :id AND stock >= :qty');
    $moveStmt = $pdo->prepare('INSERT INTO shop_stock_movements (product_id, order_id, change_qty, reason, created_at) VALUES (?,?,?,\'sale\',?)');
    foreach ($items as $item) { $p=$item[0]; $itemStmt->execute(array($orderId,$p['id'],$p['name'],$p['price'],$item[1],$item[2],$item[3],$item[4])); $stockStmt->execute(array(':qty'=>$item[1],':now'=>$now,':id'=>$p['id'])); if ($stockStmt->rowCount() !== 1) { throw new RuntimeException('庫存剛剛已變動，請重新送出。'); } $moveStmt->execute(array($p['id'],$orderId,-$item[1],$now)); }
    $pdo->commit(); $_SESSION['shop_last_order'] = time(); mis_audit('shop_order_created','shop_order',(string)$orderId,array('reference'=>$reference,'total'=>$total));
    mis_json_response(true,'訂單已收到。',201,array('reference'=>$reference,'total'=>$total));
} catch (InvalidArgumentException $e) { if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); } mis_json_response(false,$e->getMessage(),422); }
catch (RuntimeException $e) { if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); } mis_json_response(false,$e->getMessage(),409); }
catch (Throwable $e) { if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); } error_log('Shop order failure: '.$e->getMessage()); mis_json_response(false,'目前無法建立訂單，請稍後再試。',500); }


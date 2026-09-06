<?php
declare(strict_types=1);

function shop_customer(): ?array
{
    return isset($_SESSION['shop_customer']) && is_array($_SESSION['shop_customer']) ? $_SESSION['shop_customer'] : null;
}

function shop_tags(string $value): array
{
    return array_values(array_filter(array_map('trim', explode(',', $value)), function ($item) { return $item !== ''; }));
}

function shop_products(bool $includeInactive = false): array
{
    $sql = 'SELECT p.*, c.name AS category_name, c.code AS category_code FROM shop_products p JOIN shop_categories c ON c.id = p.category_id';
    if (!$includeInactive) { $sql .= ' WHERE p.active = 1'; }
    return mis_db()->query($sql . ' ORDER BY c.sort_order, p.sort_order, p.id')->fetchAll();
}

function shop_valid_table(string $code): ?array
{
    $stmt = mis_db()->prepare('SELECT * FROM shop_tables WHERE code = :code AND active = 1');
    $stmt->execute(array(':code' => strtoupper($code)));
    $row = $stmt->fetch();
    return $row ?: null;
}

function shop_recommendations(?array $customer, array $products, int $limit = 4): array
{
    $preference = null;
    $favoriteIds = array();
    if ($customer) {
        $stmt = mis_db()->prepare('SELECT * FROM shop_preferences WHERE customer_id = :id');
        $stmt->execute(array(':id' => (int) $customer['id']));
        $preference = $stmt->fetch() ?: null;
        $fav = mis_db()->prepare('SELECT product_id FROM shop_favorites WHERE customer_id = :id');
        $fav->execute(array(':id' => (int) $customer['id']));
        $favoriteIds = array_map('intval', array_column($fav->fetchAll(), 'product_id'));
    }
    foreach ($products as &$product) {
        $score = $product['stock'] > 0 ? 1 : -1000;
        $reasons = array();
        if (in_array((int) $product['id'], $favoriteIds, true)) { $score += 5; $reasons[] = '你收藏過這項商品'; }
        if ($preference) {
            $avoid = shop_tags((string) $preference['avoid_allergens']);
            if (array_intersect($avoid, shop_tags((string) $product['allergens']))) { $score = -1000; }
            if ((int) $product['price'] <= (int) $preference['budget']) { $score += 2; $reasons[] = '符合你的預算'; }
            if ($preference['temperature'] === 'any' || $product['temperature'] === 'both' || $product['temperature'] === $preference['temperature']) { $score += 1; }
            foreach (array('flavors','texture','occasions') as $field) {
                if (array_intersect(shop_tags((string) $preference[$field]), shop_tags((string) $product[$field]))) { $score += 2; $reasons[] = '符合你的' . ($field === 'flavors' ? '風味' : ($field === 'texture' ? '口感' : '使用情境')); }
            }
            if ($preference['caffeine'] !== 'any' && $product['caffeine'] === $preference['caffeine']) { $score += 2; $reasons[] = '符合咖啡因偏好'; }
        }
        $product['_score'] = $score;
        $product['_reason'] = $reasons ? implode('、', array_unique($reasons)) : '目前有庫存的教學推薦商品';
    }
    unset($product);
    usort($products, function ($a, $b) { return $a['_score'] === $b['_score'] ? (int) $a['sort_order'] <=> (int) $b['sort_order'] : $b['_score'] <=> $a['_score']; });
    return array_slice(array_values(array_filter($products, function ($p) { return $p['_score'] > -1000; })), 0, $limit);
}

function shop_reference(): string
{
    return 'WW' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

function shop_customer_login(string $email, string $password): bool
{
    $stmt = mis_db()->prepare('SELECT * FROM shop_customers WHERE email = :email AND active = 1 AND is_synthetic = 0 LIMIT 1');
    $stmt->execute(array(':email' => strtolower($email)));
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, (string) $row['password_hash'])) { return false; }
    session_regenerate_id(true);
    $_SESSION['shop_customer'] = array('id' => (int) $row['id'], 'email' => $row['email'], 'display_name' => $row['display_name'], 'personalization_consent' => (int) $row['personalization_consent']);
    return true;
}


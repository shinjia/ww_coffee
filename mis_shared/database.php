<?php
declare(strict_types=1);

function mis_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $storage = __DIR__ . '/storage';
    if (!is_dir($storage) && !mkdir($storage, 0770, true) && !is_dir($storage)) {
        throw new RuntimeException('無法建立資料儲存目錄。');
    }

    $pdo = new PDO('sqlite:' . $storage . '/mis.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    mis_migrate($pdo);
    return $pdo;
}

function mis_migrate(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        display_name TEXT NOT NULL,
        role TEXT NOT NULL CHECK (role IN (\'employee\', \'admin\')),
        active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS employees (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER UNIQUE,
        employee_no TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        department TEXT NOT NULL DEFAULT \'\',
        title TEXT NOT NULL DEFAULT \'\',
        email TEXT NOT NULL DEFAULT \'\',
        status TEXT NOT NULL DEFAULT \'active\' CHECK (status IN (\'active\', \'inactive\')),
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS feedback (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        reference_no TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        category TEXT NOT NULL,
        message TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT \'new\' CHECK (status IN (\'new\', \'processing\', \'closed\')),
        ip_hash TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action TEXT NOT NULL,
        target_type TEXT NOT NULL DEFAULT \'\',
        target_id TEXT NOT NULL DEFAULT \'\',
        details TEXT NOT NULL DEFAULT \'{}\',
        ip_address TEXT NOT NULL,
        created_at TEXT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        sort_order INTEGER NOT NULL DEFAULT 0
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL,
        sku TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        description TEXT NOT NULL DEFAULT \'\',
        price INTEGER NOT NULL CHECK (price >= 0),
        stock INTEGER NOT NULL DEFAULT 0 CHECK (stock >= 0),
        image_path TEXT NOT NULL DEFAULT \'\',
        temperature TEXT NOT NULL DEFAULT \'none\' CHECK (temperature IN (\'none\', \'hot\', \'cold\', \'both\')),
        caffeine TEXT NOT NULL DEFAULT \'none\' CHECK (caffeine IN (\'none\', \'low\', \'medium\', \'high\')),
        sweetness TEXT NOT NULL DEFAULT \'none\',
        flavors TEXT NOT NULL DEFAULT \'\',
        texture TEXT NOT NULL DEFAULT \'\',
        dietary TEXT NOT NULL DEFAULT \'\',
        allergens TEXT NOT NULL DEFAULT \'\',
        occasions TEXT NOT NULL DEFAULT \'\',
        pairings TEXT NOT NULL DEFAULT \'\',
        active INTEGER NOT NULL DEFAULT 1,
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY (category_id) REFERENCES shop_categories(id)
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_tables (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        active INTEGER NOT NULL DEFAULT 1
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        display_name TEXT NOT NULL,
        active INTEGER NOT NULL DEFAULT 1,
        personalization_consent INTEGER NOT NULL DEFAULT 0,
        is_synthetic INTEGER NOT NULL DEFAULT 0,
        synthetic_batch_id INTEGER,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_preferences (
        customer_id INTEGER PRIMARY KEY,
        categories TEXT NOT NULL DEFAULT \'\',
        caffeine TEXT NOT NULL DEFAULT \'any\',
        flavors TEXT NOT NULL DEFAULT \'\',
        texture TEXT NOT NULL DEFAULT \'\',
        temperature TEXT NOT NULL DEFAULT \'any\',
        sweetness TEXT NOT NULL DEFAULT \'any\',
        budget INTEGER NOT NULL DEFAULT 200,
        avoid_allergens TEXT NOT NULL DEFAULT \'\',
        occasions TEXT NOT NULL DEFAULT \'\',
        updated_at TEXT NOT NULL,
        FOREIGN KEY (customer_id) REFERENCES shop_customers(id) ON DELETE CASCADE
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_favorites (
        customer_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        PRIMARY KEY (customer_id, product_id),
        FOREIGN KEY (customer_id) REFERENCES shop_customers(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES shop_products(id) ON DELETE CASCADE
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        reference_no TEXT NOT NULL UNIQUE,
        customer_id INTEGER,
        table_code TEXT NOT NULL,
        service_type TEXT NOT NULL CHECK (service_type IN (\'dine_in\', \'takeaway\')),
        pickup_name TEXT NOT NULL,
        total INTEGER NOT NULL CHECK (total >= 0),
        status TEXT NOT NULL DEFAULT \'received\' CHECK (status IN (\'received\', \'preparing\', \'ready\', \'completed\', \'cancelled\')),
        is_synthetic INTEGER NOT NULL DEFAULT 0,
        synthetic_batch_id INTEGER,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY (customer_id) REFERENCES shop_customers(id) ON DELETE SET NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER,
        product_name TEXT NOT NULL,
        unit_price INTEGER NOT NULL,
        quantity INTEGER NOT NULL CHECK (quantity > 0),
        temperature TEXT NOT NULL DEFAULT \'\',
        sweetness TEXT NOT NULL DEFAULT \'\',
        line_total INTEGER NOT NULL,
        FOREIGN KEY (order_id) REFERENCES shop_orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES shop_products(id) ON DELETE SET NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_stock_movements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        order_id INTEGER,
        change_qty INTEGER NOT NULL,
        reason TEXT NOT NULL CHECK (reason IN (\'sale\', \'cancel_restore\', \'manual\', \'seed\')),
        user_id INTEGER,
        is_synthetic INTEGER NOT NULL DEFAULT 0,
        synthetic_batch_id INTEGER,
        created_at TEXT NOT NULL,
        FOREIGN KEY (product_id) REFERENCES shop_products(id),
        FOREIGN KEY (order_id) REFERENCES shop_orders(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_recommendation_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER,
        product_id INTEGER,
        method TEXT NOT NULL,
        event_type TEXT NOT NULL,
        reason TEXT NOT NULL DEFAULT \'\',
        is_synthetic INTEGER NOT NULL DEFAULT 0,
        synthetic_batch_id INTEGER,
        created_at TEXT NOT NULL,
        FOREIGN KEY (customer_id) REFERENCES shop_customers(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES shop_products(id) ON DELETE CASCADE
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS shop_synthetic_batches (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        batch_code TEXT NOT NULL UNIQUE,
        seed INTEGER NOT NULL,
        parameters TEXT NOT NULL,
        customer_count INTEGER NOT NULL DEFAULT 0,
        order_count INTEGER NOT NULL DEFAULT 0,
        event_count INTEGER NOT NULL DEFAULT 0,
        status TEXT NOT NULL,
        created_by INTEGER,
        created_at TEXT NOT NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )');

    mis_add_column($pdo, 'users', 'is_synthetic', 'INTEGER NOT NULL DEFAULT 0');
    mis_add_column($pdo, 'users', 'synthetic_batch_id', 'INTEGER');
    mis_add_column($pdo, 'employees', 'is_synthetic', 'INTEGER NOT NULL DEFAULT 0');
    mis_add_column($pdo, 'employees', 'synthetic_batch_id', 'INTEGER');
    mis_add_column($pdo, 'feedback', 'is_synthetic', 'INTEGER NOT NULL DEFAULT 0');
    mis_add_column($pdo, 'feedback', 'synthetic_batch_id', 'INTEGER');
    mis_add_column($pdo, 'shop_synthetic_batches', 'employee_count', 'INTEGER NOT NULL DEFAULT 0');
    mis_add_column($pdo, 'shop_synthetic_batches', 'feedback_count', 'INTEGER NOT NULL DEFAULT 0');

    mis_seed_shop($pdo);

    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, display_name, role, active, created_at, updated_at) VALUES (:username, :password_hash, :display_name, :role, 1, :created_at, :updated_at)');
        $seeds = array(
            array('admin', 'admin123', '系統管理者', 'admin'),
            array('employee', 'employee123', '測試員工', 'employee'),
        );
        foreach ($seeds as $seed) {
            $stmt->execute(array(':username' => $seed[0], ':password_hash' => password_hash($seed[1], PASSWORD_DEFAULT), ':display_name' => $seed[2], ':role' => $seed[3], ':created_at' => $now, ':updated_at' => $now));
        }
        $employeeUserId = (int) $pdo->query("SELECT id FROM users WHERE username = 'employee'")->fetchColumn();
        $employeeStmt = $pdo->prepare('INSERT INTO employees (user_id, employee_no, name, department, title, email, status, created_at, updated_at) VALUES (:user_id, :employee_no, :name, :department, :title, :email, \'active\', :created_at, :updated_at)');
        $employeeStmt->execute(array(':user_id' => $employeeUserId, ':employee_no' => 'EMP001', ':name' => '測試員工', ':department' => '營運部', ':title' => '專員', ':email' => '', ':created_at' => $now, ':updated_at' => $now));
    }

    mis_apply_development_password_rule($pdo);
}

function mis_add_column(PDO $pdo, string $table, string $column, string $definition): void
{
    $allowedTables = array('users', 'employees', 'feedback', 'shop_synthetic_batches');
    if (!in_array($table, $allowedTables, true) || !preg_match('/^[a-z_]+$/', $column)) {
        throw new InvalidArgumentException('資料表 migration 設定不正確。');
    }
    $columns = $pdo->query('PRAGMA table_info(' . $table . ')')->fetchAll();
    foreach ($columns as $existing) {
        if ($existing['name'] === $column) {
            return;
        }
    }
    $pdo->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $column . ' ' . $definition);
}

function mis_apply_development_password_rule(PDO $pdo): void
{
    $config = mis_load_config();
    if (($config['environment'] ?? 'production') !== 'development') {
        return;
    }
    $pdo->exec('CREATE TABLE IF NOT EXISTS system_meta (meta_key TEXT PRIMARY KEY, meta_value TEXT NOT NULL)');
    $stmt = $pdo->prepare('SELECT meta_value FROM system_meta WHERE meta_key = ?');
    $stmt->execute(array('account_password_rule'));
    if ($stmt->fetchColumn() === 'account_plus_123') {
        return;
    }
    $pdo->beginTransaction();
    try {
        $updateUser = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?');
        foreach ($pdo->query('SELECT id, username FROM users')->fetchAll() as $user) {
            $updateUser->execute(array(password_hash($user['username'] . '123', PASSWORD_DEFAULT), date('Y-m-d H:i:s'), $user['id']));
        }
        $updateCustomer = $pdo->prepare('UPDATE shop_customers SET password_hash = ?, updated_at = ? WHERE id = ?');
        foreach ($pdo->query('SELECT id, email FROM shop_customers')->fetchAll() as $customer) {
            $updateCustomer->execute(array(password_hash($customer['email'] . '123', PASSWORD_DEFAULT), date('Y-m-d H:i:s'), $customer['id']));
        }
        $pdo->prepare('INSERT OR REPLACE INTO system_meta (meta_key, meta_value) VALUES (?, ?)')->execute(array('account_password_rule', 'account_plus_123'));
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function mis_seed_shop(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM shop_products')->fetchColumn();
    if ($count > 0) {
        return;
    }
    $pdo->beginTransaction();
    try {
        $categories = array(
            array('coffee', '咖啡', 10), array('tea', '茶飲', 20), array('non_coffee', '非咖啡', 30),
            array('dessert', '點心', 40), array('light_meal', '輕食', 50), array('coffee_goods', '咖啡商品', 60), array('merch', '紀念品', 70),
        );
        $categoryStmt = $pdo->prepare('INSERT INTO shop_categories (code, name, sort_order) VALUES (?, ?, ?)');
        foreach ($categories as $category) { $categoryStmt->execute($category); }
        $ids = array();
        foreach ($pdo->query('SELECT id, code FROM shop_categories')->fetchAll() as $row) { $ids[$row['code']] = (int) $row['id']; }
        $now = date('Y-m-d H:i:s');
        $products = array(
            array('coffee','COF001','美式咖啡','乾淨俐落的咖啡香氣。',80,40,'coffee.webp','both','high','無糖','堅果,焦糖','清爽','純素','','早餐,提神','原味司康'),
            array('coffee','COF002','拿鐵','濃縮咖啡與牛奶的滑順平衡。',110,35,'coffee.webp','both','medium','低甜','堅果,焦糖','滑順','奶類','奶類','早餐,下午茶','原味司康,乳酪蛋糕'),
            array('coffee','COF003','卡布奇諾','綿密奶泡與濃縮咖啡。',110,30,'coffee.webp','hot','medium','低甜','堅果,可可','濃郁','奶類','奶類','早餐,提神','巧克力餅乾'),
            array('coffee','COF004','摩卡','咖啡、牛奶與巧克力風味。',125,25,'coffee.webp','both','medium','中甜','巧克力,焦糖','濃郁','奶類','奶類','下午茶','原味司康'),
            array('coffee','COF005','單品手沖','果香清晰的每日精選手沖。',150,20,'coffee.webp','both','high','無糖','果香,花香','清爽','純素','','提神,品飲','磅蛋糕'),
            array('tea','TEA001','紅茶','溫潤茶香，冷熱皆宜。',65,40,'tea.webp','both','medium','低甜','茶香,蜜香','清爽','純素','','早餐,下午茶','原味司康'),
            array('tea','TEA002','烏龍茶','清雅回甘的烏龍茶。',70,35,'tea.webp','both','low','無糖','茶香,花香','清爽','純素','','放鬆','磅蛋糕'),
            array('tea','TEA003','鮮奶茶','紅茶與鮮奶的柔和組合。',95,30,'tea.webp','both','medium','中甜','茶香,焦糖','滑順','奶類','奶類','下午茶','巧克力餅乾'),
            array('non_coffee','NON001','巧克力歐蕾','濃郁可可與鮮奶。',105,25,'non-coffee.webp','both','none','偏甜','巧克力','濃郁','奶類','奶類','下午茶,放鬆','原味司康'),
            array('non_coffee','NON002','抹茶歐蕾','抹茶微苦與鮮奶的平衡。',115,25,'non-coffee.webp','both','low','中甜','茶香','滑順','奶類','奶類','下午茶,放鬆','乳酪蛋糕'),
            array('dessert','DES001','原味司康','外酥內鬆的經典司康。',65,24,'dessert.webp','none','none','低甜','奶油','紮實','蛋奶素','奶類,蛋,麩質','早餐,下午茶','美式咖啡,拿鐵'),
            array('dessert','DES002','巧克力餅乾','可可香濃的手作餅乾。',50,30,'dessert.webp','none','none','偏甜','巧克力','酥脆','蛋奶素','奶類,蛋,麩質','下午茶','卡布奇諾,鮮奶茶'),
            array('dessert','DES003','磅蛋糕','奶油香氣與細緻口感。',75,18,'dessert.webp','none','none','中甜','奶油','紮實','蛋奶素','奶類,蛋,麩質','下午茶','單品手沖,烏龍茶'),
            array('dessert','DES004','乳酪蛋糕','柔滑濃郁的乳酪風味。',120,16,'dessert.webp','none','none','中甜','乳酪','滑順','蛋奶素','奶類,蛋,麩質','下午茶','拿鐵,抹茶歐蕾'),
            array('light_meal','MEA001','火腿起司吐司','簡單飽足的熱壓吐司。',100,20,'light-meal.webp','none','none','低甜','起司','酥脆','葷食','奶類,麩質','早餐','美式咖啡,紅茶'),
            array('coffee_goods','GDS001','綜合濾掛咖啡盒','方便沖煮的綜合風味濾掛。',320,15,'coffee-goods.webp','none','high','無糖','果香,堅果','清爽','純素','','送禮,居家','木質杯墊'),
            array('coffee_goods','GDS002','精選咖啡豆','教學示範用精選咖啡豆。',480,12,'coffee-goods.webp','none','high','無糖','果香,巧克力','濃郁','純素','','送禮,居家','木窗咖啡馬克杯'),
            array('merch','MER001','木窗咖啡馬克杯','溫潤陶瓷質感的示範紀念杯。',380,10,'merch.webp','none','none','none','','','','','送禮,居家','精選咖啡豆'),
            array('merch','MER002','品牌帆布袋','簡約耐用的示範帆布袋。',290,12,'merch.webp','none','none','none','','','','','送禮,日常',''),
            array('merch','MER003','木質杯墊','保留木紋觸感的杯墊。',120,20,'merch.webp','none','none','none','','','','','送禮,居家','綜合濾掛咖啡盒')
        );
        $stmt = $pdo->prepare('INSERT INTO shop_products (category_id, sku, name, description, price, stock, image_path, temperature, caffeine, sweetness, flavors, texture, dietary, allergens, occasions, pairings, active, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)');
        foreach ($products as $index => $p) {
            $stmt->execute(array($ids[$p[0]],$p[1],$p[2],$p[3],$p[4],$p[5],$p[6],$p[7],$p[8],$p[9],$p[10],$p[11],$p[12],$p[13],$p[14],$p[15],$index + 1,$now,$now));
        }
        $tableStmt = $pdo->prepare('INSERT INTO shop_tables (code, name, active) VALUES (?, ?, 1)');
        foreach (array(array('T01','一號桌'),array('T02','二號桌'),array('T03','三號桌'),array('T04','四號桌'),array('TAKEAWAY','外帶')) as $table) { $tableStmt->execute($table); }
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $error;
    }
}

<?php
require_once __DIR__ . '/../../config.php';

allow_cors();
require_api_token();

$conn = db();
$method = $_SERVER['REQUEST_METHOD'];

function fetch_all_assoc(mysqli $conn, string $sql): array
{
    $result = $conn->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function normalize_number($value, $default = 0)
{
    if ($value === null || $value === '') {
        return $default;
    }
    return is_numeric($value) ? $value + 0 : $default;
}

function clear_tables(mysqli $conn): void
{
    $conn->query('SET FOREIGN_KEY_CHECKS=0');
    foreach ([
        'order_items',
        'orders',
        'customers',
        'suppliers',
        'products',
        'categories',
        'service_repairs',
        'spare_parts',
        'technicians'
    ] as $table) {
        $conn->query("TRUNCATE TABLE `$table`");
    }
    $conn->query('SET FOREIGN_KEY_CHECKS=1');
}

function save_customers(mysqli $conn, array $items): void
{
    $stmt = $conn->prepare('INSERT INTO customers (id, name, type, company_name, department, phone, email, address, tax_code, notes, debt, total_orders) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($items as $item) {
        $id = (string)($item['id'] ?? uniqid('KH'));
        $name = (string)($item['name'] ?? '');
        $type = (string)($item['type'] ?? 'ca-nhan');
        $companyName = (string)($item['companyName'] ?? $item['company_name'] ?? '');
        $department = (string)($item['department'] ?? '');
        $phone = (string)($item['phone'] ?? '');
        $email = (string)($item['email'] ?? '');
        $address = (string)($item['address'] ?? '');
        $taxCode = (string)($item['taxCode'] ?? $item['tax_code'] ?? '');
        $notes = (string)($item['notes'] ?? '');
        $debt = normalize_number($item['debt'] ?? 0);
        $totalOrders = (int)normalize_number($item['totalOrders'] ?? $item['total_orders'] ?? 0);
        $stmt->bind_param('ssssssssssdi', $id, $name, $type, $companyName, $department, $phone, $email, $address, $taxCode, $notes, $debt, $totalOrders);
        $stmt->execute();
    }
}

function save_suppliers(mysqli $conn, array $items): void
{
    $stmt = $conn->prepare('INSERT INTO suppliers (id, name, phone, email, address, products, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($items as $item) {
        $id = (string)($item['id'] ?? uniqid('NCC'));
        $name = (string)($item['name'] ?? '');
        $phone = (string)($item['phone'] ?? '');
        $email = (string)($item['email'] ?? '');
        $address = (string)($item['address'] ?? '');
        $products = (string)($item['products'] ?? '');
        $notes = (string)($item['notes'] ?? '');
        $stmt->bind_param('sssssss', $id, $name, $phone, $email, $address, $products, $notes);
        $stmt->execute();
    }
}

function save_categories(mysqli $conn, array $items): void
{
    $stmt = $conn->prepare('INSERT INTO categories (id, name, parent) VALUES (?, ?, ?)');
    foreach ($items as $item) {
        $id = (string)($item['id'] ?? uniqid('CAT'));
        $name = (string)($item['name'] ?? '');
        $parent = $item['parent'] ?? null;
        $stmt->bind_param('sss', $id, $name, $parent);
        $stmt->execute();
    }
}

function save_products(mysqli $conn, array $items): void
{
    $stmt = $conn->prepare('INSERT INTO products (id, name, category, price, import_price, stock, min_stock, supplier, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($items as $item) {
        $id = (string)($item['id'] ?? uniqid('SP'));
        $name = (string)($item['name'] ?? '');
        $category = (string)($item['category'] ?? '');
        $price = normalize_number($item['price'] ?? 0);
        $importPrice = normalize_number($item['importPrice'] ?? $item['import_price'] ?? 0);
        $stock = (int)normalize_number($item['stock'] ?? 0);
        $minStock = (int)normalize_number($item['minStock'] ?? $item['min_stock'] ?? 0);
        $supplier = (string)($item['supplier'] ?? '');
        $notes = (string)($item['notes'] ?? '');
        $stmt->bind_param('sssddiiss', $id, $name, $category, $price, $importPrice, $stock, $minStock, $supplier, $notes);
        $stmt->execute();
    }
}

function save_orders(mysqli $conn, array $items): void
{
    $orderStmt = $conn->prepare('INSERT INTO orders (id, customer_id, customer_name, order_date, order_time, total, status, payment_method, payment_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $itemStmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)');

    foreach ($items as $item) {
        $id = (string)($item['id'] ?? uniqid('DH'));
        $customerId = (string)($item['customerId'] ?? $item['customer_id'] ?? '');
        $customerName = (string)($item['customerName'] ?? $item['customer_name'] ?? '');
        $date = (string)($item['date'] ?? date('Y-m-d'));
        $time = (string)($item['time'] ?? '00:00');
        $total = normalize_number($item['total'] ?? 0);
        $status = (string)($item['status'] ?? 'Mới');
        $paymentMethod = (string)($item['paymentMethod'] ?? $item['payment_method'] ?? '');
        $paymentStatus = (string)($item['paymentStatus'] ?? $item['payment_status'] ?? '');
        $notes = (string)($item['notes'] ?? '');
        $orderStmt->bind_param('sssssissss', $id, $customerId, $customerName, $date, $time, $total, $status, $paymentMethod, $paymentStatus, $notes);
        $orderStmt->execute();

        $products = $item['products'] ?? $item['items'] ?? [];
        if (is_array($products)) {
            foreach ($products as $product) {
                $productId = (string)($product['id'] ?? $product['productId'] ?? '');
                $productName = (string)($product['name'] ?? $product['productName'] ?? '');
                $quantity = (int)normalize_number($product['quantity'] ?? 1);
                $price = normalize_number($product['price'] ?? 0);
                $itemStmt->bind_param('sssid', $id, $productId, $productName, $quantity, $price);
                $itemStmt->execute();
            }
        }
    }
}

function save_json_table(mysqli $conn, string $table, array $items): void
{
    $stmt = $conn->prepare("INSERT INTO `$table` (id, data_json) VALUES (?, ?)");
    foreach ($items as $item) {
        $id = (string)($item['id'] ?? uniqid());
        $json = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt->bind_param('ss', $id, $json);
        $stmt->execute();
    }
}

function load_data(mysqli $conn): array
{
    $orders = fetch_all_assoc($conn, 'SELECT id, customer_id AS customerId, customer_name AS customerName, order_date AS date, order_time AS time, total, status, payment_method AS paymentMethod, payment_status AS paymentStatus, notes FROM orders ORDER BY created_at DESC');

    foreach ($orders as &$order) {
        $orderId = $conn->real_escape_string($order['id']);
        $order['products'] = fetch_all_assoc($conn, "SELECT product_id AS id, product_name AS name, quantity, price FROM order_items WHERE order_id = '$orderId'");
        $order['total'] = (float)$order['total'];
    }

    $jsonTable = function (string $table) use ($conn): array {
        $rows = fetch_all_assoc($conn, "SELECT data_json FROM `$table` ORDER BY created_at DESC");
        return array_values(array_filter(array_map(fn($row) => json_decode($row['data_json'], true), $rows)));
    };

    return [
        'customers' => fetch_all_assoc($conn, 'SELECT id, name, type, company_name AS companyName, department, phone, email, address, tax_code AS taxCode, notes, debt, total_orders AS totalOrders FROM customers ORDER BY created_at DESC'),
        'suppliers' => fetch_all_assoc($conn, 'SELECT id, name, phone, email, address, products, notes FROM suppliers ORDER BY created_at DESC'),
        'products' => fetch_all_assoc($conn, 'SELECT id, name, category, price, import_price AS importPrice, stock, min_stock AS minStock, supplier, notes FROM products ORDER BY created_at DESC'),
        'categories' => fetch_all_assoc($conn, 'SELECT id, name, parent FROM categories ORDER BY created_at DESC'),
        'orders' => $orders,
        'sales' => $orders,
        'serviceRepairs' => $jsonTable('service_repairs'),
        'spareParts' => $jsonTable('spare_parts'),
        'technicians' => $jsonTable('technicians')
    ];
}

if ($method === 'GET') {
    send_json([
        'success' => true,
        'data' => load_data($conn)
    ]);
}

if ($method === 'POST' || $method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $data = $input['data'] ?? $input;

    if (!is_array($data)) {
        send_json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ'
        ], 400);
    }

    try {
        $conn->begin_transaction();
        clear_tables($conn);
        save_customers($conn, $data['customers'] ?? []);
        save_suppliers($conn, $data['suppliers'] ?? []);
        save_categories($conn, $data['categories'] ?? []);
        save_products($conn, $data['products'] ?? []);
        save_orders($conn, $data['orders'] ?? $data['sales'] ?? []);
        save_json_table($conn, 'service_repairs', $data['serviceRepairs'] ?? []);
        save_json_table($conn, 'spare_parts', $data['spareParts'] ?? []);
        save_json_table($conn, 'technicians', $data['technicians'] ?? []);
        $conn->commit();

        send_json([
            'success' => true,
            'message' => 'Đã đồng bộ dữ liệu vào MySQL thật',
            'data' => load_data($conn)
        ]);
    } catch (Throwable $e) {
        $conn->rollback();
        send_json([
            'success' => false,
            'message' => 'Lưu database thất bại',
            'error' => $e->getMessage()
        ], 500);
    }
}

send_json([
    'success' => false,
    'message' => 'Method không hỗ trợ'
], 405);

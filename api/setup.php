<?php
require_once __DIR__ . '/config.php';

$conn = db();

$queries = [
"CREATE TABLE IF NOT EXISTS customers (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255),
    type VARCHAR(50),
    company_name VARCHAR(255),
    department VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    address TEXT,
    tax_code VARCHAR(100),
    notes TEXT,
    debt DECIMAL(18,2) DEFAULT 0,
    total_orders INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS suppliers (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    address TEXT,
    products TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS categories (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255),
    parent VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255),
    category VARCHAR(100),
    price DECIMAL(18,2),
    import_price DECIMAL(18,2),
    stock INT,
    min_stock INT,
    supplier VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(50) PRIMARY KEY,
    customer_id VARCHAR(50),
    customer_name VARCHAR(255),
    order_date DATE,
    order_time TIME,
    total DECIMAL(18,2),
    status VARCHAR(100),
    payment_method VARCHAR(100),
    payment_status VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50),
    product_id VARCHAR(50),
    product_name VARCHAR(255),
    quantity INT,
    price DECIMAL(18,2)
)",
"CREATE TABLE IF NOT EXISTS service_repairs (
    id VARCHAR(50) PRIMARY KEY,
    data_json LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS spare_parts (
    id VARCHAR(50) PRIMARY KEY,
    data_json LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS technicians (
    id VARCHAR(50) PRIMARY KEY,
    data_json LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)"
];

foreach ($queries as $sql) {
    $conn->query($sql);
}

echo "Setup hoàn tất: DB Máy Tính BK đã sẵn sàng";

<?php
/**
 * Cấu hình kết nối MySQL cho app QLBH.
 * Sau khi upload lên aaPanel, sửa DB_USER/DB_PASS/DB_NAME đúng với database thật.
 */

declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'qlbh_maytinhbk';
const DB_USER = 'root';
const DB_PASS = '';

// Để trống nếu chưa muốn dùng token. Khi dùng thật nên đặt chuỗi bí mật và nhập cùng token ở giao diện.
const API_TOKEN = '';

function send_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function allow_cors(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, PUT, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function require_api_token(): void
{
    if (API_TOKEN === '') {
        return;
    }

    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = '';

    if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
        $token = trim($matches[1]);
    }

    if (!hash_equals(API_TOKEN, $token)) {
        send_json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }
}

function db(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Throwable $e) {
        send_json([
            'success' => false,
            'message' => 'Không kết nối được database',
            'error' => $e->getMessage()
        ], 500);
    }
}

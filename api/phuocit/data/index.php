<?php
require_once __DIR__ . '/../../config.php';

allow_cors();
require_api_token();

$method = $_SERVER['REQUEST_METHOD'];
$conn = db();

if ($method === 'GET') {
    $result = $conn->query("SELECT data_json FROM app_state WHERE state_key = 'phuocit_data' LIMIT 1");
    $row = $result->fetch_assoc();

    if (!$row) {
        send_json([
            'success' => true,
            'data' => null,
            'message' => 'Chưa có dữ liệu'
        ]);
    }

    send_json([
        'success' => true,
        'data' => json_decode($row['data_json'], true)
    ]);
}

if ($method === 'PUT' || $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['data'])) {
        send_json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ'
        ], 400);
    }

    $json = json_encode($input['data'], JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("INSERT INTO app_state (state_key, data_json, updated_at) VALUES ('phuocit_data', ?, NOW()) ON DUPLICATE KEY UPDATE data_json = VALUES(data_json), updated_at = NOW()");
    $stmt->bind_param('s', $json);
    $stmt->execute();

    send_json([
        'success' => true,
        'message' => 'Đã lưu dữ liệu thành công'
    ]);
}

send_json([
    'success' => false,
    'message' => 'Method không hỗ trợ'
], 405);

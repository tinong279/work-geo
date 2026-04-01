<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/includes/db_connection.php';

try {
    $pdo = getDbConnection();

    $stmt = $pdo->prepare("
        SELECT id, name, ip_address, current_mode, display_type,
               text_content, text_color, text_size, image_path,
               trigger_source, trigger_value, last_updated
        FROM cms_status
        WHERE is_active = 1
        ORDER BY id ASC
    ");
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($rows as $row) {
        $result[(string)$row['id']] = $row;
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

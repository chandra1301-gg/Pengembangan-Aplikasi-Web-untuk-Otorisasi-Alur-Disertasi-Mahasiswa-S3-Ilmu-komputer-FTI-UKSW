<?php
/**
 * File: clear_email_logs.php
 * API untuk menghapus log email
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$log_file = __DIR__ . '/logs/email_sent.log';

if (file_exists($log_file)) {
    if (unlink($log_file)) {
        echo json_encode(['success' => true, 'message' => 'Logs cleared successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete log file']);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'No logs to clear']);
}
?>
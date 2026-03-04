<?php
/**
 * File: api_get_mahasiswa.php - Get available mahasiswa for testing
 */

require_once 'enable_cors.php';
require_once __DIR__ . '/includes/db_connect.php';

header('Content-Type: application/json');

// Get all mahasiswa
$query = "SELECT id_mahasiswa, nama_lengkap, nim FROM mahasiswa LIMIT 10";
$result = mysqli_query($conn, $query);

$mahasiswa_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $mahasiswa_list[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $mahasiswa_list,
    'count' => count($mahasiswa_list)
]);
?>
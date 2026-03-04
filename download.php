<?php
/**
 * File: download.php
 * Handler untuk mengunduh file
 */

session_start();

// Fungsi untuk membersihkan nama file
function clean_filename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
}

// Cek parameter file
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header("HTTP/1.0 400 Bad Request");
    die('File tidak ditentukan');
}

$requested_file = $_GET['file'];
$clean_file = clean_filename(basename($requested_file));

// Direktori yang diizinkan untuk diakses
$allowed_dirs = ['documents'];
$file_path = __DIR__ . '/' . $requested_file;

// Validasi path file
$real_path = realpath($file_path);
$base_dir = realpath(__DIR__);

// Cek apakah file ada dan dalam direktori yang diizinkan
if (!$real_path || strpos($real_path, $base_dir) !== 0) {
    header("HTTP/1.0 403 Forbidden");
    die('Akses file tidak diizinkan');
}

// Cek apakah file ada dan dapat dibaca
if (!file_exists($real_path) || !is_readable($real_path)) {
    header("HTTP/1.0 404 Not Found");
    die('File tidak ditemukan');
}

// Cek ekstensi file yang diizinkan
$allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];
$file_extension = strtolower(pathinfo($real_path, PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    header("HTTP/1.0 403 Forbidden");
    die('Tipe file tidak diizinkan');
}

// Set header untuk download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $clean_file . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($real_path));

// Clear output buffer
flush();
readfile($real_path);
exit;
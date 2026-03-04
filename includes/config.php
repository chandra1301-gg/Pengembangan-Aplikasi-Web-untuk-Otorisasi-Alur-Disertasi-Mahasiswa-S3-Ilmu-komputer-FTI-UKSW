<?php
/**
 * File: includes/config.php
 * Konfigurasi global sistem yang lebih robust
 */

// Cek apakah session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definisi konstanta sistem (hanya jika belum didefinisikan)
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Sistem Disertasi S3 UKSW');
}

if (!defined('SITE_URL')) {
    // Auto-detect base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    define('SITE_URL', $protocol . '://' . $host . $script . '/');
}

if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 's3tkm@uksw.edu');
}

// Konfigurasi upload file
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5242880); // 5MB dalam bytes
}

if (!defined('ALLOWED_FILE_TYPES')) {
    define('ALLOWED_FILE_TYPES', 'pdf');
}

if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
}

if (!defined('MONEV_UPLOAD_PATH')) {
    define('MONEV_UPLOAD_PATH', dirname(__DIR__) . '/uploads/monev/');
}

// Konfigurasi pagination
if (!defined('RECORDS_PER_PAGE')) {
    define('RECORDS_PER_PAGE', 10);
}

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (ubah ke 0 untuk production)
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true); // Set false untuk production
}

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__DIR__) . '/error.log');
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Ubah ke 1 jika menggunakan HTTPS

// Membuat folder upload jika belum ada
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

if (!file_exists(MONEV_UPLOAD_PATH)) {
    mkdir(MONEV_UPLOAD_PATH, 0777, true);
}

// Fungsi helper tambahan
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        echo '<script>window.location.href="' . $url . '";</script>';
        exit();
    }
}

function alert($message, $type = 'info') {
    $icons = [
        'success' => 'check-circle',
        'danger' => 'exclamation-triangle',
        'warning' => 'exclamation-triangle',
        'info' => 'info-circle'
    ];
    
    $icon = isset($icons[$type]) ? $icons[$type] : 'info-circle';
    
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                <i class="bi bi-' . $icon . ' me-2"></i>' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

function format_tanggal($date, $format = 'd F Y') {
    if (empty($date) || $date == '0000-00-00') {
        return '-';
    }
    
    $months = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    ];
    
    $formatted = date($format, strtotime($date));
    return str_replace(array_keys($months), array_values($months), $formatted);
}

function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function validate_file_upload($file) {
    $errors = [];
    
    // Cek apakah file ada
    if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return $errors; // Tidak ada file, return kosong
    }
    
    // Cek error upload
    if ($file['error'] != UPLOAD_ERR_OK) {
        $errors[] = 'Error saat upload file. Error code: ' . $file['error'];
        return $errors;
    }
    
    // Cek ukuran file
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'Ukuran file terlalu besar. Maksimal ' . format_file_size(MAX_FILE_SIZE);
    }
    
    // Cek tipe file
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = explode(',', ALLOWED_FILE_TYPES);
    
    if (!in_array($file_ext, $allowed)) {
        $errors[] = 'Tipe file tidak diizinkan. Hanya file: ' . ALLOWED_FILE_TYPES;
    }
    
    // Cek MIME type untuk keamanan tambahan
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = ['application/pdf'];
    if (!in_array($mime, $allowed_mimes)) {
        $errors[] = 'File bukan PDF yang valid';
    }
    
    return $errors;
}

function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

function upload_file($file, $prefix = '', $upload_dir = null) {
    if ($upload_dir === null) {
        $upload_dir = UPLOAD_PATH;
    }
    
    // Validasi file
    $errors = validate_file_upload($file);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Generate nama file unik
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = $prefix . '_' . time() . '_' . generate_random_string(8) . '.' . $file_ext;
    $target_path = $upload_dir . $new_filename;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return [
            'success' => true,
            'filename' => $new_filename,
            'original_name' => $file['name'],
            'path' => $target_path
        ];
    } else {
        return ['success' => false, 'errors' => ['Gagal mengupload file']];
    }
}

// Fungsi untuk log activity (opsional)
function log_activity($user_id, $action, $description = '') {
    global $conn;
    
    if (!isset($conn)) {
        return false;
    }
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $query = "INSERT INTO activity_log (user_id, action, description, ip_address, user_agent, created_at) 
              VALUES (" . intval($user_id) . ", '" . escape_string($action) . "', 
              '" . escape_string($description) . "', '" . escape_string($ip_address) . "', 
              '" . escape_string($user_agent) . "', NOW())";
    
    return db_execute($query);
}

// Auto-include auth.php jika file exists
$auth_file = __DIR__ . '/auth.php';
if (file_exists($auth_file)) {
    require_once $auth_file;
}
?>
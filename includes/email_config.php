<?php
/**
 * File: includes/email_config.php
 * Konfigurasi terpusat untuk email system - PRODUCTION READY
 */

// ==================== KONFIGURASI PRODUCTION ====================
// EDIT BAGIAN INI SESUAI DENGAN SETTING PRODUCTION ANDA

// Konfigurasi Environment
define('ENVIRONMENT', 'production'); // 'development' or 'production'

// Konfigurasi Gmail SMTP - HARUS DIEDIT!
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 's3ilkom@uksw.edu'); // GANTI dengan email Gmail Anda
define('SMTP_PASSWORD', 'wvlu srwc dccf aidi');    // GANTI dengan App Password Gmail
define('SMTP_SECURE', 'tls');

// Konfigurasi Email
define('EMAIL_FROM_NAME', 'Sistem Disertasi UKSW');
define('EMAIL_FROM_ADDRESS', 's3ilkom@uksw.edu');
define('EMAIL_REPLY_TO', 's3ilkom@uksw.edu');

// Konfigurasi Admin
define('ADMIN_EMAIL', 's3ilkom@uksw.edu');
define('ADMIN_PHONE', '0823-1371-8606');
define('ADMIN_ADDRESS', 'Bagian Administrasi Program Doktor, UKSW');

// Konfigurasi Aplikasi - HARUS DIEDIT! // GANTI dengan domain production
define('BASE_URL', 'http://localhost/disertasi_s3'); // GANTI dengan domain production
define('LOGIN_URL', BASE_URL . '/public/beranda.php');
// Pengaturan Debug
define('EMAIL_DEBUG', false); // Set false untuk production

/**
 * Fungsi untuk mendapatkan semua konfigurasi
 */
function getEmailConfig() {
    return [
        'environment' => ENVIRONMENT,
        'smtp' => [
            'host' => SMTP_HOST,
            'port' => SMTP_PORT,
            'username' => SMTP_USERNAME,
            'password' => SMTP_PASSWORD,
            'secure' => SMTP_SECURE
        ],
        'email' => [
            'from_name' => EMAIL_FROM_NAME,
            'from_address' => EMAIL_FROM_ADDRESS,
            'reply_to' => EMAIL_REPLY_TO
        ],
        'admin' => [
            'email' => ADMIN_EMAIL,
            'phone' => ADMIN_PHONE,
            'address' => ADMIN_ADDRESS
        ],
        'app' => [
            'base_url' => BASE_URL,
            'debug' => EMAIL_DEBUG
        ],
        'urls' => [
            'base_url' => BASE_URL,
            'login_url' => LOGIN_URL
        ]
    ];
}

/**
 * Fungsi untuk validasi konfigurasi email
 */
function validateEmailConfig() {
    $config = getEmailConfig();
    
    $errors = [];
    
    if (empty($config['smtp']['username']) || $config['smtp']['username'] === 'your-email@gmail.com') {
        $errors[] = "SMTP username belum dikonfigurasi - edit file email_config.php";
    }
    
    if (empty($config['smtp']['password']) || $config['smtp']['password'] === 'your-app-password') {
        $errors[] = "SMTP password belum dikonfigurasi - edit file email_config.php";
    }
    
    if ($config['app']['base_url'] === 'https://yourdomain.com/disertasi_s3') {
        $errors[] = "Base URL belum dikonfigurasi - edit file email_config.php";
    }
    
    return $errors;
}

/**
 * Cek apakah environment development
 */
function isDevelopment() {
    return ENVIRONMENT === 'development';
}
?>
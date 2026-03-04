<?php
require_once 'includes/email_config.php';
require_once 'includes/email_sender.php';

echo "<h3>🔍 Test Verification Email</h3>";

$config = getEmailConfig();
echo "<p>Environment: <strong>" . $config['environment'] . "</strong></p>";
echo "<p>Is Development: <strong>" . (isDevelopment() ? 'YES' : 'NO') . "</strong></p>";

if (isDevelopment()) {
    echo "<p style='color: orange;'>⚠️  DEVELOPMENT MODE - Email tidak seharusnya terkirim sungguhan</p>";
}

// Test kirim email verifikasi
$result = sendVerificationNotification(
    "672022281@student.uksw.edu",  // Ganti dengan email dosen test
    "Bang Manik",
    "proposal", 
    "Diterima",
    "Catatan test dari admin",
    date('d F Y H:i:s')
);

if ($result) {
    echo "<p>✅ Email verifikasi: <strong>BERHASIL</strong></p>";
    if (isDevelopment()) {
        echo "<p>📝 Tapi ingat - DEVELOPMENT MODE, email tidak benar dikirim</p>";
    }
} else {
    echo "<p>❌ Email verifikasi: <strong>GAGAL</strong></p>";
}

// Cek log
echo "<h4>📋 Cek Error Log:</h4>";
$log_paths = [
    'C:\\xampp\\php\\logs\\php_error_log',
    'C:\\laragon\\log\\php_error.log',
    __DIR__ . '/my_app.log'
];

foreach ($log_paths as $path) {
    if (file_exists($path)) {
        echo "<p>Log file: $path</p>";
        $content = file_get_contents($path);
        $lines = explode("\n", $content);
        $recent = array_slice($lines, -10);
        echo "<pre>" . implode("\n", $recent) . "</pre>";
        break;
    }
}
?>
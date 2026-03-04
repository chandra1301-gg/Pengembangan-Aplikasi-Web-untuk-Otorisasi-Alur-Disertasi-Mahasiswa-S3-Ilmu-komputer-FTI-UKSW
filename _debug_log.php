<?php
/**
 * File: debug_log.php - Versi improved
 */

// Set lokasi log project kita sendiri
$project_log = __DIR__ . '/my_app.log';

echo "<h3>🔍 Debug Email & Error Log</h3>";

// Cek log project kita
if (file_exists($project_log)) {
    echo "<h4>✅ Log Project Ditemukan: my_app.log</h4>";
    $content = file_get_contents($project_log);
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "<p>❌ Log project belum ada. Silakan:</p>";
    echo "<p><a href='enable_logging.php'>1. Aktifkan Logging</a></p>";
    echo "<p>2. Lakukan pendaftaran ujian proposal</p>";
    echo "<p>3. Refresh halaman ini</p>";
}

// Coba sistem log PHP juga
echo "<h4>🔍 Cek System PHP Log:</h4>";
$system_logs = [
    'C:\\xampp\\php\\logs\\php_error_log',
    'C:\\laragon\\log\\php_error.log',
    '/var/log/php_error.log'
];

foreach ($system_logs as $log) {
    if (file_exists($log)) {
        echo "<p>📁 System log: $log - <strong>ADA</strong></p>";
    } else {
        echo "<p>📁 System log: $log - tidak ada</p>";
    }
}
?>
<?php
// enable_logging.php - Aktifkan error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/my_app.log');
ini_set('display_errors', 0);

error_log("✅ Logging berhasil diaktifkan - " . date('Y-m-d H:i:s'));

echo "<h3>✅ Logging telah diaktifkan</h3>";
echo "<p>File log: " . __DIR__ . '/my_app.log' . "</p>";
echo "<p><a href='debug_log.php'>Lihat Log</a></p>";
?>
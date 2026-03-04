<?php
require_once 'includes/email_config.php';
require_once 'includes/email_sender.php';

echo "<h3>🔍 Environment Check</h3>";

$config = getEmailConfig();

echo "<pre>";
echo "ENVIRONMENT: " . $config['environment'] . "\n";
echo "Is Development: " . (isDevelopment() ? 'YES' : 'NO') . "\n";
echo "SMTP Username: " . $config['smtp']['username'] . "\n";
echo "Admin Email: " . $config['admin']['email'] . "\n";
echo "</pre>";

// Test function behavior
if (isDevelopment()) {
    echo "<p style='color: red;'>❌ MASIH DEVELOPMENT MODE - Email tidak seharusnya terkirim!</p>";
} else {
    echo "<p style='color: green;'>✅ PRODUCTION MODE - Email akan terkirim</p>";
}
?>
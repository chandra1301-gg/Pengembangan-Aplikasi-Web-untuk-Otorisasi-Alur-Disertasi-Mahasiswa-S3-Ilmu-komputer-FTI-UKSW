<?php
require_once 'includes/email_sender.php';

echo "Testing Email Configuration...\n";

if (testEmailConfiguration()) {
    echo "✅ Konfigurasi email valid\n";
    
    // Test send email
    $result = sendRegistrationNotification(
        "chandra",
        "672022001",
        "proposal",
        date('d F Y H:i:s')
    );
    
    if ($result) {
        echo "✅ Email test berhasil dikirim\n";
    } else {
        echo "❌ Gagal mengirim email test\n";
    }
} else {
    echo "❌ Konfigurasi email tidak valid\n";
    echo "Periksa file email_config.php\n";
}
?>
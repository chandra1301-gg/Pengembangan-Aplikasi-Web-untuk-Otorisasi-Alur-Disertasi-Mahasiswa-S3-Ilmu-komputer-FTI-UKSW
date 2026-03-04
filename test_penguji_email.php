<?php
// test_penguji_email.php
require_once 'includes/email_sender.php';

echo "Testing Penguji Email...\n";

if (testEmailConfiguration()) {
    echo "✅ Konfigurasi email valid\n";
    
    // Test email untuk promotor
    $result1 = sendDosenAssignmentNotification(
        "dosen1@example.com",
        "Dr. Promotor Test",
        "promotor",
        "Nama Mahasiswa Test",
        "672022001",
        "proposal",
        "2024-12-15 10:00:00",
        "Gedung TI Ruang 101",
        "Judul Disertasi Test"
    );
    
    // Test email untuk penguji
    $result2 = sendDosenAssignmentNotification(
        "dosen2@example.com",
        "Dr. Penguji Test",
        "penguji",
        "Nama Mahasiswa Test",
        "672022001",
        "proposal",
        "2024-12-15 10:00:00",
        "Gedung TI Ruang 101",
        "Judul Disertasi Test"
    );
    
    if ($result1 && $result2) {
        echo "✅ Email penugasan berhasil dikirim\n";
    } else {
        echo "❌ Gagal mengirim email penugasan\n";
    }
} else {
    echo "❌ Konfigurasi email tidak valid\n";
}
?>
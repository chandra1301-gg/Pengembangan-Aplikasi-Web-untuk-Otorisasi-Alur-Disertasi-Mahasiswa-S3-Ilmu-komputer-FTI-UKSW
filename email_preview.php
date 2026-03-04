<?php
/**
 * File: email_preview.php
 * Halaman untuk monitoring email yang dikirim
 */

require_once 'includes/db_connect.php';

$page_title = "Email Monitoring - Sistem Disertasi S3 UKSW";
include 'includes/header.php';

// Get email logs
$log_file = __DIR__ . '/logs/email_sent.log';
$email_logs = [];
$stats = ['success' => 0, 'failed' => 0];

if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines); // Show latest first
    
    foreach ($lines as $line) {
        $email_logs[] = $line;
        
        if (strpos($line, 'Status: SUCCESS') !== false) {
            $stats['success']++;
        } elseif (strpos($line, 'Status: FAILED') !== false) {
            $stats['failed']++;
        }
    }
}
?>

<style>
.monitoring-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Poppins', sans-serif;
}

.header {
    background: linear-gradient(135deg, #1C5EBC 0%, #1565c0 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    text-align: center;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-top: 4px solid #1C5EBC;
}

.stat-number {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 10px;
}

.stat-success { color: #28a745; }
.stat-failure { color: #dc3545; }

.log-section {
    background: white;
    border-radius: 10px;
    padding: 25px;
    margin: 20px 0;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.log-list {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
}

.log-entry {
    padding: 12px;
    border-bottom: 1px solid #f8f9fa;
    font-family: monospace;
    font-size: 13px;
}

.log-entry:last-child {
    border-bottom: none;
}

.log-success {
    background: #f0fdf9;
    border-left: 4px solid #28a745;
}

.log-failed {
    background: #fef2f2;
    border-left: 4px solid #dc3545;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #1C5EBC;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    margin: 5px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    background: #1555a8;
    transform: translateY(-2px);
}

.btn-clear {
    background: #dc3545;
}

.btn-clear:hover {
    background: #c82333;
}

.config-info {
    background: #e8f4fd;
    border: 1px solid #b6d7f2;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}
</style>

<div class="monitoring-container">
    <div class="header">
        <h1>📧 Email Monitoring System</h1>
        <p>Monitoring pengiriman email notifikasi sistem</p>
        <span style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 15px; font-size: 14px;">
            Production Environment
        </span>
    </div>

    <!-- Configuration Info -->
    <div class="config-info">
        <h3>⚙️ Konfigurasi Saat Ini</h3>
        <?php 
        $config = getEmailConfig();
        $config_errors = validateEmailConfig();
        ?>
        
        <?php if (empty($config_errors)): ?>
            <p><strong>✅ Konfigurasi Valid</strong> - Sistem email siap beroperasi</p>
            <p><small>SMTP: <?php echo $config['smtp']['host']; ?>:<?php echo $config['smtp']['port']; ?> | From: <?php echo $config['email']['from_address']; ?></small></p>
        <?php else: ?>
            <p><strong>❌ Konfigurasi Bermasalah</strong> - Periksa file email_config.php</p>
        <?php endif; ?>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($email_logs); ?></div>
            <div>Total Email</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-success"><?php echo $stats['success']; ?></div>
            <div>Berhasil</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-failure"><?php echo $stats['failed']; ?></div>
            <div>Gagal</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['success'] > 0 ? round(($stats['success'] / count($email_logs)) * 100, 1) : 0; ?>%</div>
            <div>Success Rate</div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="test_email_production.php" class="btn">🧪 Test Email System</a>
        <a href="admin/manage_users.php" class="btn">⚙️ Kelola User</a>
        <button onclick="clearLogs()" class="btn btn-clear">🗑️ Clear Logs</button>
    </div>

    <!-- Email Logs -->
    <div class="log-section">
        <h3>📋 Riwayat Pengiriman Email</h3>
        
        <?php if (!empty($email_logs)): ?>
            <div class="log-list">
                <?php foreach ($email_logs as $log_entry): ?>
                <div class="log-entry <?php echo strpos($log_entry, 'Status: SUCCESS') !== false ? 'log-success' : 'log-failed'; ?>">
                    <?php echo htmlspecialchars($log_entry); ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #6c757d;">
                <div style="font-size: 48px; margin-bottom: 20px;">📭</div>
                <h4>Belum ada riwayat email</h4>
                <p>Email yang dikirim akan muncul di sini</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function clearLogs() {
    if (confirm('Hapus semua riwayat email? Tindakan ini tidak dapat dibatalkan.')) {
        fetch('clear_email_logs.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal menghapus riwayat: ' + data.error);
                }
            });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
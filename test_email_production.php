<?php
/**
 * File: test_email_production.php
 * Testing email system untuk production
 */

require_once 'includes/db_connect.php';

$page_title = "Test Email Production - Sistem Disertasi S3 UKSW";
include 'includes/header.php';

// Test data
$test_data = [
    'email' => 'chandra080403@gmail.com', // Ganti dengan email nyata untuk testing
    'nama_lengkap' => 'Kelfin Simamora',
    'nim' => '672022132',
    'program_studi' => 'Program Doktor Ilmu Komputer',
    'username' => 'simamora',
    'angkatan' => '2025'
];

// Run tests
$config_errors = validateEmailConfig();
$approval_test = null;
$rejection_test = null;

if (empty($config_errors)) {
    $approval_test = sendApprovalEmail($test_data);
    $rejection_test = sendRejectionEmail($test_data);
}
?>

<style>
.test-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Poppins', sans-serif;
}

.test-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin: 20px 0;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.test-section {
    margin: 25px 0;
    padding: 20px;
    border-radius: 10px;
}

.config-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.config-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.test-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.test-failure {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.test-info {
    background: #cce7ff;
    border: 1px solid #b6d7f2;
    color: #004085;
}

.config-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.config-item {
    background: white;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #1C5EBC;
}

.config-label {
    font-weight: 600;
    color: #495057;
    font-size: 12px;
    text-transform: uppercase;
}

.config-value {
    color: #212529;
    margin-top: 5px;
    word-break: break-all;
}

.btn {
    display: inline-block;
    padding: 12px 25px;
    background: #1C5EBC;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    margin: 10px 5px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background: #1555a8;
    transform: translateY(-2px);
}

.btn-test {
    background: #28a745;
}

.btn-test:hover {
    background: #1e7e34;
}

.log-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    max-height: 200px;
    overflow-y: auto;
    font-family: monospace;
    font-size: 12px;
}
</style>

<div class="test-container">
    <div class="test-card">
        <h1>🧪 Production Email System Test</h1>
        <p>Testing sistem email dengan Gmail SMTP untuk environment production</p>
        
        <!-- Configuration Test -->
        <div class="test-section <?php echo empty($config_errors) ? 'config-success' : 'config-error'; ?>">
            <h3>1. 🔧 Konfigurasi Email</h3>
            <?php if (!empty($config_errors)): ?>
                <p><strong>❌ Konfigurasi Bermasalah:</strong></p>
                <ul>
                    <?php foreach ($config_errors as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><small>Edit file: <code>includes/email_config.php</code></small></p>
            <?php else: ?>
                <p><strong>✅ Konfigurasi Valid</strong></p>
                
                <div class="config-grid">
                    <?php 
                    $config = getEmailConfig();
                    foreach ($config as $category => $settings): 
                        if (is_array($settings)):
                            foreach ($settings as $key => $value):
                                if ($key === 'password') {
                                    $value = '••••••••'; // Hide password
                                }
                    ?>
                    <div class="config-item">
                        <div class="config-label"><?php echo "$category.$key"; ?></div>
                        <div class="config-value"><?php echo htmlspecialchars($value); ?></div>
                    </div>
                    <?php 
                            endforeach;
                        endif;
                    endforeach; 
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Email Test Results -->
        <?php if (empty($config_errors)): ?>
        <div class="test-section test-info">
            <h3>2. 🚀 Test Pengiriman Email</h3>
            <p>Mengirim test email ke: <strong><?php echo htmlspecialchars($test_data['email']); ?></strong></p>
            
            <div style="display: flex; gap: 15px; margin: 15px 0;">
                <button onclick="runEmailTest()" class="btn btn-test">🔄 Run Tests</button>
                <a href="admin/manage_users.php" class="btn">⚙️ Admin Panel</a>
                <a href="email_preview.php" class="btn">📊 Email Logs</a>
            </div>
        </div>

        <div id="testResults">
            <?php if ($approval_test !== null): ?>
            <div class="test-section <?php echo $approval_test ? 'test-success' : 'test-failure'; ?>">
                <h4>Approval Email Test</h4>
                <p><?php echo $approval_test ? '✅ Email persetujuan berhasil dikirim' : '❌ Gagal mengirim email persetujuan'; ?></p>
            </div>
            
            <div class="test-section <?php echo $rejection_test ? 'test-success' : 'test-failure'; ?>">
                <h4>Rejection Email Test</h4>
                <p><?php echo $rejection_test ? '✅ Email penolakan berhasil dikirim' : '❌ Gagal mengirim email penolakan'; ?></p>
            </div>

            <!-- Email Logs -->
            <div class="test-section">
                <h4>📋 Email Logs</h4>
                <div class="log-section">
                    <?php
                    $log_file = __DIR__ . '/logs/email_sent.log';
                    if (file_exists($log_file)) {
                        echo nl2br(htmlspecialchars(file_get_contents($log_file)));
                    } else {
                        echo "No email logs found.";
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Troubleshooting Guide -->
        <div class="test-section test-info">
            <h3>3. 🔍 Troubleshooting Guide</h3>
            
            <h4>Common Issues:</h4>
            <ul>
                <li><strong>Authentication Failed:</strong> Pastikan menggunakan App Password, bukan password Gmail</li>
                <li><strong>Connection Timeout:</strong> Pastikan server memiliki akses internet ke smtp.gmail.com</li>
                <li><strong>Email not delivered:</strong> Cek spam folder, verifikasi email penerima</li>
            </ul>

            <h4>Setup App Password Gmail:</h4>
            <ol>
                <li>Aktifkan 2-Factor Authentication di Google Account</li>
                <li>Buka: <a href="https://myaccount.google.com/security" target="_blank">Google Security</a></li>
                <li>Cari "App passwords"</li>
                <li>Pilih "Mail" dan device, generate password</li>
                <li>Gunakan password yang di-generate di konfigurasi</li>
            </ol>
        </div>
    </div>
</div>

<script>
function runEmailTest() {
    const testResults = document.getElementById('testResults');
    testResults.innerHTML = '<div class="test-section test-info">🔄 Running tests...</div>';
    
    // Reload page to run tests
    setTimeout(() => {
        location.reload();
    }, 1000);
}
</script>

<?php include 'includes/footer.php'; ?>
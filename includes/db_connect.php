<?php
/**
 * File: includes/db_connect.php
 * Koneksi database dengan support login NIM dan fungsi helper terbaru
 */

// Cek apakah session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== KONFIGURASI DATABASE ====================
$host = "localhost";
$port = "3306";
$user = "root";
$pass = "";
$db   = "disertasi_s3";

// Load konfigurasi email
require_once 'email_config.php';

// Membuat koneksi dengan error handling
try {
    $conn = @mysqli_connect($host, $user, $pass, $db, $port);
    
    if (!$conn) {
        // Coba koneksi tanpa port (default)
        $conn = mysqli_connect($host, $user, $pass, $db);
    }
    
    if (!$conn) {
        throw new Exception("Koneksi database gagal: " . mysqli_connect_error());
    }
    
    // Set charset ke UTF-8
    mysqli_set_charset($conn, "utf8");
    
} catch (Exception $e) {
    $error_message = "
    <div style='font-family: Arial, sans-serif; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24; max-width: 600px; margin: 50px auto;'>
        <h3 style='margin-top: 0;'>❌ Koneksi Database Gagal</h3>
        <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        <hr>
        <h4>Cara Mengatasi:</h4>
        <ol>
            <li>Periksa konfigurasi database di file <code>includes/db_connect.php</code></li>
            <li>Pastikan database server berjalan</li>
            <li>Verifikasi username dan password database</li>
            <li>Pastikan database <strong>disertasi_s3</strong> sudah dibuat</li>
        </ol>
    </div>";
    
    die($error_message);
}

// ==================== FUNGSI DATABASE HELPER ====================

/**
 * Mencegah SQL Injection
 */
function escape_string($data) {
    global $conn;
    return mysqli_real_escape_string($conn, $data);
}

/**
 * Membersihkan input
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Query dengan error handling
 */
function db_query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Database Query Error: " . mysqli_error($conn));
        return false;
    }
    
    return $result;
}

/**
 * Mendapatkan satu row
 */
function db_fetch($query) {
    $result = db_query($query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Fetch semua rows
 */
function db_fetch_all($query) {
    $result = db_query($query);
    $rows = array();
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * Execute query (insert, update, delete)
 */
function db_execute($query) {
    return db_query($query);
}

// ==================== FUNGSI USER MANAGEMENT DENGAN NIM ====================

/**
 * Cari user berdasarkan username
 */
function get_user_by_username($conn, $username) {
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

/**
 * Cari user berdasarkan NIM
 */
function get_user_by_nim($conn, $nim) {
    $query = "SELECT * FROM users WHERE nim = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

/**
 * Validasi format NIM (9-15 digit angka)
 */
function validate_nim($nim) {
    return preg_match('/^[0-9]{9,15}$/', $nim);
}

/**
 * Dapatkan data user lengkap berdasarkan identifier (username atau NIM) - SUDAH DIUPDATE UNTUK DOSEN
 */
function get_user_by_identifier($conn, $identifier) {
    $query = "SELECT u.*, 
                     m.id_mahasiswa, m.nama_lengkap as nama_mahasiswa, m.nim, m.program_studi, m.angkatan, m.email as mahasiswa_email,
                     d.id_dosen, d.nama_lengkap as nama_dosen, d.nidn, d.jabatan, d.bidang_keahlian, d.email as email_dosen
              FROM users u 
              LEFT JOIN mahasiswa m ON u.id = m.user_id 
              LEFT JOIN dosen d ON u.id = d.user_id 
              WHERE (u.username = ? OR m.nim = ? OR d.nidn = ?) 
              AND u.status IN ('approved', 'active')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $identifier, $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

/**
 * Fungsi untuk mendapatkan data dosen berdasarkan user_id
 */
function get_dosen_data($conn, $user_id) {
    $query = "SELECT d.* FROM dosen d JOIN users u ON d.user_id = u.id 
            WHERE u.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

/**
 * Fungsi untuk mendapatkan data user by ID
 */
function getUserData($user_id) {
    $query = "SELECT u.*, m.nama_lengkap, m.nim, m.email, m.program_studi, m.angkatan 
              FROM users u 
              LEFT JOIN mahasiswa m ON u.id = m.user_id 
              WHERE u.id = " . (int)$user_id;
    return db_fetch($query);
}

/**
 * Cek apakah NIM sudah terdaftar di sistem
 */
function is_nim_registered($conn, $nim) {
    $query = "SELECT COUNT(*) as total FROM users WHERE nim = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] > 0;
}

/**
 * Cek apakah username sudah terdaftar
 */
function is_username_registered($conn, $username) {
    $query = "SELECT COUNT(*) as total FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] > 0;
}

/**
 * Cek apakah email sudah terdaftar di tabel mahasiswa
 */
function is_email_registered($conn, $email) {
    $query = "SELECT COUNT(*) as total FROM mahasiswa WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] > 0;
}

// ==================== FUNGSI EMAIL PRODUCTION DENGAN GMAIL SMTP ====================

/**
 * Fungsi utama untuk mengirim email dengan Gmail SMTP
 */
function sendEmailNotification($to, $subject, $message) {
    $config = getEmailConfig();
    
    // Validasi konfigurasi
    $config_errors = validateEmailConfig();
    if (!empty($config_errors)) {
        error_log("Email config errors: " . implode(", ", $config_errors));
        return false;
    }
    
    // Gunakan PHPMailer untuk production
    return sendEmailWithPHPMailer($to, $subject, $message);
}

/**
 * Mengirim email menggunakan PHPMailer dengan Gmail SMTP
 */
function sendEmailWithPHPMailer($to, $subject, $body) {
    require_once '../vendor/autoload.php';
    
    $config = getEmailConfig();
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings untuk Gmail SMTP
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port = $config['smtp']['port'];
        
        // Debug mode
        if ($config['app']['debug']) {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
        }
        
        // Recipients
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($to);
        $mail->addReplyTo($config['email']['reply_to'], $config['email']['from_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = createEmailTemplate($body, $subject);
        $mail->AltBody = strip_tags($body); // Fallback untuk email client non-HTML
        
        // Kirim email
        $result = $mail->send();
        
        // Log hasil pengiriman
        logEmailSent($to, $subject, $result);
        
        return $result;
        
    } catch (Exception $e) {
        // Log error
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        logEmailSent($to, $subject, false, $mail->ErrorInfo);
        return false;
    }
}

/**
 * Membuat template email yang konsisten
 */
function createEmailTemplate($content, $subject) {
    $config = getEmailConfig();
    
    return "
    <!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>$subject</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: 'Poppins', Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
                line-height: 1.6;
                color: #333;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .email-header {
                background: linear-gradient(135deg, #1C5EBC 0%, #1565c0 100%);
                color: white;
                padding: 30px 20px;
                text-align: center;
            }
            .email-header h1 {
                font-size: 24px;
                margin: 0;
                font-weight: 700;
            }
            .email-content {
                padding: 30px;
            }
            .email-footer {
                background: #f8f9fa;
                padding: 20px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #e9ecef;
            }
            .button {
                display: inline-block;
                background: #1C5EBC;
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 5px;
                margin: 15px 0;
                font-weight: 600;
            }
            .status-box {
                padding: 15px;
                border-radius: 5px;
                text-align: center;
                margin: 20px 0;
                border: 1px solid;
            }
            .status-approved {
                background: #d4edda;
                color: #155724;
                border-color: #c3e6cb;
            }
            .status-rejected {
                background: #f8d7da;
                color: #721c24;
                border-color: #f5c6cb;
            }
            .info-box {
                background: #e8f4fd;
                border: 1px solid #b6d7f2;
                border-radius: 5px;
                padding: 15px;
                margin: 15px 0;
            }
            .user-details {
                background: #f8f9fa;
                border-left: 4px solid #1C5EBC;
                padding: 15px;
                margin: 15px 0;
            }
            @media only screen and (max-width: 600px) {
                .email-container {
                    margin: 10px;
                    border-radius: 0;
                }
                .email-content {
                    padding: 20px;
                }
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='email-header'>
                <h1>📚 Sistem Disertasi S3 UKSW</h1>
            </div>
            <div class='email-content'>
                $content
            </div>
            <div class='email-footer'>
                <p><strong>Universitas Kristen Satya Wacana</strong></p>
                <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                <p>© " . date('Y') . " Sistem Disertasi S3 UKSW. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Log pengiriman email untuk monitoring
 */
function logEmailSent($to, $subject, $success, $error_message = null) {
    $log_dir = __DIR__ . '/../logs/';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $status = $success ? 'SUCCESS' : 'FAILED';
    $error_info = $error_message ? " | Error: $error_message" : "";
    
    $log_entry = date('Y-m-d H:i:s') . " | To: $to | Subject: $subject | Status: $status$error_info\n";
    
    file_put_contents($log_dir . 'email_sent.log', $log_entry, FILE_APPEND);
    
    // Juga log ke system error log
    error_log("Email sent: $to | $subject | $status");
}

/**
 * Fungsi untuk mengirim email persetujuan
 */
function sendApprovalEmail($user_data) {
    $config = getEmailConfig();
    $to = $user_data['email'];
    $subject = " Akun Anda Telah Disetujui - Sistem Disertasi S3 UKSW";
    
    $login_url = $config['urls']['login_url'];
    
    $message = "
        <h2>Selamat, {$user_data['nama_lengkap']}!</h2>
        
        <div class='status-box status-approved'>
            <strong>✅ STATUS AKUN: DISETUJUI</strong>
        </div>
        
        <p>Akun Anda di <strong>Sistem Disertasi S3 UKSW</strong> telah berhasil disetujui oleh administrator.</p>
        
        <div class='user-details'>
            <h4>📋 Detail Akun Anda:</h4>
            <p><strong>Nama:</strong> {$user_data['nama_lengkap']}</p>
            <p><strong>NIM:</strong> {$user_data['nim']}</p>
            <p><strong>Program Studi:</strong> {$user_data['program_studi']}</p>
            <p><strong>Angkatan:</strong> {$user_data['angkatan']}</p>
            <p><strong>Username:</strong> {$user_data['username']}</p>
            <p><strong>Status:</strong> <span style='color: green; font-weight: bold;'>AKTIF</span></p>
        </div>
        
        <p>Sekarang Anda dapat mengakses semua fitur dalam sistem menggunakan akun yang telah didaftarkan.</p>
        
        <div style='text-align: center; margin: 25px 0;'>
            <a href='$login_url' class='button'>🚀 Login ke Sistem</a>
        </div>
        
        <div class='info-box'>
            <h4>📝 Panduan Login:</h4>
            <ol>
                <li>Kunjungi halaman login: <a href='$login_url'>$login_url</a></li>
                <li>Masukkan username: <strong>{$user_data['username']}</strong> atau NIM: <strong>{$user_data['nim']}</strong></li>
                <li>Masukkan password yang Anda buat saat registrasi</li>
                <li>Klik tombol <strong>Login</strong></li>
            </ol>
        </div>
        
        <p><strong>📞 Butuh Bantuan?</strong></p>
        <p>Jika Anda mengalami kendala atau lupa password, silakan hubungi administrator:</p>
        <ul>
            <li>Email: {$config['admin']['email']}</li>
            <li>Telepon: {$config['admin']['phone']}</li>
        </ul>
        
        <p>Terima kasih telah bergabung dengan Sistem Disertasi S3 UKSW!</p>
    ";
    
    return sendEmailNotification($to, $subject, $message);
}

/**
 * Fungsi untuk mengirim email penolakan
 */
function sendRejectionEmail($user_data) {
    $config = getEmailConfig();
    $to = $user_data['email'];
    $subject = "❌ Status Pendaftaran Akun - Sistem Disertasi S3 UKSW";
    
    $message = "
        <h2>Kepada {$user_data['nama_lengkap']},</h2>
        
        <p>Kami memberitahukan bahwa pendaftaran akun Anda di <strong>Sistem Disertasi S3 UKSW</strong> <strong>tidak dapat disetujui</strong>.</p>
        
        <div class='status-box status-rejected'>
            <strong>❌ STATUS PENDAFTARAN: DITOLAK</strong>
        </div>
        
        <div class='user-details'>
            <h4>📋 Detail Pendaftaran:</h4>
            <p><strong>Nama:</strong> {$user_data['nama_lengkap']}</p>
            <p><strong>NIM:</strong> {$user_data['nim']}</p>
            <p><strong>Program Studi:</strong> {$user_data['program_studi']}</p>
            <p><strong>Username:</strong> {$user_data['username']}</p>
        </div>
        
        <div class='info-box'>
            <h4>📋 Alasan penolakan mungkin karena:</h4>
            <ul>
                <li>Data yang tidak lengkap atau tidak valid</li>
                <li>NIM tidak terdaftar dalam database akademik</li>
                <li>Duplikasi data atau username sudah digunakan</li>
                <li>Alasan administratif lainnya</li>
            </ul>
        </div>
        
        <p><strong>📞 Untuk Informasi Lebih Lanjut:</strong></p>
        <p>Jika Anda merasa ini adalah kesalahan, atau untuk informasi lebih lanjut, silakan hubungi:</p>
        <ul>
            <li>Email: {$config['admin']['email']}</li>
            <li>Telepon: {$config['admin']['phone']}</li>
            <li>Alamat: {$config['admin']['address']}</li>
        </ul>
        
        <p>Terima kasih atas pengertiannya.</p>
    ";
    
    return sendEmailNotification($to, $subject, $message);
}

/**
 * Fungsi untuk mengirim email notifikasi penambahan akun dosen
 */
function sendDosenAccountEmail($dosen_data, $password) {
    $config = getEmailConfig();
    $to = $dosen_data['email'];
    $subject = " Akun Dosen Berhasil Dibuat - Sistem Disertasi S3 UKSW";
    
    $login_url = $config['urls']['login_url'];

    // $login_url = $config['app']['base_url'] . "/login.php";
    
    $message = "
        <h2>Selamat, {$dosen_data['nama_lengkap']}!</h2>
        
        <div class='status-box status-approved'>
            <strong>✅ AKUN DOSEN BERHASIL DIBUAT</strong>
        </div>
        
        <p>Akun Anda sebagai <strong>Dosen</strong> di <strong>Sistem Disertasi S3 UKSW</strong> telah berhasil dibuat oleh administrator.</p>
        
        <div class='user-details'>
            <h4>📋 Detail Akun Anda:</h4>
            <p><strong>Nama Lengkap:</strong> {$dosen_data['nama_lengkap']}</p>
            <p><strong>NIDN:</strong> {$dosen_data['nidn']}</p>
            <p><strong>Bidang Keahlian:</strong> {$dosen_data['bidang_keahlian']}</p>
            <p><strong>Email:</strong> {$dosen_data['email']}</p>
            <p><strong>Username:</strong> <strong style='color: #1C5EBC;'>{$dosen_data['username']}</strong></p>
            <p><strong>Password:</strong> <strong style='color: #DC2626;'>{$password}</strong></p>
            <p><strong>Status:</strong> <span style='color: green; font-weight: bold;'>AKTIF</span></p>
        </div>
        
        <div class='info-box'>
            <h4>⚠️ Keamanan Akun:</h4>
            <p>Untuk keamanan akun Anda, disarankan untuk:</p>
            <ol>
                <li>Login segera setelah menerima email ini</li>
                <li>Mengganti password default dengan password yang lebih kuat</li>
                <li>Tidak membagikan username dan password kepada siapapun</li>
            </ol>
        </div>
        
        <p>Sekarang Anda dapat mengakses semua fitur dosen dalam sistem menggunakan akun yang telah dibuat.</p>
        
        <div style='text-align: center; margin: 25px 0;'>
            <a href='$login_url' class='button'>🚀 Login ke Sistem</a>
        </div>
        
        <div class='info-box'>
            <h4>📝 Panduan Login:</h4>
            <ol>
                <li>Kunjungi halaman login: <a href='$login_url'>$login_url</a></li>
                <li>Masukkan username: <strong>{$dosen_data['username']}</strong></li>
                <li>Masukkan password: <strong>{$password}</strong></li>
                <li>Klik tombol <strong>Login</strong></li>
                <li>Setelah login, segera ganti password di halaman profil</li>
            </ol>
        </div>
        
        <p><strong>📞 Butuh Bantuan?</strong></p>
        <p>Jika Anda mengalami kendala dalam login atau memiliki pertanyaan, silakan hubungi administrator:</p>
        <ul>
            <li>Email: {$config['admin']['email']}</li>
            <li>Telepon: {$config['admin']['phone']}</li>
        </ul>
        
        <p>Terima kasih telah bergabung sebagai Dosen di Sistem Disertasi S3 UKSW!</p>
    ";
    
    return sendEmailNotification($to, $subject, $message);
}

// ==================== FUNGSI TAMBAHAN UNTUK SISTEM UJIAN ====================

/**
 * Validasi tahapan ujian bertahap
 */
function validate_tahapan_ujian($conn, $id_mahasiswa, $jenis_ujian) {
    $tahapan = [
        'proposal' => 1,
        'kualifikasi' => 2, 
        'kelayakan' => 3,
        'tertutup' => 4
    ];
    
    $current_tahap = $tahapan[$jenis_ujian];
    
    // Ujian proposal selalu boleh
    if ($current_tahap == 1) {
        return ['boleh' => true, 'alasan' => ''];
    }
    
    // Cek semua tahap sebelumnya harus lulus
    foreach ($tahapan as $ujian => $tahap) {
        if ($tahap < $current_tahap) {
            $sql = "SELECT status_kelulusan FROM registrasi 
                    WHERE id_mahasiswa = ? AND jenis_ujian = ? 
                    ORDER BY id_registrasi DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $id_mahasiswa, $ujian);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if (!$row || $row['status_kelulusan'] != 'lulus') {
                return [
                    'boleh' => false, 
                    'alasan' => 'Anda harus lulus ujian ' . ucfirst($ujian) . ' terlebih dahulu sebelum mendaftar ujian ' . ucfirst($jenis_ujian)
                ];
            }
        }
    }
    
    return ['boleh' => true, 'alasan' => ''];
}

/**
 * Cek apakah mahasiswa sedang memiliki ujian yang pending
 */
function has_pending_ujian($conn, $id_mahasiswa, $jenis_ujian) {
    $sql = "SELECT COUNT(*) as total FROM registrasi 
            WHERE id_mahasiswa = ? AND jenis_ujian = ? AND status = 'Menunggu'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_mahasiswa, $jenis_ujian);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] > 0;
}
?>
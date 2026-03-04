<?php
/**
 * File: includes/email_sender.php
 * Fungsi untuk mengirim email notifikasi
 */

require_once 'email_config.php';
require_once '../vendor/autoload.php'; // Pastikan PHPMailer sudah diinstall

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Mengirim notifikasi pendaftaran ujian ke admin
 */
function sendRegistrationNotification($student_name, $student_nim, $exam_type, $registration_date) {
    $config = getEmailConfig();
    
    // Jika di development mode, log saja tanpa kirim email
    // if (isDevelopment()) {
    //     error_log("[DEV MODE] Email notifikasi untuk: $student_name - $exam_type");
    //     return true;
    // }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];
        
        if ($config['app']['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        // Recipients
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($config['admin']['email'], 'Admin S3 Ilmu Komputer');
        $mail->addReplyTo($config['email']['reply_to'], $config['email']['from_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = " Notifikasi Pendaftaran Ujian " . ucfirst($exam_type);
        
        $email_body = createRegistrationEmailTemplate($student_name, $student_nim, $exam_type, $registration_date);
        $mail->Body = $email_body;
        $mail->AltBody = createRegistrationTextTemplate($student_name, $student_nim, $exam_type, $registration_date);
        
        $mail->send();
        
        error_log("Email notifikasi berhasil dikirim untuk: $student_name - $exam_type");
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error - Pendaftaran $exam_type: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template HTML untuk email notifikasi
 */
/**
 * Template HTML untuk email notifikasi
 */
function createRegistrationEmailTemplate($student_name, $student_nim, $exam_type, $registration_date) {
    $config = getEmailConfig();
    $exam_type_display = ucfirst($exam_type);
    $verification_url = $config['urls']['base_url'] . '/admin/verifikasi.php';
    
    // Ambil nilai config ke variabel terpisah
    $from_name = $config['email']['from_name'];
    $admin_email = $config['admin']['email'];
    $admin_phone = $config['admin']['phone'];
    $admin_address = $config['admin']['address'];
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background: #f5f5f5;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%); 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 24px; 
                font-weight: 600;
            }
            .header h2 { 
                margin: 10px 0 0 0; 
                font-size: 18px; 
                font-weight: 400;
                opacity: 0.9;
            }
            .content { 
                padding: 30px; 
            }
            .notification-badge {
                background: #27DAA3;
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
                display: inline-block;
                margin-bottom: 20px;
            }
            .info-card {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .info-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
            }
            .info-table td { 
                padding: 12px 8px; 
                border-bottom: 1px solid #e9ecef; 
                vertical-align: top;
            }
            .info-table td:first-child { 
                font-weight: 600; 
                width: 35%; 
                color: #495057;
            }
            .action-button {
                display: inline-block;
                background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%);
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin: 20px 0;
                text-align: center;
            }
            .footer { 
                background: #343a40; 
                color: white; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px; 
            }
            .contact-info {
                margin: 10px 0;
                color: #adb5bd;
            }
            .urgent {
                color: #dc3545;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎓 SISTEM DISERTASI S3 ILMU KOMPUTER UKSW</h1>
                <h2>Notifikasi Pendaftaran Ujian Baru</h2>
            </div>
            
            <div class='content'>
                <div class='notification-badge'>PENDAFTARAN BARU</div>
                
                <p>Halo <strong>Admin S3 Ilmu Komputer</strong>,</p>
                <p>Mahasiswa berikut telah melakukan pendaftaran ujian melalui sistem:</p>
                
                <div class='info-card'>
                    <table class='info-table'>
                        <tr>
                            <td>👤 Nama Mahasiswa</td>
                            <td><strong>$student_name</strong></td>
                        </tr>
                        <tr>
                            <td>🎯 NIM</td>
                            <td>$student_nim</td>
                        </tr>
                        <tr>
                            <td>📝 Jenis Ujian</td>
                            <td><strong>$exam_type_display</strong></td>
                        </tr>
                        <tr>
                            <td>📅 Tanggal Pendaftaran</td>
                            <td>$registration_date</td>
                        </tr>
                        <tr>
                            <td>🔄 Status</td>
                            <td><span class='urgent'>MENUNGGU VERIFIKASI</span></td>
                        </tr>
                    </table>
                </div>
                
                <p style='text-align: center;'>
                    <strong>Segera lakukan verifikasi pendaftaran ini:</strong>
                </p>
                
                <p style='text-align: center;'>
                    <a href='$verification_url' class='action-button'>
                        📋 VERIFIKASI SEKARANG
                    </a>
                </p>
                
                <p><small>Link verifikasi: <a href='$verification_url'>$verification_url</a></small></p>
            </div>
            
            <div class='footer'>
                <p><strong>$from_name</strong></p>
                <div class='contact-info'>
                    <p>📧 $admin_email | 📞 $admin_phone</p>
                    <p>📍 $admin_address</p>
                </div>
                <p>Email ini dikirim secara otomatis dari Sistem Disertasi S3 Ilmu Komputer UKSW</p>
                <p>© " . date('Y') . " - Universitas Kristen Satya Wacana</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Template plain text untuk email notifikasi
 */
function createRegistrationTextTemplate($student_name, $student_nim, $exam_type, $registration_date) {
    $config = getEmailConfig();
    $verification_url = $config['urls']['base_url'] . '/admin/verifikasi.php';
    
    // Ambil nilai config ke variabel terpisah
    $from_name = $config['email']['from_name'];
    $admin_email = $config['admin']['email'];
    $admin_phone = $config['admin']['phone'];
    $admin_address = $config['admin']['address'];
    
    return "
NOTIFIKASI PENDAFTARAN UJIAN BARU
SISTEM DISERTASI S3 ILMU KOMPUTER UKSW

Halo Admin,

Mahasiswa berikut telah melakukan pendaftaran ujian:

Nama Mahasiswa: $student_name
NIM: $student_nim
Jenis Ujian: " . ucfirst($exam_type) . "
Tanggal Pendaftaran: $registration_date
Status: MENUNGGU VERIFIKASI

Segera lakukan verifikasi pendaftaran ini melalui link berikut:
$verification_url

Link Verifikasi: $verification_url

--
$from_name
$admin_email | $admin_phone
$admin_address

Email ini dikirim secara otomatis.
© " . date('Y') . " - Universitas Kristen Satya Wacana
    ";
}
/**
 * Validasi dan test konfigurasi email
 */
function testEmailConfiguration() {
    $config = getEmailConfig();
    $errors = validateEmailConfig();
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            error_log("Email Config Error: $error");
        }
        return false;
    }
    
    return true;
}

function sendVerificationNotification($student_email, $student_name, $exam_type, $verification_status, $admin_notes = '', $verification_date) {
    $config = getEmailConfig();
    
    // Jika di development mode, log saja tanpa kirim email
    // if (isDevelopment()) {
    //     error_log("[DEV MODE] Email verifikasi untuk: $student_name - Status: $verification_status");
    //     return true;
    // }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];
        
        if ($config['app']['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        // Recipients
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($student_email, $student_name);
        $mail->addReplyTo($config['email']['reply_to'], $config['email']['from_name']);
        
        // Content
        $mail->isHTML(true);
        
        if ($verification_status === 'Diterima') {
            $mail->Subject = "✅ Pendaftaran Ujian " . ucfirst($exam_type) . " Anda Diterima";
        } else {
            $mail->Subject = "❌ Pendaftaran Ujian " . ucfirst($exam_type) . " Anda Membutuhkan Perbaikan";
        }
        
        $email_body = createVerificationEmailTemplate($student_name, $exam_type, $verification_status, $admin_notes, $verification_date);
        $mail->Body = $email_body;
        $mail->AltBody = createVerificationTextTemplate($student_name, $exam_type, $verification_status, $admin_notes, $verification_date);
        
        $mail->send();
        
        error_log("Email verifikasi berhasil dikirim untuk: $student_name - Status: $verification_status");
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error - Verifikasi $exam_type: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template HTML untuk email verifikasi
 */
function createVerificationEmailTemplate($student_name, $exam_type, $verification_status, $admin_notes, $verification_date) {
    $config = getEmailConfig();
    $exam_type_display = ucfirst($exam_type);
    $dashboard_url = $config['urls']['base_url'] . '/mahasiswa/dashboard.php';
    $login_url = $config['urls']['login_url'];
    
    $status_color = $verification_status === 'Diterima' ? '#27DAA3' : '#FF6B6B';
    $status_icon = $verification_status === 'Diterima' ? '✅' : '❌';
    $status_title = $verification_status === 'Diterima' ? 'DITERIMA' : 'DITOLAK / PERLU PERBAIKAN';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background: #f5f5f5;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%); 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 24px; 
                font-weight: 600;
            }
            .content { 
                padding: 30px; 
            }
            .status-badge {
                background: $status_color;
                color: white;
                padding: 12px 24px;
                border-radius: 25px;
                font-size: 16px;
                font-weight: 600;
                display: inline-block;
                margin-bottom: 20px;
            }
            .info-card {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .info-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
            }
            .info-table td { 
                padding: 12px 8px; 
                border-bottom: 1px solid #e9ecef; 
                vertical-align: top;
            }
            .info-table td:first-child { 
                font-weight: 600; 
                width: 35%; 
                color: #495057;
            }
            .action-button {
                display: inline-block;
                background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%);
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin: 20px 0;
                text-align: center;
            }
            .notes-box {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                border-radius: 6px;
                padding: 15px;
                margin: 15px 0;
            }
            .footer { 
                background: #343a40; 
                color: white; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px; 
            }
            .contact-info {
                margin: 10px 0;
                color: #adb5bd;
            }
            .next-steps {
                background: #d1ecf1;
                border: 1px solid #bee5eb;
                border-radius: 6px;
                padding: 15px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎓 SISTEM DISERTASI S3 ILMU KOMPUTER UKSW</h1>
                <h2>Hasil Verifikasi Pendaftaran Ujian</h2>
            </div>
            
            <div class='content'>
                <div class='status-badge'>
                    $status_icon STATUS: $status_title
                </div>
                
                <p>Halo <strong>$student_name</strong>,</p>
                <p>Pendaftaran ujian Anda telah diverifikasi oleh Admin S3 Ilmu Komputer:</p>
                
                <div class='info-card'>
                    <table class='info-table'>
                        <tr>
                            <td>👤 Nama Mahasiswa</td>
                            <td><strong>$student_name</strong></td>
                        </tr>
                        <tr>
                            <td>📝 Jenis Ujian</td>
                            <td><strong>$exam_type_display</strong></td>
                        </tr>
                        <tr>
                            <td>📅 Tanggal Verifikasi</td>
                            <td>$verification_date</td>
                        </tr>
                        <tr>
                            <td>🔄 Status Verifikasi</td>
                            <td><strong style='color: $status_color;'>$verification_status</strong></td>
                        </tr>
                    </table>
                </div>
    " . ($verification_status === 'Diterima' ? "
                <div class='next-steps'>
                    <h3>🎉 Selamat! Langkah Selanjutnya:</h3>
                    <p>Pendaftaran ujian <strong>$exam_type_display</strong> Anda telah <strong>diterima</strong>. Silakan lanjutkan ke tahap berikutnya sesuai jadwal yang telah ditentukan.</p>
                </div>
    " : "
                <div class='notes-box'>
                    <h3>📝 Catatan dari Admin:</h3>
                    <p>" . ($admin_notes ? nl2br(htmlspecialchars($admin_notes)) : "Silakan perbaiki berkas pendaftaran Anda sesuai dengan ketentuan yang berlaku.") . "</p>
                </div>
                
                <div class='next-steps'>
                    <h3>🔧 Tindakan yang Diperlukan:</h3>
                    <p>Silakan perbaiki pendaftaran Anda dan kirim ulang melalui sistem.</p>
                </div>
    ") . "
                
                <p style='text-align: center;'>
                    <a href='$dashboard_url' class='action-button'>
                        📊 BUKA DASHBOARD
                    </a>
                </p>
                
                <p><small>Link dashboard: <a href='$dashboard_url'>$dashboard_url</a></small></p>
                <p><small>Jika mengalami kendala, silakan login di: <a href='$login_url'>$login_url</a></small></p>
            </div>
            
            <div class='footer'>
                <p><strong>{$config['email']['from_name']}</strong></p>
                <div class='contact-info'>
                    <p>📧 {$config['admin']['email']} | 📞 {$config['admin']['phone']}</p>
                    <p>📍 {$config['admin']['address']}</p>
                </div>
                <p>Email ini dikirim secara otomatis dari Sistem Disertasi S3 Ilmu Komputer UKSW</p>
                <p>© " . date('Y') . " - Universitas Kristen Satya Wacana</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Template plain text untuk email verifikasi
 */
function createVerificationTextTemplate($student_name, $exam_type, $verification_status, $admin_notes, $verification_date) {
    $config = getEmailConfig();
    $dashboard_url = $config['urls']['base_url'] . '/mahasiswa/dashboard.php';
    
    $status_message = $verification_status === 'Diterima' 
        ? "Pendaftaran ujian " . ucfirst($exam_type) . " Anda telah DITERIMA. Selamat!"
        : "Pendaftaran ujian " . ucfirst($exam_type) . " Anda DITOLAK atau membutuhkan PERBAIKAN.";
    
    return "
HASIL VERIFIKASI PENDAFTARAN UJIAN
SISTEM DISERTASI S3 ILMU KOMPUTER UKSW

Halo $student_name,

Pendaftaran ujian Anda telah diverifikasi oleh Admin S3 Ilmu Komputer.

$status_message

Detail Verifikasi:
- Nama Mahasiswa: $student_name
- Jenis Ujian: " . ucfirst($exam_type) . "
- Tanggal Verifikasi: $verification_date
- Status: $verification_status
" . ($admin_notes ? "
Catatan dari Admin:
$admin_notes
" : "") . "

" . ($verification_status === 'Diterima' ? "
Langkah Selanjutnya:
Pendaftaran Anda telah diterima. Silakan lanjutkan ke tahap berikutnya sesuai jadwal.
" : "
Tindakan yang Diperlukan:
Silakan perbaiki pendaftaran Anda dan kirim ulang melalui sistem.
") . "

Akses dashboard Anda di:
$dashboard_url

--
{$config['email']['from_name']}
{$config['admin']['email']} | {$config['admin']['phone']}
{$config['admin']['address']}

Email ini dikirim secara otomatis.
© " . date('Y') . " - Universitas Kristen Satya Wacana
    ";
}

/**
 * Mendapatkan data mahasiswa berdasarkan ID registrasi
 */
function getStudentDataByRegistration($conn, $id_registrasi) {
    $query = "SELECT m.email, m.nama_lengkap, m.nim, r.jenis_ujian 
              FROM registrasi r 
              JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
              WHERE r.id_registrasi = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_registrasi);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Mengirim notifikasi kelulusan ujian dan pembukaan akses ujian berikutnya
 */
function sendGraduationNotification($student_email, $student_name, $completed_exam, $next_exam_available, $graduation_date) {
    $config = getEmailConfig();
    
    // Jika di development mode, log saja tanpa kirim email
    // if (isDevelopment()) {
    //     error_log("[DEV MODE] Email kelulusan untuk: $student_name - Ujian: $completed_exam");
    //     return true;
    // }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];
        
        if ($config['app']['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        // Recipients
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($student_email, $student_name);
        $mail->addReplyTo($config['email']['reply_to'], $config['email']['from_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = " Selamat! Anda Lulus Ujian " . ucfirst($completed_exam) . " - Akses Ujian " . ucfirst($next_exam_available) . " Telah Dibuka";
        
        $email_body = createGraduationEmailTemplate($student_name, $completed_exam, $next_exam_available, $graduation_date);
        $mail->Body = $email_body;
        $mail->AltBody = createGraduationTextTemplate($student_name, $completed_exam, $next_exam_available, $graduation_date);
        
        $mail->send();
        
        error_log("Email kelulusan berhasil dikirim untuk: $student_name - Ujian: $completed_exam -> $next_exam_available");
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error - Kelulusan $completed_exam: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template HTML untuk email kelulusan
 */
function createGraduationEmailTemplate($student_name, $completed_exam, $next_exam_available, $graduation_date) {
    $config = getEmailConfig();
    $completed_exam_display = ucfirst($completed_exam);
    $next_exam_display = ucfirst($next_exam_available);
    $registration_url = $config['urls']['base_url'] . '/mahasiswa/registrasi.php';
    $dashboard_url = $config['urls']['base_url'] . '/mahasiswa/dashboard.php';
    
    // Tentukan pesan berdasarkan ujian yang diselesaikan
    $exam_messages = [
        'proposal' => [
            'title' => 'Ujian Proposal Berhasil Diselesaikan',
            'message' => 'Anda telah menyelesaikan ujian proposal dengan baik. Sekarang Anda dapat melanjutkan ke ujian kualifikasi.',
            'next_step' => 'Ujian Kualifikasi'
        ],
        'kualifikasi' => [
            'title' => 'Ujian Kualifikasi Berhasil Diselesaikan', 
            'message' => 'Selamat! Anda telah lulus ujian kualifikasi. Tahap selanjutnya adalah ujian kelayakan.',
            'next_step' => 'Ujian Kelayakan'
        ],
        'kelayakan' => [
            'title' => 'Ujian Kelayakan Berhasil Diselesaikan',
            'message' => 'Excellent! Anda telah menyelesaikan ujian kelayakan. Sekarang Anda dapat mengikuti ujian tertutup.',
            'next_step' => 'Ujian Tertutup'
        ],
        'tertutup' => [
            'title' => '🎓 SELAMAT! SEMUA UJIAN TELAH DISELESAIKAN',
            'message' => 'SELAMAT! Anda telah menyelesaikan semua tahap ujian disertasi. Ini adalah pencapaian yang luar biasa!',
            'next_step' => 'Proses Wisuda'
        ]
    ];
    
    $message_data = $exam_messages[$completed_exam] ?? $exam_messages['proposal'];
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .container { 
                max-width: 600px; 
                margin: 20px auto; 
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            .header { 
                background: linear-gradient(135deg, #27DAA3 0%, #20B38A 100%); 
                color: white; 
                padding: 40px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 28px; 
                font-weight: 700;
            }
            .header h2 { 
                margin: 10px 0 0 0; 
                font-size: 18px; 
                font-weight: 400;
                opacity: 0.9;
            }
            .content { 
                padding: 40px 30px; 
            }
            .congratulations {
                background: linear-gradient(135deg, #FFD93D 0%, #FF9A3D 100%);
                color: #333;
                padding: 20px;
                border-radius: 10px;
                text-align: center;
                margin: 20px 0;
                font-weight: 600;
                font-size: 18px;
            }
            .info-card {
                background: #f8f9fa;
                border: 2px solid #e9ecef;
                border-radius: 10px;
                padding: 25px;
                margin: 25px 0;
            }
            .info-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
            }
            .info-table td { 
                padding: 12px 8px; 
                border-bottom: 1px solid #e9ecef; 
                vertical-align: top;
            }
            .info-table td:first-child { 
                font-weight: 600; 
                width: 40%; 
                color: #495057;
            }
            .action-button {
                display: inline-block;
                background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%);
                color: white;
                padding: 15px 35px;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
                margin: 20px 0;
                text-align: center;
                transition: all 0.3s ease;
            }
            .action-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(84, 149, 255, 0.4);
            }
            .next-steps {
                background: linear-gradient(135deg, #6BCF7F 0%, #27DAA3 100%);
                color: white;
                border-radius: 10px;
                padding: 25px;
                margin: 25px 0;
            }
            .footer { 
                background: #343a40; 
                color: white; 
                padding: 25px; 
                text-align: center; 
                font-size: 12px; 
            }
            .contact-info {
                margin: 10px 0;
                color: #adb5bd;
            }
            .timeline {
                display: flex;
                justify-content: space-between;
                margin: 30px 0;
                position: relative;
            }
            .timeline::before {
                content: '';
                position: absolute;
                top: 20px;
                left: 10%;
                right: 10%;
                height: 3px;
                background: #27DAA3;
                z-index: 1;
            }
            .timeline-step {
                text-align: center;
                position: relative;
                z-index: 2;
                flex: 1;
            }
            .timeline-step.completed .step-icon {
                background: #27DAA3;
                color: white;
            }
            .timeline-step.current .step-icon {
                background: #5495FF;
                color: white;
                transform: scale(1.1);
            }
            .timeline-step .step-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: #e9ecef;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 10px;
                font-weight: bold;
                transition: all 0.3s ease;
            }
            .step-label {
                font-size: 12px;
                font-weight: 600;
                color: #495057;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎓 SISTEM DISERTASI S3 ILMU KOMPUTER UKSW</h1>
                <h2>Notifikasi Kelulusan Ujian</h2>
            </div>
            
            <div class='content'>
                <div class='congratulations'>
                    🎉 {$message_data['title']} 🎉
                </div>
                
                <p>Halo <strong>$student_name</strong>,</p>
                <p>{$message_data['message']}</p>
                
                " . ($completed_exam !== 'tertutup' ? "
                <div class='timeline'>
                    <div class='timeline-step completed'>
                        <div class='step-icon'>1</div>
                        <div class='step-label'>Proposal</div>
                    </div>
                    <div class='timeline-step " . ($completed_exam === 'proposal' ? 'current' : 'completed') . "'>
                        <div class='step-icon'>2</div>
                        <div class='step-label'>Kualifikasi</div>
                    </div>
                    <div class='timeline-step " . (in_array($completed_exam, ['kualifikasi', 'kelayakan', 'tertutup']) ? 'completed' : '') . "'>
                        <div class='step-icon'>3</div>
                        <div class='step-label'>Kelayakan</div>
                    </div>
                    <div class='timeline-step " . (in_array($completed_exam, ['kelayakan', 'tertutup']) ? 'completed' : '') . "'>
                        <div class='step-icon'>4</div>
                        <div class='step-label'>Tertutup</div>
                    </div>
                </div>
                " : "") . "
                
                <div class='info-card'>
                    <table class='info-table'>
                        <tr>
                            <td>👤 Nama Mahasiswa</td>
                            <td><strong>$student_name</strong></td>
                        </tr>
                        <tr>
                            <td>✅ Ujian yang Diselesaikan</td>
                            <td><strong>$completed_exam_display</strong></td>
                        </tr>
                        <tr>
                            <td>📅 Tanggal Kelulusan</td>
                            <td>$graduation_date</td>
                        </tr>
                        <tr>
                            <td>🚀 Status Selanjutnya</td>
                            <td><strong>" . ($completed_exam !== 'tertutup' ? "Akses $next_exam_display Telah Dibuka" : "SEMUA TAHAP TELAH SELESAI") . "</strong></td>
                        </tr>
                    </table>
                </div>
                
                " . ($completed_exam !== 'tertutup' ? "
                <div class='next-steps'>
                    <h3>📝 Langkah Selanjutnya: $next_exam_display</h3>
                    <p>Silakan daftar ujian <strong>$next_exam_display</strong> melalui sistem. Pastikan semua persyaratan telah dipersiapkan.</p>
                </div>
                
                <p style='text-align: center;'>
                    <a href='$registration_url' class='action-button'>
                        📋 DAFTAR UJIAN $next_exam_display
                    </a>
                </p>
                
                <p style='text-align: center;'>
                    <a href='$dashboard_url' style='color: #5495FF; text-decoration: none;'>
                        📊 atau Buka Dashboard
                    </a>
                </p>
                " : "
                <div class='next-steps'>
                    <h3>🎓 PENCAPAIAN LUAR BIASA!</h3>
                    <p>Anda telah menyelesaikan seluruh rangkaian ujian disertasi. Ini adalah pencapaian yang membanggakan!</p>
                    <p><strong>Selanjutnya:</strong> Proses administrasi wisuda dan persiapan sidang terbuka.</p>
                </div>
                
                <p style='text-align: center;'>
                    <a href='$dashboard_url' class='action-button'>
                        🎉 LIHAT DASHBOARD
                    </a>
                </p>
                ") . "
            </div>
            
            <div class='footer'>
                <p><strong>{$config['email']['from_name']}</strong></p>
                <div class='contact-info'>
                    <p>📧 {$config['admin']['email']} | 📞 {$config['admin']['phone']}</p>
                    <p>📍 {$config['admin']['address']}</p>
                </div>
                <p>Email ini dikirim secara otomatis dari Sistem Disertasi S3 Ilmu Komputer UKSW</p>
                <p>© " . date('Y') . " - Universitas Kristen Satya Wacana</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Template plain text untuk email kelulusan
 */
function createGraduationTextTemplate($student_name, $completed_exam, $next_exam_available, $graduation_date) {
    $config = getEmailConfig();
    $registration_url = $config['urls']['base_url'] . '/mahasiswa/registrasi.php';
    $dashboard_url = $config['urls']['base_url'] . '/mahasiswa/dashboard.php';
    
    $completed_exam_display = ucfirst($completed_exam);
    $next_exam_display = ucfirst($next_exam_available);
    
    if ($completed_exam === 'tertutup') {
        return "
SELAMAT! SEMUA UJIAN TELAH DISELESAIKAN
SISTEM DISERTASI S3 ILMU KOMPUTER UKSW

Halo $student_name,

🎉 SELAMAT! Anda telah menyelesaikan semua tahap ujian disertasi. 
Ini adalah pencapaian yang luar biasa!

Detail Kelulusan:
- Nama Mahasiswa: $student_name
- Ujian Terakhir: $completed_exam_display
- Tanggal Kelulusan: $graduation_date
- Status: SEMUA TAHAP TELAH SELESAI

Pencapaian Luar Biasa!
Anda telah menyelesaikan seluruh rangkaian ujian disertasi. 
Selanjutnya: Proses administrasi wisuda dan persiapan sidang terbuka.

Akses dashboard Anda di:
$dashboard_url

--
{$config['email']['from_name']}
{$config['admin']['email']} | {$config['admin']['phone']}
{$config['admin']['address']}

Email ini dikirim secara otomatis.
© " . date('Y') . " - Universitas Kristen Satya Wacana
        ";
    } else {
        return "
SELAMAT! ANDA LULUS UJIAN $completed_exam_display
SISTEM DISERTASI S3 ILMU KOMPUTER UKSW

Halo $student_name,

🎉 Selamat! Anda telah lulus ujian $completed_exam_display.

Detail Kelulusan:
- Nama Mahasiswa: $student_name
- Ujian yang Diselesaikan: $completed_exam_display
- Tanggal Kelulusan: $graduation_date
- Status Selanjutnya: Akses $next_exam_display Telah Dibuka

Langkah Selanjutnya: $next_exam_display
Silakan daftar ujian $next_exam_display melalui sistem. 
Pastikan semua persyaratan telah dipersiapkan.

Daftar Ujian Berikutnya:
$registration_url

Atau akses dashboard Anda:
$dashboard_url

--
{$config['email']['from_name']}
{$config['admin']['email']} | {$config['admin']['phone']}
{$config['admin']['address']}

Email ini dikirim secara otomatis.
© " . date('Y') . " - Universitas Kristen Satya Wacana
        ";
    }
}

/**
 * Mengirim notifikasi penugasan sebagai penguji/promotor ke dosen
 */
function sendDosenAssignmentNotification($dosen_email, $dosen_name, $role, $student_name, $student_nim, $exam_type, $exam_date, $exam_place, $judul_disertasi) {
    $config = getEmailConfig();
    
    // Jika di development mode, log saja tanpa kirim email
    // if (isDevelopment()) {
    //     error_log("[DEV MODE] Email penugasan untuk: $dosen_name - Role: $role");
    //     return true;
    // }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];
        
        if ($config['app']['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        // Recipients
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($dosen_email, $dosen_name);
        $mail->addReplyTo($config['email']['reply_to'], $config['email']['from_name']);
        
        // Content
        $mail->isHTML(true);
        
        // Sesuaikan subject berdasarkan role
        $role_display = ucfirst(str_replace('_', ' ', $role));
        $mail->Subject = " Penugasan Sebagai $role_display - Ujian " . ucfirst($exam_type);
        
        $email_body = createDosenAssignmentEmailTemplate($dosen_name, $role, $student_name, $student_nim, $exam_type, $exam_date, $exam_place, $judul_disertasi);
        $mail->Body = $email_body;
        $mail->AltBody = createDosenAssignmentTextTemplate($dosen_name, $role, $student_name, $student_nim, $exam_type, $exam_date, $exam_place, $judul_disertasi);
        
        $mail->send();
        
        error_log("Email penugasan berhasil dikirim ke: $dosen_name - Role: $role");
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error - Penugasan $role: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template HTML untuk email penugasan dosen
 */
function createDosenAssignmentEmailTemplate($dosen_name, $role, $student_name, $student_nim, $exam_type, $exam_date, $exam_place, $judul_disertasi) {
    $config = getEmailConfig();
    $role_display = ucfirst(str_replace('_', ' ', $role));
    $exam_type_display = ucfirst($exam_type);
    $exam_date_formatted = date('d F Y H:i', strtotime($exam_date));
    $login_url = $config['urls']['login_url'];
    $dashboard_url = $config['urls']['base_url'] . '/dosen/dashboard.php';
    
    // Tentukan ikon dan warna berdasarkan role
    $role_config = [
        'promotor' => ['icon' => '👨‍🏫', 'color' => '#27DAA3'],
        'co_promotor' => ['icon' => '👨‍🏫', 'color' => '#20B38A'],
        'co_promotor2' => ['icon' => '👨‍🏫', 'color' => '#1A9D76'],
        'penguji' => ['icon' => '📝', 'color' => '#5495FF']
    ];
    
    $role_style = $role_config[$role] ?? $role_config['penguji'];
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background: #f5f5f5;
            }
            .container { 
                max-width: 600px; 
                margin: 20px auto; 
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, {$role_style['color']} 0%, darken({$role_style['color']}, 10%) 100%); 
                color: white; 
                padding: 40px 30px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 26px; 
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 15px;
            }
            .header h2 { 
                margin: 10px 0 0 0; 
                font-size: 18px; 
                font-weight: 400;
                opacity: 0.9;
            }
            .content { 
                padding: 35px 30px; 
            }
            .role-badge {
                background: {$role_style['color']};
                color: white;
                padding: 12px 25px;
                border-radius: 25px;
                font-size: 16px;
                font-weight: 600;
                display: inline-block;
                margin-bottom: 25px;
                text-align: center;
            }
            .info-card {
                background: #f8f9fa;
                border: 2px solid #e9ecef;
                border-radius: 10px;
                padding: 25px;
                margin: 25px 0;
            }
            .info-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
            }
            .info-table td { 
                padding: 14px 10px; 
                border-bottom: 1px solid #e9ecef; 
                vertical-align: top;
            }
            .info-table td:first-child { 
                font-weight: 600; 
                width: 35%; 
                color: #495057;
            }
            .action-button {
                display: inline-block;
                background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%);
                color: white;
                padding: 14px 35px;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
                margin: 25px 0;
                text-align: center;
                transition: all 0.3s ease;
            }
            .action-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(84, 149, 255, 0.4);
            }
            .responsibilities {
                background: linear-gradient(135deg, #FFD93D 0%, #FF9A3D 100%);
                color: #333;
                border-radius: 10px;
                padding: 25px;
                margin: 25px 0;
            }
            .footer { 
                background: #343a40; 
                color: white; 
                padding: 25px; 
                text-align: center; 
                font-size: 12px; 
            }
            .contact-info {
                margin: 15px 0;
                color: #adb5bd;
            }
            .deadline {
                background: #dc3545;
                color: white;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                text-align: center;
                font-weight: 600;
            }
            .important-note {
                border-left: 4px solid {$role_style['color']};
                background: #e8f4ff;
                padding: 15px;
                margin: 20px 0;
            }
            .timeline {
                display: flex;
                gap: 20px;
                margin: 30px 0;
                flex-wrap: wrap;
            }
            .timeline-item {
                flex: 1;
                min-width: 150px;
                text-align: center;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
                border: 1px solid #e9ecef;
            }
            .timeline-item .icon {
                font-size: 24px;
                margin-bottom: 10px;
                display: block;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>
                    {$role_style['icon']} PENUGASAN SEBAGAI $role_display
                </h1>
                <h2>Sistem Disertasi S3 Ilmu Komputer UKSW</h2>
            </div>
            
            <div class='content'>
                <div class='role-badge'>
                    {$role_style['icon']} ANDA DITUGASKAN SEBAGAI: $role_display
                </div>
                
                <p>Yth. <strong>Bapak/Ibu $dosen_name</strong>,</p>
                <p>Anda telah ditugaskan sebagai <strong>$role_display</strong> untuk ujian disertasi mahasiswa berikut:</p>
                
                <div class='info-card'>
                    <table class='info-table'>
                        <tr>
                            <td>🎓 Mahasiswa</td>
                            <td><strong>$student_name</strong><br><small>NIM: $student_nim</small></td>
                        </tr>
                        <tr>
                            <td>📝 Jenis Ujian</td>
                            <td><strong>$exam_type_display</strong></td>
                        </tr>
                        <tr>
                            <td>📅 Tanggal Ujian</td>
                            <td><strong>$exam_date_formatted</strong></td>
                        </tr>
                        <tr>
                            <td>📍 Tempat Ujian</td>
                            <td><strong>$exam_place</strong></td>
                        </tr>
                        <tr>
                            <td>📋 Judul Disertasi</td>
                            <td><em>\"$judul_disertasi\"</em></td>
                        </tr>
                    </table>
                </div>
                
                <div class='responsibilities'>
                    <h4>📌 TUGAS DAN TANGGUNG JAWAB:</h4>
                    " . getResponsibilitiesByRole($role, $exam_type) . "
                </div>
                
                <div class='important-note'>
                    <h5>⚠️ INFORMASI PENTING:</h5>
                    <p>1. Pastikan untuk mempersiapkan diri sebelum ujian<br>
                    2. Akses sistem untuk melihat detail lengkap dan dokumen terkait<br>
                    3. Konfirmasi kehadiran minimal 3 hari sebelum ujian</p>
                </div>
                
                <div class='timeline'>
                    <div class='timeline-item'>
                        <span class='icon'>📅</span>
                        <strong>Persiapan</strong><br>
                        Persiapan materi ujian
                    </div>
                    <div class='timeline-item'>
                        <span class='icon'>📝</span>
                        <strong>Review</strong><br>
                        Review dokumen disertasi
                    </div>
                    <div class='timeline-item'>
                        <span class='icon'>🎯</span>
                        <strong>Ujian</strong><br>
                        Pelaksanaan ujian
                    </div>
                    <div class='timeline-item'>
                        <span class='icon'>📊</span>
                        <strong>Penilaian</strong><br>
                        Pengisian form penilaian
                    </div>
                </div>
                
                <p style='text-align: center;'>
                    <a href='$dashboard_url' class='action-button'>
                        📋 BUKA DASHBOARD DOSEN
                    </a>
                </p>
                
                <p style='text-align: center;'>
                    <small>Link akses: <a href='$login_url'>$login_url</a></small>
                </p>
                
                <div class='deadline'>
                    ⏰ DEADLINE: Silakan konfirmasi dan persiapkan materi sebelum tanggal ujian
                </div>
            </div>
            
            <div class='footer'>
                <p><strong>{$config['email']['from_name']}</strong></p>
                <div class='contact-info'>
                    <p>📧 {$config['admin']['email']} | 📞 {$config['admin']['phone']}</p>
                    <p>📍 {$config['admin']['address']}</p>
                </div>
                <p>Email ini dikirim secara otomatis dari Sistem Disertasi S3 Ilmu Komputer UKSW</p>
                <p>© " . date('Y') . " - Universitas Kristen Satya Wacana</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Template plain text untuk email penugasan dosen
 */
function createDosenAssignmentTextTemplate($dosen_name, $role, $student_name, $student_nim, $exam_type, $exam_date, $exam_place, $judul_disertasi) {
    $config = getEmailConfig();
    $role_display = ucfirst(str_replace('_', ' ', $role));
    $exam_type_display = ucfirst($exam_type);
    $exam_date_formatted = date('d F Y H:i', strtotime($exam_date));
    $login_url = $config['urls']['login_url'];
    
    return "
PENUGASAN SEBAGAI $role_display
SISTEM DISERTASI S3 ILMU KOMPUTER UKSW

Yth. Bapak/Ibu $dosen_name,

Anda telah ditugaskan sebagai $role_display untuk ujian disertasi mahasiswa:

DETAIL PENUGASAN:
- Nama Mahasiswa: $student_name
- NIM: $student_nim  
- Jenis Ujian: $exam_type_display
- Tanggal Ujian: $exam_date_formatted
- Tempat Ujian: $exam_place
- Judul Disertasi: \"$judul_disertasi\"
- Peran Anda: $role_display

TUGAS DAN TANGGUNG JAWAB:
" . getResponsibilitiesTextByRole($role, $exam_type) . "

INFORMASI PENTING:
1. Pastikan untuk mempersiapkan diri sebelum ujian
2. Akses sistem untuk melihat detail lengkap dan dokumen terkait
3. Konfirmasi kehadiran minimal 3 hari sebelum ujian

TIMELINE:
📅 Persiapan - Persiapan materi ujian
📝 Review - Review dokumen disertasi
🎯 Ujian - Pelaksanaan ujian  
📊 Penilaian - Pengisian form penilaian

DEADLINE: ⏰
Silakan konfirmasi dan persiapkan materi sebelum tanggal ujian

Akses sistem di: $login_url

--
{$config['email']['from_name']}
{$config['admin']['email']} | {$config['admin']['phone']}
{$config['admin']['address']}

Email ini dikirim secara otomatis.
© " . date('Y') . " - Universitas Kristen Satya Wacana
    ";
}

/**
 * Fungsi untuk mendapatkan tanggung jawab berdasarkan role
 */
function getResponsibilitiesByRole($role, $exam_type) {
    $responsibilities = [
        'promotor' => [
            'proposal' => "1. Memimpin sesi ujian proposal<br>2. Memberikan arahan dan bimbingan<br>3. Mengevaluasi kesesuaian proposal dengan bidang ilmu<br>4. Memberikan rekomendasi perbaikan",
            'kualifikasi' => "1. Memimpin ujian kualifikasi<br>2. Mengevaluasi pemahaman teori<br>3. Menilai kemampuan analisis mahasiswa<br>4. Memberikan feedback konstruktif",
            'kelayakan' => "1. Memimpin ujian kelayakan<br>2. Menilai kesiapan penelitian<br>3. Mengevaluasi metodologi<br>4. Memberikan persetujuan kelayakan",
            'tertutup' => "1. Memimpin sidang tertutup<br>2. Mengevaluasi hasil penelitian<br>3. Menilai kontribusi ilmiah<br>4. Membimbing finalisasi disertasi"
        ],
        'co_promotor' => [
            'proposal' => "1. Mendampingi promotor dalam evaluasi<br>2. Memberikan perspektif spesifik bidang<br>3. Menilai aspek metodologis<br>4. Memberikan masukan teknis",
            'kualifikasi' => "1. Mendampingi evaluasi kualifikasi<br>2. Menilai pemahaman bidang khusus<br>3. Memberikan feedback spesifik<br>4. Membantu penilaian kemampuan analisis",
            'kelayakan' => "1. Mendampingi penilaian kelayakan<br>2. Mengevaluasi aspek teknis penelitian<br>3. Memberikan masukan metodologis<br>4. Menilai kesiapan instrumen penelitian",
            'tertutup' => "1. Mendampingi sidang tertutup<br>2. Mengevaluasi aspek teknis hasil<br>3. Memberikan masukan spesifik bidang<br>4. Membantu finalisasi"
        ],
        'co_promotor2' => [
            'proposal' => "1. Memberikan perspektif tambahan<br>2. Menilai aspek interdisipliner<br>3. Memberikan masukan implementasi<br>4. Mendukung evaluasi komprehensif",
            'kualifikasi' => "1. Memberikan evaluasi komplementer<br>2. Menilai integrasi pengetahuan<br>3. Memberikan perspektif aplikatif<br>4. Mendukung penilaian menyeluruh",
            'kelayakan' => "1. Memberikan penilaian kelayakan dari sudut pandang berbeda<br>2. Mengevaluasi aspek implementasi<br>3. Memberikan masukan praktis<br>4. Mendukung keputusan kelayakan",
            'tertutup' => "1. Memberikan evaluasi tambahan<br>2. Menilai aspek aplikatif hasil<br>3. Memberikan masukan publikasi<br>4. Mendukung finalisasi"
        ],
        'penguji' => [
            'proposal' => "1. Mengevaluasi kualitas proposal<br>2. Memberikan pertanyaan kritis<br>3. Menilai orisinalitas ide<br>4. Memberikan rekomendasi perbaikan<br>5. Mengisi form penilaian proposal",
            'kualifikasi' => "1. Mengevaluasi penguasaan materi<br>2. Memberikan pertanyaan komprehensif<br>3. Menilai kemampuan analisis<br>4. Mengevaluasi metodologi yang diusulkan<br>5. Mengisi form penilaian kualifikasi",
            'kelayakan' => "1. Mengevaluasi kesiapan penelitian<br>2. Menilai kelayakan metodologi<br>3. Memberikan masukan perbaikan<br>4. Mengevaluasi rencana penelitian<br>5. Mengisi form penilaian kelayakan",
            'tertutup' => "1. Mengevaluasi hasil penelitian<br>2. Memberikan pertanyaan mendalam<br>3. Menilai kontribusi ilmiah<br>4. Mengevaluasi kesesuaian dengan tujuan<br>5. Mengisi form penilaian tertutup"
        ]
    ];
    
    $default = "1. Hadir tepat waktu sesuai jadwal<br>2. Membaca dokumen terkait sebelum ujian<br>3. Memberikan evaluasi yang objektif<br>4. Mengisi form penilaian sesuai ketentuan<br>5. Memberikan feedback yang konstruktif";
    
    return $responsibilities[$role][$exam_type] ?? $default;
}

/**
 * Fungsi untuk mendapatkan tanggung jawab dalam format text
 */
function getResponsibilitiesTextByRole($role, $exam_type) {
    $html = getResponsibilitiesByRole($role, $exam_type);
    return strip_tags(str_replace("<br>", "\n", $html));
}

/**
 * Mengirim notifikasi ke mahasiswa bahwa penguji telah ditetapkan
 */
function sendStudentNotificationPenguji($student_email, $student_name, $exam_type, $exam_date, $exam_place) {
    $config = getEmailConfig();
    
    // if (isDevelopment()) {
    //     error_log("[DEV MODE] Email notifikasi penguji ke mahasiswa: $student_name");
    //     return true;
    // }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];
        
        if ($config['app']['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        // Recipients
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($student_email, $student_name);
        $mail->addReplyTo($config['email']['reply_to'], $config['email']['from_name']);
        
        // Content
        $mail->isHTML(true);
        $exam_type_display = ucfirst($exam_type);
        $mail->Subject = " Penguji Ujian $exam_type_display Anda Telah Ditentukan";
        
        $exam_date_formatted = date('d F Y H:i', strtotime($exam_date));
        $dashboard_url = $config['urls']['base_url'] . '/mahasiswa/dashboard.php';
        
        $email_body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Segoe UI', sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; }
                .info-card { background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 10px; padding: 20px; margin: 20px 0; }
                .action-button { display: inline-block; background: #27DAA3; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>👥 Penguji Ujian Telah Ditentukan</h2>
                    <p>Sistem Disertasi S3 Ilmu Komputer UKSW</p>
                </div>
                
                <div class='content'>
                    <p>Halo <strong>$student_name</strong>,</p>
                    <p>Penguji untuk ujian <strong>$exam_type_display</strong> Anda telah ditetapkan oleh admin:</p>
                    
                    <div class='info-card'>
                        <p><strong>📅 Tanggal Ujian:</strong> $exam_date_formatted</p>
                        <p><strong>📍 Tempat Ujian:</strong> $exam_place</p>
                        <p><strong>📝 Jenis Ujian:</strong> $exam_type_display</p>
                    </div>
                    
                    <p><strong>🎯 Langkah Selanjutnya:</strong></p>
                    <ol>
                        <li>Persiapkan diri untuk ujian sesuai jadwal</li>
                        <li>Periksa jadwal dan tempat ujian di dashboard</li>
                        <li>Pastikan semua dokumen telah lengkap</li>
                        <li>Hubungi promotor untuk bimbingan terakhir jika diperlukan</li>
                    </ol>
                    
                    <p style='text-align: center;'>
                        <a href='$dashboard_url' class='action-button'>
                            📊 BUKA DASHBOARD
                        </a>
                    </p>
                    
                    <p><em>Semoga sukses dalam ujian Anda!</em> </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->Body = $email_body;
        $mail->AltBody = "Penguji Ujian $exam_type_display Anda Telah Ditentukan\n\nTanggal: $exam_date_formatted\nTempat: $exam_place\n\nAkses dashboard untuk info lengkap.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error - Notifikasi Penguji ke Mahasiswa: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Mengirim notifikasi revisi dari dosen ke mahasiswa
 * DIGUNAKAN DI: form_penilaian.php ketika dosen memberi catatan revisi
 */
function sendRevisionNotification($student_email, $student_name, $dosen_name, $exam_type, $revision_notes, $deadline_days = 7) {
    $config = getEmailConfig();
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];
        
        if ($config['app']['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        // Recipients
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($student_email, $student_name);
        $mail->addReplyTo($config['email']['reply_to'], $config['email']['from_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = " Revisi Disertasi " . ucfirst($exam_type) . " dari " . $dosen_name;
        
        $email_body = createRevisionEmailTemplate($student_name, $dosen_name, $exam_type, $revision_notes, $deadline_days);
        $mail->Body = $email_body;
        $mail->AltBody = createRevisionTextTemplate($student_name, $dosen_name, $exam_type, $revision_notes, $deadline_days);
        
        $mail->send();
        
        error_log("Email notifikasi revisi berhasil dikirim ke mahasiswa: $student_name - dari: $dosen_name");
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error - Notifikasi revisi ke mahasiswa: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template HTML untuk email revisi dari dosen ke mahasiswa
 */
function createRevisionEmailTemplate($student_name, $dosen_name, $exam_type, $revision_notes, $deadline_days) {
    $config = getEmailConfig();
    $exam_type_display = ucfirst($exam_type);
    $revision_url = $config['urls']['base_url'] . '/mahasiswa/revisi_disertasi.php';
    $deadline_date = date('d F Y', strtotime("+$deadline_days days"));
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background: #f5f5f5;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #FF6B6B 0%, #FF8E8E 100%); 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 24px; 
                font-weight: 600;
            }
            .header h2 { 
                margin: 10px 0 0 0; 
                font-size: 18px; 
                font-weight: 400;
                opacity: 0.9;
            }
            .content { 
                padding: 30px; 
            }
            .revision-badge {
                background: #FF6B6B;
                color: white;
                padding: 10px 20px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
                display: inline-block;
                margin-bottom: 20px;
            }
            .notes-box {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
                border-left: 4px solid #ffc107;
            }
            .deadline-box {
                background: #dc3545;
                color: white;
                border-radius: 8px;
                padding: 15px;
                margin: 20px 0;
                text-align: center;
                font-weight: 600;
            }
            .action-button {
                display: inline-block;
                background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%);
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin: 20px 0;
                text-align: center;
            }
            .info-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
            }
            .info-table td { 
                padding: 12px 8px; 
                border-bottom: 1px solid #e9ecef; 
                vertical-align: top;
            }
            .info-table td:first-child { 
                font-weight: 600; 
                width: 35%; 
                color: #495057;
            }
            .footer { 
                background: #343a40; 
                color: white; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px; 
            }
            .contact-info {
                margin: 10px 0;
                color: #adb5bd;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📝 REVISI DISERTASI</h1>
                <h2>Notifikasi Revisi dari Dosen</h2>
            </div>
            
            <div class='content'>
                <div class='revision-badge'>
                    ⚠️ PERLU REVISI
                </div>
                
                <p>Halo <strong>$student_name</strong>,</p>
                <p>Anda menerima notifikasi revisi untuk ujian <strong>$exam_type_display</strong> dari:</p>
                
                <div style='background: #f8f9fa; border-radius: 8px; padding: 15px; margin: 15px 0;'>
                    <table class='info-table'>
                        <tr>
                            <td>👨‍🏫 Dosen Pemberi Revisi</td>
                            <td><strong>$dosen_name</strong></td>
                        </tr>
                        <tr>
                            <td>📝 Jenis Ujian</td>
                            <td><strong>$exam_type_display</strong></td>
                        </tr>
                        <tr>
                            <td>📅 Batas Waktu</td>
                            <td><strong>$deadline_date</strong> (7 hari)</td>
                        </tr>
                    </table>
                </div>
                
                <div class='deadline-box'>
                    ⏰ DEADLINE: $deadline_date
                </div>
                
                <div class='notes-box'>
                    <h4>📋 Catatan Revisi dari $dosen_name:</h4>
                    <p>" . nl2br(htmlspecialchars($revision_notes)) . "</p>
                </div>
                
                <p><strong>🎯 Tindakan yang Diperlukan:</strong></p>
                <ol>
                    <li>Baca dan pahami catatan revisi di atas</li>
                    <li>Lakukan perbaikan pada disertasi Anda</li>
                    <li>Upload file revisi melalui sistem</li>
                    <li>Berikan catatan tentang revisi yang telah dilakukan</li>
                    <li>Kirim sebelum batas waktu yang ditentukan</li>
                </ol>
                
                <p style='text-align: center;'>
                    <a href='$revision_url' class='action-button'>
                        📤 UPLOAD REVISI SEKARANG
                    </a>
                </p>
                
                <p><small>Link upload revisi: <a href='$revision_url'>$revision_url</a></small></p>
                
                <p><strong>💡 Tips:</strong></p>
                <ul>
                    <li>Pastikan file revisi dalam format PDF/DOC/DOCX</li>
                    <li>Maksimal ukuran file 10MB</li>
                    <li>Berikan catatan yang jelas tentang perubahan yang dilakukan</li>
                    <li>Jika ada kendala, hubungi dosen pembimbing</li>
                </ul>
            </div>
            
            <div class='footer'>
                <p><strong>{$config['email']['from_name']}</strong></p>
                <div class='contact-info'>
                    <p>📧 {$config['admin']['email']} | 📞 {$config['admin']['phone']}</p>
                    <p>📍 {$config['admin']['address']}</p>
                </div>
                <p>Email ini dikirim secara otomatis dari Sistem Disertasi S3 Ilmu Komputer UKSW</p>
                <p>© " . date('Y') . " - Universitas Kristen Satya Wacana</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Template plain text untuk email revisi dari dosen ke mahasiswa
 */
function createRevisionTextTemplate($student_name, $dosen_name, $exam_type, $revision_notes, $deadline_days) {
    $config = getEmailConfig();
    $revision_url = $config['urls']['base_url'] . '/mahasiswa/revisi_disertasi.php';
    $deadline_date = date('d F Y', strtotime("+$deadline_days days"));
    
    return "
NOTIFIKASI REVISI DISERTASI
SISTEM DISERTASI S3 ILMU KOMPUTER UKSW

Halo $student_name,

Anda menerima notifikasi revisi untuk ujian " . ucfirst($exam_type) . " dari $dosen_name.

Detail:
- Dosen Pemberi Revisi: $dosen_name
- Jenis Ujian: " . ucfirst($exam_type) . "
- Batas Waktu: $deadline_date (7 hari)

Catatan Revisi dari $dosen_name:
$revision_notes

Tindakan yang Diperlukan:
1. Baca dan pahami catatan revisi
2. Lakukan perbaikan pada disertasi
3. Upload file revisi melalui sistem
4. Berikan catatan tentang revisi yang telah dilakukan
5. Kirim sebelum batas waktu

Upload revisi di: $revision_url

Link: $revision_url

DEADLINE: $deadline_date

Tips:
- Format file: PDF, DOC, DOCX
- Maksimal ukuran: 10MB
- Berikan catatan yang jelas
- Hubungi dosen jika ada kendala

--
{$config['email']['from_name']}
{$config['admin']['email']} | {$config['admin']['phone']}
{$config['admin']['address']}

Email ini dikirim secara otomatis.
© " . date('Y') . " - Universitas Kristen Satya Wacana
    ";
}

/**
 * Mengirim notifikasi ke DOSEN SPESIFIK bahwa mahasiswa telah mengirim revisi
 * HANYA dikirim ke dosen yang memberikan catatan revisi tersebut
 * DIGUNAKAN DI: revisi_disertasi.php ketika mahasiswa upload revisi
 */
function sendRevisionToSpecificDosen($dosen_email, $dosen_name, $student_name, $student_nim, $exam_type, $revision_notes, $file_name, $original_notes = '') {
    $config = getEmailConfig();
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];
        
        if ($config['app']['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        // Recipients
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($dosen_email, $dosen_name);
        $mail->addReplyTo($config['email']['reply_to'], $config['email']['from_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = " Revisi dari $student_name untuk Catatan Anda";
        
        $email_body = createRevisionToDosenEmailTemplate($dosen_name, $student_name, $student_nim, $exam_type, $revision_notes, $file_name, $original_notes);
        $mail->Body = $email_body;
        $mail->AltBody = createRevisionToDosenTextTemplate($dosen_name, $student_name, $student_nim, $exam_type, $revision_notes, $file_name, $original_notes);
        
        $mail->send();
        
        error_log("Email notifikasi revisi ke dosen spesifik berhasil dikirim ke: $dosen_name");
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error - Notifikasi revisi ke dosen spesifik: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template HTML untuk email revisi ke dosen spesifik
 */
function createRevisionToDosenEmailTemplate($dosen_name, $student_name, $student_nim, $exam_type, $revision_notes, $file_name, $original_notes = '') {
    $config = getEmailConfig();
    $exam_type_display = ucfirst($exam_type);
    $review_url = $config['urls']['base_url'] . '/dosen/daftar_ujian.php';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background: #f5f5f5;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%); 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 24px; 
                font-weight: 600;
            }
            .header h2 { 
                margin: 10px 0 0 0; 
                font-size: 18px; 
                font-weight: 400;
                opacity: 0.9;
            }
            .content { 
                padding: 30px; 
            }
            .personal-badge {
                background: #3B82F6;
                color: white;
                padding: 10px 20px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
                display: inline-block;
                margin-bottom: 20px;
            }
            .info-card {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .original-notes {
                background: #fef3c7;
                border: 1px solid #fde68a;
                border-radius: 8px;
                padding: 15px;
                margin: 15px 0;
                border-left: 4px solid #f59e0b;
            }
            .revision-notes {
                background: #dbeafe;
                border: 1px solid #93c5fd;
                border-radius: 8px;
                padding: 15px;
                margin: 15px 0;
                border-left: 4px solid #3B82F6;
            }
            .file-info {
                background: #d1fae5;
                border: 1px solid #a7f3d0;
                border-radius: 8px;
                padding: 15px;
                margin: 15px 0;
            }
            .action-button {
                display: inline-block;
                background: linear-gradient(135deg, #10B981 0%, #059669 100%);
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin: 20px 0;
                text-align: center;
            }
            .info-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
            }
            .info-table td { 
                padding: 12px 8px; 
                border-bottom: 1px solid #e9ecef; 
                vertical-align: top;
            }
            .info-table td:first-child { 
                font-weight: 600; 
                width: 40%; 
                color: #495057;
            }
            .footer { 
                background: #343a40; 
                color: white; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px; 
            }
            .contact-info {
                margin: 10px 0;
                color: #adb5bd;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📝 REVISI UNTUK CATATAN ANDA</h1>
                <h2>Mahasiswa Telah Memberikan Tanggapan</h2>
            </div>
            
            <div class='content'>
                <div class='personal-badge'>
                    👨‍🏫 REVISI KHUSUS UNTUK ANDA
                </div>
                
                <p>Yth. <strong>Bapak/Ibu $dosen_name</strong>,</p>
                <p>Mahasiswa berikut telah mengirimkan revisi <strong>khusus untuk catatan yang Anda berikan</strong>:</p>
                
                <div class='info-card'>
                    <table class='info-table'>
                        <tr>
                            <td>👤 Mahasiswa</td>
                            <td><strong>$student_name</strong></td>
                        </tr>
                        <tr>
                            <td>🎯 NIM</td>
                            <td>$student_nim</td>
                        </tr>
                        <tr>
                            <td>📝 Ujian</td>
                            <td><strong>$exam_type_display</strong></td>
                        </tr>
                        <tr>
                            <td>📅 Tanggal Kirim</td>
                            <td>" . date('d F Y H:i') . "</td>
                        </tr>
                    </table>
                </div>
                
                " . ($original_notes ? "
                <div class='original-notes'>
                    <h4>📋 Catatan Awal Anda:</h4>
                    <p>" . nl2br(htmlspecialchars($original_notes)) . "</p>
                </div>
                " : "") . "
                
                <div class='file-info'>
                    <h4>📎 File Revisi yang Dikirim:</h4>
                    <p><strong>$file_name</strong></p>
                    <p><small>File ini merupakan respons khusus untuk catatan Anda</small></p>
                </div>
                
                <div class='revision-notes'>
                    <h4>💬 Tanggapan Mahasiswa:</h4>
                    <p>" . ($revision_notes ? nl2br(htmlspecialchars($revision_notes)) : "Mahasiswa tidak memberikan catatan tambahan.") . "</p>
                </div>
                
                <p><strong>🎯 Mohon Review Revisi Ini:</strong></p>
                <ol>
                    <li>Periksa apakah revisi sudah sesuai dengan catatan Anda</li>
                    <li>Evaluasi kualitas perbaikan yang dilakukan</li>
                    <li>Berikan feedback lebih lanjut jika diperlukan</li>
                    <li>Tentukan status revisi (Disetujui/Perlu Perbaikan)</li>
                </ol>
                
                <p style='text-align: center;'>
                    <a href='$review_url' class='action-button'>
                        👁️ REVIEW REVISI INI
                    </a>
                </p>
                
                <p><small>Link review: <a href='$review_url'>$review_url</a></small></p>
                
                <p><em>Catatan: Email ini hanya dikirim kepada Anda karena mahasiswa merespons catatan spesifik yang Anda berikan.</em></p>
            </div>
            
            <div class='footer'>
                <p><strong>{$config['email']['from_name']}</strong></p>
                <div class='contact-info'>
                    <p>📧 {$config['admin']['email']} | 📞 {$config['admin']['phone']}</p>
                    <p>📍 {$config['admin']['address']}</p>
                </div>
                <p>Email ini dikirim secara otomatis dari Sistem Disertasi S3 Ilmu Komputer UKSW</p>
                <p>© " . date('Y') . " - Universitas Kristen Satya Wacana</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Template plain text untuk email revisi ke dosen spesifik
 */
function createRevisionToDosenTextTemplate($dosen_name, $student_name, $student_nim, $exam_type, $revision_notes, $file_name, $original_notes = '') {
    $config = getEmailConfig();
    $review_url = $config['urls']['base_url'] . '/dosen/daftar_ujian.php';
    
    return "
REVISI KHUSUS UNTUK CATATAN ANDA
SISTEM DISERTASI S3 ILMU KOMPUTER UKSW

Yth. Bapak/Ibu $dosen_name,

Mahasiswa berikut telah mengirimkan revisi KHUSUS untuk catatan yang Anda berikan:

Mahasiswa: $student_name
NIM: $student_nim
Ujian: " . ucfirst($exam_type) . "
Tanggal Kirim: " . date('d F Y H:i') . "

" . ($original_notes ? "Catatan Awal Anda:
$original_notes

" : "") . "File Revisi: $file_name
(File ini merupakan respons khusus untuk catatan Anda)

Tanggapan Mahasiswa:
" . ($revision_notes ? $revision_notes : "Mahasiswa tidak memberikan catatan tambahan.") . "

Mohon Review Revisi Ini:
1. Periksa apakah revisi sudah sesuai dengan catatan Anda
2. Evaluasi kualitas perbaikan yang dilakukan
3. Berikan feedback lebih lanjut jika diperlukan
4. Tentukan status revisi (Disetujui/Perlu Perbaikan)

Review revisi di: $review_url

Link: $review_url

CATATAN: Email ini hanya dikirim kepada Anda karena mahasiswa merespons 
catatan spesifik yang Anda berikan.

--
{$config['email']['from_name']}
{$config['admin']['email']} | {$config['admin']['phone']}
{$config['admin']['address']}

Email ini dikirim secara otomatis.
© " . date('Y') . " - Universitas Kristen Satya Wacana
    ";
}

/**
 * Mengirim notifikasi hasil review revisi dari dosen ke mahasiswa
 */
function sendRevisionReviewNotification($student_email, $student_name, $dosen_name, $exam_type, $review_status, $review_notes, $review_date) {
    $config = getEmailConfig();
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];
        
        if ($config['app']['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        // Recipients
        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($student_email, $student_name);
        $mail->addReplyTo($config['email']['reply_to'], $config['email']['from_name']);
        
        // Content
        $mail->isHTML(true);
        
        if ($review_status === 'diterima') {
            $mail->Subject = " Revisi Disertasi Anda Diterima oleh " . $dosen_name;
        } else {
            $mail->Subject = " Revisi Disertasi Anda Perlu Perbaikan dari " . $dosen_name;
        }
        
        $email_body = createRevisionReviewEmailTemplate($student_name, $dosen_name, $exam_type, $review_status, $review_notes, $review_date);
        $mail->Body = $email_body;
        $mail->AltBody = createRevisionReviewTextTemplate($student_name, $dosen_name, $exam_type, $review_status, $review_notes, $review_date);
        
        $mail->send();
        
        error_log("Email notifikasi review revisi berhasil dikirim ke: $student_name - Status: $review_status");
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error - Review revisi: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template HTML untuk email hasil review revisi
 */
function createRevisionReviewEmailTemplate($student_name, $dosen_name, $exam_type, $review_status, $review_notes, $review_date) {
    $config = getEmailConfig();
    $exam_type_display = ucfirst($exam_type);
    $dashboard_url = $config['urls']['base_url'] . '/mahasiswa/dashboard.php';
    $revision_url = $config['urls']['base_url'] . '/mahasiswa/revisi_disertasi.php';
    
    $status_color = $review_status === 'diterima' ? '#27DAA3' : '#FF6B6B';
    $status_icon = $review_status === 'diterima' ? '✅' : '❌';
    $status_title = $review_status === 'diterima' ? 'DITERIMA' : 'DITOLAK / PERLU PERBAIKAN';
    $status_message = $review_status === 'diterima' 
        ? "Revisi disertasi Anda untuk ujian <strong>$exam_type_display</strong> telah <strong>diterima</strong> oleh dosen."
        : "Revisi disertasi Anda untuk ujian <strong>$exam_type_display</strong> <strong>ditolak</strong> dan membutuhkan perbaikan lebih lanjut.";
    
    $next_steps = $review_status === 'diterima'
        ? "<p>🎉 <strong>Selamat!</strong> Revisi Anda telah disetujui.</p>"
        : "<p>🔧 <strong>Tindakan yang diperlukan:</strong> Silakan perbaiki revisi sesuai catatan dari dosen dan kirim ulang.</p>";
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background: #f5f5f5;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, {$status_color} 0%, darken({$status_color}, 10%) 100%); 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 24px; 
                font-weight: 600;
            }
            .header h2 { 
                margin: 10px 0 0 0; 
                font-size: 18px; 
                font-weight: 400;
                opacity: 0.9;
            }
            .content { 
                padding: 30px; 
            }
            .status-badge {
                background: {$status_color};
                color: white;
                padding: 12px 24px;
                border-radius: 25px;
                font-size: 16px;
                font-weight: 600;
                display: inline-block;
                margin-bottom: 20px;
            }
            .info-card {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .info-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 15px 0; 
            }
            .info-table td { 
                padding: 12px 8px; 
                border-bottom: 1px solid #e9ecef; 
                vertical-align: top;
            }
            .info-table td:first-child { 
                font-weight: 600; 
                width: 35%; 
                color: #495057;
            }
            .action-button {
                display: inline-block;
                background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%);
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin: 20px 0;
                text-align: center;
                transition: all 0.2s ease;
            }
            .action-button:hover {
                background: #3D7FE8;
                transform: translateY(-1px);
            }
            .notes-box {
                background: " . ($review_status === 'diterima' ? '#d1fae5' : '#fee2e2') . ";
                border: 1px solid " . ($review_status === 'diterima' ? '#a7f3d0' : '#fecaca') . ";
                border-radius: 6px;
                padding: 20px;
                margin: 20px 0;
                border-left: 4px solid {$status_color};
            }
            .footer { 
                background: #343a40; 
                color: white; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px; 
            }
            .contact-info {
                margin: 10px 0;
                color: #adb5bd;
            }
            .next-steps {
                background: " . ($review_status === 'diterima' ? '#e8f4fd' : '#fef3c7') . ";
                border: 1px solid " . ($review_status === 'diterima' ? '#b6e0fe' : '#fde68a') . ";
                border-radius: 6px;
                padding: 20px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📝 HASIL REVIEW REVISI DISERTASI</h1>
                <h2>Notifikasi dari Dosen Penguji</h2>
            </div>
            
            <div class='content'>
                <div class='status-badge'>
                    {$status_icon} STATUS: {$status_title}
                </div>
                
                <p>Halo <strong>$student_name</strong>,</p>
                <p>$status_message</p>
                
                <div class='info-card'>
                    <table class='info-table'>
                        <tr>
                            <td>👨‍🏫 Dosen Reviewer</td>
                            <td><strong>$dosen_name</strong></td>
                        </tr>
                        <tr>
                            <td>📝 Jenis Ujian</td>
                            <td><strong>$exam_type_display</strong></td>
                        </tr>
                        <tr>
                            <td>📅 Tanggal Review</td>
                            <td>$review_date</td>
                        </tr>
                        <tr>
                            <td>🔄 Status Review</td>
                            <td><strong style='color: $status_color;'>" . ($review_status === 'diterima' ? 'Diterima' : 'Ditolak / Perlu Perbaikan') . "</strong></td>
                        </tr>
                    </table>
                </div>
                
                " . ($review_notes ? "
                <div class='notes-box'>
                    <h4>📋 Catatan dari $dosen_name:</h4>
                    <p>" . nl2br(htmlspecialchars($review_notes)) . "</p>
                </div>
                " : "") . "
                
                <div class='next-steps'>
                    <h4>🎯 Langkah Selanjutnya:</h4>
                    $next_steps
                    
                    " . ($review_status === 'diterima' ? "
                    <p>Revisi Anda telah disetujui.</p>
                    " : "
                    <p>Silakan perbaiki revisi berdasarkan catatan di atas dan kirim ulang melalui sistem.</p>
                    ") . "
                </div>
                
                <p style='text-align: center;'>
                    " . ($review_status === 'diterima' ? "
                    <a href='$dashboard_url' class='action-button'>
                        📊 BUKA DASHBOARD
                    </a>
                    " : "
                    <a href='$revision_url' class='action-button'>
                        📤 KIRIM ULANG REVISI
                    </a>
                    ") . "
                </p>
                
                <p><small>Link akses: " . ($review_status === 'diterima' ? "<a href='$dashboard_url'>$dashboard_url</a>" : "<a href='$revision_url'>$revision_url</a>") . "</small></p>
                
                <p><em>Jika ada pertanyaan, silakan hubungi dosen pembimbing Anda.</em></p>
            </div>
            
            <div class='footer'>
                <p><strong>{$config['email']['from_name']}</strong></p>
                <div class='contact-info'>
                    <p>📧 {$config['admin']['email']} | 📞 {$config['admin']['phone']}</p>
                    <p>📍 {$config['admin']['address']}</p>
                </div>
                <p>Email ini dikirim secara otomatis dari Sistem Disertasi S3 Ilmu Komputer UKSW</p>
                <p>© " . date('Y') . " - Universitas Kristen Satya Wacana</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Template plain text untuk email hasil review revisi
 */
function createRevisionReviewTextTemplate($student_name, $dosen_name, $exam_type, $review_status, $review_notes, $review_date) {
    $config = getEmailConfig();
    $dashboard_url = $config['urls']['base_url'] . '/mahasiswa/dashboard.php';
    $revision_url = $config['urls']['base_url'] . '/mahasiswa/revisi_disertasi.php';
    
    $status_message = $review_status === 'diterima'
        ? "Revisi disertasi Anda untuk ujian " . ucfirst($exam_type) . " telah DITERIMA oleh $dosen_name."
        : "Revisi disertasi Anda untuk ujian " . ucfirst($exam_type) . " DITOLAK dan membutuhkan perbaikan lebih lanjut.";
    
    return "
HASIL REVIEW REVISI DISERTASI
SISTEM DISERTASI S3 ILMU KOMPUTER UKSW

Halo $student_name,

$status_message

Detail Review:
- Dosen Reviewer: $dosen_name
- Jenis Ujian: " . ucfirst($exam_type) . "
- Tanggal Review: $review_date
- Status: " . ($review_status === 'diterima' ? 'DITERIMA' : 'DITOLAK / PERLU PERBAIKAN') . "
" . ($review_notes ? "
Catatan dari $dosen_name:
$review_notes
" : "") . "

Langkah Selanjutnya:
" . ($review_status === 'diterima' ? "
🎉 Selamat! Revisi Anda telah disetujui. Anda dapat melanjutkan ke tahap berikutnya.
Akses dashboard Anda di: $dashboard_url
" : "
🔧 Tindakan yang diperlukan: Silakan perbaiki revisi berdasarkan catatan di atas dan kirim ulang.
Kirim ulang revisi di: $revision_url
") . "

Jika ada pertanyaan, silakan hubungi dosen pembimbing Anda.

--
{$config['email']['from_name']}
{$config['admin']['email']} | {$config['admin']['phone']}
{$config['admin']['address']}

Email ini dikirim secara otomatis.
© " . date('Y') . " - Universitas Kristen Satya Wacana
    ";
}

/**
 * Mendapatkan data mahasiswa berdasarkan ID penilaian
 */
function getStudentDataByPenilaian($conn, $id_penilaian) {
    $query = "SELECT m.email, m.nama_lengkap, m.nim, r.jenis_ujian, 
                     d.nama_lengkap as nama_dosen
              FROM penilaian_ujian p
              JOIN registrasi r ON p.id_registrasi = r.id_registrasi
              JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
              JOIN dosen d ON p.id_dosen = d.id_dosen
              WHERE p.id_penilaian = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_penilaian);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

?>
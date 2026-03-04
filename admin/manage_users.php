<?php
/**
 * File: admin/manage_users.php  
 * Halaman untuk admin mengelola pendaftaran user dengan notifikasi email production
 * SUDAH DIUPDATE SESUAI STRUKTUR DATABASE ANDA
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

$success = '';
$error = '';

// Proses persetujuan/penolakan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek apakah ini proses tambah dosen
    if (isset($_POST['add_dosen'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password']; // Simpan password asli untuk dikirim via email
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $nidn = trim($_POST['nidn']);
        $email = trim($_POST['email']);
        $bidang_keahlian = trim($_POST['bidang_keahlian']);
        
        // Validasi input
        if (empty($username) || empty($password) || empty($nama_lengkap) || empty($nidn) || empty($email)) {
            $error = "Semua field wajib diisi!";
        } else {
            // Cek apakah username sudah ada
            $check_user = mysqli_query($conn, "SELECT id FROM users WHERE username = '" . escape_string($username) . "'");
            if (mysqli_num_rows($check_user) > 0) {
                $error = "Username sudah digunakan!";
            } else {
                // Cek apakah email sudah ada di tabel dosen
                $check_email = mysqli_query($conn, "SELECT id_dosen FROM dosen WHERE email = '" . escape_string($email) . "'");
                if (mysqli_num_rows($check_email) > 0) {
                    $error = "Email sudah digunakan!";
                } else {
                    // Hash password untuk disimpan di database
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Mulai transaction
                    mysqli_begin_transaction($conn);
                    
                    try {
                        // Insert ke tabel users
                        $user_query = "INSERT INTO users (username, password, role, status) 
                                      VALUES ('" . escape_string($username) . "', 
                                              '" . escape_string($hashed_password) . "', 
                                              'dosen', 
                                              'approved')";
                        
                        if (mysqli_query($conn, $user_query)) {
                            $user_id = mysqli_insert_id($conn);
                            
                            // Insert ke tabel dosen
                            $dosen_query = "INSERT INTO dosen (user_id, nama_lengkap, nidn, email, bidang_keahlian, status) 
                                           VALUES ('" . (int)$user_id . "', 
                                                   '" . escape_string($nama_lengkap) . "', 
                                                   '" . escape_string($nidn) . "', 
                                                   '" . escape_string($email) . "', 
                                                   '" . escape_string($bidang_keahlian) . "', 
                                                   'active')";
                            
                            if (mysqli_query($conn, $dosen_query)) {
                                mysqli_commit($conn);
                                
                                // Kirim email notifikasi ke dosen
                                $dosen_data = [
                                    'username' => $username,
                                    'nama_lengkap' => $nama_lengkap,
                                    'nidn' => $nidn,
                                    'email' => $email,
                                    'bidang_keahlian' => $bidang_keahlian
                                ];
                                
                                $email_result = sendDosenAccountEmail($dosen_data, $password);
                                
                                if ($email_result) {
                                    $success = "Akun dosen <strong>{$nama_lengkap}</strong> berhasil ditambahkan! ✅ Email notifikasi telah dikirim ke {$email}.";
                                } else {
                                    $success = "Akun dosen <strong>{$nama_lengkap}</strong> berhasil ditambahkan! ⚠️ Berhasil disimpan tapi gagal mengirim email notifikasi.";
                                    error_log("Failed to send dosen account email to: " . $email);
                                }
                                
                            } else {
                                throw new Exception("Gagal menambahkan data dosen: " . mysqli_error($conn));
                            }
                        } else {
                            throw new Exception("Gagal menambahkan user: " . mysqli_error($conn));
                        }
                    } catch (Exception $e) {
                        mysqli_rollback($conn);
                        $error = $e->getMessage();
                    }
                }
            }
        }
    } else {
        // Proses persetujuan/penolakan user mahasiswa (kode yang sudah ada)
        $user_id = $_POST['user_id'];
        $action = $_POST['action'];
        
        // Ambil data user sebelum update - menggunakan fungsi dari db_connect.php
        $user_data = getUserData($user_id);
        
        if (!$user_data) {
            $error = "User tidak ditemukan!";
        } else {
            if ($action === 'approve') {
                $query = "UPDATE users SET status = 'approved' WHERE id = " . (int)$user_id;
                $message = "User <strong>{$user_data['nama_lengkap']}</strong> berhasil disetujui.";
                
                // Kirim email notifikasi persetujuan
                $email_result = sendApprovalEmail($user_data);
                
            } elseif ($action === 'reject') {
                $query = "UPDATE users SET status = 'rejected' WHERE id = " . (int)$user_id;
                $message = "User <strong>{$user_data['nama_lengkap']}</strong> berhasil ditolak.";
                
                // Kirim email notifikasi penolakan
                $email_result = sendRejectionEmail($user_data);
            }
            
            if (mysqli_query($conn, $query)) {
                $success = $message;
                if (isset($email_result)) {
                    if ($email_result) {
                        $success .= " ✅ Email notifikasi telah dikirim ke {$user_data['email']}.";
                    } else {
                        $success .= " ⚠️ Berhasil disimpan tapi gagal mengirim email notifikasi.";
                        // Log error lebih detail
                        error_log("Failed to send email to: " . $user_data['email']);
                    }
                }
            } else {
                $error = "Terjadi kesalahan: " . mysqli_error($conn);
            }
        }
    }
}

// Filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';

// Query berdasarkan filter - SESUAI STRUKTUR TABEL ANDA
$where_conditions = [];
if ($status_filter !== 'all') {
    $where_conditions[] = "u.status = '" . escape_string($status_filter) . "'";
}

if ($role_filter !== 'all') {
    $where_conditions[] = "u.role = '" . escape_string($role_filter) . "'";
} else {
    // Default tampilkan mahasiswa dan dosen
    $where_conditions[] = "(u.role = 'mahasiswa' OR u.role = 'dosen')";
}

$where_clause = count($where_conditions) > 0 ? implode(' AND ', $where_conditions) : "1=1";

$query = "SELECT u.*, 
                 COALESCE(m.nama_lengkap, d.nama_lengkap) as nama_lengkap, 
                 m.nim, 
                 COALESCE(m.email, d.email) as email, 
                 m.program_studi, 
                 m.angkatan,
                 d.nidn,
                 d.jabatan,
                 d.bidang_keahlian,
                 d.status as status_dosen
          FROM users u 
          LEFT JOIN mahasiswa m ON u.id = m.user_id 
          LEFT JOIN dosen d ON u.id = d.user_id 
          WHERE $where_clause
          ORDER BY u.role, u.created_at DESC";
$result = mysqli_query($conn, $query);

$page_title = "Kelola User - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_admin.php';

// Cek konfigurasi email
$config_errors = validateEmailConfig();
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* Global Styles */
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(123.95deg, #EEF2FF 0%, #F8F9FC 29.06%);
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

/* Content Wrapper Fix */
.content-wrapper {
    margin-left: 0;
    background: transparent;
    min-height: 100vh;
}

/* Main Content dengan Sidebar */
@media (min-width: 769px) {
    .content-wrapper {
        margin-left: 269px;
    }
}

/* Hero Section - Sesuai Figma */
.hero-section {
    position: relative;
    width: 100%;
    height: 355px;
    margin-bottom: 36px;
    overflow: hidden;
}

/* Background Image */
.hero-section::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 355px;
    left: 0;
    top: 0;
    background: url('../assets/foto_header.png') center/cover;
    z-index: 0;
}

/* Gradient Overlay */
.hero-section::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 355px;
    left: 0;
    top: 0;
    background: linear-gradient(90deg, rgba(109, 150, 101, 0.6) 0%, rgba(124, 105, 65, 0.6) 31.25%, rgba(0, 0, 0, 0.8) 98.08%);
    z-index: 1;
}

/* Hero Content - Sesuai Figma Position */
.hero-content {
    position: absolute;
    width: auto;
    max-width: 90%;
    height: auto;
    left: 59px;
    top: 147px;
    z-index: 10;
}

.hero-content h1 {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 700;
    font-size: 25px;
    line-height: 38px;
    letter-spacing: 0.01em;
    color: #FFFFFF;
    margin: 0 0 8px 0;
}

.hero-breadcrumb {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 500;
    font-size: 15px;
    line-height: 22px;
    letter-spacing: 0.01em;
    color: #FFFFFF;
    margin: 0;
}

.hero-breadcrumb .separator {
    margin: 0 8px;
}

/* Main Container */
.main-container {
    position: relative;
    padding: 37px 36px 60px 36px;
    max-width: 1440px;
    margin: 0 auto;
    background: transparent;
}

/* Page Title */
.page-title {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 20.5475px;
    line-height: 31px;
    letter-spacing: 0.03em;
    color: #000000;
    margin: 0 0 3px 0;
}

.title-divider {
    position: relative;
    max-width: 100%;
    width: 100%;
    height: 0px;
    border: 1px solid #000000;
    margin: 16px 0 24px 0;
}

/* Alert Messages */
.alert-custom {
    background: #FFFFFF;
    border: 0.5px solid #C4C4C4;
    box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.08);
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    line-height: 20px;
    letter-spacing: 0.01em;
}

.alert-success {
    border-left: 4px solid #27DAA3;
    background: #F0FDF9;
    color: #065F46;
}

.alert-danger {
    border-left: 4px solid #FF5252;
    background: #FEF2F2;
    color: #991B1B;
}

.alert-icon {
    font-size: 18px;
    flex-shrink: 0;
}

.alert-close {
    margin-left: auto;
    background: transparent;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #6B7280;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.alert-close:hover {
    color: #374151;
}

/* Filter Section */
.filter-section {
    background: #FFFFFF;
    border: 0.3px solid #E5E7EB;
    box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 20px 24px;
    margin-bottom: 20px;
}

.filter-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.filter-header {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #000000;
    margin: 0;
}

.filter-select {
    width: 100%;
    max-width: 280px;
    height: 42px;
    background: #FFFFFF;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    padding: 0 14px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 13px;
    line-height: 20px;
    letter-spacing: 0.01em;
    color: #374151;
    box-sizing: border-box;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #5495FF;
    box-shadow: 0 0 0 3px rgba(84, 149, 255, 0.1);
}

.filter-select:hover {
    border-color: #9CA3AF;
}

/* Table Card */
.table-card {
    background: #FFFFFF;
    border: 0.3px solid #E5E7EB;
    box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 40px;
}

.table-wrapper {
    overflow-x: auto;
}

/* Table Styles */
.table-custom {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.table-custom thead {
    background: #F9FAFB;
    border-bottom: 1px solid #E5E7EB;
}

.table-custom thead th {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 12px;
    line-height: 18px;
    letter-spacing: 0.02em;
    color: #374151;
    padding: 14px 18px;
    text-align: left;
    white-space: nowrap;
}

.table-custom tbody td {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 13px;
    line-height: 20px;
    letter-spacing: 0.01em;
    color: #1F2937;
    padding: 14px 18px;
    border-bottom: 1px solid #F3F4F6;
    vertical-align: middle;
}

.table-custom tbody tr:last-child td {
    border-bottom: none;
}

.table-custom tbody tr:hover {
    background: #F9FAFB;
}

/* Username Styling */
.username-cell {
    font-weight: 600;
    color: #111827;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 14px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 500;
    font-size: 11px;
    line-height: 16px;
    letter-spacing: 0.02em;
    text-align: center;
    white-space: nowrap;
}

.badge-menunggu {
    background: #FEF3C7;
    color: #D97706;
    border: 0.5px solid #F59E0B;
}

.badge-diterima {
    background: #D1FAE5;
    color: #059669;
    border: 0.5px solid #10B981;
}

.badge-ditolak {
    background: #FEE2E2;
    color: #DC2626;
    border: 0.5px solid #EF4444;
}

/* Action Buttons */
.action-group {
    display: flex;
    gap: 6px;
    flex-wrap: nowrap;
}

.btn-action {
    padding: 6px 12px;
    border-radius: 5px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 500;
    font-size: 11px;
    line-height: 16px;
    letter-spacing: 0.01em;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);
}

.btn-approve {
    background: #10B981;
    color: #FFFFFF;
}

.btn-approve:hover {
    background: #059669;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.btn-reject {
    background: #EF4444;
    color: #FFFFFF;
}

.btn-reject:hover {
    background: #DC2626;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.text-no-action {
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    color: #9CA3AF;
    font-style: italic;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-state-icon {
    font-size: 72px;
    color: #D1D5DB;
    margin-bottom: 20px;
    opacity: 0.8;
}

.empty-state-title {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 18px;
    line-height: 27px;
    color: #4B5563;
    margin: 0 0 8px 0;
}

.empty-state-text {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 14px;
    line-height: 21px;
    color: #6B7280;
    margin: 0 0 28px 0;
}

.btn-primary-custom {
    background: #5495FF;
    color: #FFFFFF;
    padding: 10px 24px;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
    box-shadow: 0px 2px 4px rgba(84, 149, 255, 0.3);
}

.btn-primary-custom:hover {
    background: #3D7FE8;
    color: #FFFFFF;
    text-decoration: none;
    box-shadow: 0px 4px 8px rgba(84, 149, 255, 0.4);
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .hero-section {
        height: 250px;
    }
    
    .hero-content {
        left: 20px;
        top: 100px;
        width: auto;
        max-width: calc(100% - 40px);
    }
    
    .hero-content h1 {
        font-size: 20px;
        line-height: 30px;
    }
    
    .hero-breadcrumb {
        font-size: 13px;
        line-height: 20px;
    }
    
    .main-container {
        padding: 20px 15px 40px 15px;
    }
    
    .filter-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filter-select {
        max-width: 100%;
    }
    
    .table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-custom {
        min-width: 1000px;
    }
    
    .action-group {
        flex-direction: column;
        gap: 4px;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}

.config-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    color: #856404;
}

.config-warning h4 {
    margin: 0 0 10px 0;
    color: #856404;
}

.config-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    color: #155724;
}

/* Tambahan styling untuk email status */
.email-status-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.status-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border-top: 4px solid #1C5EBC;
}

.status-icon {
    font-size: 24px;
    margin-bottom: 10px;
}

.status-title {
    font-weight: 600;
    margin-bottom: 5px;
}

.status-desc {
    font-size: 12px;
    color: #666;
}

/* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 0;
        border-radius: 10px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    .modal-header {
        background: #5495FF;
        color: white;
        padding: 20px;
        border-radius: 10px 10px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .close-modal {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .close-modal:hover {
        opacity: 0.8;
    }

    .modal-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 14px;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #5495FF;
        box-shadow: 0 0 0 3px rgba(84, 149, 255, 0.1);
    }

    .form-row {
        display: flex;
        gap: 15px;
    }

    .form-col {
        flex: 1;
    }

    .modal-footer {
        padding: 20px 25px;
        background: #F9FAFB;
        border-radius: 0 0 10px 10px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary {
        background: #6B7280;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s;
    }

    .btn-secondary:hover {
        background: #4B5563;
    }

    .btn-primary {
        background: #5495FF;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s;
    }

    .btn-primary:hover {
        background: #3D7FE8;
    }

    /* Badge untuk role */
    .badge-role {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-mahasiswa {
        background: #E0F2FE;
        color: #0369A1;
        border: 1px solid #BAE6FD;
    }

    .badge-dosen {
        background: #F0FDF4;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .badge-admin {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }

    /* Button Add Dosen */
    .btn-add-dosen {
        background: #10B981;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
        text-decoration: none;
    }

    .btn-add-dosen:hover {
        background: #059669;
        color: white;
        text-decoration: none;
    }
</style>

<div class="content-wrapper">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Kelola Pendaftaran User</h1>
            <p class="hero-breadcrumb">Dashboard<span class="separator">›</span>Kelola User</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h2 class="page-title">Kelola Pendaftaran User</h2>
            <button type="button" class="btn-add-dosen" onclick="openAddDosenModal()">
                <span>+</span> Tambah Dosen
            </button>
        </div>
        <hr class="title-divider">

        <!-- Notifikasi Konfigurasi Email -->
        <?php if (!empty($config_errors)): ?>
        <div class="config-warning">
            <h4>⚠️ Konfigurasi Email Perlu Diperbaiki</h4>
            <ul>
                <?php foreach ($config_errors as $error_msg): ?>
                <li><?php echo $error_msg; ?></li>
                <?php endforeach; ?>
            </ul>
            <p><small>Edit file: <code>includes/email_config.php</code></small></p>
        </div>
        <?php else: ?>
        <div class="config-success">
            <h4>✅ Konfigurasi Email Sudah Benar</h4>
            <p>Sistem siap mengirim notifikasi email ke user melalui Gmail SMTP.</p>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert-custom alert-success">
            <span class="alert-icon">✓</span>
            <span><?php echo $success; ?></span>
            <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert-custom alert-danger">
            <span class="alert-icon">⚠</span>
            <span><?php echo $error; ?></span>
            <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-row">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <h5 class="filter-header">Filter Data User</h5>
                    <form method="GET" style="display: flex; gap: 15px; align-items: center;">
                        <select name="role" class="filter-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>Semua Role</option>
                            <option value="mahasiswa" <?php echo $role_filter === 'mahasiswa' ? 'selected' : ''; ?>>Mahasiswa</option>
                            <option value="dosen" <?php echo $role_filter === 'dosen' ? 'selected' : ''; ?>>Dosen</option>
                        </select>
                        <select name="status" class="filter-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu Persetujuan</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-card">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-wrapper">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>NIM/NIDN</th>
                                <th>Email</th>
                                <th>Program Studi/Jabatan</th>
                                <th>Angkatan/Bidang</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <?php
                                    $badge_role_class = 'badge-' . $user['role'];
                                    $role_text = ucfirst($user['role']);
                                    ?>
                                    <span class="status-badge <?php echo $badge_role_class; ?>"><?php echo $role_text; ?></span>
                                </td>
                                <td class="username-cell"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                <td>
                                    <?php 
                                    if ($user['role'] === 'mahasiswa') {
                                        echo htmlspecialchars($user['nim'] ?? '-');
                                    } else {
                                        echo htmlspecialchars($user['nidn'] ?? '-');
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                    if ($user['role'] === 'mahasiswa') {
                                        echo htmlspecialchars($user['program_studi'] ?? '-');
                                    } else {
                                        echo htmlspecialchars($user['jabatan'] ?? '-');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($user['role'] === 'mahasiswa') {
                                        echo htmlspecialchars($user['angkatan'] ?? '-');
                                    } else {
                                        echo htmlspecialchars($user['bidang_keahlian'] ?? '-');
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    $status_text = '';
                                    if ($user['status'] === 'pending') {
                                        $badge_class = 'badge-menunggu';
                                        $status_text = 'Pending';
                                    } elseif ($user['status'] === 'approved') {
                                        $badge_class = 'badge-diterima';
                                        $status_text = 'Approved';
                                    } else {
                                        $badge_class = 'badge-ditolak';
                                        $status_text = 'Rejected';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td>
                                    <?php if ($user['role'] === 'mahasiswa' && $user['status'] === 'pending'): ?>
                                    <div class="action-group">
                                        <form method="POST" style="display: inline; margin: 0;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn-action btn-approve" 
                                                    onclick="return confirm('Setujui user <?php echo htmlspecialchars($user['nama_lengkap']); ?>? Email notifikasi akan dikirim ke <?php echo htmlspecialchars($user['email']); ?>')">
                                                ✓ Setujui
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline; margin: 0;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn-action btn-reject" 
                                                    onclick="return confirm('Tolak user <?php echo htmlspecialchars($user['nama_lengkap']); ?>? Email notifikasi akan dikirim ke <?php echo htmlspecialchars($user['email']); ?>')">
                                                × Tolak
                                            </button>
                                        </form>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-no-action">Tidak ada aksi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <h5 class="empty-state-title">Tidak ada data user</h5>
                    <p class="empty-state-text">Tidak ditemukan user dengan status yang dipilih.</p>
                    <a href="manage_users.php" class="btn-primary-custom">Lihat Semua User</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal Tambah Dosen -->
        <div id="addDosenModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Tambah Akun Dosen Baru</h3>
                    <button type="button" class="close-modal" onclick="closeAddDosenModal()">×</button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="add_dosen" value="1">
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="username">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="password">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="nama_lengkap">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="nidn">NIDN *</label>
                                    <input type="text" class="form-control" id="nidn" name="nidn" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="bidang_keahlian">Bidang Keahlian</label>
                            <input type="text" class="form-control" id="bidang_keahlian" name="bidang_keahlian">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeAddDosenModal()">Batal</button>
                        <button type="submit" class="btn-primary">Simpan Dosen</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email Status Section -->
        <div class="email-status-section">
            <h5 class="filter-header">📧 Status Email System</h5>
            <div class="status-grid">
                <div class="status-card">
                    <div class="status-icon">📨</div>
                    <div class="status-title">Gmail SMTP</div>
                    <div class="status-desc">Production Ready</div>
                </div>
                <div class="status-card">
                    <div class="status-icon">🔧</div>
                    <div class="status-title">PHPMailer</div>
                    <div class="status-desc">v6.8+</div>
                </div>
                <div class="status-card">
                    <div class="status-icon">📊</div>
                    <div class="status-title">Email Logs</div>
                    <div class="status-desc">Active Monitoring</div>
                </div>
                <div class="status-card">
                    <div class="status-icon">✅</div>
                    <div class="status-title">Auto Notifications</div>
                    <div class="status-desc">Approve/Reject</div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="../test_email_production.php" class="btn-primary-custom" style="text-decoration: none;">
                    🧪 Test Email System
                </a>
                <a href="../email_preview.php" class="btn-primary-custom" style="text-decoration: none; margin-left: 10px;">
                    📊 View Email Logs
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function openAddDosenModal() {
    document.getElementById('addDosenModal').style.display = 'block';
}

function closeAddDosenModal() {
    document.getElementById('addDosenModal').style.display = 'none';
}

// Close modal ketika klik di luar modal
window.onclick = function(event) {
    const modal = document.getElementById('addDosenModal');
    if (event.target === modal) {
        closeAddDosenModal();
    }
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthText = document.getElementById('password-strength');
    
    if (!strengthText) {
        // Create strength indicator if not exists
        const strengthDiv = document.createElement('div');
        strengthDiv.id = 'password-strength';
        strengthDiv.style.marginTop = '5px';
        strengthDiv.style.fontSize = '12px';
        e.target.parentNode.appendChild(strengthDiv);
    }
    
    const strength = checkPasswordStrength(password);
    const strengthElement = document.getElementById('password-strength');
    strengthElement.innerHTML = `Kekuatan password: <strong style="color: ${strength.color}">${strength.text}</strong>`;
});

function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/\d/)) strength++;
    if (password.match(/[^a-zA-Z\d]/)) strength++;
    
    switch(strength) {
        case 0:
        case 1:
            return { text: 'Lemah', color: '#DC2626' };
        case 2:
            return { text: 'Sedang', color: '#D97706' };
        case 3:
            return { text: 'Kuat', color: '#059669' };
        case 4:
            return { text: 'Sangat Kuat', color: '#065F46' };
        default:
            return { text: 'Lemah', color: '#DC2626' };
    }
}
</script>

<?php include '../includes/footer.php'; ?>
<?php
/**
 * File: penguji/daftar_ujian.php
 * Dashboard dosen untuk melihat mahasiswa yang diuji - SUDAH DIUPDATE DENGAN AUTO APPROVAL SYSTEM
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/template_penilaian.php';

// Cek role dosen dengan fungsi dari auth.php
require_dosen();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle approve revisi - UPDATE DENGAN AUTO APPROVAL SYSTEM
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_revisi'])) {
    $id_penilaian = $_POST['id_penilaian'];
    $status = $_POST['status']; // 'diterima' atau 'ditolak'
    $catatan_approval = clean_input($_POST['catatan_approval']);
    
    // Update status di tabel penilaian_ujian
    $sql_penilaian = "UPDATE penilaian_ujian 
                     SET status_revisi = ?, 
                         catatan_approval = ? 
                     WHERE id_penilaian = ? AND id_dosen = ?";
    
    $stmt_penilaian = $conn->prepare($sql_penilaian);
    $stmt_penilaian->bind_param("ssii", $status, $catatan_approval, $id_penilaian, $id_dosen);
    
    if ($stmt_penilaian->execute()) {
        // Update juga di tabel revisi_disertasi
        $status_revisi = ($status == 'diterima') ? 'disetujui' : 'perlu_perbaikan';
        
        $sql_revisi = "UPDATE revisi_disertasi 
                      SET status = ?, 
                          catatan_dosen = ?,
                          tanggal_approve = NOW()
                      WHERE id_penilaian = ?";
        
        $stmt_revisi = $conn->prepare($sql_revisi);
        $stmt_revisi->bind_param("ssi", $status_revisi, $catatan_approval, $id_penilaian);
        $stmt_revisi->execute();
        
        $success = "Revisi berhasil " . ($status == 'diterima' ? 'disetujui' : 'ditolak') . "!";
        
        // **KIRIM NOTIFIKASI EMAIL KE MAHASISWA**
        require_once '../includes/email_sender.php';
        $student_data = getStudentDataByPenilaian($conn, $id_penilaian);
        
        if ($student_data) {
            $email_sent = sendRevisionReviewNotification(
                $student_data['email'], 
                $student_data['nama_lengkap'],
                $student_data['nama_dosen'],
                $student_data['jenis_ujian'],
                $status,
                $catatan_approval,
                date('d F Y H:i')
            );
            
            if ($email_sent) {
                $success .= " Notifikasi telah dikirim ke email mahasiswa.";
            } else {
                $error .= " Gagal mengirim notifikasi email.";
            }
        }
        
        // AUTO APPROVAL SYSTEM - YANG DIPERBAIKI
        if ($status == 'diterima') {
            require_once '../includes/auto_approval.php';
            $approval_result = afterApproveRevisi($conn, $id_penilaian);
            
            if ($approval_result) {
                $success .= " Sistem: Akses ujian berikutnya telah dibuka otomatis.";
                
                // Notifikasi tambahan
                error_log("SUKSES: Auto approval berhasil - Penilaian ID: $id_penilaian");
            } else {
                error_log("INFO: Auto approval belum memenuhi syarat - Penilaian ID: $id_penilaian");
            }
        }
        
        // Refresh halaman untuk update data
        header("Location: daftar_ujian.php");
        exit();
    } else {
        $error = "Gagal memproses revisi: " . $stmt_penilaian->error;
    }
}

// Ambil ID dosen dari tabel dosen berdasarkan user_id
$sql_dosen = "SELECT id_dosen FROM dosen WHERE user_id = ?";
$stmt_dosen = $conn->prepare($sql_dosen);
$stmt_dosen->bind_param("i", $user_id);
$stmt_dosen->execute();
$result_dosen = $stmt_dosen->get_result();
$dosen_data = $result_dosen->fetch_assoc();

if (!$dosen_data) {
    die("Data dosen tidak ditemukan untuk user ini.");
}

$id_dosen = $dosen_data['id_dosen'];

// PERBAIKAN: Query utama yang disederhanakan dan diperbaiki
$sql = "SELECT j.*, r.*, m.nama_lengkap, m.nim, m.program_studi, m.email as email_mahasiswa,
               r.jenis_ujian, r.judul_disertasi, r.status as status_registrasi, r.status_kelulusan,
               r.tanggal_pengajuan, r.catatan_admin, r.promotor as id_promotor_reg, r.co_promotor as id_co_promotor_reg,
               p.id_penilaian, p.nilai_total, p.catatan, 
               COALESCE(rev.status, p.status_revisi) as status_revisi, 
               COALESCE(rev.file_revisi, p.file_revisi) as file_revisi,
               p.tanggal_penilaian, 
               COALESCE(rev.tanggal_kirim, p.tanggal_revisi) as tanggal_revisi,
               p.catatan_approval, p.jenis_nilai,
               rev.catatan_revisi, rev.catatan_dosen as catatan_revisi_dosen,
               d_promotor.nama_lengkap as nama_promotor,
               d_co_promotor.nama_lengkap as nama_co_promotor,
               d_co_promotor2.nama_lengkap as nama_co_promotor2,    
               d_penguji1.nama_lengkap as nama_penguji1,
               d_penguji2.nama_lengkap as nama_penguji2,
               d_penguji3.nama_lengkap as nama_penguji3,
               -- Hanya ambil penguji yang diperlukan untuk menghindari query terlalu kompleks
               (SELECT COUNT(*) FROM detail_penilaian dp WHERE dp.id_penilaian = p.id_penilaian) as jumlah_detail_penilaian,
               (SELECT COUNT(*) FROM lampiran l WHERE l.id_registrasi = r.id_registrasi) as jumlah_lampiran,
               (SELECT GROUP_CONCAT(nama_berkas SEPARATOR '||') FROM lampiran l WHERE l.id_registrasi = r.id_registrasi) as nama_berkas_lampiran,
               -- Tentukan peran dosen dalam ujian ini
               CASE 
                 WHEN j.promotor = ? THEN 'promotor'
                 WHEN j.penguji_1 = ? THEN 'penguji_1'
                 WHEN j.penguji_2 = ? THEN 'penguji_2'
                 WHEN j.penguji_3 = ? THEN 'penguji_3'
                 WHEN r.promotor = ? THEN 'promotor_reg'
                 WHEN r.co_promotor = ? THEN 'co_promotor_reg'
                 WHEN r.co_promotor2 = ? THEN 'co_promotor2_reg'
                 ELSE 'tidak_terdeteksi'
               END as peran_dosen
        FROM jadwal_ujian j
        JOIN registrasi r ON j.id_registrasi = r.id_registrasi
        JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
        LEFT JOIN dosen d_promotor ON j.promotor = d_promotor.id_dosen
        LEFT JOIN dosen d_co_promotor ON r.co_promotor = d_co_promotor.id_dosen
        LEFT JOIN dosen d_co_promotor2 ON r.co_promotor2 = d_co_promotor2.id_dosen
        LEFT JOIN dosen d_penguji1 ON j.penguji_1 = d_penguji1.id_dosen
        LEFT JOIN dosen d_penguji2 ON j.penguji_2 = d_penguji2.id_dosen
        LEFT JOIN dosen d_penguji3 ON j.penguji_3 = d_penguji3.id_dosen
        LEFT JOIN penilaian_ujian p ON (r.id_registrasi = p.id_registrasi AND p.id_dosen = ?)
        LEFT JOIN revisi_disertasi rev ON (r.id_registrasi = rev.id_registrasi AND rev.id_penilaian = p.id_penilaian)
        WHERE (j.promotor = ? OR j.penguji_1 = ? OR j.penguji_2 = ? OR j.penguji_3 = ? OR
               r.promotor = ? OR r.co_promotor = ? OR r.co_promotor2 = ?)
        AND r.status = 'Diterima'
        ORDER BY j.tanggal_ujian DESC, rev.tanggal_kirim DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

// PERBAIKAN: Sesuaikan dengan jumlah tanda tanya yang sebenarnya - 13 parameter
$stmt->bind_param("iiiiiiiiiiiiiii", 
    $id_dosen, // 1 - promotor jadwal (peran_dosen)
    $id_dosen, // 2 - penguji1 (peran_dosen)
    $id_dosen, // 3 - penguji2 (peran_dosen)
    $id_dosen, // 4 - penguji3 (peran_dosen)
    $id_dosen, // 5 - promotor_reg (peran_dosen)
    $id_dosen, // 6 - co_promotor_reg (peran_dosen)
    $id_dosen, // 7 - co_promotor2_reg (peran_dosen)
    $id_dosen, // 8 - penilaian
    $id_dosen, // 9 - promotor jadwal (WHERE)
    $id_dosen, // 10 - penguji1 (WHERE)
    $id_dosen, // 11 - penguji2 (WHERE)
    $id_dosen, // 12 - penguji3 (WHERE)
    $id_dosen, // 13 - promotor registrasi (WHERE)
    $id_dosen, // 14 - co_promotor registrasi (WHERE)
    $id_dosen  // 15 - co_promotor2 registrasi (WHERE)
);

$stmt->execute();
$result = $stmt->get_result();

$ujian_list = [];
while ($row = $result->fetch_assoc()) {
    $ujian_list[] = $row;
}

// **ALTERNATIF: QUERY BIMBINGAN YANG SANGAT SEDERHANA**
$sql_bimbingan = "SELECT DISTINCT
    m.id_mahasiswa, m.nama_lengkap, m.nim, m.program_studi, m.email,
    r.jenis_ujian, r.judul_disertasi, r.status as status_registrasi,
    r.tanggal_pengajuan,
    CASE 
      WHEN r.promotor = ? THEN 'Promotor'
      WHEN r.co_promotor = ? THEN 'Co-Promotor'
      WHEN r.co_promotor2 = ? THEN 'Co-Promotor 2'
      ELSE 'Penguji'
    END as peran_bimbingan
    FROM mahasiswa m
    JOIN registrasi r ON m.id_mahasiswa = r.id_mahasiswa
    WHERE (r.promotor = ? OR r.co_promotor = ? OR r.co_promotor2 = ?)
    AND r.status = 'Diterima'
    ORDER BY r.tanggal_pengajuan DESC";

$stmt_bimbingan = $conn->prepare($sql_bimbingan);
if ($stmt_bimbingan) {
    // **HANYA 4 PARAMETER - SANGAT SEDERHANA**
    $stmt_bimbingan->bind_param("iiiiii", 
        $id_dosen, // 1 - promotor (CASE)
        $id_dosen, // 2 - co-promotor (CASE)
        $id_dosen, // 3 - co-promotor2 (CASE)
        $id_dosen, // 4 - promotor (WHERE)
        $id_dosen, // 5 - co-promotor (WHERE)
        $id_dosen  // 6 - co-promotor2 (WHERE)
    );
    $stmt_bimbingan->execute();
    $result_bimbingan = $stmt_bimbingan->get_result();

    $bimbingan_list = [];
    while ($row = $result_bimbingan->fetch_assoc()) {
        $bimbingan_list[] = $row;
    }
} else {
    $bimbingan_list = [];
    error_log("Error preparing bimbingan query: " . $conn->error);
}

// Fungsi helper untuk format ukuran file
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return number_format($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

$page_title = "Daftar Ujian & Bimbingan - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_penguji.php';
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

/* Style untuk modal detail */
.list-group-item {
    border: 1px solid #e9ecef;
    margin-bottom: 8px;
    border-radius: 6px;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

/* Tooltip */
.tooltip-inner {
    max-width: 300px;
}

/* Styles tambahan untuk lampiran */
.attachment-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.attachment-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.attachment-item-v2 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
}

.attachment-item-v2:hover {
    border-color: #5495FF;
    box-shadow: 0 2px 8px rgba(84, 149, 255, 0.1);
    background: #F9FAFB;
}

.attachment-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.attachment-number {
    width: 28px;
    height: 28px;
    background: #5495FF;
    color: #FFFFFF;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}

.attachment-details {
    flex: 1;
}

.attachment-name-v2 {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #111827;
    margin-bottom: 2px;
}

.attachment-meta {
    font-family: 'Poppins', sans-serif;
    font-size: 11px;
    color: #6B7280;
}

.attachment-actions {
    display: flex;
    gap: 6px;
}

.btn-download-v3 {
    padding: 7px 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #10B981;
    color: #FFFFFF;
    border: none;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-download-v3:hover {
    background: #059669;
    color: #FFFFFF;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.empty-attachment {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-text {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: #9CA3AF;
    font-style: italic;
}

.file-preview-inline {
    margin-top: 0;
    background: #F9FAFB;
    border: 1px solid #E5E7EB;
    border-top: none;
    border-radius: 0 0 8px 8px;
    padding: 16px;
    overflow: hidden;
}

.preview-content {
    background: #FFFFFF;
    border: 2px dashed #D1D5DB;
    border-radius: 6px;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.preview-loading {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: #6B7280;
    display: flex;
    align-items: center;
    gap: 8px;
}

.preview-content iframe,
.preview-content embed {
    width: 100%;
    min-height: 400px;
    border: none;
}

.preview-content img {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
}

.preview-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 20px;
    text-align: center;
}

.preview-error-icon {
    font-size: 48px;
    opacity: 0.5;
}

.preview-error-text {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: #6B7280;
}

.preview-download-btn {
    padding: 8px 16px;
    background: #5495FF;
    color: #FFFFFF;
    border: none;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.preview-download-btn:hover {
    background: #3D7FE8;
    color: #FFFFFF;
    text-decoration: none;
    transform: translateY(-1px);
}

/* Info Card Styles */
.info-card {
    background: #F9FAFB;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 16px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-label {
    font-family: 'Poppins', sans-serif;
    font-size: 11px;
    font-weight: 500;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #111827;
    line-height: 1.5;
}

.modal-section-title {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 14px;
    color: #111827;
    margin: 0 0 14px 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Style untuk modal detail */
.exam-type-badge {
    background: #DBEAFE;
    color: #1E40AF;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

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

.badge-warning {
    background: #FEF3C7;
    color: #D97706;
    border: 0.5px solid #F59E0B;
}

/* Verification Card Styles */
.verification-card {
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    padding: 18px;
    margin-top: 16px;
}

.form-group-v2 {
    margin-bottom: 16px;
}

.form-label-v2 {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 12px;
    color: #374151;
    margin-bottom: 8px;
    display: block;
}

.form-control-v2 {
    width: 100%;
    height: 42px;
    background: #FFFFFF;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    padding: 0 14px;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: #374151;
    transition: all 0.2s ease;
}

.form-control-v2:focus {
    outline: none;
    border-color: #5495FF;
    box-shadow: 0 0 0 3px rgba(84, 149, 255, 0.1);
}

textarea.form-control-v2 {
    height: auto;
    padding: 12px 14px;
    resize: vertical;
}

.btn-submit-v2 {
    width: 100%;
    height: 44px;
    background: #10B981;
    color: #FFFFFF;
    border: none;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-submit-v2:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0px 4px 12px rgba(16, 185, 129, 0.3);
}
</style>

<div class="main-content">
    <div class="container-fluid">
        <!-- TAB NAVIGASI BARU -->
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ujian-tab" data-bs-toggle="tab" data-bs-target="#ujian" type="button" role="tab">
                    🎓 Daftar Ujian
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bimbingan-tab" data-bs-toggle="tab" data-bs-target="#bimbingan" type="button" role="tab">
                    👥 Mahasiswa Bimbingan
                </button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- TAB DAFTAR UJIAN -->
            <div class="tab-pane fade show active" id="ujian" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">📋 Daftar Mahasiswa yang Diuji</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($success): ?>
                                    <div class="alert alert-success"><?= $success ?></div>
                                <?php endif; ?>
                                
                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= $error ?></div>
                                <?php endif; ?>

                                <?php if (empty($ujian_list)): ?>
                                    <div class="alert alert-info">
                                        Anda belum ditugaskan sebagai penguji untuk ujian apapun.
                                    </div>
                                <?php else: ?>
                                    <!-- Tabel daftar ujian (sama seperti sebelumnya) -->
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Mahasiswa</th>
                                                    <th>NIM</th>
                                                    <th>Jenis Ujian</th>
                                                    <th>Judul Disertasi</th>
                                                    <th>Tanggal Pengajuan</th>
                                                    <th>Lampiran</th>
                                                    <th>Tanggal Ujian</th>
                                                    <th>Status Penilaian</th>
                                                    <th>Status Revisi</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ujian_list as $ujian): ?>
                                                    <tr>
                                                        <!-- Isi tabel ujian sama seperti sebelumnya -->
                                                        <td>
                                                            <strong><?= $ujian['nama_lengkap'] ?></strong>
                                                            <br><small class="text-muted"><?= $ujian['email_mahasiswa'] ?></small>
                                                            <br><small class="text-muted"><?= $ujian['program_studi'] ?></small>
                                                        </td>
                                                        <td><?= $ujian['nim'] ?></td>
                                                        <td>
                                                            <span class="badge bg-info"><?= ucfirst($ujian['jenis_ujian']) ?></span>
                                                            <br><small><?= ucfirst($ujian['jenis_nilai'] ?? '-') ?></small>
                                                        </td>
                                                        <td>
                                                            <small><?= $ujian['judul_disertasi'] ?></small>
                                                        </td>
                                                        <td>
                                                            <?php if ($ujian['tanggal_pengajuan']): ?>
                                                                <?= date('d/m/Y', strtotime($ujian['tanggal_pengajuan'])) ?>
                                                                <br><small class="text-muted"><?= date('H:i', strtotime($ujian['tanggal_pengajuan'])) ?></small>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $jumlah_lampiran = $ujian['jumlah_lampiran'] ?? 0;
                                                            if ($jumlah_lampiran > 0): 
                                                                $nama_berkas = $ujian['nama_berkas_lampiran'] ?? '';
                                                                $berkas_list = explode('||', $nama_berkas);
                                                            ?>
                                                                <span class="badge bg-success" data-bs-toggle="tooltip" title="<?= $jumlah_lampiran ?> berkas">
                                                                    📎 <?= $jumlah_lampiran ?> File
                                                                </span>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <?php 
                                                                    $display_berkas = array_slice($berkas_list, 0, 2);
                                                                    echo implode(', ', $display_berkas);
                                                                    if (count($berkas_list) > 2) {
                                                                        echo '...';
                                                                    }
                                                                    ?>
                                                                </small>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Tidak ada</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($ujian['tanggal_ujian']): ?>
                                                                <?= date('d/m/Y', strtotime($ujian['tanggal_ujian'])) ?>
                                                                <br><small><?= date('H:i', strtotime($ujian['tanggal_ujian'])) ?></small>
                                                                <?php if ($ujian['tempat']): ?>
                                                                    <br><small class="text-muted"><?= $ujian['tempat'] ?></small>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $sudah_dinilai = ($ujian['id_penilaian'] && $ujian['jumlah_detail_penilaian'] > 0);
                                                            ?>
                                                            
                                                            <?php if ($sudah_dinilai && $ujian['nilai_total']): ?>
                                                                <span class="badge bg-success">
                                                                    ✓ Dinilai (<?= number_format($ujian['nilai_total'], 1) ?>)
                                                                </span>
                                                                <br>
                                                                <small><?= date('d/m/Y', strtotime($ujian['tanggal_penilaian'])) ?></small>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning">Belum Dinilai</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $status_revisi = $ujian['status_revisi'] ?? 'belum';
                                                            
                                                            $status_mapping = [
                                                                'belum' => 'bg-secondary',
                                                                'menunggu' => 'bg-warning',
                                                                'dikirim' => 'bg-warning', 
                                                                'diajukan' => 'bg-warning',
                                                                'disetujui' => 'bg-success',
                                                                'diterima' => 'bg-success',
                                                                'perlu_perbaikan' => 'bg-danger',
                                                                'ditolak' => 'bg-danger'
                                                            ];
                                                            
                                                            $badge_class = $status_mapping[$status_revisi] ?? 'bg-secondary';
                                                            
                                                            $status_display = [
                                                                'belum' => 'Belum Revisi',
                                                                'menunggu' => 'Menunggu Review',
                                                                'dikirim' => 'Revisi Dikirim', 
                                                                'diajukan' => 'Revisi Diajukan',
                                                                'disetujui' => 'Disetujui',
                                                                'diterima' => 'Diterima',
                                                                'perlu_perbaikan' => 'Perlu Perbaikan',
                                                                'ditolak' => 'Ditolak'
                                                            ];
                                                            ?>
                                                            <span class="badge <?= $badge_class ?>">
                                                                <?= $status_display[$status_revisi] ?? ucfirst($status_revisi) ?>
                                                            </span>
                                                            <?php if ($ujian['tanggal_revisi']): ?>
                                                                <br><small><?= date('d/m/Y', strtotime($ujian['tanggal_revisi'])) ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group-vertical">
                                                                <!-- Tombol Beri/Lihat Nilai -->
                                                                <?php 
                                                                $sudah_dinilai = ($ujian['id_penilaian'] && $ujian['jumlah_detail_penilaian'] > 0);
                                                                $peran_dosen = $ujian['peran_dosen'] ?? '';
                                                                $jenis_ujian = $ujian['jenis_ujian'] ?? '';
                                                                
                                                                // Tentukan jenis penilaian berdasarkan peran
                                                                $jenis_penilaian = '';
                                                                if (strpos($peran_dosen, 'promotor') !== false) {
                                                                    $jenis_penilaian = 'promotor';
                                                                } elseif (strpos($peran_dosen, 'penguji') !== false) {
                                                                    $jenis_penilaian = 'penguji';
                                                                } elseif (strpos($peran_dosen, 'co_promotor') !== false) {
                                                                    $jenis_penilaian = 'promotor'; // Co-promotor juga pakai jenis promotor
                                                                }
                                                                ?>
                                                                
                                                                <?php if (!$sudah_dinilai): ?>
                                                                    <a href="form_penilaian.php?id_registrasi=<?= $ujian['id_registrasi'] ?>&jenis=<?= $jenis_penilaian ?>" 
                                                                    class="btn btn-sm btn-primary mb-1">
                                                                        ✏️ Beri Nilai
                                                                    </a>
                                                                <?php else: ?>
                                                                    <a href="lihat_nilai.php?id_penilaian=<?= $ujian['id_penilaian'] ?>" 
                                                                    class="btn btn-sm btn-outline-primary mb-1">
                                                                        👁️ Lihat Nilai
                                                                    </a>
                                                                <?php endif; ?>
                                                                
                                                                <!-- PERBAIKAN: Tombol Review Revisi - KONDISI DIPERBAIKI -->
                                                                <?php if (in_array($ujian['status_revisi'], ['dikirim', 'diajukan', 'menunggu'])): ?>
                                                                    <button class="btn btn-sm btn-success mb-1" 
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#modalRevisi<?= $ujian['id_penilaian'] ?>">
                                                                        ✅ Review Revisi
                                                                    </button>
                                                                <?php endif; ?>
                                                                
                                                                <!-- Tombol Lihat File Revisi -->
                                                                <?php if ($ujian['file_revisi'] && !in_array($ujian['status_revisi'], ['dikirim', 'diajukan', 'menunggu'])): ?>
                                                                    <a href="../uploads/revisi/<?= $ujian['file_revisi'] ?>" 
                                                                    target="_blank" 
                                                                    class="btn btn-sm btn-outline-info mb-1">
                                                                        📄 Lihat Revisi
                                                                    </a>
                                                                <?php endif; ?>
                                                                
                                                                <!-- Tombol Detail Pendaftaran -->
                                                                <button class="btn btn-sm btn-outline-secondary mb-1" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#modalDetailPendaftaran<?= $ujian['id_registrasi'] ?>">
                                                                    📋 Detail
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <!-- PERBAIKAN: Modal Review Revisi - DIPINDAHKAN KE LUAR ROW TABEL -->
                                                    <?php if (in_array($ujian['status_revisi'], ['dikirim', 'diajukan', 'menunggu'])): ?>
                                                    <div class="modal fade" id="modalRevisi<?= $ujian['id_penilaian'] ?>" tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-success text-white">
                                                                    <h5 class="modal-title">✅ Review Revisi - <?= $ujian['nama_lengkap'] ?></h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <!-- Info Revisi -->
                                                                    <div class="info-card">
                                                                        <h6 class="modal-section-title">📝 Informasi Revisi</h6>
                                                                        <div class="info-grid">
                                                                            <div class="info-item">
                                                                                <span class="info-label">Mahasiswa</span>
                                                                                <span class="info-value"><?= $ujian['nama_lengkap'] ?> (<?= $ujian['nim'] ?>)</span>
                                                                            </div>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Jenis Ujian</span>
                                                                                <span class="info-value"><?= ucfirst($ujian['jenis_ujian']) ?></span>
                                                                            </div>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Tanggal Kirim Revisi</span>
                                                                                <span class="info-value"><?= date('d F Y H:i', strtotime($ujian['tanggal_revisi'])) ?></span>
                                                                            </div>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Status Saat Ini</span>
                                                                                <span class="info-value">
                                                                                    <span class="status-badge badge-warning">Menunggu Review</span>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- File Revisi -->
                                                                    <?php if ($ujian['file_revisi']): ?>
                                                                    <div class="info-card">
                                                                        <h6 class="modal-section-title">📄 File Revisi</h6>
                                                                        <div class="attachment-list">
                                                                            <div class="attachment-wrapper">
                                                                                <div class="attachment-item-v2">
                                                                                    <div class="attachment-info">
                                                                                        <div class="attachment-number">1</div>
                                                                                        <div class="attachment-details">
                                                                                            <div class="attachment-name-v2">File Revisi Disertasi</div>
                                                                                            <div class="attachment-meta">
                                                                                                Format: <?= strtoupper(pathinfo($ujian['file_revisi'], PATHINFO_EXTENSION)) ?>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="attachment-actions">
                                                                                        <a href="../uploads/revisi/<?= $ujian['file_revisi'] ?>" 
                                                                                        download 
                                                                                        class="btn-download-v3">
                                                                                            Download
                                                                                        </a>
                                                                                        <a href="../uploads/revisi/<?= $ujian['file_revisi'] ?>" 
                                                                                        target="_blank" 
                                                                                        class="btn-download-v3" 
                                                                                        style="background: #5495FF;">
                                                                                            Lihat
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>

                                                                    <!-- Catatan dari Mahasiswa -->
                                                                    <?php if (!empty($ujian['catatan_revisi'])): ?>
                                                                    <div class="info-card">
                                                                        <h6 class="modal-section-title">💬 Catatan dari Mahasiswa</h6>
                                                                        <div style="background: #F3F4F6; padding: 12px; border-radius: 6px; border-left: 3px solid #9CA3AF;">
                                                                            <p style="margin: 0; font-size: 13px; color: #374151; line-height: 1.5;">
                                                                                <?= nl2br(htmlspecialchars($ujian['catatan_revisi'])) ?>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>

                                                                    <!-- Form Approve Revisi -->
                                                                    <div class="verification-card">
                                                                        <h6 class="modal-section-title">✅ Form Review Revisi</h6>
                                                                        <form method="POST">
                                                                            <input type="hidden" name="id_penilaian" value="<?= $ujian['id_penilaian'] ?>">
                                                                            
                                                                            <div class="form-group-v2">
                                                                                <label class="form-label-v2">Status Review <span style="color: #EF4444;">*</span></label>
                                                                                <select name="status" class="form-control-v2" required onchange="toggleCatatan(this.value, 'catatanGroup<?= $ujian['id_penilaian'] ?>')">
                                                                                    <option value="">-- Pilih Status --</option>
                                                                                    <option value="diterima">✅ Setujui Revisi</option>
                                                                                    <option value="ditolak">❌ Tolak Revisi (Perlu Perbaikan)</option>
                                                                                </select>
                                                                            </div>
                                                                            
                                                                            <div class="form-group-v2" id="catatanGroup<?= $ujian['id_penilaian'] ?>" style="display: none;">
                                                                                <label class="form-label-v2">Catatan untuk Mahasiswa</label>
                                                                                <textarea name="catatan_approval" class="form-control-v2" rows="4" 
                                                                                        placeholder="Berikan catatan atau masukan untuk perbaikan revisi..."><?= $ujian['catatan_revisi_dosen'] ?? '' ?></textarea>
                                                                                <small style="font-size: 11px; color: #6B7280; margin-top: 4px; display: block;">
                                                                                    Catatan ini akan ditampilkan kepada mahasiswa
                                                                                </small>
                                                                            </div>
                                                                            
                                                                            <button type="submit" name="approve_revisi" class="btn-submit-v2">
                                                                                💾 Simpan Review
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>

                                                    <!-- Modal Detail Pendaftaran -->
                                                    <div class="modal fade" id="modalDetailPendaftaran<?= $ujian['id_registrasi'] ?>" tabindex="-1">
                                                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-primary text-white">
                                                                    <h5 class="modal-title">📋 Detail Pendaftaran - <?= $ujian['nama_lengkap'] ?></h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <!-- Data Mahasiswa -->
                                                                    <div class="info-card">
                                                                        <h6 class="modal-section-title">👤 Data Mahasiswa</h6>
                                                                        <div class="info-grid">
                                                                            <div class="info-item">
                                                                                <span class="info-label">Nama Lengkap</span>
                                                                                <span class="info-value"><?= $ujian['nama_lengkap'] ?></span>
                                                                            </div>
                                                                            <div class="info-item">
                                                                                <span class="info-label">NIM</span>
                                                                                <span class="info-value"><?= $ujian['nim'] ?></span>
                                                                            </div>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Program Studi</span>
                                                                                <span class="info-value"><?= $ujian['program_studi'] ?></span>
                                                                            </div>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Email</span>
                                                                                <span class="info-value"><?= $ujian['email_mahasiswa'] ?></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Data Pendaftaran -->
                                                                    <div class="info-card">
                                                                        <h6 class="modal-section-title">📋 Data Pendaftaran</h6>
                                                                        <div class="info-grid">
                                                                            <div class="info-item">
                                                                                <span class="info-label">Jenis Ujian</span>
                                                                                <span class="info-value"><span class="exam-type-badge"><?= ucfirst($ujian['jenis_ujian']) ?></span></span>
                                                                            </div>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Judul Disertasi</span>
                                                                                <span class="info-value"><?= $ujian['judul_disertasi'] ?></span>
                                                                            </div>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Tanggal Pengajuan</span>
                                                                                <span class="info-value"><?= date('d F Y H:i', strtotime($ujian['tanggal_pengajuan'])) ?></span>
                                                                            </div>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Status</span>
                                                                                <span class="info-value">
                                                                                    <?php 
                                                                                    $badge_class = match($ujian['status_registrasi']) {
                                                                                        'Menunggu' => 'badge-menunggu',
                                                                                        'Diterima' => 'badge-diterima',
                                                                                        default => 'badge-ditolak'
                                                                                    };
                                                                                    ?>
                                                                                    <span class="status-badge <?= $badge_class ?>"><?= $ujian['status_registrasi'] ?></span>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Lampiran Berkas - DITAMBAHKAN -->
                                                                    <div class="info-card">
                                                                        <h6 class="modal-section-title">📎 Lampiran Berkas</h6>
                                                                        <?php
                                                                        // Query untuk mendapatkan lampiran
                                                                        $lampiran_query = "SELECT * FROM lampiran WHERE id_registrasi = ?";
                                                                        $stmt_lampiran = $conn->prepare($lampiran_query);
                                                                        $stmt_lampiran->bind_param("i", $ujian['id_registrasi']);
                                                                        $stmt_lampiran->execute();
                                                                        $lampiran_result = $stmt_lampiran->get_result();
                                                                        
                                                                        if ($lampiran_result->num_rows > 0): ?>
                                                                            <div class="attachment-list">
                                                                                <?php 
                                                                                $lamp_no = 1;
                                                                                while ($lamp = $lampiran_result->fetch_assoc()): 
                                                                                    $path = "../uploads/" . $lamp['path_berkas'];
                                                                                    $ext = strtolower(pathinfo($lamp['path_berkas'], PATHINFO_EXTENSION));
                                                                                    $file_id = 'file_detail_' . $ujian['id_registrasi'] . '_' . $lamp_no;
                                                                                ?>
                                                                                <div class="attachment-wrapper">
                                                                                    <div class="attachment-item-v2" onclick="togglePreview('<?= $file_id ?>', '<?= $path ?>', '<?= $ext ?>')">
                                                                                        <div class="attachment-info">
                                                                                            <div class="attachment-number"><?= $lamp_no++ ?></div>
                                                                                            <div class="attachment-details">
                                                                                                <div class="attachment-name-v2"><?= $lamp['nama_berkas'] ?></div>
                                                                                                <div class="attachment-meta">Format: <?= strtoupper($ext) ?></div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="attachment-actions">
                                                                                            <a href="<?= $path ?>" download class="btn-download-v3" onclick="event.stopPropagation();">
                                                                                                Download
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <!-- Preview Area -->
                                                                                    <div id="preview_<?= $file_id ?>" class="file-preview-inline" style="display: none;">
                                                                                        <div class="preview-content" id="content_<?= $file_id ?>">
                                                                                            <div class="preview-loading">⏳ Memuat preview...</div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <?php endwhile; ?>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="empty-attachment">
                                                                                <span class="empty-icon">📭</span>
                                                                                <span class="empty-text">Tidak ada lampiran</span>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>

                                                                    <!-- Tim Penguji -->
                                                                    <div class="info-card">
                                                                        <h6 class="modal-section-title">👨‍🏫 Tim Penguji</h6>
                                                                        <div class="info-grid">
                                                                            <?php if (!empty($ujian['nama_promotor'])): ?>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Promotor</span>
                                                                                <span class="info-value">
                                                                                    <?= $ujian['nama_promotor'] ?>
                                                                                    <?php if ($ujian['peran_dosen'] == 'promotor'): ?>
                                                                                        <span style="color: #059669; font-size: 10px; margin-left: 4px;">(Anda)</span>
                                                                                    <?php endif; ?>
                                                                                </span>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($ujian['nama_co_promotor'])): ?>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Co-Promotor</span>
                                                                                <span class="info-value">
                                                                                    <?= $ujian['nama_co_promotor'] ?>
                                                                                    <?php if ($ujian['peran_dosen'] == 'co_promotor_reg'): ?>
                                                                                        <span style="color: #059669; font-size: 10px; margin-left: 4px;">(Anda)</span>
                                                                                    <?php endif; ?>
                                                                                </span>
                                                                            </div>
                                                                            <?php endif; ?>

                                                                            <?php if (!empty($ujian['nama_co_promotor2'])): ?>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Co-Promotor 2</span>
                                                                                <span class="info-value">
                                                                                    <?= $ujian['nama_co_promotor2'] ?>
                                                                                    <?php if ($ujian['peran_dosen'] == 'co_promotor2_reg'): ?>
                                                                                        <span style="color: #059669; font-size: 10px; margin-left: 4px;">(Anda)</span>
                                                                                    <?php endif; ?>
                                                                                </span>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($ujian['nama_penguji1'])): ?>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Penguji 1</span>
                                                                                <span class="info-value">
                                                                                    <?= $ujian['nama_penguji1'] ?>
                                                                                    <?php if ($ujian['peran_dosen'] == 'penguji_1'): ?>
                                                                                        <span style="color: #059669; font-size: 10px; margin-left: 4px;">(Anda)</span>
                                                                                    <?php endif; ?>
                                                                                </span>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($ujian['nama_penguji2'])): ?>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Penguji 2</span>
                                                                                <span class="info-value">
                                                                                    <?= $ujian['nama_penguji2'] ?>
                                                                                    <?php if ($ujian['peran_dosen'] == 'penguji_2'): ?>
                                                                                        <span style="color: #059669; font-size: 10px; margin-left: 4px;">(Anda)</span>
                                                                                    <?php endif; ?>
                                                                                </span>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <?php if (!empty($ujian['nama_penguji3'])): ?>
                                                                            <div class="info-item">
                                                                                <span class="info-label">Penguji 3</span>
                                                                                <span class="info-value">
                                                                                    <?= $ujian['nama_penguji3'] ?>
                                                                                    <?php if ($ujian['peran_dosen'] == 'penguji_3'): ?>
                                                                                        <span style="color: #059669; font-size: 10px; margin-left: 4px;">(Anda)</span>
                                                                                    <?php endif; ?>
                                                                                </span>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Statistik Ujian -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card text-white bg-info">
                                    <div class="card-body">
                                        <h5 class="card-title">Menunggu Penilaian</h5>
                                        <p class="card-text display-6">
                                            <?= count(array_filter($ujian_list, function($u) { 
                                                return !($u['id_penilaian'] && $u['jumlah_detail_penilaian'] > 0); 
                                            })) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h5 class="card-title">Sudah Dinilai</h5>
                                        <p class="card-text display-6">
                                            <?= count(array_filter($ujian_list, function($u) { 
                                                return ($u['id_penilaian'] && $u['jumlah_detail_penilaian'] > 0); 
                                            })) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                        <h5 class="card-title">Revisi Diajukan</h5>
                                        <p class="card-text display-6">
                                            <?= count(array_filter($ujian_list, function($u) { 
                                                return in_array($u['status_revisi'], ['dikirim', 'diajukan', 'menunggu']); 
                                            })) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Ujian</h5>
                                        <p class="card-text display-6"><?= count($ujian_list) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB MAHASISWA BIMBINGAN -->
            <div class="tab-pane fade" id="bimbingan" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h4 class="mb-0">👥 Daftar Mahasiswa Bimbingan</h4>
                            </div>
                            <div class="card-body">
                                <?php if (empty($bimbingan_list)): ?>
                                    <div class="alert alert-info">
                                        Anda belum memiliki mahasiswa bimbingan.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Mahasiswa</th>
                                                    <th>NIM</th>
                                                    <th>Program Studi</th>
                                                    <th>Jenis Ujian</th>
                                                    <th>Judul Disertasi</th>
                                                    <th>Tanggal Pengajuan</th>
                                                    <th>Peran Anda</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($bimbingan_list as $bimbingan): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= $bimbingan['nama_lengkap'] ?></strong>
                                                            <br><small class="text-muted"><?= $bimbingan['email'] ?></small>
                                                        </td>
                                                        <td><?= $bimbingan['nim'] ?></td>
                                                        <td><?= $bimbingan['program_studi'] ?></td>
                                                        <td>
                                                            <span class="badge bg-info"><?= ucfirst($bimbingan['jenis_ujian']) ?></span>
                                                        </td>
                                                        <td>
                                                            <small><?= $bimbingan['judul_disertasi'] ?></small>
                                                        </td>
                                                        <td>
                                                            <?php if ($bimbingan['tanggal_pengajuan']): ?>
                                                                <?= date('d/m/Y', strtotime($bimbingan['tanggal_pengajuan'])) ?>
                                                                <br><small class="text-muted"><?= date('H:i', strtotime($bimbingan['tanggal_pengajuan'])) ?></small>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $badge_class = match($bimbingan['peran_bimbingan']) {
                                                                'Promotor' => 'bg-success',
                                                                'Co-Promotor' => 'bg-info', 
                                                                'Penguji 1' => 'bg-primary',
                                                                'Penguji 2' => 'bg-primary',
                                                                'Penguji 3' => 'bg-primary',
                                                                default => 'bg-secondary'
                                                            };
                                                            ?>
                                                            <span class="badge <?= $badge_class ?>">
                                                                <?= $bimbingan['peran_bimbingan'] ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $status_badge = match($bimbingan['status_registrasi']) {
                                                                'Diterima' => 'badge-success',
                                                                'Menunggu' => 'badge-warning',
                                                                default => 'badge-danger'
                                                            };
                                                            ?>
                                                            <span class="badge <?= $status_badge ?>">
                                                                <?= $bimbingan['status_registrasi'] ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Statistik Bimbingan -->
                                    <div class="row mt-4">
                                        <div class="col-md-3">
                                            <div class="card text-white bg-success">
                                                <div class="card-body">
                                                    <h5 class="card-title">Total Bimbingan</h5>
                                                    <p class="card-text display-6"><?= count($bimbingan_list) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card text-white bg-info">
                                                <div class="card-body">
                                                    <h5 class="card-title">Sebagai Promotor</h5>
                                                    <p class="card-text display-6">
                                                        <?= count(array_filter($bimbingan_list, function($b) { 
                                                            return $b['peran_bimbingan'] == 'Promotor'; 
                                                        })) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card text-white bg-warning">
                                                <div class="card-body">
                                                    <h5 class="card-title">Sebagai Co-Promotor</h5>
                                                    <p class="card-text display-6">
                                                        <?= count(array_filter($bimbingan_list, function($b) { 
                                                            return $b['peran_bimbingan'] == 'Co-Promotor'; 
                                                        })) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card text-white bg-primary">
                                                <div class="card-body">
                                                    <h5 class="card-title">Sebagai Penguji</h5>
                                                    <p class="card-text display-6">
                                                        <?= count(array_filter($bimbingan_list, function($b) { 
                                                            return strpos($b['peran_bimbingan'], 'Penguji') !== false; 
                                                        })) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript untuk tab -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi tab
    var triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    });

    // Simpan tab aktif di sessionStorage
    var activeTab = sessionStorage.getItem('activeTab');
    if (activeTab) {
        var triggerEl = document.querySelector('[data-bs-target="' + activeTab + '"]');
        if (triggerEl) {
            bootstrap.Tab.getInstance(triggerEl).show();
        }
    }

    // Update sessionStorage ketika tab berubah
    var tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(function(tabEl) {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            sessionStorage.setItem('activeTab', event.target.getAttribute('data-bs-target'));
        });
    });
});

// PERBAIKAN: Fungsi untuk toggle catatan berdasarkan status dengan ID unik
function toggleCatatan(status, catatanGroupId) {
    const catatanGroup = document.getElementById(catatanGroupId);
    if (status === 'ditolak') {
        catatanGroup.style.display = 'block';
    } else {
        catatanGroup.style.display = 'none';
    }
}

// Inisialisasi tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

// JavaScript untuk preview file (sama seperti di verifikasi.php)
let currentOpenPreview = null;

function togglePreview(fileId, filePath, fileExtension) {
    const previewElement = document.getElementById('preview_' + fileId);
    const contentElement = document.getElementById('content_' + fileId);
    
    if (currentOpenPreview === fileId) {
        previewElement.style.display = 'none';
        currentOpenPreview = null;
        return;
    }
    
    if (currentOpenPreview) {
        document.getElementById('preview_' + currentOpenPreview).style.display = 'none';
    }
    
    previewElement.style.display = 'block';
    currentOpenPreview = fileId;
    
    contentElement.innerHTML = '<div class="preview-loading">⏳ Memuat preview...</div>';
    
    setTimeout(() => {
        loadPreview(contentElement, filePath, fileExtension);
    }, 300);
}

function loadPreview(container, filePath, fileExtension) {
    if (fileExtension === 'pdf') {
        container.innerHTML = `<embed src="${filePath}" type="application/pdf" width="100%" style="min-height: 500px;" />`;
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(fileExtension)) {
        container.innerHTML = `<img src="${filePath}" alt="Preview" style="max-width: 100%; height: auto;" />`;
    } else if (['doc', 'docx'].includes(fileExtension)) {
        const fullUrl = window.location.origin + '/' + filePath;
        container.innerHTML = `<iframe src="https://docs.google.com/viewer?url=${encodeURIComponent(fullUrl)}&embedded=true" width="100%" style="min-height: 500px;"></iframe>`;
    } else {
        container.innerHTML = `
            <div class="preview-error">
                <span class="preview-error-icon">📄</span>
                <span class="preview-error-text">Preview tidak tersedia</span>
                <a href="${filePath}" download class="preview-download-btn">⬇️ Download</a>
            </div>
        `;
    }
}

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});
</script>

<?php include '../includes/footer.php'; ?>
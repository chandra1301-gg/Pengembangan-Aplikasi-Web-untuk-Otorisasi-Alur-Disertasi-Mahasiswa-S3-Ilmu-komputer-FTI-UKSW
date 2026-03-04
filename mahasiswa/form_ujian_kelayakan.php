<?php
/**
 * File: mahasiswa/form_ujian_kelayakan.php
 * Form pendaftaran ujian kelayakan dengan 2 tahap
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];

// VALIDASI: Cek apakah boleh daftar ujian kelayakan
function bolehDaftarUjian($conn, $id_mahasiswa, $jenis_ujian) {
    $tahapan = [
        'proposal' => 1,
        'kualifikasi' => 2, 
        'kelayakan' => 3,
        'tertutup' => 4
    ];
    
    $current_tahap = $tahapan[$jenis_ujian];
    
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

$boleh_daftar = bolehDaftarUjian($conn, $id_mahasiswa, 'kelayakan');

if (!$boleh_daftar['boleh']) {
    $_SESSION['error_message'] = "Tidak dapat mendaftar ujian kelayakan: " . $boleh_daftar['alasan'];
    header("Location: registrasi.php");
    exit();
}

// Cek apakah sudah ada registrasi pending
$sql = "SELECT COUNT(*) as total FROM registrasi 
        WHERE id_mahasiswa = ? AND jenis_ujian = 'kelayakan' AND status = 'Menunggu'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    $_SESSION['error_message'] = "Anda sudah memiliki registrasi ujian kelayakan yang menunggu approval.";
    header("Location: registrasi.php");
    exit();
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';

// Simpan data step 1
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step1'])) {
    $_SESSION['kelayakan_data']['nama_lengkap'] = $_POST['nama_lengkap'];
    $_SESSION['kelayakan_data']['tempat_lahir'] = $_POST['tempat_lahir'];
    $_SESSION['kelayakan_data']['nim'] = $_POST['nim'];
    $_SESSION['kelayakan_data']['alamat'] = $_POST['alamat'];
    $_SESSION['kelayakan_data']['no_telp'] = $_POST['no_telp'];
    $_SESSION['kelayakan_data']['tempat_pelaksanaan'] = $_POST['tempat_pelaksanaan'];
    $_SESSION['kelayakan_data']['jabatan_dalam_pekerjaan'] = $_POST['jabatan_dalam_pekerjaan'];
    $_SESSION['kelayakan_data']['alamat_pekerjaan'] = $_POST['alamat_pekerjaan'];
    $_SESSION['kelayakan_data']['tanggal_pengumuman'] = $_POST['tanggal_pengumuman'];
    $_SESSION['kelayakan_data']['tanggal_ujian'] = $_POST['tanggal_ujian'];
    header("Location: ?step=2");
    exit();
}

// Submit final dengan upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $judul = "Registrasi Ujian Kelayakan - " . $mahasiswa['nama_lengkap'];
    
    $query = "INSERT INTO registrasi (id_mahasiswa, jenis_ujian, judul_disertasi, tanggal_pengajuan, status) 
              VALUES (" . $mahasiswa['id_mahasiswa'] . ", 'kelayakan', '" . escape_string($judul) . "', NOW(), 'Menunggu')";
    
    if (mysqli_query($conn, $query)) {
        $id_registrasi = mysqli_insert_id($conn);

        // KIRIM EMAIL NOTIFIKASI KE ADMIN
    require_once '../includes/email_sender.php';
    
    $student_name = $mahasiswa['nama_lengkap'];
    $student_nim = $mahasiswa['nim'];
    $exam_type = 'kelayakan';
    $registration_date = date('d F Y H:i:s');
    
    if (testEmailConfiguration()) {
        $email_sent = sendRegistrationNotification($student_name, $student_nim, $exam_type, $registration_date);
        
        if (!$email_sent) {
            error_log("Gagal mengirim email notifikasi untuk pendaftaran kelayakan ID: $id_registrasi");
        }
    }
        
        // Upload files
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_fields = [
            'berkas_hardcopy',
            'bukti_pembayaran',
            'surat_pernyataan',
            'acceptance_1',
            'acceptance_2',
            'file_proceeding',
            'bukti_jurnal',
            'surat_tidak_plagiat',
            'scan_ijazah_transkrip',
            'transkrip_nilai_terbaru',
            'loa_publikasi',
            'bukti_pembayaran_jurnal',
            'form_ujian_kelayakan'
        ];
        
        foreach ($file_fields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                $file_ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                $new_filename = $field . '_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $target_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_path)) {
                    $query_file = "INSERT INTO lampiran (id_registrasi, nama_berkas, path_berkas) 
                                  VALUES ($id_registrasi, '" . escape_string($_FILES[$field]['name']) . "', '" . escape_string($new_filename) . "')";
                    mysqli_query($conn, $query_file);
                }
            }
        }
        
        unset($_SESSION['kelayakan_data']);
        $_SESSION['success_message'] = "Registrasi Ujian Kelayakan berhasil!";
        header("Location: dashboard.php");
        exit();
    }
}

$page_title = "Pendaftaran Ujian Kelayakan - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_mahasiswa.php';
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
    width: 509px;
    height: 61px;
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
    margin: 0;
    width: 509px;
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

/* Main Container */
.main-container {
    position: relative;
    padding: 37px 36px;
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
    max-width: 1098px;
    width: 100%;
    height: 0px;
    border: 1px solid #000000;
    margin: 16px 0 31px 0;
}

/* Form Section */
.form-section {
    position: relative;
    background: transparent;
    padding: 0;
    margin-bottom: 53px;
    max-width: 1098px;
}

.section-header h6 {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.03em;
    color: #000000;
    margin-bottom: 4px;
}

.section-header p {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 13px;
    line-height: 20px;
    letter-spacing: 0.01em;
    color: #000000;
    margin-bottom: 21px;
}

/* Form Controls */
.form-group {
    margin-bottom: 13px;
}

.form-label {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 13.9448px;
    line-height: 21px;
    letter-spacing: 0.03em;
    color: #000000;
    display: block;
    margin-bottom: 7px;
}

.form-label .required {
    color: #FF0000;
    font-weight: 500;
    font-size: 17px;
    margin-left: 2px;
}

.form-control-custom {
    width: 100%;
    max-width: 100%;
    height: 40.19px;
    background: #FFFFFF;
    border: 0.246085px solid #000000;
    border-radius: 4.10141px;
    padding: 0 13.12px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 11.484px;
    line-height: 17px;
    letter-spacing: 0.03em;
    color: #000000;
    box-sizing: border-box;
}

.form-control-custom:focus {
    outline: none;
    border-color: #5495FF;
}

textarea.form-control-custom {
    height: 98.43px;
    padding: 11.59px 13.12px;
    resize: vertical;
}

.form-control-custom.small-input {
    max-width: 270px;
}

/* Upload Section */
.upload-item {
    background: #FFFFFF;
    border: 0.3px solid #C4C4C4;
    box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.15);
    border-radius: 30px;
    padding: 21px 27px;
    margin-bottom: 7px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.upload-info h6 {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 500;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #000000;
    margin: 0 0 4px 0;
}

.upload-info p {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 11px;
    line-height: 16px;
    color: #676767;
    margin: 0;
}

/* File Upload Controls */
.upload-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* File Name Display */
.file-display {
    display: none;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 0.5px solid #C7C7C7;
    border-radius: 8px;
    padding: 8px 12px;
    box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
}

.file-display.show {
    display: flex;
}

.file-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-icon::before {
    content: "📄";
    font-size: 16px;
}

.file-name {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 12px;
    color: #333;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-remove {
    width: 18px;
    height: 18px;
    background: transparent;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #666;
    padding: 0;
    line-height: 1;
}

.file-remove:hover {
    color: #FF0000;
}

/* Upload Button */
.upload-btn {
    background: #FFFFFF;
    border: 0.2px solid #C7C7C7;
    box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.25);
    border-radius: 5.36286px;
    padding: 7px 16.5px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 500;
    font-size: 11.2141px;
    line-height: 17px;
    letter-spacing: 0.03em;
    color: #000000;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.3s;
}

.upload-btn:hover {
    background: #F8F8F8;
}

.upload-btn.has-file {
    display: none;
}

/* Hidden file input */
input[type="file"] {
    display: none;
}

/* Buttons */
.button-group {
    position: relative;
    display: flex;
    justify-content: flex-end;
    gap: 21px;
    margin-top: 41px;
    padding-bottom: 50px;
}

.btn-custom {
    width: 90.05px;
    height: 36.92px;
    border-radius: 4.21923px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 14.3454px;
    line-height: 22px;
    letter-spacing: 0.03em;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
}

.btn-back {
    background: #FFFFFF;
    border: 0.979684px solid #27DAA3;
    color: #27DAA3;
    box-shadow: 0px 1.95937px 3.91874px rgba(0, 0, 0, 0.25);
}

.btn-back:hover {
    background: #27DAA3;
    color: #FFFFFF;
}

.btn-next {
    background: #27DAA3;
    color: #FFFFFF;
    box-shadow: 0px 1.95937px 3.91874px rgba(0, 0, 0, 0.25);
}

.btn-next:hover {
    background: #20B38A;
}

.btn-submit {
    background: #5495FF;
    color: #FFFFFF;
    box-shadow: 0px 1.95937px 3.91874px rgba(0, 0, 0, 0.25);
}

.btn-submit:hover {
    background: #3D7FE8;
}

.btn-cancel {
    background: #FFFFFF;
    border: 0.979685px solid #5495FF;
    color: #5495FF;
    box-shadow: 0px 1.95937px 3.91874px rgba(0, 0, 0, 0.25);
}

.btn-cancel:hover {
    background: #5495FF;
    color: #FFFFFF;
}

/* Form Row for Date inputs */
.form-row-date {
    display: flex;
    gap: 20px;
}

.form-row-date .form-group {
    flex: 1;
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
    }
    
    .hero-content h1 {
        font-size: 20px;
        line-height: 30px;
        width: auto;
    }
    
    .hero-breadcrumb {
        font-size: 14px;
        line-height: 21px;
    }
    
    .main-container {
        padding: 20px 15px;
    }
    
    .title-divider {
        width: 100%;
    }
    
    .form-section {
        width: 100%;
    }
    
    .form-control-custom {
        max-width: 100%;
    }
    
    .form-row-date {
        flex-direction: column;
        gap: 13px;
    }
    
    .upload-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .upload-controls {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }
    
    .file-display {
        width: 100%;
    }
    
    .file-name {
        max-width: 150px;
    }
    
    .button-group {
        justify-content: space-between;
    }
}
</style>

<div class="content-wrapper">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Pendaftaran Ujian Kelayakan</h1>
            <p class="hero-breadcrumb">Registrasi > Ujian Kelayakan</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <h2 class="page-title">Pendaftaran Ujian Kelayakan</h2>
        <hr class="title-divider">
        
        <form method="POST" enctype="multipart/form-data">
            <?php if ($step == 1): ?>
            <!-- Step 1: Identitas Mahasiswa -->
            <div class="form-section">
                <div class="section-header">
                    <h6>A. Identitas Mahasiswa</h6>
                    <p>Please fill out all the required section</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nama Lengkap<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="nama_lengkap" value="<?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tempat/Tgl Lahir<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="tempat_lahir" placeholder="Salatiga, 15 Januari 1990" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">NIM<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="nim" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Alamat Asal<span class="required">*</span></label>
                    <textarea class="form-control-custom" name="alamat" required placeholder="Jl. Gungkuo Sari No.147b, Salatiga, Kec. Sidorejo, Kota Salatiga, Jawa Tengah 50711"><?php echo htmlspecialchars($mahasiswa['alamat']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">No. Telepon/HP<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="no_telp" value="<?php echo htmlspecialchars($mahasiswa['no_telp']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tempat Pelaksanaan<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="tempat_pelaksanaan" placeholder="Gedung Teknologi Informasi" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Jabatan/Posisi Dalam Pekerjaan<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="jabatan_dalam_pekerjaan" placeholder="Data Scientist" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Alamat Pekerjaan<span class="required">*</span></label>
                    <textarea class="form-control-custom" name="alamat_pekerjaan" required placeholder="Jl. Dr. Soetomo"></textarea>
                </div>
                
                <div class="form-row-date">
                    <div class="form-group">
                        <label class="form-label">Tanggal Pengumpulan<span class="required">*</span></label>
                        <input type="date" class="form-control-custom" name="tanggal_pengumuman" required>
                    </div>
                    <!-- <div class="form-group">
                        <label class="form-label">Tanggal Ujian Kelayakan<span class="required">*</span></label>
                        <input type="date" class="form-control-custom" name="tanggal_ujian" required>
                    </div> -->
                </div>
            </div>
            
            <div class="button-group">
                <button type="button" onclick="window.location.href='registrasi.php'" class="btn-custom btn-back">Kembali</button>
                <button type="submit" name="step1" class="btn-custom btn-next">Berikut</button>
            </div>
            
            <?php elseif ($step == 2): ?>
            <!-- Step 2: Lampiran Persyaratan -->
            <div class="form-section">
                <div class="section-header">
                    <p>Make sure to upload each file in the correct section</p>
                </div>
                
                <!-- 1. Berkas Hardcopy -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>6 Bendel Hardcopy Naskah Ujian Kelayakan</h6>
                        <p>dilampiri dengan form persetujuan ujian kelayakan dari promotor dan co promotor</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-1">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-1"></span>
                            <button type="button" class="file-remove" onclick="removeFile(1)">×</button>
                        </div>
                        <input type="file" name="berkas_hardcopy" accept=".pdf,.doc,.docx" id="file-input-1" onchange="handleFileUpload(1)">
                        <button type="button" class="upload-btn" id="upload-btn-1" onclick="document.getElementById('file-input-1').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 2. Bukti Pembayaran -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Bukti Pembayaran Uang Kuliah Terakhir (Cap Lunas)</h6>
                        <p>Dalam bentuk salinan/copy.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-2">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-2"></span>
                            <button type="button" class="file-remove" onclick="removeFile(2)">×</button>
                        </div>
                        <input type="file" name="bukti_pembayaran" accept=".pdf,.doc,.docx" id="file-input-2" onchange="handleFileUpload(2)">
                        <button type="button" class="upload-btn" id="upload-btn-2" onclick="document.getElementById('file-input-2').click()">Upload</button>
                    </div>
                </div>

                <!-- 3. Surat Pernyataan -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Surat Pernyataan tidak Melakukan Tindakan Plagiasi (bermeterai Rp.10.000,-)</h6>
                        <p>Asli.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-3">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-3"></span>
                            <button type="button" class="file-remove" onclick="removeFile(3)">×</button>
                        </div>
                        <input type="file" name="surat_pernyataan" accept=".pdf,.doc,.docx" id="file-input-3" onchange="handleFileUpload(3)">
                        <button type="button" class="upload-btn" id="upload-btn-3" onclick="document.getElementById('file-input-3').click()">Upload</button>
                    </div>
                </div>

                <!-- 6. Acceptance Letter 1 -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6 style="font-style: italic;">Acceptance Letter Publikasi 1</h6>
                        <p style="font-style: italic;">1 Lembar.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-4">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-4"></span>
                            <button type="button" class="file-remove" onclick="removeFile(4)">×</button>
                        </div>
                        <input type="file" name="acceptance_1" accept=".pdf,.doc,.docx" id="file-input-4" onchange="handleFileUpload(4)">
                        <button type="button" class="upload-btn" id="upload-btn-4" onclick="document.getElementById('file-input-4').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 7. Acceptance Letter 2 -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6 style="font-style: italic;">Acceptance Letter Publikasi 2</h6>
                        <p>1 Lembar</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-5">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-5"></span>
                            <button type="button" class="file-remove" onclick="removeFile(5)">×</button>
                        </div>
                        <input type="file" name="acceptance_2" accept=".pdf,.doc,.docx" id="file-input-5" onchange="handleFileUpload(5)">
                        <button type="button" class="upload-btn" id="upload-btn-5" onclick="document.getElementById('file-input-5').click()">Upload</button>
                    </div>
                </div>

                <!-- 8. File Proceeding -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>File Proceeding</h6>
                        <p>Dalam bentuk salinan/copy</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-6">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-6"></span>
                            <button type="button" class="file-remove" onclick="removeFile(6)">×</button>
                        </div>
                        <input type="file" name="file_proceeding" accept=".pdf,.doc,.docx" id="file-input-6" onchange="handleFileUpload(6)">
                        <button type="button" class="upload-btn" id="upload-btn-6" onclick="document.getElementById('file-input-6').click()">Upload</button>
                    </div>
                </div>

                <!-- 9. Bukti Jurnal -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Bukti Pembayaran Jurnal 1 & Jurnal 2</h6>
                        <p style="font-style: italic;">2 Lembar</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-7">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-7"></span>
                            <button type="button" class="file-remove" onclick="removeFile(7)">×</button>
                        </div>
                        <input type="file" name="bukti_jurnal" accept=".pdf,.doc,.docx" id="file-input-7" onchange="handleFileUpload(7)">
                        <button type="button" class="upload-btn" id="upload-btn-7" onclick="document.getElementById('file-input-7').click()">Upload</button>
                    </div>
                </div>

                <!-- 10. Surat Tidak Plagiat -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Surat Pernyataan Tidak Plagiat</h6>
                        <p style="color: #5495FF;">Template Pernyataan Tidak Plagiat.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-8">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-8"></span>
                            <button type="button" class="file-remove" onclick="removeFile(8)">×</button>
                        </div>
                        <input type="file" name="surat_tidak_plagiat" accept=".pdf,.doc,.docx" id="file-input-8" onchange="handleFileUpload(8)">
                        <button type="button" class="upload-btn" id="upload-btn-8" onclick="document.getElementById('file-input-8').click()">Upload</button>
                    </div>
                </div>

                <!-- 3. Scan Ijazah & Transkrip -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Scan Ijazah & Transkrip Nilai S1 & S2</h6>
                        <p>Dalam bentuk salinan/copy yang dilegalisir</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-9">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-9"></span>
                            <button type="button" class="file-remove" onclick="removeFile(9)">×</button>
                        </div>
                        <input type="file" name="scan_ijazah_transkrip" accept=".pdf,.doc,.docx" id="file-input-9" onchange="handleFileUpload(9)">
                        <button type="button" class="upload-btn" id="upload-btn-9" onclick="document.getElementById('file-input-9').click()">Upload</button>
                    </div>
                </div>
                
                
                
                <!-- 5. Transkrip Nilai -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Transkrip Nilai Terbaru</h6>
                        <p>Transkrip nilai S3 terbaru yang sudah dilegalisir</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-10">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-10"></span>
                            <button type="button" class="file-remove" onclick="removeFile(10)">×</button>
                        </div>
                        <input type="file" name="transkrip_nilai_terbaru" accept=".pdf,.doc,.docx" id="file-input-10" onchange="handleFileUpload(10)">
                        <button type="button" class="upload-btn" id="upload-btn-10" onclick="document.getElementById('file-input-10').click()">Upload</button>
                    </div>
                </div>

                <!-- 13. LOA Publikasi 1 dan 2 -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>LOA Publikasi 1 dan 2</h6>
                        <p>Letter of Acceptance untuk kedua publikasi</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-11">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-11"></span>
                            <button type="button" class="file-remove" onclick="removeFile(11)">×</button>
                        </div>
                        <input type="file" name="loa_publikasi" accept=".pdf,.doc,.docx" id="file-input-11" onchange="handleFileUpload(11)">
                        <button type="button" class="upload-btn" id="upload-btn-11" onclick="document.getElementById('file-input-11').click()">Upload</button>
                    </div>
                </div>
                
    
                <!-- 12. Bukti Pembayaran Jurnal -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Bukti Pembayaran Jurnal 1 & Jurnal 2</h6>
                        <p style="font-style: italic;">2 Lembar</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-12">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-12"></span>
                            <button type="button" class="file-remove" onclick="removeFile(12)">×</button>
                        </div>
                        <input type="file" name="bukti_pembayaran_jurnal" accept=".pdf,.doc,.docx" id="file-input-12" onchange="handleFileUpload(12)">
                        <button type="button" class="upload-btn" id="upload-btn-12" onclick="document.getElementById('file-input-12').click()">Upload</button>
                    </div>
                </div>

                <!-- 14. Form Ujian Kelayakan -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Form Ujian Kelayakan</h6>
                        <p>Formulir pendaftaran ujian kelayakan yang sudah diisi dan ditandatangani</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-13">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-13"></span>
                            <button type="button" class="file-remove" onclick="removeFile(13)">×</button>
                        </div>
                        <input type="file" name="form_ujian_kelayakan" accept=".pdf,.doc,.docx" id="file-input-13" onchange="handleFileUpload(13)">
                        <button type="button" class="upload-btn" id="upload-btn-13" onclick="document.getElementById('file-input-13').click()">Upload</button>
                    </div>
                </div>
            </div>
            
            <div class="button-group">
                <button type="button" onclick="window.location.href='?step=1'" class="btn-custom btn-cancel">Kembali</button>
                <button type="submit" name="submit" class="btn-custom btn-submit">Kirim</button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
function handleFileUpload(id) {
    const fileInput = document.getElementById('file-input-' + id);
    const fileDisplay = document.getElementById('file-display-' + id);
    const fileName = document.getElementById('file-name-' + id);
    const uploadBtn = document.getElementById('upload-btn-' + id);
    
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        
        // Truncate filename if too long
        let displayName = file.name;
        if (displayName.length > 30) {
            const extension = displayName.split('.').pop();
            displayName = displayName.substring(0, 25) + '...' + extension;
        }
        
        // Show file display
        fileName.textContent = displayName;
        fileName.title = file.name; // Show full name on hover
        fileDisplay.classList.add('show');
        uploadBtn.classList.add('has-file');
    }
}

function removeFile(id) {
    const fileInput = document.getElementById('file-input-' + id);
    const fileDisplay = document.getElementById('file-display-' + id);
    const uploadBtn = document.getElementById('upload-btn-' + id);
    
    // Clear file input
    fileInput.value = '';
    
    // Hide file display
    fileDisplay.classList.remove('show');
    uploadBtn.classList.remove('has-file');
}
</script>

<?php include '../includes/footer.php'; ?>
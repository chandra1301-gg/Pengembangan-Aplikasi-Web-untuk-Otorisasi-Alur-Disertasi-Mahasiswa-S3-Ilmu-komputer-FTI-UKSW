<?php
/**
 * File: mahasiswa/form_ujian_kualifikasi.php
 * Form pendaftaran ujian kualifikasi disertasi dengan 3 tahap - SUDAH DITAMBAH CO-PROMOTOR 2
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];

// VALIDASI: Cek apakah boleh daftar ujian kualifikasi
function bolehDaftarUjian($conn, $id_mahasiswa, $jenis_ujian) {
    $tahapan = [
        'proposal' => 1,
        'kualifikasi' => 2, 
        'kelayakan' => 3,
        'tertutup' => 4
    ];
    
    $current_tahap = $tahapan[$jenis_ujian];
    
    // Cek tahap sebelumnya harus lulus
    foreach ($tahapan as $ujian => $tahap) {
        if ($tahap == $current_tahap - 1) {
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

$boleh_daftar = bolehDaftarUjian($conn, $id_mahasiswa, 'kualifikasi');

if (!$boleh_daftar['boleh']) {
    $_SESSION['error_message'] = "Tidak dapat mendaftar ujian kualifikasi: " . $boleh_daftar['alasan'];
    header("Location: registrasi.php");
    exit();
}

// Cek apakah sudah ada registrasi pending
$sql = "SELECT COUNT(*) as total FROM registrasi 
        WHERE id_mahasiswa = ? AND jenis_ujian = 'kualifikasi' AND status = 'Menunggu'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    $_SESSION['error_message'] = "Anda sudah memiliki registrasi ujian kualifikasi yang menunggu approval.";
    header("Location: registrasi.php");
    exit();
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Simpan data step 1
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step1'])) {
    $_SESSION['kualifikasi_data']['nama_lengkap'] = $_POST['nama_lengkap'];
    $_SESSION['kualifikasi_data']['nim'] = $_POST['nim'];
    $_SESSION['kualifikasi_data']['no_telp'] = $_POST['no_telp'];
    header("Location: ?step=2");
    exit();
}

// Simpan data step 2 - DITAMBAH CO_PROMOTOR2
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['step2'])) {
    $_SESSION['kualifikasi_data']['judul_disertasi'] = $_POST['judul_disertasi'];
    $_SESSION['kualifikasi_data']['promotor'] = $_POST['promotor'];
    $_SESSION['kualifikasi_data']['co_promotor'] = $_POST['co_promotor'];
    $_SESSION['kualifikasi_data']['co_promotor2'] = $_POST['co_promotor2'];
    header("Location: ?step=3");
    exit();
}

// Submit final dengan upload - DITAMBAH CO_PROMOTOR2
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $judul_disertasi = clean_input($_SESSION['kualifikasi_data']['judul_disertasi']);
    $promotor = $_SESSION['kualifikasi_data']['promotor'];
    $co_promotor = $_SESSION['kualifikasi_data']['co_promotor'];
    $co_promotor2 = $_SESSION['kualifikasi_data']['co_promotor2'];
    
    // QUERY DIUPDATE - tambah co_promotor2
    $query = "INSERT INTO registrasi (id_mahasiswa, jenis_ujian, judul_disertasi, promotor, co_promotor, co_promotor2, tanggal_pengajuan, status) 
            VALUES (" . $mahasiswa['id_mahasiswa'] . ", 'kualifikasi', '" . escape_string($judul_disertasi) . "', 
                    '" . (int)$promotor . "', '" . (int)$co_promotor . "', '" . (int)$co_promotor2 . "', NOW(), 'Menunggu')";
    
    if (mysqli_query($conn, $query)) {
        $id_registrasi = mysqli_insert_id($conn);

        // KIRIM EMAIL NOTIFIKASI KE ADMIN
        require_once '../includes/email_sender.php';
        
        $student_name = $mahasiswa['nama_lengkap'];
        $student_nim = $mahasiswa['nim'];
        $exam_type = 'kualifikasi';
        $registration_date = date('d F Y H:i:s');
        
        if (testEmailConfiguration()) {
            $email_sent = sendRegistrationNotification($student_name, $student_nim, $exam_type, $registration_date);
            
            if (!$email_sent) {
                error_log("Gagal mengirim email notifikasi untuk pendaftaran kualifikasi ID: $id_registrasi");
            }
        }
        
        // Upload files
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_fields = [
            'draft_jurnal',
            'form_pendaftaran_kualifikasi'
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
        
        unset($_SESSION['kualifikasi_data']);
        $_SESSION['success_message'] = "Registrasi Ujian Kualifikasi berhasil!";
        header("Location: dashboard.php");
        exit();
    }
}

$page_title = "Pendaftaran Ujian Kualifikasi - Sistem Disertasi S3 UKSW";
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

.hero-breadcrumb .separator {
    margin: 0 4px;
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
            <h1>Pendaftaran Ujian Kualifikasi</h1>
            <p class="hero-breadcrumb">Registrasi > Ujian Kualifikasi</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <h2 class="page-title">Pendaftaran Ujian Kualifikasi Disertasi</h2>
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
                    <input type="text" class="form-control-custom" name="nama_lengkap" value="<?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?>" required placeholder="Sherly Septiani">
                </div>
                
                <div class="form-group">
                    <label class="form-label">NIM<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="nim" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" required placeholder="672022259">
                </div>
                
                <div class="form-group">
                    <label class="form-label">No. Telp<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="no_telp" value="<?php echo htmlspecialchars($mahasiswa['no_telp']); ?>" required placeholder="088233736987">
                </div>
            </div>
            
            <div class="button-group">
                <button type="button" onclick="window.location.href='registrasi.php'" class="btn-custom btn-back">Kembali</button>
                <button type="submit" name="step1" class="btn-custom btn-next">Berikut</button>
            </div>
            
            <?php elseif ($step == 2): ?>
            <!-- Step 2: Informasi Disertasi -->
            <div class="form-section">
                <div class="section-header">
                    <h6>B. Informasi Disertasi</h6>
                    <p>Please fill out all the required section</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Judul Disertasi<span class="required">*</span></label>
                    <textarea class="form-control-custom" name="judul_disertasi" required placeholder="Integrated Customer Data Analysis (ICDA) Berbasis Deep Learning untuk Optimalisasi Customer Relationship Management"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Promotor<span class="required">*</span></label>
                    <select class="form-control-custom" name="promotor" required>
                        <option value="">Pilih Promotor</option>
                        <?php
                        // Ambil data dosen dari database
                        $dosen_query = "SELECT * FROM dosen WHERE status = 'active' ORDER BY nama_lengkap";
                        $dosen_result = mysqli_query($conn, $dosen_query);
                        while ($dosen = mysqli_fetch_assoc($dosen_result)) {
                            echo '<option value="' . $dosen['id_dosen'] . '">' . htmlspecialchars($dosen['nama_lengkap']) . ' - ' . htmlspecialchars($dosen['bidang_keahlian']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Co-Promotor 1<span class="required">*</span></label>
                    <select class="form-control-custom" name="co_promotor" required>
                        <option value="">Pilih Co-Promotor 1</option>
                        <?php
                        mysqli_data_seek($dosen_result, 0); // Reset pointer result
                        while ($dosen = mysqli_fetch_assoc($dosen_result)) {
                            echo '<option value="' . $dosen['id_dosen'] . '">' . htmlspecialchars($dosen['nama_lengkap']) . ' - ' . htmlspecialchars($dosen['bidang_keahlian']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Co-Promotor 2<span class="required">*</span></label>
                    <select class="form-control-custom" name="co_promotor2" required>
                        <option value="">Pilih Co-Promotor 2</option>
                        <?php
                        mysqli_data_seek($dosen_result, 0); // Reset pointer result
                        while ($dosen = mysqli_fetch_assoc($dosen_result)) {
                            echo '<option value="' . $dosen['id_dosen'] . '">' . htmlspecialchars($dosen['nama_lengkap']) . ' - ' . htmlspecialchars($dosen['bidang_keahlian']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="button-group">
                <button type="button" onclick="window.location.href='?step=1'" class="btn-custom btn-back">Kembali</button>
                <button type="submit" name="step2" class="btn-custom btn-next">Berikut</button>
            </div>
            
            <?php elseif ($step == 3): ?>
            <!-- Step 3: Lampiran Persyaratan -->
            <div class="form-section">
                <div class="section-header">
                    <h6>C. Lampiran Persyaratan</h6>
                    <p>Make sure to upload each file to this correct section</p>
                </div>
                
                <!-- 1. Draft Jurnal -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Mengirimkan draft jurnal yang sudah disetujui promotor & co-promotor</h6>
                        <p>Soft file of the approved journal draft</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-1">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-1"></span>
                            <button type="button" class="file-remove" onclick="removeFile(1)">×</button>
                        </div>
                        <input type="file" name="draft_jurnal" accept=".pdf,.doc,.docx" id="file-input-1" onchange="handleFileUpload(1)">
                        <button type="button" class="upload-btn" id="upload-btn-1" onclick="document.getElementById('file-input-1').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 2. Form Pendaftaran -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Form Pendaftaran Ujian Kualifikasi Disertasi</h6>
                        <p>Formulir pendaftaran telah di tanda tangani</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-2">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-2"></span>
                            <button type="button" class="file-remove" onclick="removeFile(2)">×</button>
                        </div>
                        <input type="file" name="form_pendaftaran_kualifikasi" accept=".pdf,.doc,.docx" id="file-input-2" onchange="handleFileUpload(2)">
                        <button type="button" class="upload-btn" id="upload-btn-2" onclick="document.getElementById('file-input-2').click()">Upload</button>
                    </div>
                </div>
            </div>
            
            <div class="button-group">
                <button type="button" onclick="window.location.href='?step=2'" class="btn-custom btn-cancel">Kembali</button>
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
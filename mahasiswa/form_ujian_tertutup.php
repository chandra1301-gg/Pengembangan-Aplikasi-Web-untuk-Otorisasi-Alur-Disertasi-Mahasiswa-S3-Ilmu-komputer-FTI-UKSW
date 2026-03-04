<?php
/**
 * File: mahasiswa/form_ujian_tertutup.php
 * Form pendaftaran ujian tertutup dengan 3 tahap - PERBAIKAN BUG
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];

// VALIDASI: Cek apakah boleh daftar ujian tertutup
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

$boleh_daftar = bolehDaftarUjian($conn, $id_mahasiswa, 'tertutup');

if (!$boleh_daftar['boleh']) {
    $_SESSION['error_message'] = "Tidak dapat mendaftar ujian tertutup: " . $boleh_daftar['alasan'];
    header("Location: registrasi.php");
    exit();
}

// Cek apakah sudah ada registrasi pending
$sql = "SELECT COUNT(*) as total FROM registrasi 
        WHERE id_mahasiswa = ? AND jenis_ujian = 'tertutup' AND status = 'Menunggu'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    $_SESSION['error_message'] = "Anda sudah memiliki registrasi ujian tertutup yang menunggu approval.";
    header("Location: registrasi.php");
    exit();
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Inisialisasi session data jika belum ada
if (!isset($_SESSION['form_data'])) {
    $_SESSION['form_data'] = [];
}
if (!isset($_SESSION['tertutup_data'])) {
    $_SESSION['tertutup_data'] = [];
}

// Simpan data di session untuk multi-step
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step1'])) {
        // Step 1: Simpan data identitas
        $_SESSION['form_data']['nama_lengkap'] = $_POST['nama_lengkap'];
        $_SESSION['form_data']['nim'] = $_POST['nim'];
        $_SESSION['form_data']['jenis_kelamin'] = $_POST['jenis_kelamin'];
        $_SESSION['form_data']['no_telp'] = $_POST['no_telp'];
        $_SESSION['form_data']['alamat'] = $_POST['alamat'];
        header("Location: ?step=2");
        exit();
    }
    
    if (isset($_POST['step2'])) {
        // Step 2: Simpan data disertasi dan promotor - PERBAIKAN: Simpan ke session yang benar
        $_SESSION['form_data']['judul_disertasi'] = $_POST['judul_disertasi'];
        
        // Simpan data promotor ke session yang benar
        $_SESSION['tertutup_data']['promotor'] = !empty($_POST['promotor']) ? (int)$_POST['promotor'] : NULL;
        $_SESSION['tertutup_data']['co_promotor'] = !empty($_POST['co_promotor']) ? (int)$_POST['co_promotor'] : NULL;
        $_SESSION['tertutup_data']['co_promotor2'] = !empty($_POST['co_promotor2']) ? (int)$_POST['co_promotor2'] : NULL;
        
        header("Location: ?step=3");
        exit();
    }
    
    if (isset($_POST['submit_final'])) {
        // Insert ke database - PERBAIKAN: Gunakan data dari session yang benar
        $judul_disertasi = clean_input($_SESSION['form_data']['judul_disertasi']);
        
        // PERBAIKAN: Ambil data promotor dari session yang benar
        $promotor = isset($_SESSION['tertutup_data']['promotor']) ? $_SESSION['tertutup_data']['promotor'] : NULL;
        $co_promotor = isset($_SESSION['tertutup_data']['co_promotor']) ? $_SESSION['tertutup_data']['co_promotor'] : NULL;
        $co_promotor2 = isset($_SESSION['tertutup_data']['co_promotor2']) ? $_SESSION['tertutup_data']['co_promotor2'] : NULL;
        
        // PERBAIKAN: Gunakan prepared statement untuk menghindari SQL injection dan foreign key error
        $query = "INSERT INTO registrasi (id_mahasiswa, jenis_ujian, judul_disertasi, promotor, co_promotor, co_promotor2, tanggal_pengajuan, status) 
                  VALUES (?, 'tertutup', ?, ?, ?, ?, NOW(), 'Menunggu')";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isiii", $id_mahasiswa, $judul_disertasi, $promotor, $co_promotor, $co_promotor2);
        
        if ($stmt->execute()) {
            $id_registrasi = $stmt->insert_id;

            // KIRIM EMAIL NOTIFIKASI KE ADMIN
    require_once '../includes/email_sender.php';
    
    $student_name = $mahasiswa['nama_lengkap'];
    $student_nim = $mahasiswa['nim'];
    $exam_type = 'tertutup';
    $registration_date = date('d F Y H:i:s');
    
    if (testEmailConfiguration()) {
        $email_sent = sendRegistrationNotification($student_name, $student_nim, $exam_type, $registration_date);
        
        if (!$email_sent) {
            error_log("Gagal mengirim email notifikasi untuk pendaftaran tertutup ID: $id_registrasi");
        }
    }
            
            // Upload files
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Define file fields
            $file_fields = [
                'surat_persetujuan',
                'letter_acceptance',
                'bukti_pembayaran',
                'surat_keterangan',
                'transkrip_nilai',
                'skor_toefl_tpa',
                'surat_cuti',
                'ijazah_s2',
                'disertasi',
                'bukti_bimbingan'
            ];
            
            foreach ($file_fields as $field) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                    $file_ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                    $new_filename = $field . '_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                    $target_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_path)) {
                        // PERBAIKAN: Gunakan prepared statement untuk insert lampiran
                        $query_file = "INSERT INTO lampiran (id_registrasi, nama_berkas, path_berkas) VALUES (?, ?, ?)";
                        $stmt_file = $conn->prepare($query_file);
                        $stmt_file->bind_param("iss", $id_registrasi, $_FILES[$field]['name'], $new_filename);
                        $stmt_file->execute();
                    }
                }
            }
            
            // Bersihkan session
            unset($_SESSION['form_data']);
            unset($_SESSION['tertutup_data']);
            
            $_SESSION['success_message'] = "Registrasi Ujian Tertutup berhasil! Mohon menunggu verifikasi dari admin.";
            header("Location: dashboard.php");
            exit();
        } else {
            // Handle error
            $_SESSION['error_message'] = "Gagal menyimpan registrasi: " . $stmt->error;
            header("Location: ?step=3");
            exit();
        }
    }
}

// PERBAIKAN: Ambil data dosen untuk dropdown
$dosen_query = "SELECT * FROM dosen WHERE status = 'active' ORDER BY nama_lengkap";
$dosen_result = mysqli_query($conn, $dosen_query);
$dosen_list = [];
while ($dosen = mysqli_fetch_assoc($dosen_result)) {
    $dosen_list[] = $dosen;
}

$page_title = "Pendaftaran Ujian Tertutup - Sistem Disertasi S3 UKSW";
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

/* Radio Buttons */
.radio-group {
    display: flex;
    gap: 136px;
    margin-top: 6px;
}

.radio-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.radio-item input[type="radio"] {
    width: 16px;
    height: 16px;
    margin: 0;
}

.radio-item label {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 11.484px;
    line-height: 17px;
    letter-spacing: 0.03em;
    color: #000000;
    margin: 0;
    cursor: pointer;
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
    
    .radio-group {
        gap: 40px;
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
            <h1>Pendaftaran Ujian Tertutup</h1>
            <p class="hero-breadcrumb">Registrasi > Ujian Tertutup</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <h2 class="page-title">Pendaftaran Ujian Tertutup</h2>
        <hr class="title-divider">
        
        <form method="POST" enctype="multipart/form-data" id="registrationForm">
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
                    <label class="form-label">NIM<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="nim" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin<span class="required">*</span></label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" name="jenis_kelamin" id="perempuan" value="Perempuan" <?php echo ($mahasiswa['jenis_kelamin'] == 'Perempuan') ? 'checked' : ''; ?>>
                            <label for="perempuan">Perempuan</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" name="jenis_kelamin" id="laki" value="Laki-laki" <?php echo ($mahasiswa['jenis_kelamin'] == 'Laki-laki') ? 'checked' : ''; ?>>
                            <label for="laki">Laki-laki</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">No. Telp<span class="required">*</span></label>
                    <input type="text" class="form-control-custom" name="no_telp" value="<?php echo htmlspecialchars($mahasiswa['no_telp']); ?>" placeholder="082123456789" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Alamat<span class="required">*</span></label>
                    <textarea class="form-control-custom" name="alamat" required placeholder="Jl. Gungkuo Sari No.147b, Salatiga, Kec. Sidorejo, Kota Salatiga, Jawa Tengah 50711"><?php echo htmlspecialchars($mahasiswa['alamat']); ?></textarea>
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
                    <textarea class="form-control-custom" name="judul_disertasi" required placeholder="Integrated Customer Data Analysis (ICDA) Berbasis Deep-Learning untuk Optimalisasi Customer Relationship Management"></textarea>
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
                
                <!-- Co-Promotor 1 -->
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
                
                <!-- Co-Promotor 2 -->
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
                    <p>Make sure to upload each file in the correct section</p>
                </div>
                
                <!-- 1. Surat Persetujuan -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Surat Persetujuan Dosen Pembimbing (Asli)</h6>
                        <p>2 lembar</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-1">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-1"></span>
                            <button type="button" class="file-remove" onclick="removeFile(1)">×</button>
                        </div>
                        <input type="file" name="surat_persetujuan" accept=".pdf,.doc,.docx" id="file-input-1" onchange="handleFileUpload(1)">
                        <button type="button" class="upload-btn" id="upload-btn-1" onclick="document.getElementById('file-input-1').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 2. Letter of Acceptance -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6 style="font-style: italic;">Letter of Acceptance dan Bukti Pembayaran</h6>
                        <p>2 Jurnal Internasional bereputasi dan 1 Proceeding Internasional bereputasi. Jika status published, bisa digantikan dengan tangkapan layar abstrak dan URL.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-2">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-2"></span>
                            <button type="button" class="file-remove" onclick="removeFile(2)">×</button>
                        </div>
                        <input type="file" name="letter_acceptance" accept=".pdf,.doc,.docx" id="file-input-2" onchange="handleFileUpload(2)">
                        <button type="button" class="upload-btn" id="upload-btn-2" onclick="document.getElementById('file-input-2').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 3. Bukti Pembayaran SPP -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Bukti Pembayaran SPP/Status Aktif</h6>
                        <p>Dalam bentuk salinan/copy.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-3">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-3"></span>
                            <button type="button" class="file-remove" onclick="removeFile(3)">×</button>
                        </div>
                        <input type="file" name="bukti_pembayaran" accept=".pdf,.doc,.docx" id="file-input-3" onchange="handleFileUpload(3)">
                        <button type="button" class="upload-btn" id="upload-btn-3" onclick="document.getElementById('file-input-3').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 4. Surat Keterangan Lunas -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Surat Keterangan Lulus Ujian Komprehensif/Kelayakan</h6>
                        <p>Dalam bentuk salinan/copy.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-4">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-4"></span>
                            <button type="button" class="file-remove" onclick="removeFile(4)">×</button>
                        </div>
                        <input type="file" name="surat_keterangan" accept=".pdf,.doc,.docx" id="file-input-4" onchange="handleFileUpload(4)">
                        <button type="button" class="upload-btn" id="upload-btn-4" onclick="document.getElementById('file-input-4').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 5. Transkrip Nilai -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Transkrip Mata Kuliah (Lembar Kemajuan Belajar)</h6>
                        <p>Dalam bentuk salinan/copy.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-5">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-5"></span>
                            <button type="button" class="file-remove" onclick="removeFile(5)">×</button>
                        </div>
                        <input type="file" name="transkrip_nilai" accept=".pdf,.doc,.docx" id="file-input-5" onchange="handleFileUpload(5)">
                        <button type="button" class="upload-btn" id="upload-btn-5" onclick="document.getElementById('file-input-5').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 6. Skor TOEFL dan TPA -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Skor TOEFL ≥ 500 dan TPA ≥ 550</h6>
                        <p>Dalam bentuk salinan/copy.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-6">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-6"></span>
                            <button type="button" class="file-remove" onclick="removeFile(6)">×</button>
                        </div>
                        <input type="file" name="skor_toefl_tpa" accept=".pdf,.doc,.docx" id="file-input-6" onchange="handleFileUpload(6)">
                        <button type="button" class="upload-btn" id="upload-btn-6" onclick="document.getElementById('file-input-6').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 7. Surat Cuti -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Surat Cuti</h6>
                        <p>Hanya untuk yang pernah cuti. Dalam bentuk salinan/copy.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-7">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-7"></span>
                            <button type="button" class="file-remove" onclick="removeFile(7)">×</button>
                        </div>
                        <input type="file" name="surat_cuti" accept=".pdf,.doc,.docx" id="file-input-7" onchange="handleFileUpload(7)">
                        <button type="button" class="upload-btn" id="upload-btn-7" onclick="document.getElementById('file-input-7').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 8. Ijazah S2 -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Ijazah dan Transkrip Nilai S1 dan S2</h6>
                        <p>Dalam bentuk salinan/copy</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-8">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-8"></span>
                            <button type="button" class="file-remove" onclick="removeFile(8)">×</button>
                        </div>
                        <input type="file" name="ijazah_s2" accept=".pdf,.doc,.docx" id="file-input-8" onchange="handleFileUpload(8)">
                        <button type="button" class="upload-btn" id="upload-btn-8" onclick="document.getElementById('file-input-8').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 9. Disertasi -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Disertasi yang Ditandatangani Tim Promotor</h6>
                        <p style="font-style: italic;">Hardcopy 7 buku disertasi, format sesuai ketentuan dari DIK</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-9">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-9"></span>
                            <button type="button" class="file-remove" onclick="removeFile(9)">×</button>
                        </div>
                        <input type="file" name="disertasi" accept=".pdf,.doc,.docx" id="file-input-9" onchange="handleFileUpload(9)">
                        <button type="button" class="upload-btn" id="upload-btn-9" onclick="document.getElementById('file-input-9').click()">Upload</button>
                    </div>
                </div>
                
                <!-- 10. Buku Bimbingan -->
                <div class="upload-item">
                    <div class="upload-info">
                        <h6>Buku Bimbingan yang ditandangani Pembimbing dan Kaprodi</h6>
                        <p>1 lembar dalam bentuk salinan/copy. Bisa diganti dengan bukti email selama bimbingan.</p>
                    </div>
                    <div class="upload-controls">
                        <div class="file-display" id="file-display-10">
                            <div class="file-icon"></div>
                            <span class="file-name" id="file-name-10"></span>
                            <button type="button" class="file-remove" onclick="removeFile(10)">×</button>
                        </div>
                        <input type="file" name="bukti_bimbingan" accept=".pdf,.doc,.docx" id="file-input-10" onchange="handleFileUpload(10)">
                        <button type="button" class="upload-btn" id="upload-btn-10" onclick="document.getElementById('file-input-10').click()">Upload</button>
                    </div>
                </div>
            </div>
            
            <div class="button-group">
                <button type="button" onclick="window.location.href='?step=2'" class="btn-custom btn-cancel">Kembali</button>
                <button type="submit" name="submit_final" class="btn-custom btn-submit">Kirim</button>
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
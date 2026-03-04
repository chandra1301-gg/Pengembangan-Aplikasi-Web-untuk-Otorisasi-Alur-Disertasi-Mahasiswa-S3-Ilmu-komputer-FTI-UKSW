<?php
/**
 * File: mahasiswa/monev.php
 * Halaman upload dan melihat laporan monitoring evaluasi sesuai desain CSS
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
$success = '';
$error = '';

// Proses upload monev
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jawaban1 = clean_input($_POST['jawaban1']);
    $jawaban2 = clean_input($_POST['jawaban2']);
    $jawaban3 = clean_input($_POST['jawaban3']);
    $jawaban4 = clean_input($_POST['jawaban4']);
    $jawaban5 = clean_input($_POST['jawaban5']);
    
    $deskripsi = json_encode([
        'jawaban1' => $jawaban1,
        'jawaban2' => $jawaban2,
        'jawaban3' => $jawaban3,
        'jawaban4' => $jawaban4,
        'jawaban5' => $jawaban5
    ]);
    
    $periode = date('Y-m');
    
    $query = "INSERT INTO monev (id_mahasiswa, periode, deskripsi, tanggal_upload) 
              VALUES (" . $mahasiswa['id_mahasiswa'] . ", '" . escape_string($periode) . "', 
              '" . escape_string($deskripsi) . "', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $success = "Laporan Monev berhasil disimpan!";
    } else {
        $error = "Gagal menyimpan data monev.";
    }
}

$page_title = "Laporan Kemajuan Penelitian - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_mahasiswa.php';
?>

<div class="main-content-monev">
    <div class="container-fluid p-0">
        <!-- Header Image Section -->
        <div class="header-image-section">
            <div class="header-content">
                <h1>Laporan Kemajuan Penelitian Disertasi</h1>
                <p class="header-breadcrumb">Registrasi > Monitoring Evaluasi</p>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-wrapper-monev">
            <!-- Title Section -->
            <div class="title-section-monev">
                <h2>Laporan Kemajuan Penelitian Disertasi</h2>
                <p class="subtitle-monev">Penelitian bagian disertasi mahasiswa S3 Ilmu Komputer UKSW Salatiga</p>
            </div>
            
            <div class="divider-line-monev"></div>
            
            <!-- Form Section -->
            <form method="POST" class="monev-form">
                <!-- Identity Fields -->
                <div class="form-row-identity">
                    <!-- Nama Lengkap -->
                    <div class="form-group-monev">
                        <label class="form-label-monev">Nama Lengkap</label>
                        <div class="input-box-monev">
                            <input type="text" value="<?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?>" readonly>
                        </div>
                    </div>
                    
                    <!-- Judul Penelitian -->
                    <div class="form-group-monev">
                        <label class="form-label-monev">Judul Penelitian</label>
                        <div class="textarea-box-monev">
                            <textarea name="judul_penelitian" placeholder= "Integrated Customer Data Analysis (ICDA) Berbasis Deep Learning untuk Optimalisasi Customer Relationship Management" required></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Laporan Mahasiswa Section -->
                <div class="laporan-section">
                    <div class="laporan-title">
                        <h3>Laporan Mahasiswa</h3>
                        <div class="title-underline"></div>
                    </div>
                    
                    <!-- Question 1 -->
                    <div class="question-group">
                        <label class="question-label">
                            I. Jabarkan kegiatan yang sudah dapat direalisasikan (4 bulan yang lalu).
                            <span class="required-mark">*</span>
                        </label>
                        <div class="answer-box">
                            <textarea name="jawaban1" placeholder="Type your answer here..." required></textarea>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="question-group">
                        <label class="question-label">
                            II. Kegiatan yang seharusnya selesai, tetapi tidak/belum dapat direalisasikan. Sebutkan hambatannya dan rencana penyelesaiannya.
                            <span class="required-mark">*</span>
                        </label>
                        <div class="answer-box">
                            <textarea name="jawaban2" placeholder="Type your answer here..." required></textarea>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="question-group">
                        <label class="question-label">
                            III. Kegiatan penelitian yang direncanakan dalam 4 bulan yang akan datang.
                            <span class="required-mark">*</span>
                        </label>
                        <div class="answer-box">
                            <textarea name="jawaban3" placeholder="Type your answer here..." required></textarea>
                        </div>
                    </div>
                    
                    <!-- Question 4 -->
                    <div class="question-group">
                        <label class="question-label">
                            IV. Garis besar kegiatan penelitian selanjutnya.
                            <span class="required-mark">*</span>
                        </label>
                        <div class="answer-box">
                            <textarea name="jawaban4" placeholder="Type your answer here..." required></textarea>
                        </div>
                    </div>
                    
                    <!-- Question 5 -->
                    <div class="question-group">
                        <label class="question-label">
                            V. Saran/masukan/komentar untuk perbaikan (dari reviewer).
                            <span class="required-mark">*</span>
                        </label>
                        <p class="question-note">Dari hasil klarifikasi paparan mahasiswa dan mencermati poin I s/d IV di atas, reviewer memberi masukan, saran, dan komentar.</p>
                        <div class="answer-box">
                            <textarea name="jawaban5" placeholder="Type your answer here..." required></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="form-buttons">
                    <a href="dashboard.php" class="btn-cancel-monev">Batal</a>
                    <button type="submit" class="btn-submit-monev">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Import Poppins Font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* Main Content Monev */
.main-content-monev {
    margin-left: 269px;
    padding: 0 !important;
    background: linear-gradient(123.95deg, #EEF2FF 0%, #F8F9FC 29.06%);
    min-height: 100vh;
}

/* Header Image Section - Sesuai Figma */
.header-image-section {
    position: relative;
    width: 100%;
    height: 355px;
    left: 0;
    top: 0;
    overflow: hidden;
}

/* Background Image */
.header-image-section::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background: url('../assets/foto_header.png') center/cover;
    z-index: 0;
}

/* Gradient Overlay */
.header-image-section::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background: linear-gradient(90deg, rgba(109, 150, 101, 0.6) 0%, rgba(124, 105, 65, 0.6) 31.25%, rgba(0, 0, 0, 0.8) 98.08%);
    z-index: 1;
}

/* Header Content - Sesuai Figma Position */
.header-content {
    position: absolute;
    width: 509px;
    height: 61px;
    left: 59px;
    top: 147px;
    z-index: 10;
}

.header-content h1 {
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

.header-breadcrumb {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 500;
    font-size: 15px;
    line-height: 22px;
    letter-spacing: 0.01em;
    color: #FFFFFF;
    margin: 0;
}

/* Content Wrapper - Sesuai Figma */
.content-wrapper-monev {
    position: relative;
    padding: 37px 36px;
    max-width: 1440px;
    margin: 0 auto;
}

/* Title Section - Sesuai Figma */
.title-section-monev {
    position: relative;
    max-width: 1098px;
    margin-bottom: 0;
}

.title-section-monev h2 {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 20.5475px;
    line-height: 31px;
    letter-spacing: 0.03em;
    color: #000000;
    margin: 0 0 3px 0;
}

.subtitle-monev {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 300;
    font-size: 13.8879px;
    line-height: 21px;
    letter-spacing: 0.03em;
    color: #000000;
    margin: 0;
}

/* Divider - Sesuai Figma */
.divider-line-monev {
    position: relative;
    max-width: 1098px;
    width: 100%;
    height: 0px;
    border: 1px solid #000000;
    margin: 16px 0 31px 0;
}

/* Form Row Identity - Sesuai Figma */
.form-row-identity {
    position: relative;
    width: 561px;
    margin-bottom: 53px;
}

/* Form Group */
.form-group-monev {
    margin-bottom: 17px;
}

.form-label-monev {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.03em;
    color: #000000;
    display: block;
    margin-bottom: 4px;
}

/* Input Box - Sesuai Figma */
.input-box-monev {
    box-sizing: border-box;
    width: 561px;
    height: 32px;
    background: #FFFFFF;
    border: 0.1px solid #000000;
    box-shadow: 1px 1px 5px 1px rgba(0, 0, 0, 0.11);
    border-radius: 4.10141px;
    position: relative;
}

.input-box-monev input {
    width: 100%;
    height: 100%;
    border: none;
    background: transparent;
    padding: 0 8px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 13px;
    line-height: 20px;
    letter-spacing: 0.01em;
    color: #464646;
}

/* Textarea Box - Sesuai Figma */
.textarea-box-monev {
    box-sizing: border-box;
    width: 561px;
    height: 88px;
    background: #FFFFFF;
    border: 0.1px solid #000000;
    box-shadow: 1px 1px 5px 1px rgba(0, 0, 0, 0.11);
    border-radius: 4.10141px;
}

.textarea-box-monev textarea {
    width: 100%;
    height: 100%;
    border: none;
    background: transparent;
    padding: 6px 8px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 13px;
    line-height: 20px;
    letter-spacing: 0.01em;
    color: #464646;
    resize: vertical;
}

/* Laporan Section - Sesuai Figma */
.laporan-section {
    position: relative;
    max-width: 1080px;
    width: 100%;
    margin-top: 46px;
}

/* Laporan Title - Sesuai Figma */
.laporan-title {
    position: relative;
    width: 193px;
    height: 33.5px;
    margin-bottom: 23px;
}

.laporan-title h3 {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 700;
    font-size: 18px;
    line-height: 27px;
    letter-spacing: 0.02em;
    color: #000000;
    margin: 0 0 6px 0;
}

.title-underline {
    width: 193px;
    height: 0px;
    border: 1px solid #000000;
}

/* Question Group - Sesuai Figma */
.question-group {
    position: relative;
    margin-bottom: 31px;
}

.question-label {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 500;
    font-size: 14.7214px;
    line-height: 22px;
    letter-spacing: 0.01em;
    color: #000000;
    display: block;
    margin-bottom: 6px;
}

.required-mark {
    color: #FF0000;
    margin-left: 3px;
}

.question-note {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 12.8812px;
    line-height: 19px;
    letter-spacing: 0.01em;
    color: #000000;
    margin: 5px 0 6px 17.48px;
}

/* Answer Box - Sesuai Figma */
.answer-box {
    position: relative;
    max-width: 1080px;
    width: 100%;
}

.answer-box textarea {
    box-sizing: border-box;
    width: 100%;
    max-width: 1080px;
    min-height: 117px;
    background: #FFFFFF;
    border: 0.0920086px solid #000000;
    box-shadow: 0.920086px 0.920086px 4.60043px 0.920086px rgba(0, 0, 0, 0.11);
    border-radius: 3.77365px;
    padding: 7px 12px;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 13px;
    line-height: 20px;
    letter-spacing: 0.01em;
    color: #000000;
    resize: vertical;
}

.answer-box textarea::placeholder {
    color: rgba(0, 0, 0, 0.4);
}

.answer-box textarea:focus {
    outline: none;
    border-color: #5495FF;
    box-shadow: 0 0 0 2px rgba(84, 149, 255, 0.2);
}

/* Form Buttons - Sesuai Figma */
.form-buttons {
    position: relative;
    display: flex;
    justify-content: flex-end;
    gap: 21px;
    margin-top: 41px;
    padding-bottom: 50px;
}

/* Cancel Button - Sesuai Figma */
.btn-cancel-monev {
    box-sizing: border-box;
    width: 100px;
    height: 41px;
    border: 1.08799px solid #EB6171;
    filter: drop-shadow(0px 2.17598px 4.35196px rgba(0, 0, 0, 0.25));
    border-radius: 4.68568px;
    background: transparent;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 15.9313px;
    line-height: 24px;
    letter-spacing: 0.03em;
    color: #F93636;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-cancel-monev:hover {
    background: #FFF5F5;
    color: #F93636;
}

/* Submit Button - Sesuai Figma */
.btn-submit-monev {
    width: 100px;
    height: 41px;
    background: #5495FF;
    box-shadow: 0px 2.17598px 4.35196px rgba(0, 0, 0, 0.25);
    border-radius: 4.68568px;
    border: none;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 15.9313px;
    line-height: 24px;
    letter-spacing: 0.03em;
    color: #FFFFFF;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-submit-monev:hover {
    background: #3D7FE5;
    transform: translateY(-1px);
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .main-content-monev {
        margin-left: 0;
    }
    
    .header-image-section {
        height: 250px;
    }
    
    .header-content {
        left: 20px;
        top: 100px;
        width: auto;
    }
    
    .header-content h1 {
        font-size: 20px;
        line-height: 30px;
        width: auto;
    }
    
    .header-breadcrumb {
        font-size: 14px;
        line-height: 21px;
    }
    
    .content-wrapper-monev {
        padding: 20px 15px;
    }
    
    .title-section-monev {
        width: 100%;
    }
    
    .divider-line-monev {
        width: 100%;
    }
    
    .form-row-identity {
        width: 100%;
    }
    
    .input-box-monev,
    .textarea-box-monev {
        width: 100%;
    }
    
    .laporan-section {
        width: 100%;
    }
    
    .answer-box,
    .answer-box textarea {
        width: 100%;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
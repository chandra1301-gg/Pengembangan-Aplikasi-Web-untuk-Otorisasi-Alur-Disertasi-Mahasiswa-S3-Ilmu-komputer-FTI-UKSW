<?php
/**
 * File: mahasiswa/registrasi.php
 * Halaman registrasi ujian mahasiswa sesuai desain CSS
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';


$page_title = "Registrasi Ujian - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_publik.php';
?>

<div class="main-content">
    <div class="container-fluid p-0">
        <!-- Header Image Section -->
        <div class="header-image-section">
            <div class="header-overlay"></div>
            <div class="header-text">
                <h1>Registrasi</h1>
                <p>Beranda > Registrasi</p>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-wrapper">
            <!-- Title Section -->
            <div class="title-section">
                <h2>Registrasi</h2>
                <p class="subtitle">Silakan lakukan pendaftaran pada bagian ini</p>
            </div>
            
            <div class="divider-line"></div>
            
            <!-- Registration Cards Grid -->
            <div class="registration-grid">
                <!-- Row 1 -->
                <div class="reg-row">
                    <!-- Monitoring Evaluasi -->
                    <div class="reg-card">
                        <h3 class="card-title">Monitoring Evaluasi</h3>
                        <p class="card-desc">Kegiatan pemantauan dan penilaian berkala terhadap kemajuan studi serta penelitian mahasiswa.</p>
                        <a href='../login.php' class="btn-register">Register</a>
                    </div>
                    
                    <!-- Ujian Proposal -->
                    <div class="reg-card">
                        <h3 class="card-title">Ujian Proposal</h3>
                        <p class="card-desc">Uji kelayakan dan kebaruan rencana penelitian yang diajukan mahasiswa.</p>
                        <a href='../login.php' class="btn-register">Register</a>
                    </div>
                </div>
                
                <!-- Row 2 -->
                <div class="reg-row">
                    <!-- Ujian Kualifikasi -->
                    <div class="reg-card">
                        <h3 class="card-title">Ujian Kualifikasi</h3>
                        <p class="card-desc">Menilai penguasaan teori, metodologi, dan kesiapan mahasiswa melanjutkan ke tahap penelitian.</p>
                        <a href='../login.php' class="btn-register">Register</a>
                    </div>
                    
                    <!-- Ujian Tertutup -->
                    <div class="reg-card">
                        <h3 class="card-title">Ujian Tertutup</h3>
                        <p class="card-desc">Ujian pembelaan disertasi di hadapan tim penguji sebagai tahap akhir sebelum ujian terbuka/promosi doktor.</p>
                        <a href='../login.php' class="btn-register">Register</a>
                    </div>
                </div>
                
                <!-- Row 3 -->
                <div class="reg-row">
                    <!-- Ujian Kelayakan -->
                    <div class="reg-card">
                        <h3 class="card-title">Ujian Kelayakan</h3>
                        <p class="card-desc">Menilai kelayakan disertasi mahasiswa untuk persiapan ujian tertutup.</p>
                        <a href='../login.php' class="btn-register">Register</a>
                    </div>
                    
                    <!-- Empty card for spacing -->
                    <div class="reg-card" style="visibility: hidden;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Import Poppins Font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* Reset main-content for this page */
.main-content {
    margin-left: 269px;
    padding: 0 !important;
    background: linear-gradient(123.95deg, #EEF2FF 0%, #F8F9FC 39.7%);
    min-height: 100vh;
}

/* Header Image Section */
.header-image-section {
    position: relative;
    width: 100%;
    height: 355px;
    background: url('../assets/foto_header.png') center/cover;
    overflow: hidden;
}

.header-overlay {
    position: absolute;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, rgba(109, 150, 101, 0.6) 0%, rgba(124, 105, 65, 0.6) 31.25%, rgba(0, 0, 0, 0.8) 98.08%);
}

.header-text {
    position: absolute;
    left: 59px;
    top: 147px;
    z-index: 10;
}

.header-text h1 {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 25px;
    line-height: 38px;
    letter-spacing: 0.01em;
    color: #FFFFFF;
    margin: 0;
}

.header-text p {
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    font-size: 15px;
    line-height: 22px;
    letter-spacing: 0.01em;
    color: #FFFFFF;
    margin: 0;
}

/* Content Wrapper */
.content-wrapper {
    padding: 31px 36px;
}

/* Title Section */
.title-section {
    margin-bottom: 7px;
}

.title-section h2 {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 20.5475px;
    line-height: 31px;
    letter-spacing: 0.03em;
    color: #000000;
    margin: 0 0 3px 0;
}

.title-section .subtitle {
    font-family: 'Poppins', sans-serif;
    font-weight: 300;
    font-size: 13.2672px;
    line-height: 20px;
    letter-spacing: 0.03em;
    color: #000000;
    margin: 0;
}

/* Divider Line */
.divider-line {
    width: 100%;
    max-width: 1098px;
    height: 1px;
    background: #000000;
    margin: 20px 0 34px 0;
}

/* Registration Grid */
.registration-grid {
    max-width: 1050px;
}

.reg-row {
    display: flex;
    gap: 27px;
    margin-bottom: 27px;
}

/* Registration Card */
.reg-card {
    box-sizing: border-box;
    width: 480px;
    height: 150px;
    background: #FFFFFF;
    border: 0.185687px solid #787878;
    box-shadow: 0px 0px 4.64217px 0.928433px rgba(0, 0, 0, 0.16);
    border-radius: 4.64217px;
    padding: 17px 21px;
    position: relative;
    transition: all 0.3s ease;
}

.reg-card:hover {
    transform: translateY(-3px);
    box-shadow: 0px 2px 8px 2px rgba(0, 0, 0, 0.2);
}

/* Card Title */
.card-title {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 15.7834px;
    line-height: 24px;
    letter-spacing: 0.01em;
    color: #000000;
    margin: 0 0 5px 0;
}

/* Card Description */
.card-desc {
    font-family: 'Poppins', sans-serif;
    font-weight: 300;
    font-size: 12.3177px;
    line-height: 18px;
    color: #000000;
    margin: 0 0 15px 0;
    max-height: 36px;
    overflow: hidden;
}

/* Register Button */
.btn-register {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 93.69px;
    height: 29.59px;
    background: #FFFFFF;
    border: 0.4px solid #C0C0C0;
    box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.18);
    border-radius: 5px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 13.5603px;
    line-height: 20px;
    letter-spacing: 0.03em;
    color: #000000;
    text-decoration: none;
    transition: all 0.3s ease;
    position: absolute;
    bottom: 17px;
    left: 21px;
}

.btn-register:hover {
    background: #f8f9fa;
    border-color: #000000;
    color: #000000;
    transform: translateY(-1px);
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.25);
}

/* Responsive */
@media (max-width: 1200px) {
    .reg-row {
        flex-direction: column;
    }
    
    .reg-card {
        width: 100%;
        max-width: 480px;
    }
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
    }
    
    .header-image-section {
        height: 250px;
    }
    
    .header-text {
        left: 20px;
        top: 100px;
    }
    
    .header-text h1 {
        font-size: 20px;
    }
    
    .header-text p {
        font-size: 13px;
    }
    
    .content-wrapper {
        padding: 20px 15px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
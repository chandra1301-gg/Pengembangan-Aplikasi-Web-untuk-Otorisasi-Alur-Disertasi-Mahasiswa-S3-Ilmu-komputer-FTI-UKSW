<?php
/**
 * File: mahasiswa/unduh_dokumen.php
 * Halaman untuk mengunduh dokumen persyaratan
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);

// Data dokumen
$dokumen_list = [
    [
        'id' => 1,
        'nama' => 'Template Proposal Usulan Penelitian DIK-UKSW',
        'ukuran' => '711 KB',
        'file_path' => 'assets/unduh_dokumen/template_proposal_usulan_penelitian_dik_uksw.pdf',
        'icon' => 'bi-file-earmark-word'
    ],
    [
        'id' => 2,
        'nama' => 'Form Monev S3 DIK UKSW',
        'ukuran' => '119 KB',
        'file_path' => 'assets/unduh_dokumen/form_monev_s3_dik_uksw.docx',
        'icon' => 'bi-file-earmark-word'
    ],
    [
        'id' => 3,
        'nama' => 'Form Ujian Proposal S3 DIK UKSW',
        'ukuran' => '524 KB',
        'file_path' => 'assets/unduh_dokumen/form_ujian_proposal_s3_dik_uksw.docx',
        'icon' => 'bi-file-earmark-pdf'
    ],
    [
        'id' => 4,
        'nama' => 'Form Ujian Kualifikasi S3 DIK UKSW',
        'ukuran' => '204 KB',
        'file_path' => 'assets/unduh_dokumen/form_ujian_kualifikasi_s3_dik_uksw.docx',
        'icon' => 'bi-file-earmark-pdf'
    ],
    [
        'id' => 5,
        'nama' => 'Form Ujian Kelayakan S3 DIK UKSW',
        'ukuran' => '263 KB',
        'file_path' => 'assets/unduh_dokumen/form_ujian_kelayakan_s3_dik_uksw.docx',
        'icon' => 'bi-file-earmark-word'
    ],
    [
        'id' => 6,
        'nama' => 'Form Ujian Tertutup S3 DIK UKSW',
        'ukuran' => '230 KB',
        'file_path' => 'assets/unduh_dokumen/form_ujian_tertutup_s3_dik_uksw.docx',
        'icon' => 'bi-file-earmark-word'
    ],
    [
        'id' => 7,
        'nama' => 'Pedoman Tata Tulis Buku Ujian Disertasi S3 DIK UKSW',
        'ukuran' => '1.2 MB',
        'file_path' => 'assets/unduh_dokumen/pedoman_tata_tulis_buku_ujian_disertasi_s3_dik_uksw.pdf',
        'icon' => 'bi-file-earmark-text'
    ],
    [
        'id' => 8,
        'nama' => 'Format Cover Ujian Proposal Disertasi',
        'ukuran' => '24 KB',
        'file_path' => 'assets/unduh_dokumen/format_cover_ujian_proposal.docx',
        'icon' => 'bi-file-earmark-word'
    ],
    [
        'id' => 9,
        'nama' => 'Format Cover Ujian Kelayakan',
        'ukuran' => '48 KB',
        'file_path' => 'assets/unduh_dokumen/format_cover_ujian_kelayakan.docx',
        'icon' => 'bi-file-earmark-word'
    ]
];

$page_title = "Unduh Dokumen - Sistem Disertasi S3 UKSW";
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
    margin-bottom: 31px;
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
    width: 219px;
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
    margin: 0 0 0 0;
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
    padding: 0 37px;
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

.page-subtitle {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 300;
    font-size: 13.8879px;
    line-height: 21px;
    letter-spacing: 0.03em;
    color: #000000;
    margin: 0 0 16px 0;
}

.title-divider {
    position: relative;
    max-width: 1098px;
    width: 100%;
    height: 0px;
    border: 1px solid #000000;
    margin: 0 0 30px 0;
}

/* Document Table Wrapper */
.document-table-wrapper {
    position: relative;
    max-width: 1095px;
    width: 100%;
    background: #FFFFFF;
    border: 0.308621px solid #737373;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 53px;
}

/* Table Styling */
.document-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.document-table thead th {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 13.8879px;
    line-height: 21px;
    letter-spacing: 0.03em;
    color: #000000;
    padding: 17px 31px;
    border-bottom: 0.308621px solid #737373;
    background: #FFFFFF;
    text-align: left;
}

.document-table thead th:first-child {
    width: 80px;
    padding-left: 31px;
}

.document-table thead th:nth-child(2) {
    width: auto;
}

.document-table thead th:nth-child(3) {
    width: 120px;
}

.document-table thead th:last-child {
    width: 150px;
    text-align: left;
    padding-left: 31px;
}

.document-table tbody td {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 400;
    font-size: 13.8879px;
    line-height: 21px;
    letter-spacing: 0.03em;
    color: #000000;
    padding: 11px 31px;
    border-bottom: 0.308621px solid #737373;
    vertical-align: middle;
}

.document-table tbody tr:last-child td {
    border-bottom: none;
}

.document-table tbody td:first-child {
    padding-left: 31px;
}

.document-table tbody td:last-child {
    padding-left: 31px;
}

/* Download Button - Sesuai Figma */
.btn-download {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 104px;
    height: 25px;
    background: #2B2F43;
    border: 0.5px solid #1D1D1D;
    border-radius: 3px;
    color: #FDFFFF;
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 500;
    font-size: 12.3448px;
    line-height: 19px;
    text-decoration: none;
    gap: 6px;
    transition: all 0.3s;
    cursor: pointer;
}

.btn-download:hover {
    background: #3a3f5c;
    color: #FDFFFF;
}

.btn-download i {
    font-size: 12px;
}

.btn-disabled {
    background: #E4E4E4;
    border: 0.5px solid #C8C8C8;
    color: #747474;
    cursor: not-allowed;
}

.btn-disabled:hover {
    background: #E4E4E4;
    color: #747474;
}

/* Remove Bootstrap overrides */
.table {
    margin-bottom: 0;
}

.table-hover tbody tr:hover {
    background-color: transparent;
}

/* Responsive */
@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
    }
    
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
    }
    
    .hero-breadcrumb {
        font-size: 14px;
        line-height: 21px;
    }
    
    .main-container {
        padding: 0 20px;
    }
    
    .document-table-wrapper {
        overflow-x: auto;
    }
    
    .document-table thead th,
    .document-table tbody td {
        padding: 10px 15px;
        font-size: 12px;
    }
    
    .btn-download {
        width: 90px;
        font-size: 11px;
    }
}
</style>

<div class="content-wrapper">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Unduh Dokumen</h1>
            <p class="hero-breadcrumb">Beranda > Unduh Dokumen</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <h2 class="page-title">Dokumen</h2>
        <p class="page-subtitle">Dokumen persyaratan tersedia di bawah ini untuk diunduh</p>
        <hr class="title-divider">
        
        <!-- Documents Table -->
        <div class="document-table-wrapper">
            <table class="document-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Documents</th>
                        <th>Size</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dokumen_list as $dokumen): ?>
                    <tr>
                        <td><?php echo $dokumen['id']; ?>.</td>
                        <td><?php echo htmlspecialchars($dokumen['nama']); ?></td>
                        <td><?php echo $dokumen['ukuran']; ?></td>
                        <td>
                            <?php if ($dokumen['file_path'] && file_exists('../' . $dokumen['file_path'])): ?>
                            <a href="../download.php?file=<?php echo urlencode($dokumen['file_path']); ?>" 
                               class="btn-download" 
                               download>
                                <i class="bi bi-download"></i>
                                Download
                            </a>
                            <?php else: ?>
                            <span class="btn-download btn-disabled">
                                <i class="bi bi-clock"></i>
                                Download
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
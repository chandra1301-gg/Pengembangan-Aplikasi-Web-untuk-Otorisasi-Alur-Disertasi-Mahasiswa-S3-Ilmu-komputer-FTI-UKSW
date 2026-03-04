<?php
/**
 * File: admin/monev.php
 * Monitoring Evaluasi Mahasiswa
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = "";
if (!empty($search)) {
    $where_clause = "WHERE m.nama_lengkap LIKE '%$search%' 
                     OR m.nim LIKE '%$search%' 
                     OR mv.periode LIKE '%$search%'
                     OR mv.deskripsi LIKE '%$search%'";
}

// Query untuk mendapatkan total monev
$query_total = "SELECT COUNT(*) as total FROM monev mv 
                JOIN mahasiswa m ON mv.id_mahasiswa = m.id_mahasiswa 
                $where_clause";
$result_total = mysqli_query($conn, $query_total);
$total_monev = mysqli_fetch_assoc($result_total)['total'];
$total_pages = ceil($total_monev / $limit);

// Query untuk mendapatkan data monev dengan pagination
$query_monev = "SELECT mv.*, m.nama_lengkap, m.nim, m.program_studi
               FROM monev mv 
               JOIN mahasiswa m ON mv.id_mahasiswa = m.id_mahasiswa 
               $where_clause 
               ORDER BY mv.tanggal_upload DESC 
               LIMIT $limit OFFSET $offset";
$result_monev = mysqli_query($conn, $query_monev);

// Statistik
$query_total_laporan = "SELECT COUNT(*) as total FROM monev";
$result_total_laporan = mysqli_query($conn, $query_total_laporan);
$total_laporan = mysqli_fetch_assoc($result_total_laporan)['total'];

$query_periode_aktif = "SELECT COUNT(DISTINCT periode) as total FROM monev";
$result_periode_aktif = mysqli_query($conn, $query_periode_aktif);
$total_periode = mysqli_fetch_assoc($result_periode_aktif)['total'];

$page_title = "Monitoring Evaluasi - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_admin.php';
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

/* Hero Section */
.hero-section {
    position: relative;
    width: 100%;
    height: 355px;
    margin-bottom: 36px;
    overflow: hidden;
}

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

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.stat-card {
    background: #FFFFFF;
    border: 0.3px solid #E5E7EB;
    box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
}

.stat-card:hover {
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.stat-icon.success {
    background: #D1FAE5;
    color: #059669;
}

.stat-icon.info {
    background: #DBEAFE;
    color: #1E40AF;
}

.stat-icon.warning {
    background: #FEF3C7;
    color: #D97706;
}

.stat-icon.primary {
    background: #E0E7FF;
    color: #5495FF;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 28px;
    line-height: 1;
    color: #111827;
    margin-bottom: 4px;
}

.stat-label {
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    font-size: 13px;
    color: #6B7280;
}

/* Search Section */
.search-section {
    background: #FFFFFF;
    border: 0.3px solid #E5E7EB;
    box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 20px 24px;
    margin-bottom: 20px;
}

.search-form {
    display: flex;
    gap: 12px;
}

.search-input-wrapper {
    flex: 1;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9CA3AF;
    font-size: 16px;
}

.search-input {
    width: 100%;
    height: 42px;
    background: #F9FAFB;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    padding: 0 14px 0 40px;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: #374151;
    transition: all 0.2s ease;
}

.search-input:focus {
    outline: none;
    border-color: #5495FF;
    box-shadow: 0 0 0 3px rgba(84, 149, 255, 0.1);
    background: #FFFFFF;
}

.btn-search {
    height: 42px;
    padding: 0 24px;
    background: #10B981;
    color: #FFFFFF;
    border: none;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-search:hover {
    background: #059669;
    box-shadow: 0px 2px 4px rgba(16, 185, 129, 0.3);
}

.btn-reset {
    height: 42px;
    padding: 0 24px;
    background: #FFFFFF;
    color: #6B7280;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.btn-reset:hover {
    background: #F9FAFB;
    color: #374151;
    text-decoration: none;
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

.table-header {
    background: #F9FAFB;
    padding: 18px 24px;
    border-bottom: 1px solid #E5E7EB;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-title {
    font-family: 'Poppins', sans-serif;
    font-style: normal;
    font-weight: 600;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.table-count {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: #6B7280;
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

.student-name {
    font-weight: 600;
    color: #111827;
    display: block;
    margin-bottom: 2px;
}

.student-info {
    font-size: 11px;
    color: #6B7280;
}

.badge-period {
    background: #DBEAFE;
    color: #1E40AF;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
}

.badge-has-file {
    background: #D1FAE5;
    color: #059669;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
}

.badge-no-file {
    background: #F3F4F6;
    color: #6B7280;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
}

.description-text {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    font-size: 12px;
    color: #4B5563;
    line-height: 1.5;
}

/* Action Buttons */
.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #D1D5DB;
    background: #FFFFFF;
    color: #6B7280;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    text-decoration: none;
}

.action-btn:hover {
    border-color: #10B981;
    color: #10B981;
    background: #F0FDF9;
    text-decoration: none;
}

.action-btn.primary:hover {
    border-color: #5495FF;
    color: #5495FF;
    background: #EFF6FF;
}

.action-btn.danger:hover {
    border-color: #EF4444;
    color: #EF4444;
    background: #FEF2F2;
}

/* Pagination */
.pagination-wrapper {
    padding: 20px 24px;
    border-top: 1px solid #E5E7EB;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.page-item {
    display: inline-block;
}

.page-link {
    padding: 8px 14px;
    background: #FFFFFF;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    color: #374151;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.page-link:hover {
    background: #F9FAFB;
    border-color: #5495FF;
    color: #5495FF;
    text-decoration: none;
}

.page-item.active .page-link {
    background: #5495FF;
    border-color: #5495FF;
    color: #FFFFFF;
}

.page-item.disabled .page-link {
    background: #F9FAFB;
    border-color: #E5E7EB;
    color: #9CA3AF;
    cursor: not-allowed;
    pointer-events: none;
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
    background: #10B981;
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
    box-shadow: 0px 2px 4px rgba(16, 185, 129, 0.3);
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

/* Modal Styles */
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0px 10px 40px rgba(0, 0, 0, 0.15);
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #E5E7EB;
    border-radius: 12px 12px 0 0;
}

.modal-header.bg-primary {
    background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%);
}

.modal-title {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 18px;
    letter-spacing: 0.02em;
    color: #FFFFFF;
    margin: 0;
}

.modal-body {
    padding: 24px;
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

/* Answer Sections */
.answer-section {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #E5E7EB;
}

.answer-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.answer-label {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 13px;
    color: #1F2937;
    margin-bottom: 10px;
    line-height: 1.6;
}

.answer-content {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: #4B5563;
    line-height: 1.8;
    padding: 14px;
    background: #FFFFFF;
    border-radius: 8px;
    border: 1px solid #E5E7EB;
}

.answer-content .text-muted {
    color: #9CA3AF;
    font-style: italic;
}

/* Attachment Item V2 */
.attachment-item-v2 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.attachment-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.attachment-number {
    width: 40px;
    height: 40px;
    background: #EFF6FF;
    color: #1E40AF;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
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

/* Responsive */
@media (max-width: 768px) {
    .hero-section {
        height: 250px;
    }
    
    .hero-content {
        left: 20px;
        top: 100px;
        max-width: calc(100% - 40px);
    }
    
    .hero-content h1 {
        font-size: 20px;
        line-height: 30px;
    }
    
    .main-container {
        padding: 20px 15px 40px 15px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
    
    .search-form {
        flex-direction: column;
    }
    
    .table-wrapper {
        overflow-x: auto;
    }
    
    .table-custom {
        min-width: 900px;
    }
}
</style>

<div class="content-wrapper">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Monitoring Evaluasi</h1>
            <p class="hero-breadcrumb">Dashboard<span class="separator">›</span>Monitoring Evaluasi</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <h2 class="page-title">Monitoring & Evaluasi Mahasiswa</h2>
        <hr class="title-divider">

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon success">
                    📄
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $total_laporan; ?></div>
                    <div class="stat-label">Total Laporan</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    👥
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <?php 
                        $query_mahasiswa_monev = "SELECT COUNT(DISTINCT id_mahasiswa) as total FROM monev";
                        $result_mahasiswa_monev = mysqli_query($conn, $query_mahasiswa_monev);
                        echo mysqli_fetch_assoc($result_mahasiswa_monev)['total'];
                        ?>
                    </div>
                    <div class="stat-label">Mahasiswa Aktif</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    📅
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $total_periode; ?></div>
                    <div class="stat-label">Periode Aktif</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon primary">
                    ☁️
                </div>
                <div class="stat-content">
                    <div class="stat-number">
                        <?php 
                        $query_with_file = "SELECT COUNT(*) as total FROM monev WHERE file_laporan IS NOT NULL";
                        $result_with_file = mysqli_query($conn, $query_with_file);
                        echo mysqli_fetch_assoc($result_with_file)['total'];
                        ?>
                    </div>
                    <div class="stat-label">Dengan File</div>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <div class="search-input-wrapper">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" name="search" 
                           placeholder="Cari berdasarkan nama mahasiswa, NIM, periode, atau deskripsi..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn-search">Cari</button>
                <a href="monev.php" class="btn-reset">Reset</a>
            </form>
        </div>

        <!-- Main Table -->
        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title">📋 Data Monitoring Evaluasi</h5>
                <span class="table-count">Total: <?php echo $total_monev; ?> laporan</span>
            </div>
            
            <?php if (mysqli_num_rows($result_monev) > 0): ?>
                <div class="table-wrapper">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Mahasiswa</th>
                                <th>Periode</th>
                                <th>Deskripsi</th>
                                <th>File</th>
                                <th>Tanggal Upload</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1;
                            while ($monev = mysqli_fetch_assoc($result_monev)): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <span class="student-name"><?php echo htmlspecialchars($monev['nama_lengkap']); ?></span>
                                    <span class="student-info">
                                        <?php echo htmlspecialchars($monev['nim']); ?> | 
                                        <?php echo htmlspecialchars($monev['program_studi']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-period">
                                        <?php echo htmlspecialchars($monev['periode']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    // Parse JSON deskripsi
                                    $deskripsi_data = json_decode($monev['deskripsi'], true);
                                    if ($deskripsi_data && is_array($deskripsi_data)) {
                                        // Tampilkan preview singkat
                                        $preview = '';
                                        if (!empty($deskripsi_data['jawaban1'])) {
                                            $preview = substr(strip_tags($deskripsi_data['jawaban1']), 0, 50) . '...';
                                        }
                                        echo '<div class="description-text" style="cursor: pointer;" onclick="showDescription' . $monev['id_monev'] . '()">';
                                        echo htmlspecialchars($preview);
                                        echo '<br><small style="color: #5495FF; font-weight: 500;">Klik untuk lihat detail →</small>';
                                        echo '</div>';
                                    } else if (!empty($monev['deskripsi'])) {
                                        // Fallback untuk format lama
                                        $desc = htmlspecialchars($monev['deskripsi']);
                                        if (strlen($desc) > 100) {
                                            echo '<div class="description-text">' . substr($desc, 0, 100) . '...</div>';
                                        } else {
                                            echo '<div class="description-text">' . $desc . '</div>';
                                        }
                                    } else {
                                        echo '<span style="color: #9CA3AF;">-</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($monev['file_laporan'])): ?>
                                        <span class="badge-has-file">✓ Ada</span>
                                    <?php else: ?>
                                        <span class="badge-no-file">✗ Tidak Ada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small style="color: #6B7280;">
                                        <?php echo date('d M Y H:i', strtotime($monev['tanggal_upload'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" onclick="showDescription<?= $monev['id_monev']; ?>()" 
                                           class="action-btn" title="Lihat Detail">
                                            👁️
                                        </button>
                                        <a href="download_monev_pdf.php?id=<?php echo $monev['id_monev']; ?>" 
                                           class="action-btn primary" title="Download PDF" target="_blank">
                                            📄
                                        </a>
                                        <?php if (!empty($monev['file_laporan'])): ?>
                                        <a href="../uploads/monev/<?php echo $monev['file_laporan']; ?>" 
                                           class="action-btn primary" title="Download File Lampiran" target="_blank">
                                            ⬇️
                                        </a>
                                        <?php endif; ?>
                                        <button type="button" class="action-btn danger" 
                                                title="Hapus" onclick="confirmDelete(<?php echo $monev['id_monev']; ?>)">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Modal untuk Detail Deskripsi -->
                <?php 
                mysqli_data_seek($result_monev, 0); // Reset pointer
                while ($monev = mysqli_fetch_assoc($result_monev)): 
                    $deskripsi_data = json_decode($monev['deskripsi'], true);
                ?>
                <div class="modal fade" id="modalDeskripsi<?= $monev['id_monev']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <h5 class="modal-title">📋 Detail Laporan Monitoring Evaluasi</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Info Mahasiswa -->
                                <div class="info-card">
                                    <h6 class="modal-section-title">👤 Informasi Mahasiswa</h6>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <span class="info-label">Nama Lengkap</span>
                                            <span class="info-value"><?= htmlspecialchars($monev['nama_lengkap']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">NIM</span>
                                            <span class="info-value"><?= htmlspecialchars($monev['nim']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Program Studi</span>
                                            <span class="info-value"><?= htmlspecialchars($monev['program_studi']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Periode</span>
                                            <span class="info-value"><span class="badge-period"><?= htmlspecialchars($monev['periode']); ?></span></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Tanggal Upload</span>
                                            <span class="info-value"><?= date('d F Y H:i', strtotime($monev['tanggal_upload'])); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Laporan Detail -->
                                <div class="info-card">
                                    <h6 class="modal-section-title">📝 Laporan Monitoring Evaluasi</h6>
                                    
                                    <?php if ($deskripsi_data && is_array($deskripsi_data)): ?>
                                        <!-- Jawaban I -->
                                        <div class="answer-section">
                                            <div class="answer-label">I. Jabarkan kegiatan yang sudah dapat direalisasikan (4 bulan yang lalu)</div>
                                            <div class="answer-content">
                                                <?= !empty($deskripsi_data['jawaban1']) ? nl2br(htmlspecialchars($deskripsi_data['jawaban1'])) : '<span class="text-muted">Tidak ada jawaban</span>'; ?>
                                            </div>
                                        </div>

                                        <!-- Jawaban II -->
                                        <div class="answer-section">
                                            <div class="answer-label">II. Kegiatan yang seharusnya selesai, tetapi tidak/belum dapat direalisasikan. Sebutkan hambatannya dan rencana penyelesaiannya</div>
                                            <div class="answer-content">
                                                <?= !empty($deskripsi_data['jawaban2']) ? nl2br(htmlspecialchars($deskripsi_data['jawaban2'])) : '<span class="text-muted">Tidak ada jawaban</span>'; ?>
                                            </div>
                                        </div>

                                        <!-- Jawaban III -->
                                        <div class="answer-section">
                                            <div class="answer-label">III. Kegiatan penelitian yang direncanakan dalam 4 bulan yang akan datang</div>
                                            <div class="answer-content">
                                                <?= !empty($deskripsi_data['jawaban3']) ? nl2br(htmlspecialchars($deskripsi_data['jawaban3'])) : '<span class="text-muted">Tidak ada jawaban</span>'; ?>
                                            </div>
                                        </div>

                                        <!-- Jawaban IV -->
                                        <div class="answer-section">
                                            <div class="answer-label">IV. Garis besar kegiatan penelitian selanjutnya</div>
                                            <div class="answer-content">
                                                <?= !empty($deskripsi_data['jawaban4']) ? nl2br(htmlspecialchars($deskripsi_data['jawaban4'])) : '<span class="text-muted">Tidak ada jawaban</span>'; ?>
                                            </div>
                                        </div>

                                        <!-- Jawaban V -->
                                        <div class="answer-section">
                                            <div class="answer-label">V. Saran/masukan/komentar untuk perbaikan (dari reviewer)</div>
                                            <div class="answer-content">
                                                <?= !empty($deskripsi_data['jawaban5']) ? nl2br(htmlspecialchars($deskripsi_data['jawaban5'])) : '<span class="text-muted">Tidak ada jawaban</span>'; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="answer-content">
                                            <?= !empty($monev['deskripsi']) ? nl2br(htmlspecialchars($monev['deskripsi'])) : '<span class="text-muted">Tidak ada laporan</span>'; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- File Laporan -->
                                <?php if (!empty($monev['file_laporan'])): ?>
                                <div class="info-card">
                                    <h6 class="modal-section-title">📎 File Laporan</h6>
                                    <div class="attachment-item-v2" style="cursor: default;">
                                        <div class="attachment-info">
                                            <div class="attachment-number">📄</div>
                                            <div class="attachment-details">
                                                <div class="attachment-name-v2"><?= htmlspecialchars($monev['file_laporan']); ?></div>
                                                <div class="attachment-meta">File Laporan Monitoring Evaluasi</div>
                                            </div>
                                        </div>
                                        <div class="attachment-actions">
                                            <a href="../uploads/monev/<?= $monev['file_laporan']; ?>" download class="btn-download-v3">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function showDescription<?= $monev['id_monev']; ?>() {
                    const modal = new bootstrap.Modal(document.getElementById('modalDeskripsi<?= $monev['id_monev']; ?>'));
                    modal.show();
                }
                </script>
                <?php endwhile; ?>

                <?php mysqli_data_seek($result_monev, 0); // Reset pointer again for pagination ?>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <ul class="pagination">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                ‹
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                ›
                            </a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h5 class="empty-state-title">Tidak ada data monitoring evaluasi</h5>
                    <p class="empty-state-text">
                        <?php if (!empty($search)): ?>
                            Tidak ditemukan laporan dengan kata kunci "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            Belum ada laporan monitoring evaluasi yang diupload
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search)): ?>
                        <a href="monev.php" class="btn-primary-custom">
                            Tampilkan Semua
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data monitoring evaluasi ini?')) {
        window.location.href = 'delete_monev.php?id=' + id;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
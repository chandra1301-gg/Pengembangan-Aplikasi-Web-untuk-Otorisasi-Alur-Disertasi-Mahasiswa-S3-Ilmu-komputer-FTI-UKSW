<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);

// Get statistik registrasi dengan pengecekan null
$total_menunggu = 0;
$total_diterima = 0;
$total_ditolak = 0;

$query_stats = "SELECT status, COUNT(*) as jumlah FROM registrasi WHERE id_mahasiswa = " . $mahasiswa['id_mahasiswa'] . " GROUP BY status";
$result_stats = mysqli_query($conn, $query_stats);
while ($row = mysqli_fetch_assoc($result_stats)) {
    if ($row['status'] == 'Menunggu') $total_menunggu = $row['jumlah'];
    if ($row['status'] == 'Diterima') $total_diterima = $row['jumlah'];
    if ($row['status'] == 'Ditolak') $total_ditolak = $row['jumlah'];
}

$total_registrasi = $total_menunggu + $total_diterima + $total_ditolak;

// Get pengumuman terbaru
$query_pengumuman = "SELECT * FROM pengumuman ORDER BY tanggal_post DESC LIMIT 3";
$result_pengumuman = mysqli_query($conn, $query_pengumuman);

// Get registrasi terbaru
$query_reg_recent = "SELECT r.* FROM registrasi r WHERE r.id_mahasiswa = " . $mahasiswa['id_mahasiswa'] . " ORDER BY r.tanggal_pengajuan DESC LIMIT 5";
$result_reg_recent = mysqli_query($conn, $query_reg_recent);

$page_title = "Dashboard Mahasiswa - Sistem Disertasi S3 UKSW";
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

/* Content Wrapper */
.content-wrapper {
    margin-left: 0;
    background: transparent;
    min-height: 100vh;
}

@media (min-width: 769px) {
    .content-wrapper {
        margin-left: 269px;
    }
}

.main-content {
    background: transparent;
}

/* Hero Section */
.hero-section {
    position: relative;
    width: 100%;
    height: 355px;
    margin-bottom: 31px;
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

.hero-subtitle {
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

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #FFFFFF;
    border: 0.308621px solid #E4E4E4;
    border-radius: 10px;
    padding: 24px 20px;
    text-align: center;
    box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.stat-card:hover {
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.stat-icon i {
    font-size: 28px;
}

.stat-icon.warning {
    background: rgba(255, 193, 7, 0.1);
}

.stat-icon.warning i {
    color: #FFC107;
}

.stat-icon.success {
    background: rgba(25, 135, 84, 0.1);
}

.stat-icon.success i {
    color: #198754;
}

.stat-icon.danger {
    background: rgba(220, 53, 69, 0.1);
}

.stat-icon.danger i {
    color: #DC3545;
}

.stat-icon.primary {
    background: rgba(13, 110, 253, 0.1);
}

.stat-icon.primary i {
    color: #0D6EFD;
}

.stat-card h3 {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 32px;
    line-height: 48px;
    color: #000000;
    margin: 0 0 4px 0;
}

.stat-card p {
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    font-size: 14px;
    line-height: 21px;
    color: #6C757D;
    margin: 0;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 40px;
}

/* Card Styles */
.content-card {
    background: #FFFFFF;
    border: 0.308621px solid #E4E4E4;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.05);
}

.content-card-header {
    background: #FFFFFF;
    padding: 20px 24px;
    border-bottom: 1px solid #E4E4E4;
}

.content-card-header h5 {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 18px;
    line-height: 27px;
    color: #000000;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.content-card-body {
    padding: 24px;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
}

.custom-table thead th {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 13px;
    line-height: 20px;
    color: #000000;
    padding: 12px 16px;
    text-align: left;
    background: #F8F9FA;
    border-bottom: 1px solid #E4E4E4;
}

.custom-table tbody td {
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    font-size: 13px;
    line-height: 20px;
    color: #000000;
    padding: 14px 16px;
    border-bottom: 1px solid #F0F0F0;
}

.custom-table tbody tr:last-child td {
    border-bottom: none;
}

.custom-table tbody tr:hover {
    background: #F8F9FA;
}

/* Badge Styles */
.badge-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    font-size: 12px;
    line-height: 18px;
}

.badge-menunggu {
    background: #FFF3CD;
    color: #664D03;
}

.badge-diterima {
    background: #D1E7DD;
    color: #0F5132;
}

.badge-ditolak {
    background: #F8D7DA;
    color: #842029;
}

/* Announcement Item */
.announcement-item {
    padding-bottom: 16px;
    margin-bottom: 16px;
    border-bottom: 1px solid #E4E4E4;
}

.announcement-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.announcement-item h6 {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 14px;
    line-height: 21px;
    color: #000000;
    margin: 0 0 6px 0;
}

.announcement-item p {
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    font-size: 12px;
    line-height: 18px;
    color: #6C757D;
    margin: 0 0 6px 0;
}

.announcement-date {
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    font-size: 11px;
    line-height: 16px;
    color: #0D6EFD;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Quick Links */
.quick-links {
    display: grid;
    gap: 12px;
    margin-top: 20px;
}

.quick-link-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    background: #5495FF;
    border: none;
    border-radius: 6px;
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    font-size: 14px;
    line-height: 21px;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.quick-link-btn:hover {
    background: #3D7FE8;
    color: #FFFFFF;
    transform: translateY(-1px);
}

.quick-link-btn.outline {
    background: transparent;
    border: 1px solid #5495FF;
    color: #5495FF;
}

.quick-link-btn.outline:hover {
    background: #5495FF;
    color: #FFFFFF;
}

.quick-link-btn.secondary {
    background: transparent;
    border: 1px solid #6C757D;
    color: #6C757D;
}

.quick-link-btn.secondary:hover {
    background: #6C757D;
    color: #FFFFFF;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state i {
    font-size: 48px;
    color: #CED4DA;
    margin-bottom: 16px;
}

.empty-state p {
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    font-size: 14px;
    color: #6C757D;
    margin: 0 0 16px 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
}

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
    }
    
    .hero-content h1 {
        font-size: 20px;
        line-height: 30px;
    }
    
    .hero-subtitle {
        font-size: 14px;
    }
    
    .main-container {
        padding: 0 20px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .stat-card {
        padding: 20px 16px;
    }
}
</style>

<div class="content-wrapper">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Selamat Datang, <?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?>! 👋</h1>
            <p class="hero-subtitle">NIM: <?php echo htmlspecialchars($mahasiswa['nim']); ?> | Program Studi: <?php echo htmlspecialchars($mahasiswa['program_studi']); ?></p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h3><?php echo $total_menunggu; ?></h3>
                <p>Menunggu Verifikasi</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h3><?php echo $total_diterima; ?></h3>
                <p>Diterima</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="bi bi-x-circle"></i>
                </div>
                <h3><?php echo $total_ditolak; ?></h3>
                <p>Ditolak</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <h3><?php echo $total_registrasi; ?></h3>
                <p>Total Registrasi</p>
            </div>
        </div>
        
        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Registrasi Terbaru -->
            <div class="content-card">
                <div class="content-card-header">
                    <h5><i class="bi bi-list-check"></i>Riwayat Registrasi Terbaru</h5>
                </div>
                <div class="content-card-body">
                    <?php if (mysqli_num_rows($result_reg_recent) > 0): ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Jenis Ujian</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_reg_recent)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo ucfirst($row['jenis_ujian']); ?></strong><br>
                                        <small style="color: #6C757D;"><?php echo substr($row['judul_disertasi'], 0, 50); ?>...</small>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                    <td>
                                        <?php 
                                        $badge_class = '';
                                        if ($row['status'] == 'Menunggu') $badge_class = 'badge-menunggu';
                                        elseif ($row['status'] == 'Diterima') $badge_class = 'badge-diterima';
                                        else $badge_class = 'badge-ditolak';
                                        ?>
                                        <span class="badge-status <?php echo $badge_class; ?>"><?php echo $row['status']; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada registrasi</p>
                        <a href="registrasi.php" class="quick-link-btn" style="display: inline-flex; width: auto;">
                            <i class="bi bi-plus-circle"></i>Daftar Sekarang
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div>
                <!-- Pengumuman -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h5><i class="bi bi-megaphone"></i>Pengumuman Terbaru</h5>
                    </div>
                    <div class="content-card-body">
                        <?php if (mysqli_num_rows($result_pengumuman) > 0): ?>
                        <?php while ($pengumuman = mysqli_fetch_assoc($result_pengumuman)): ?>
                        <div class="announcement-item">
                            <h6><?php echo htmlspecialchars($pengumuman['judul']); ?></h6>
                            <p><?php echo substr($pengumuman['isi'], 0, 100); ?>...</p>
                            <div class="announcement-date">
                                <i class="bi bi-calendar3"></i>
                                <?php echo date('d M Y', strtotime($pengumuman['tanggal_post'])); ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <p style="text-align: center; color: #6C757D; padding: 20px 0;">Tidak ada pengumuman</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="content-card" style="margin-top: 20px;">
                    <div class="content-card-header">
                        <h5><i class="bi bi-link-45deg"></i>Akses Cepat</h5>
                    </div>
                    <div class="content-card-body">
                        <div class="quick-links">
                            <a href="registrasi.php" class="quick-link-btn">
                                <i class="bi bi-clipboard-check"></i>Daftar Ujian
                            </a>
                            <a href="monev.php" class="quick-link-btn outline">
                                <i class="bi bi-file-earmark-arrow-up"></i>Upload Monev
                            </a>
                            <a href="profil.php" class="quick-link-btn secondary">
                                <i class="bi bi-person-circle"></i>Edit Profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
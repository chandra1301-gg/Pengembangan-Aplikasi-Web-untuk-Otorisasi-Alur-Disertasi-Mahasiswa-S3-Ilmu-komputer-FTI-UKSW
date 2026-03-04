<?php
/**
 * File: announcement_all.php
 * Halaman lihat semua pengumuman - desain modern (FIXED)
 */

session_start();
include '../includes/header.php';
include '../includes/sidebar_publik.php';

// Koneksi database
require_once '../includes/db_connect.php';

// Konfigurasi pagination
$limit = 6; // Jumlah item per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$sql_count = "SELECT COUNT(*) as total FROM announcements WHERE status = 'active'";
$result_count = $conn->query($sql_count);
$total_data = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data pengumuman dengan pagination
$sql_announcements = "SELECT * FROM announcements WHERE status = 'active' ORDER BY publish_date DESC LIMIT $limit OFFSET $offset";
$result_announcements = $conn->query($sql_announcements);
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Header Hero Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="announcement-hero bg-gradient-primary text-white rounded-3 p-5 position-relative overflow-hidden">
                    <div class="hero-overlay"></div>
                    <div class="position-relative z-1">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-light mb-3">
                                <li class="breadcrumb-item"><a href="beranda.php" class="text-white-50 text-decoration-none">HOME</a></li>
                                <li class="breadcrumb-item active text-white" aria-current="page">PENGUMUMAN</li>
                            </ol>
                        </nav>
                        
                        <h1 class="display-5 fw-bold mb-3">Pengumuman</h1>
                        <p class="lead mb-0 opacity-75">Informasi terbaru dan penting dari Program Studi Doktor Ilmu Komputer</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-0 text-dark"><?php echo $total_data; ?> Pengumuman Ditemukan</h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-filter me-2"></i>Urutkan
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?sort=newest">Terbaru</a></li>
                                        <li><a class="dropdown-item" href="?sort=oldest">Terlama</a></li>
                                        <li><a class="dropdown-item" href="?sort=popular">Populer</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengumuman Grid -->
        <div class="row">
            <?php if ($result_announcements->num_rows > 0): ?>
                <?php while ($announcement = $result_announcements->fetch_assoc()): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card announcement-card border-0 shadow-hover h-100">
                            <!-- Badge Status -->
                            <div class="card-badge">
                                <?php
                                // Cek apakah pengumuman penting berdasarkan title atau content
                                $is_important = false;
                                $title_lower = strtolower($announcement['title']);
                                $desc_lower = strtolower($announcement['description']);
                                $important_keywords = ['penting', 'urgent', 'segera', 'deadline', 'batas', 'wajib'];
                                
                                foreach ($important_keywords as $keyword) {
                                    if (strpos($title_lower, $keyword) !== false || strpos($desc_lower, $keyword) !== false) {
                                        $is_important = true;
                                        break;
                                    }
                                }
                                ?>
                                <span class="badge bg-<?php echo $is_important ? 'danger' : 'primary'; ?>">
                                    <i class="bi bi-<?php echo $is_important ? 'exclamation-triangle' : 'megaphone'; ?> me-1"></i>
                                    <?php echo $is_important ? 'Penting' : 'Pengumuman'; ?>
                                </span>
                            </div>

                            <!-- Gambar Pengumuman -->
                            <?php if (!empty($announcement['image'])): ?>
                                <div class="card-img-container">
                                    <img src="../assets/uploads/announcements/<?php echo htmlspecialchars($announcement['image']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($announcement['title']); ?>">
                                    <div class="card-img-overlay-top">
                                        <span class="badge bg-dark bg-opacity-50 text-white">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo date('d M Y', strtotime($announcement['publish_date'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card-img-container bg-light">
                                    <div class="card-img-placeholder d-flex align-items-center justify-content-center h-100">
                                        <div class="text-center text-muted p-4">
                                            <i class="bi bi-megaphone display-4"></i>
                                            <p class="mt-2 mb-0"><?php echo htmlspecialchars($announcement['title']); ?></p>
                                        </div>
                                    </div>
                                    <div class="card-img-overlay-top">
                                        <span class="badge bg-dark bg-opacity-50 text-white">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo date('d M Y', strtotime($announcement['publish_date'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <!-- Kategori -->
                                <div class="mb-3">
                                    <span class="badge bg-light text-primary border">
                                        <i class="bi bi-tag me-1"></i>
                                        <?php echo htmlspecialchars($announcement['category'] ?? 'Umum'); ?>
                                    </span>
                                </div>

                                <!-- Judul -->
                                <h5 class="card-title fw-bold text-dark line-clamp-2">
                                    <?php echo htmlspecialchars($announcement['title']); ?>
                                </h5>

                                <!-- Deskripsi -->
                                <p class="card-text text-muted line-clamp-3">
                                    <?php echo htmlspecialchars($announcement['description']); ?>
                                </p>

                                <!-- Info Penting -->
                                <div class="announcement-info mb-3">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <small class="text-primary">
                                                <i class="bi bi-calendar-check me-1"></i>
                                                <strong>Tanggal:</strong> 
                                                <?php echo date('d F Y', strtotime($announcement['publish_date'])); ?>
                                            </small>
                                        </div>
                                        <?php if (!empty($announcement['registration_deadline'])): ?>
                                            <div class="col-12">
                                                <small class="text-warning">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <strong>Pendaftaran:</strong> 
                                                    <?php echo date('d F Y', strtotime($announcement['registration_deadline'])); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-12">
                                            <small class="text-info">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <strong>Lokasi:</strong> 
                                                <?php 
                                                // Deteksi lokasi otomatis
                                                $location_type = 'Online';
                                                $content_lower = strtolower($announcement['description'] . ' ' . $announcement['title']);
                                                if (strpos($content_lower, 'offline') !== false || strpos($content_lower, 'kampus') !== false || strpos($content_lower, 'gedung') !== false) {
                                                    $location_type = 'Offline';
                                                }
                                                echo $location_type; 
                                                ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-light text-primary rounded-circle">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                            </div>
                                            <div class="ms-2">
                                                <small class="text-muted">By</small>
                                                <small class="fw-semibold"><?php echo htmlspecialchars($announcement['author']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <a href="announcement_detail.php?id=<?php echo $announcement['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            Baca Selengkapnya <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-0 text-center py-5">
                        <div class="card-body">
                            <div class="empty-state">
                                <i class="bi bi-megaphone display-1 text-muted"></i>
                                <h4 class="mt-3 text-muted">Belum Ada Pengumuman</h4>
                                <p class="text-muted">Tidak ada pengumuman yang tersedia saat ini.</p>
                                <a href="dashboard.php" class="btn btn-primary mt-3">
                                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Page -->
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 bg-light">
                    <div class="card-body py-4">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="stat-item">
                                    <h3 class="fw-bold text-primary mb-1"><?php echo $total_data; ?></h3>
                                    <p class="text-muted mb-0">Total Pengumuman</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="stat-item">
                                    <h3 class="fw-bold text-success mb-1">
                                        <?php 
                                        // Hitung pengumuman bulan ini
                                        $sql_month = "SELECT COUNT(*) as month_count FROM announcements WHERE status = 'active' AND MONTH(publish_date) = MONTH(CURRENT_DATE()) AND YEAR(publish_date) = YEAR(CURRENT_DATE())";
                                        $result_month = $conn->query($sql_month);
                                        echo $result_month->fetch_assoc()['month_count'];
                                        ?>
                                    </h3>
                                    <p class="text-muted mb-0">Bulan Ini</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <div class="stat-item">
                                    <h3 class="fw-bold text-warning mb-1">
                                        <?php 
                                        // Hitung pengumuman minggu ini
                                        $sql_week = "SELECT COUNT(*) as week_count FROM announcements WHERE status = 'active' AND publish_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)";
                                        $result_week = $conn->query($sql_week);
                                        echo $result_week->fetch_assoc()['week_count'];
                                        ?>
                                    </h3>
                                    <p class="text-muted mb-0">Minggu Ini</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-item">
                                    <h3 class="fw-bold text-info mb-1">
                                        <?php 
                                        // Hitung pengumuman mendatang
                                        $sql_upcoming = "SELECT COUNT(*) as upcoming FROM announcements WHERE status = 'active' AND publish_date > CURRENT_DATE()";
                                        $result_upcoming = $conn->query($sql_upcoming);
                                        echo $result_upcoming->fetch_assoc()['upcoming'];
                                        ?>
                                    </h3>
                                    <p class="text-muted mb-0">Mendatang</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .announcement-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        position: relative;
        overflow: hidden;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="0,0 1000,50 1000,100 0,100"/></svg>') bottom center/cover no-repeat;
    }

    .breadcrumb-light .breadcrumb-item.active {
        color: rgba(255, 255, 255, 0.9) !important;
    }

    .announcement-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
    }

    .announcement-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
    }

    .card-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 2;
    }

    .card-img-container {
        position: relative;
        overflow: hidden;
        height: 200px;
    }

    .card-img-container img {
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .card-img-placeholder {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .announcement-card:hover .card-img-container img {
        transform: scale(1.05);
    }

    .card-img-overlay-top {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 2;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .avatar-sm {
        width: 32px;
        height: 32px;
    }

    .avatar-title {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .shadow-hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .empty-state {
        opacity: 0.7;
    }

    .stat-item h3 {
        font-size: 2rem;
    }

    .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .page-link {
        color: #0d6efd;
        border-radius: 8px;
        margin: 0 3px;
        border: 1px solid #dee2e6;
    }

    @media (max-width: 768px) {
        .announcement-hero {
            padding: 2rem 1rem !important;
        }
        
        .announcement-hero h1 {
            font-size: 2rem;
        }
        
        .card-badge {
            top: 10px;
            left: 10px;
        }
        
        .card-img-overlay-top {
            top: 10px;
            right: 10px;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>
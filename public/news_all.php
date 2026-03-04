<?php
/**
 * File: news.php
 * Halaman lihat semua berita - desain modern
 */

session_start();
include '../includes/header.php';
include '../includes/sidebar_publik.php';

// Koneksi database
require_once '../includes/db_connect.php';

// Konfigurasi pagination
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$sql_count = "SELECT COUNT(*) as total FROM news WHERE status = 'active'";
$result_count = $conn->query($sql_count);
$total_data = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data berita dengan pagination
$sql_news = "SELECT * FROM news WHERE status = 'active' ORDER BY news_date DESC LIMIT $limit OFFSET $offset";
$result_news = $conn->query($sql_news);
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Header Hero Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="news-hero bg-gradient-success text-white rounded-3 p-5 position-relative overflow-hidden">
                    <div class="hero-overlay"></div>
                    <div class="position-relative z-1">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-light mb-3">
                                <li class="breadcrumb-item"><a href="beranda.php" class="text-white-50 text-decoration-none">HOME</a></li>
                                <li class="breadcrumb-item active text-white" aria-current="page">BERITA</li>
                            </ol>
                        </nav>
                        
                        <h1 class="display-5 fw-bold mb-3">Berita Terbaru</h1>
                        <p class="lead mb-0 opacity-75">Update dan informasi terkini dari Program Studi Doktor Ilmu Komputer</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured News (Jika ada) -->
        <?php if ($result_news->num_rows > 0): ?>
            <?php 
            $first_news = $result_news->fetch_assoc();
            $remaining_news = $result_news->num_rows;
            ?>
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card featured-news border-0 shadow-lg overflow-hidden">
                        <div class="row g-0">
                            <div class="col-lg-6">
                                <?php if (!empty($first_news['image'])): ?>
                                    <img src="../assets/uploads/news/<?php echo htmlspecialchars($first_news['image']); ?>" 
                                         class="img-fluid h-100" alt="<?php echo htmlspecialchars($first_news['title']); ?>" 
                                         style="object-fit: cover; min-height: 400px;">
                                <?php else: ?>
                                    <div class="bg-primary h-100 d-flex align-items-center justify-content-center text-white">
                                        <div class="text-center p-4">
                                            <i class="bi bi-newspaper display-1"></i>
                                            <h4 class="mt-3">Berita Utama</h4>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg-6">
                                <div class="card-body p-4 p-lg-5 d-flex flex-column h-100">
                                    <div class="mb-3">
                                        <span class="badge bg-danger mb-2">
                                            <i class="bi bi-star-fill me-1"></i>Featured
                                        </span>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo date('d M Y', strtotime($first_news['news_date'])); ?>
                                        </span>
                                    </div>
                                    
                                    <h2 class="card-title fw-bold text-dark mb-3">
                                        <?php echo htmlspecialchars($first_news['title']); ?>
                                    </h2>
                                    
                                    <p class="card-text text-muted flex-grow-1">
                                        <?php echo substr(strip_tags($first_news['content']), 0, 200); ?>...
                                    </p>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar-sm me-3">
                                                <div class="avatar-title bg-light text-success rounded-circle">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <small class="text-muted">Ditulis oleh</small>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($first_news['author']); ?></div>
                                            </div>
                                        </div>
                                        <a href="news_detail.php?id=<?php echo $first_news['id']; ?>" 
                                           class="btn btn-success btn-lg">
                                            Baca Selengkapnya <i class="bi bi-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-0 text-dark"><?php echo $total_data; ?> Berita Ditemukan</h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-success active">Semua</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Berita Grid -->
        <div class="row">
            <?php if ($remaining_news > 0): ?>
                <?php while ($news = $result_news->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card news-card border-0 shadow-hover h-100">
                            <!-- Gambar Berita -->
                            <div class="card-img-container position-relative">
                                <?php if (!empty($news['image'])): ?>
                                    <img src="../assets/uploads/news/<?php echo htmlspecialchars($news['image']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($news['title']); ?>">
                                <?php else: ?>
                                    <div class="bg-secondary h-100 d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                        <i class="bi bi-newspaper display-4"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Tanggal Overlay -->
                                <div class="card-date-overlay">
                                    <div class="date-badge bg-success text-white text-center">
                                        <div class="day fw-bold"><?php echo date('d', strtotime($news['news_date'])); ?></div>
                                        <div class="month"><?php echo date('M', strtotime($news['news_date'])); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- Kategori -->
                                <div class="mb-3">
                                    <span class="badge bg-light text-success border">
                                        <i class="bi bi-bookmark me-1"></i>
                                        <?php echo htmlspecialchars($news['category'] ?? 'Berita'); ?>
                                    </span>
                                </div>

                                <!-- Judul -->
                                <h5 class="card-title fw-bold text-dark line-clamp-2">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </h5>

                                <!-- Konten -->
                                <p class="card-text text-muted line-clamp-3">
                                    <?php echo substr(strip_tags($news['content']), 0, 120); ?>...
                                </p>

                                <!-- Metadata -->
                                <div class="news-meta mt-auto">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <div class="avatar-title bg-light text-success rounded-circle">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($news['author']); ?></small>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?php echo time_elapsed_string($news['news_date']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="news_detail.php?id=<?php echo $news['id']; ?>" 
                                   class="btn btn-outline-success w-100">
                                    Baca Selengkapnya <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-0 text-center py-5">
                        <div class="card-body">
                            <div class="empty-state">
                                <i class="bi bi-newspaper display-1 text-muted"></i>
                                <h4 class="mt-3 text-muted">Belum Ada Berita Lainnya</h4>
                                <p class="text-muted">Tidak ada berita lainnya yang tersedia saat ini.</p>
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
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

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
    </div>
</div>

<?php
// Fungsi untuk menampilkan waktu relatif
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
}
?>

<style>
    .news-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #0d6efd 100%);
        position: relative;
        overflow: hidden;
    }

    .featured-news {
        border-radius: 16px;
        overflow: hidden;
    }

    .news-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
    }

    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
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

    .news-card:hover .card-img-container img {
        transform: scale(1.05);
    }

    .card-date-overlay {
        position: absolute;
        top: 15px;
        left: 15px;
    }

    .date-badge {
        border-radius: 8px;
        padding: 8px 12px;
        line-height: 1.2;
    }

    .date-badge .day {
        font-size: 1.25rem;
    }

    .date-badge .month {
        font-size: 0.75rem;
        opacity: 0.9;
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

    .avatar-xs {
        width: 24px;
        height: 24px;
    }

    .avatar-sm {
        width: 40px;
        height: 40px;
    }

    .avatar-title {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        font-size: 0.875rem;
    }

    .shadow-hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
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
        .news-hero {
            padding: 2rem 1rem !important;
        }
        
        .news-hero h1 {
            font-size: 2rem;
        }
        
        .featured-news .card-body {
            padding: 1.5rem !important;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>
<?php
session_start();
include '../includes/header.php';
include '../includes/sidebar_publik.php';

// Koneksi database
require_once '../includes/db_connect.php';

$id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM news WHERE id = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

if (!$news) {
    echo "<script>alert('Berita tidak ditemukan!'); window.history.back();</script>";
    exit();
}
?>

<div class="main-content">
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="beranda.php">Beranda</a></li>
                <li class="breadcrumb-item"><a href="news_all.php">Berita</a></li>
                <li class="breadcrumb-item active">Detail Berita</li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm">
            <?php if (!empty($news['image'])): ?>
                <img src="../assets/uploads/news/<?php echo htmlspecialchars($news['image']); ?>" 
                     class="card-img-top" alt="Gambar Berita" style="height: 400px; object-fit: cover;">
            <?php endif; ?>
            
            <div class="card-body p-4">
                <h1 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($news['title']); ?></h1>
                
                <div class="d-flex justify-content-between align-items-center mb-4 text-muted">
                    <div>
                        <i class="bi bi-calendar-event"></i> 
                        <?php echo date('d F Y', strtotime($news['news_date'])); ?>
                    </div>
                    <div>
                        <i class="bi bi-person"></i> 
                        By <?php echo htmlspecialchars($news['author']); ?>
                    </div>
                </div>

                <div class="content">
                    <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <a href="beranda.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Berita
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
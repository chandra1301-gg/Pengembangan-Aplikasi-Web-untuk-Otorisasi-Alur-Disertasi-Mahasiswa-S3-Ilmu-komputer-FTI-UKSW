<?php
session_start();
include '../includes/header.php';
include '../includes/sidebar_publik.php';

// Koneksi database
require_once '../includes/db_connect.php';

$id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM announcements WHERE id = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();

if (!$announcement) {
    echo "<script>alert('Pengumuman tidak ditemukan!'); window.history.back();</script>";
    exit();
}
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="beranda.php">Beranda</a></li>
                <li class="breadcrumb-item active">Detail Pengumuman</li>
            </ol>
        </nav> -->

        <div class="card border-0 shadow-sm">
            <?php if (!empty($announcement['image'])): ?>
                <img src="../assets/uploads/announcements/<?php echo htmlspecialchars($announcement['image']); ?>" 
                     class="card-img-top" alt="Gambar Pengumuman" style="height: 400px; object-fit: cover;">
            <?php endif; ?>
            
            <div class="card-body p-4">
                <h1 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($announcement['title']); ?></h1>
                
                <div class="d-flex justify-content-between align-items-center mb-4 text-muted">
                    <div>
                        <i class="bi bi-calendar-event"></i> 
                        <?php echo date('d F Y', strtotime($announcement['publish_date'])); ?>
                    </div>
                    <div>
                        <i class="bi bi-person"></i> 
                        By <?php echo htmlspecialchars($announcement['author']); ?>
                    </div>
                </div>

                <div class="content">
                    <?php echo nl2br(htmlspecialchars($announcement['description'])); ?>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <a href="beranda.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Pengumuman
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
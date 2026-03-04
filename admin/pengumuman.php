<?php
/**
 * File: admin/pengumuman.php
 * Halaman CRUD pengumuman oleh admin
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

$success = '';
$error = '';

// Proses tambah pengumuman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $judul = clean_input($_POST['judul']);
    $isi = clean_input($_POST['isi']);
    
    $query = "INSERT INTO pengumuman (judul, isi) VALUES ('" . escape_string($judul) . "', '" . escape_string($isi) . "')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Pengumuman berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan pengumuman!";
    }
}

// Proses edit pengumuman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = intval($_POST['id_pengumuman']);
    $judul = clean_input($_POST['judul']);
    $isi = clean_input($_POST['isi']);
    
    $query = "UPDATE pengumuman SET judul = '" . escape_string($judul) . "', isi = '" . escape_string($isi) . "' WHERE id_pengumuman = $id";
    
    if (mysqli_query($conn, $query)) {
        $success = "Pengumuman berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate pengumuman!";
    }
}

// Proses hapus pengumuman
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $query = "DELETE FROM pengumuman WHERE id_pengumuman = $id";
    
    if (mysqli_query($conn, $query)) {
        $success = "Pengumuman berhasil dihapus!";
    } else {
        $error = "Gagal menghapus pengumuman!";
    }
}

// Get data pengumuman
$query_pengumuman = "SELECT * FROM pengumuman ORDER BY tanggal_post DESC";
$result_pengumuman = mysqli_query($conn, $query_pengumuman);

$page_title = "Kelola Pengumuman - Sistem Disertasi S3 UKSW";
include '../includes/sidebar_admin.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-color: #0D6EFD;
        --success-color: #198754;
        --warning-color: #FFC107;
        --danger-color: #DC3545;
        --info-color: #0DCAF0;
        --light-bg: #F8F9FA;
        --dark-text: #212529;
        --muted-text: #6C757D;
        --border-color: #E4E4E4;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(123.95deg, #EEF2FF 0%, #F8F9FC 29.06%);
        min-height: 100vh;
        color: var(--dark-text);
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: none;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1030;
        background: var(--primary-color);
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 12px 18px;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .mobile-menu-btn:hover {
        background: #0b5ed7;
        transform: scale(1.05);
    }

    .mobile-menu-btn.active {
        background: #0b5ed7;
    }

    /* Overlay untuk mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1010;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sidebar-overlay.show {
        display: block;
        opacity: 1;
    }

    /* Content dengan Sidebar */
    .content-wrapper {
        margin-left: 0;
        background: transparent;
        min-height: 100vh;
        transition: margin-left 0.3s ease;
    }

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
        padding: 0 37px 60px 37px;
        max-width: 1440px;
        margin: 0 auto;
        background: transparent;
    }

    /* Alert Messages */
    .alert {
        border: none;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 24px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.08);
    }

    .alert-success {
        background: #D1E7DD;
        color: #0F5132;
    }

    .alert-danger {
        background: #F8D7DA;
        color: #842029;
    }

    .alert i {
        margin-right: 8px;
    }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }

    /* Card Styles */
    .content-card {
        background: #FFFFFF;
        border: 0.308621px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0px 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .content-card:hover {
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .content-card-header {
        background: var(--primary-color);
        padding: 20px 24px;
        color: #FFFFFF;
    }

    .content-card-header h5 {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
        line-height: 27px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .content-card-body {
        padding: 24px;
    }

    /* Form Styles */
    .form-label {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 14px;
        color: var(--dark-text);
        margin-bottom: 8px;
    }

    .form-control,
    .form-select {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    /* Button Styles */
    .btn {
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-primary {
        background: var(--primary-color);
        color: #FFFFFF;
    }

    .btn-primary:hover {
        background: #0b5ed7;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    .btn-outline-primary {
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        background: transparent;
    }

    .btn-outline-primary:hover {
        background: var(--primary-color);
        color: #FFFFFF;
    }

    .btn-outline-danger {
        border: 1px solid var(--danger-color);
        color: var(--danger-color);
        background: transparent;
    }

    .btn-outline-danger:hover {
        background: var(--danger-color);
        color: #FFFFFF;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }

    /* Pengumuman Item */
    .pengumuman-item {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.3s ease;
    }

    .pengumuman-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .pengumuman-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 12px;
    }

    .pengumuman-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 16px;
        color: var(--dark-text);
        margin: 0;
        flex: 1;
    }

    .pengumuman-actions {
        display: flex;
        gap: 8px;
    }

    .pengumuman-content {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        line-height: 24px;
        color: var(--muted-text);
        margin-bottom: 12px;
    }

    .pengumuman-date {
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: #CED4DA;
        margin-bottom: 16px;
    }

    .empty-state p {
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        font-size: 14px;
        color: var(--muted-text);
        margin: 0;
    }

    /* Modal Styles */
    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: var(--primary-color);
        color: #FFFFFF;
        border-radius: 12px 12px 0 0;
        padding: 20px 24px;
        border-bottom: none;
    }

    .modal-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
    }

    .btn-close {
        filter: brightness(0) invert(1);
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Back to Top Button */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: var(--primary-color);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        transition: all 0.3s ease;
        opacity: 0;
        visibility: hidden;
        z-index: 999;
    }

    .back-to-top.show {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        background: #0b5ed7;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .main-container {
            padding: 0 25px 40px 25px;
        }
        
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .content-wrapper {
            margin-left: 0;
        }
        
        .mobile-menu-btn {
            display: block;
        }
        
        .hero-section {
            height: 250px;
        }
        
        .hero-section::before,
        .hero-section::after {
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
        
        .hero-breadcrumb {
            font-size: 14px;
        }
        
        .main-container {
            padding: 0 20px 30px 20px;
        }
        
        .content-grid {
            grid-template-columns: 1fr;
        }

        .content-card-body {
            padding: 20px;
        }

        .pengumuman-item {
            padding: 16px;
        }

        .pengumuman-header {
            flex-direction: column;
            gap: 12px;
        }

        .pengumuman-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }

    @media (max-width: 480px) {
        .hero-section {
            height: 200px;
        }
        
        .hero-section::before,
        .hero-section::after {
            height: 200px;
        }
        
        .hero-content {
            left: 15px;
            top: 80px;
        }
        
        .hero-content h1 {
            font-size: 18px;
            line-height: 24px;
        }
        
        .hero-breadcrumb {
            font-size: 12px;
        }
        
        .main-container {
            padding: 0 15px 20px 15px;
        }

        .content-card-body {
            padding: 16px;
        }
    }
    </style>
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" aria-label="Back to top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <div class="content-wrapper">
        <!-- Hero Section -->
        <section class="hero-section" role="banner">
            <div class="hero-content">
                <h1>Kelola Pengumuman</h1>
                <p class="hero-breadcrumb">Beranda<span class="separator">›</span>Pengumuman</p>
            </div>
        </section>
        
        <!-- Main Content -->
        <main class="main-container" role="main">
            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show fade-in-up">
                <i class="bi bi-check-circle"></i><?= htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show fade-in-up">
                <i class="bi bi-exclamation-triangle"></i><?= htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="content-grid">
                <!-- Form Tambah Pengumuman -->
                <article class="content-card fade-in-up">
                    <div class="content-card-header">
                        <h5><i class="bi bi-plus-circle"></i>Tambah Pengumuman</h5>
                    </div>
                    <div class="content-card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Judul Pengumuman <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="judul" required placeholder="Masukkan judul pengumuman">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Isi Pengumuman <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="isi" rows="6" required placeholder="Masukkan isi pengumuman"></textarea>
                            </div>
                            
                            <button type="submit" name="tambah" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-2"></i>Tambah Pengumuman
                            </button>
                        </form>
                    </div>
                </article>
                
                <!-- Daftar Pengumuman -->
                <article class="content-card fade-in-up">
                    <div class="content-card-header">
                        <h5><i class="bi bi-megaphone-fill"></i>Daftar Pengumuman (<?= mysqli_num_rows($result_pengumuman); ?>)</h5>
                    </div>
                    <div class="content-card-body">
                        <?php if (mysqli_num_rows($result_pengumuman) > 0): ?>
                            <?php while ($pengumuman = mysqli_fetch_assoc($result_pengumuman)): ?>
                            <div class="pengumuman-item">
                                <div class="pengumuman-header">
                                    <h6 class="pengumuman-title"><?= htmlspecialchars($pengumuman['judul']); ?></h6>
                                    <div class="pengumuman-actions">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $pengumuman['id_pengumuman']; ?>" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?hapus=<?= $pengumuman['id_pengumuman']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus pengumuman ini?')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                                <p class="pengumuman-content"><?= htmlspecialchars($pengumuman['isi']); ?></p>
                                <div class="pengumuman-date">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d F Y H:i', strtotime($pengumuman['tanggal_post'])); ?>
                                </div>
                            </div>
                            
                            <!-- Modal Edit -->
                            <div class="modal fade" id="modalEdit<?= $pengumuman['id_pengumuman']; ?>" tabindex="-1" aria-labelledby="modalEditLabel<?= $pengumuman['id_pengumuman']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalEditLabel<?= $pengumuman['id_pengumuman']; ?>">Edit Pengumuman</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_pengumuman" value="<?= $pengumuman['id_pengumuman']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Judul Pengumuman <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="judul" value="<?= htmlspecialchars($pengumuman['judul']); ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Isi Pengumuman <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="isi" rows="6" required><?= htmlspecialchars($pengumuman['isi']); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="edit" class="btn btn-primary">
                                                    <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Belum ada pengumuman</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const menuBtn = document.querySelector('.mobile-menu-btn');
        
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        menuBtn.classList.toggle('active');
        
        // Prevent body scroll when sidebar is open
        if (sidebar.classList.contains('show')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }

    // Back to top functionality
    const backToTopButton = document.querySelector('.back-to-top');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });
    
    backToTopButton.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Add intersection observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all fade-in-up elements
    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Auto dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    </script>
</body>
</html>
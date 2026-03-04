<?php
/**
 * File: admin/dashboard.php
 * Dashboard admin - statistik dan ringkasan data
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// Function untuk menjalankan query dengan prepared statements
function executeQuery($conn, $query, $params = [], $fetchAll = false) {
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Query preparation failed: " . mysqli_error($conn));
        return false;
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Query execution failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if ($fetchAll) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $data;
    } else {
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }
}

// Statistik dengan prepared statements
$total_mahasiswa = executeQuery($conn, "SELECT COUNT(*) as total FROM mahasiswa")['total'] ?? 0;
$total_registrasi = executeQuery($conn, "SELECT COUNT(*) as total FROM registrasi")['total'] ?? 0;
$total_menunggu = executeQuery($conn, "SELECT COUNT(*) as total FROM registrasi WHERE status = 'Menunggu'")['total'] ?? 0;
$total_diterima = executeQuery($conn, "SELECT COUNT(*) as total FROM registrasi WHERE status = 'Diterima'")['total'] ?? 0;

// Statistik user
$total_user_pending = executeQuery($conn, "SELECT COUNT(*) as total FROM users WHERE status = 'pending' AND role = 'mahasiswa'")['total'] ?? 0;
$total_user_approved = executeQuery($conn, "SELECT COUNT(*) as total FROM users WHERE status = 'approved' AND role = 'mahasiswa'")['total'] ?? 0;
$total_user_rejected = executeQuery($conn, "SELECT COUNT(*) as total FROM users WHERE status = 'rejected' AND role = 'mahasiswa'")['total'] ?? 0;

// Data untuk tabel
$recent_pending = executeQuery($conn, 
    "SELECT u.*, m.nama_lengkap, m.nim, m.email, m.program_studi 
     FROM users u 
     LEFT JOIN mahasiswa m ON u.id = m.user_id 
     WHERE u.status = 'pending' AND u.role = 'mahasiswa' 
     ORDER BY u.created_at DESC LIMIT 5", [], true) ?: [];

$recent_registrations = executeQuery($conn,
    "SELECT r.*, m.nama_lengkap, m.nim 
     FROM registrasi r 
     JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
     ORDER BY r.tanggal_pengajuan DESC LIMIT 10", [], true) ?: [];

$page_title = "Dashboard Admin - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin-styles.css" rel="stylesheet">
</head>
<body>
    <div class="content-wrapper">
        <!-- Hero Section -->
        <div class="hero-section" role="banner">
            <div class="hero-content">
                <h1>Dashboard Administrator 👨‍💼</h1>
                <p class="hero-subtitle">Sistem Registrasi dan Monitoring Disertasi S3 UKSW</p>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="main-container" role="main">
            <!-- Stats Cards -->
            <section class="stats-grid" aria-label="Statistik Utama">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="bi bi-people-fill" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($total_mahasiswa); ?></h3>
                    <p>Total Mahasiswa</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="bi bi-clipboard-check" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($total_registrasi); ?></h3>
                    <p>Total Registrasi</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="bi bi-person-plus" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($total_user_pending); ?></h3>
                    <p>User Menunggu</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="bi bi-clock-history" aria-hidden="true"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($total_menunggu); ?></h3>
                    <p>Verifikasi Ujian</p>
                </div>
            </section>
            
            <!-- Content Grid -->
            <section class="content-grid" aria-label="Data Terbaru">
                <!-- User Pending Approval -->
                <article class="content-card">
                    <div class="content-card-header">
                        <h5><i class="bi bi-person-check" aria-hidden="true"></i>Pendaftaran User Baru</h5>
                        <a href="manage_users.php" class="btn-header">Kelola Semua</a>
                    </div>
                    <div class="content-card-body">
                        <?php if (count($recent_pending) > 0): ?>
                        <div class="table-responsive">
                            <table class="custom-table" aria-label="Daftar User Baru">
                                <thead>
                                    <tr>
                                        <th scope="col">Username</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Program Studi</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_pending as $user): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                                            <small style="color: #6C757D;"><?php echo htmlspecialchars($user['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($user['program_studi']); ?></td>
                                        <td>
                                            <form method="POST" action="manage_users.php" style="display: inline;" onsubmit="return confirmAction('approve')">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn-action success" title="Setujui" aria-label="Setujui user <?php echo htmlspecialchars($user['username']); ?>">
                                                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="manage_users.php" style="display: inline;" onsubmit="return confirmAction('reject')">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn-action danger" title="Tolak" aria-label="Tolak user <?php echo htmlspecialchars($user['username']); ?>">
                                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-check-circle text-success" aria-hidden="true"></i>
                            <p>Tidak ada pendaftaran user baru</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                
                <!-- Registrasi Terbaru -->
                <article class="content-card">
                    <div class="content-card-header">
                        <h5><i class="bi bi-list-check" aria-hidden="true"></i>Registrasi Ujian Terbaru</h5>
                        <a href="verifikasi.php" class="btn-header">Lihat Semua</a>
                    </div>
                    <div class="content-card-body">
                        <?php if (count($recent_registrations) > 0): ?>
                        <div class="table-responsive">
                            <table class="custom-table" aria-label="Daftar Registrasi Ujian Terbaru">
                                <thead>
                                    <tr>
                                        <th scope="col">Mahasiswa</th>
                                        <th scope="col">Jenis Ujian</th>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_registrations as $reg): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($reg['nama_lengkap']); ?></strong><br>
                                            <small style="color: #6C757D;"><?php echo htmlspecialchars($reg['nim']); ?></small>
                                        </td>
                                        <td><?php echo ucfirst(htmlspecialchars($reg['jenis_ujian'])); ?></td>
                                        <td><?php echo date('d M Y', strtotime($reg['tanggal_pengajuan'])); ?></td>
                                        <td>
                                            <?php 
                                            $badge_class = '';
                                            if ($reg['status'] == 'Menunggu') $badge_class = 'badge-menunggu';
                                            elseif ($reg['status'] == 'Diterima') $badge_class = 'badge-diterima';
                                            else $badge_class = 'badge-ditolak';
                                            ?>
                                            <span class="badge-status <?php echo $badge_class; ?>"><?php echo htmlspecialchars($reg['status']); ?></span>
                                        </td>
                                        <td>
                                            <a href="detail_verifikasi.php?id=<?php echo $reg['id_registrasi']; ?>" class="btn-action primary" title="Lihat Detail" aria-label="Lihat detail registrasi <?php echo htmlspecialchars($reg['nama_lengkap']); ?>">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox" aria-hidden="true"></i>
                            <p>Tidak ada registrasi ujian terbaru</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
            </section>

            <!-- Quick Stats -->
            <section class="quick-stats-grid" aria-label="Statistik Cepat">
                <div class="quick-stat-card">
                    <div class="quick-stat-content">
                        <h6>User Disetujui</h6>
                        <h3 style="color: #198754;"><?php echo htmlspecialchars($total_user_approved); ?></h3>
                    </div>
                    <div class="quick-stat-icon" style="background: rgba(25, 135, 84, 0.1);">
                        <i class="bi bi-check-circle" style="color: #198754;" aria-hidden="true"></i>
                    </div>
                </div>
                
                <div class="quick-stat-card">
                    <div class="quick-stat-content">
                        <h6>User Ditolak</h6>
                        <h3 style="color: #DC3545;"><?php echo htmlspecialchars($total_user_rejected); ?></h3>
                    </div>
                    <div class="quick-stat-icon" style="background: rgba(220, 53, 69, 0.1);">
                        <i class="bi bi-x-circle" style="color: #DC3545;" aria-hidden="true"></i>
                    </div>
                </div>
                
                <div class="quick-stat-card">
                    <div class="quick-stat-content">
                        <h6>Total User Aktif</h6>
                        <h3 style="color: #0D6EFD;"><?php echo htmlspecialchars($total_user_approved + $total_user_rejected + $total_user_pending); ?></h3>
                    </div>
                    <div class="quick-stat-icon" style="background: rgba(13, 110, 253, 0.1);">
                        <i class="bi bi-people" style="color: #0D6EFD;" aria-hidden="true"></i>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
    // Konfirmasi aksi
    function confirmAction(action) {
        const message = action === 'approve' 
            ? 'Apakah Anda yakin ingin menyetujui user ini?'
            : 'Apakah Anda yakin ingin menolak user ini?';
        
        return confirm(message);
    }

    // Animasi loading untuk tombol aksi
    document.addEventListener('DOMContentLoaded', function() {
        const actionButtons = document.querySelectorAll('.btn-action');
        
        actionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Tambah loading state untuk form submission
                if (this.closest('form')) {
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<div class="loading"></div>';
                    this.disabled = true;
                    
                    // Reset setelah 3 detik jika form tidak submit
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                    }, 3000);
                }
            });
        });
        
        // Tambah efek hover untuk card
        const cards = document.querySelectorAll('.stat-card, .content-card, .quick-stat-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>
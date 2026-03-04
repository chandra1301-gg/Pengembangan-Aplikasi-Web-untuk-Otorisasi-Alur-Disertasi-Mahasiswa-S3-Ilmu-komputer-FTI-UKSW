<?php
/**
 * File: admin/daftar_mahasiswa.php
 * Daftar mahasiswa yang terdaftar dalam sistem
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
                     OR m.email LIKE '%$search%'
                     OR m.program_studi LIKE '%$search%'";
}

// Query untuk mendapatkan total mahasiswa
$query_total = "SELECT COUNT(*) as total FROM mahasiswa m $where_clause";
$result_total = mysqli_query($conn, $query_total);
$total_mahasiswa = mysqli_fetch_assoc($result_total)['total'];
$total_pages = ceil($total_mahasiswa / $limit);

// Query untuk mendapatkan data mahasiswa dengan pagination
$query_mahasiswa = "SELECT m.*, u.username, u.status as user_status, u.created_at as tanggal_daftar
                   FROM mahasiswa m 
                   LEFT JOIN users u ON m.user_id = u.id 
                   $where_clause 
                   ORDER BY m.id_mahasiswa DESC 
                   LIMIT $limit OFFSET $offset";
$result_mahasiswa = mysqli_query($conn, $query_mahasiswa);

$page_title = "Daftar Mahasiswa - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>
<link href="../assets/css/admin-styles.css" rel="stylesheet">
<div class="main-content">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-2"><i class="bi bi-people-fill me-2"></i>Daftar Mahasiswa</h3>
                                <p class="mb-0">Manajemen data mahasiswa program S3 UKSW</p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <i class="bi bi-mortarboard" style="font-size: 4rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 h-100">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-people-fill text-primary" style="font-size: 1.8rem;"></i>
                        </div>
                        <h3 class="mb-1"><?php echo $total_mahasiswa; ?></h3>
                        <p class="text-muted mb-0">Total Mahasiswa</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card border-0 h-100">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-check-circle text-success" style="font-size: 1.8rem;"></i>
                        </div>
                        <h3 class="mb-1">
                            <?php 
                            $query_aktif = "SELECT COUNT(*) as total FROM mahasiswa m 
                                          JOIN users u ON m.user_id = u.id 
                                          WHERE u.status = 'approved'";
                            $result_aktif = mysqli_query($conn, $query_aktif);
                            echo mysqli_fetch_assoc($result_aktif)['total'];
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Mahasiswa Aktif</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card border-0 h-100">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-clock-history text-warning" style="font-size: 1.8rem;"></i>
                        </div>
                        <h3 class="mb-1">
                            <?php 
                            $query_pending = "SELECT COUNT(*) as total FROM mahasiswa m 
                                            JOIN users u ON m.user_id = u.id 
                                            WHERE u.status = 'pending'";
                            $result_pending = mysqli_query($conn, $query_pending);
                            echo mysqli_fetch_assoc($result_pending)['total'];
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Menunggu Verifikasi</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card border-0 h-100">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-mortarboard text-info" style="font-size: 1.8rem;"></i>
                        </div>
                        <h3 class="mb-1">
                            <?php 
                            $query_prodi = "SELECT COUNT(DISTINCT program_studi) as total FROM mahasiswa";
                            $result_prodi = mysqli_query($conn, $query_prodi);
                            echo mysqli_fetch_assoc($result_prodi)['total'];
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Program Studi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" name="search" 
                                           placeholder="Cari mahasiswa berdasarkan nama, NIM, email, atau program studi..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-1"></i>Cari
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="daftar_mahasiswa.php" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Data Mahasiswa</h5>
                        <div>
                            <span class="text-muted me-3">Total: <?php echo $total_mahasiswa; ?> mahasiswa</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result_mahasiswa) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Lengkap</th>
                                            <th>NIM</th>
                                            <th>Email</th>
                                            <th>Program Studi</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Angkatan</th>
                                            <th>Status</th>
                                            <th>Tanggal Daftar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        while ($mahasiswa = mysqli_fetch_assoc($result_mahasiswa)): 
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></strong>
                                                <?php if (!empty($mahasiswa['username'])): ?>
                                                    <br><small class="text-muted">@<?php echo htmlspecialchars($mahasiswa['username']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($mahasiswa['nim']); ?></code>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($mahasiswa['email']); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    <?php echo htmlspecialchars($mahasiswa['program_studi']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($mahasiswa['jenis_kelamin'] == 'Laki-laki') {
                                                    echo '<span class="badge bg-primary bg-opacity-10 text-primary">Laki-laki</span>';
                                                } elseif ($mahasiswa['jenis_kelamin'] == 'Perempuan') {
                                                    echo '<span class="badge bg-pink bg-opacity-10 text-pink">Perempuan</span>';
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo !empty($mahasiswa['angkatan']) ? htmlspecialchars($mahasiswa['angkatan']) : '<span class="text-muted">-</span>'; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $badge_class = '';
                                                $status_text = '';
                                                if ($mahasiswa['user_status'] == 'approved') {
                                                    $badge_class = 'badge-diterima';
                                                    $status_text = 'Aktif';
                                                } elseif ($mahasiswa['user_status'] == 'pending') {
                                                    $badge_class = 'badge-menunggu';
                                                    $status_text = 'Menunggu';
                                                } else {
                                                    $badge_class = 'badge-ditolak';
                                                    $status_text = 'Ditolak';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php 
                                                    if (!empty($mahasiswa['tanggal_daftar'])) {
                                                        echo date('d M Y', strtotime($mahasiswa['tanggal_daftar']));
                                                    } else {
                                                        echo '<span class="text-muted">-</span>';
                                                    }
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="detail_mahasiswa.php?id=<?php echo $mahasiswa['id_mahasiswa']; ?>" 
                                                       class="btn btn-outline-primary" title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-4">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                            <i class="bi bi-chevron-left"></i>
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
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                                <h5 class="text-muted mt-3">Tidak ada data mahasiswa</h5>
                                <p class="text-muted">
                                    <?php if (!empty($search)): ?>
                                        Tidak ditemukan mahasiswa dengan kata kunci "<?php echo htmlspecialchars($search); ?>"
                                    <?php else: ?>
                                        Belum ada mahasiswa yang terdaftar dalam sistem
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty($search)): ?>
                                    <a href="daftar_mahasiswa.php" class="btn btn-primary">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Tampilkan Semua
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
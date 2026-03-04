<?php
/**
 * File: admin/detail_mahasiswa.php
 * Detail informasi mahasiswa
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: daftar_mahasiswa.php');
    exit();
}

$id_mahasiswa = (int)$_GET['id'];

// Query untuk mendapatkan data mahasiswa
$query_mahasiswa = "SELECT m.*, u.username, u.status as user_status, u.created_at as tanggal_daftar
                   FROM mahasiswa m 
                   LEFT JOIN users u ON m.user_id = u.id 
                   WHERE m.id_mahasiswa = $id_mahasiswa";
$result_mahasiswa = mysqli_query($conn, $query_mahasiswa);

if (mysqli_num_rows($result_mahasiswa) === 0) {
    header('Location: daftar_mahasiswa.php');
    exit();
}

$mahasiswa = mysqli_fetch_assoc($result_mahasiswa);

// Query untuk mendapatkan riwayat registrasi ujian
$query_registrasi = "SELECT r.*, r.status as status_ujian
                    FROM registrasi r 
                    WHERE r.id_mahasiswa = $id_mahasiswa 
                    ORDER BY r.tanggal_pengajuan DESC";
$result_registrasi = mysqli_query($conn, $query_registrasi);
$total_registrasi = mysqli_num_rows($result_registrasi);

$page_title = "Detail Mahasiswa - Sistem Disertasi S3 UKSW";
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
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-2">
                                        <li class="breadcrumb-item"><a href="daftar_mahasiswa.php" class="text-white-50">Daftar Mahasiswa</a></li>
                                        <li class="breadcrumb-item active text-white">Detail Mahasiswa</li>
                                    </ol>
                                </nav>
                                <h3 class="mb-2"><i class="bi bi-person-badge me-2"></i><?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></h3>
                                <p class="mb-0">Detail informasi mahasiswa program S3 UKSW</p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <i class="bi bi-mortarboard" style="font-size: 4rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informasi Pribadi -->
            <div class="col-md-6 mb-4">
                <div class="card border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Pribadi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Nama Lengkap</div>
                            <div class="col-sm-8"><?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">NIM</div>
                            <div class="col-sm-8">
                                <code><?php echo htmlspecialchars($mahasiswa['nim']); ?></code>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Email</div>
                            <div class="col-sm-8">
                                <i class="bi bi-envelope me-1"></i>
                                <?php echo htmlspecialchars($mahasiswa['email']); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">No. Telepon</div>
                            <div class="col-sm-8">
                                <i class="bi bi-telephone me-1"></i>
                                <?php echo !empty($mahasiswa['no_telp']) ? htmlspecialchars($mahasiswa['no_telp']) : '<span class="text-muted">-</span>'; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Jenis Kelamin</div>
                            <div class="col-sm-8">
                                <?php 
                                if ($mahasiswa['jenis_kelamin'] == 'Laki-laki') {
                                    echo '<span class="badge bg-success bg-opacity-10 text-success">Laki-laki</span>';
                                } elseif ($mahasiswa['jenis_kelamin'] == 'Perempuan') {
                                    echo '<span class="badge bg-warning bg-opacity-10 text-warning">Perempuan</span>';
                                } else {
                                    echo '<span class="text-muted">-</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Username</div>
                            <div class="col-sm-8">
                                <i class="bi bi-person me-1"></i>
                                <?php echo !empty($mahasiswa['username']) ? '@' . htmlspecialchars($mahasiswa['username']) : '<span class="text-muted">-</span>'; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 fw-bold">Alamat</div>
                            <div class="col-sm-8">
                                <?php echo !empty($mahasiswa['alamat']) ? nl2br(htmlspecialchars($mahasiswa['alamat'])) : '<span class="text-muted">-</span>'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Akademik -->
            <div class="col-md-6 mb-4">
                <div class="card border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-mortarboard me-2"></i>Informasi Akademik</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Program Studi</div>
                            <div class="col-sm-8">
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <?php echo htmlspecialchars($mahasiswa['program_studi']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Angkatan</div>
                            <div class="col-sm-8">
                                <?php echo !empty($mahasiswa['angkatan']) ? htmlspecialchars($mahasiswa['angkatan']) : '<span class="text-muted">-</span>'; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Pembimbing 1</div>
                            <div class="col-sm-8">
                                <?php echo !empty($mahasiswa['pembimbing_1']) ? htmlspecialchars($mahasiswa['pembimbing_1']) : '<span class="text-muted">-</span>'; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Pembimbing 2</div>
                            <div class="col-sm-8">
                                <?php echo !empty($mahasiswa['pembimbing_2']) ? htmlspecialchars($mahasiswa['pembimbing_2']) : '<span class="text-muted">-</span>'; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Status</div>
                            <div class="col-sm-8">
                                <?php 
                                $badge_class = '';
                                $status_text = '';
                                if ($mahasiswa['user_status'] == 'approved') {
                                    $badge_class = 'badge-diterima';
                                    $status_text = 'Aktif';
                                } elseif ($mahasiswa['user_status'] == 'pending') {
                                    $badge_class = 'badge-menunggu';
                                    $status_text = 'Menunggu Verifikasi';
                                } else {
                                    $badge_class = 'badge-ditolak';
                                    $status_text = 'Ditolak';
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 fw-bold">Tanggal Daftar</div>
                            <div class="col-sm-8">
                                <i class="bi bi-calendar me-1"></i>
                                <?php 
                                if (!empty($mahasiswa['tanggal_daftar'])) {
                                    echo date('d F Y', strtotime($mahasiswa['tanggal_daftar']));
                                } else {
                                    echo '<span class="text-muted">-</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Registrasi Ujian -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Registrasi Ujian</h5>
                        <span class="badge bg-primary"><?php echo $total_registrasi; ?> registrasi</span>
                    </div>
                    <div class="card-body">
                        <?php if ($total_registrasi > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Jenis Ujian</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Status</th>
                                            <th>Catatan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($registrasi = mysqli_fetch_assoc($result_registrasi)): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo ucfirst($registrasi['jenis_ujian']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo date('d M Y', strtotime($registrasi['tanggal_pengajuan'])); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $badge_class = '';
                                                if ($registrasi['status_ujian'] == 'Menunggu') $badge_class = 'badge-menunggu';
                                                elseif ($registrasi['status_ujian'] == 'Diterima') $badge_class = 'badge-diterima';
                                                else $badge_class = 'badge-ditolak';
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $registrasi['status_ujian']; ?></span>
                                            </td>
                                            <td>
                                                <?php echo !empty($registrasi['catatan']) ? htmlspecialchars($registrasi['catatan']) : '<span class="text-muted">-</span>'; ?>
                                            </td>
                                            <td>
                                                <a href="detail_verifikasi.php?id=<?php echo $registrasi['id_registrasi']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">Belum ada riwayat registrasi ujian</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="daftar_mahasiswa.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
                    </a>
                    <div>
                        <!-- Tombol aksi lainnya bisa ditambahkan di sini -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
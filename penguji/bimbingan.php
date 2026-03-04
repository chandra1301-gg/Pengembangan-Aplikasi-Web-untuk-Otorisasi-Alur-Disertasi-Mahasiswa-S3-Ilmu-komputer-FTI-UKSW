<?php
/**
 * File: dosen/bimbingan.php
 * Halaman untuk melihat mahasiswa bimbingan dosen
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_dosen();

$user_id = $_SESSION['user_id'];

// Ambil ID dosen
$sql_dosen = "SELECT id_dosen FROM dosen WHERE user_id = ?";
$stmt_dosen = $conn->prepare($sql_dosen);
$stmt_dosen->bind_param("i", $user_id);
$stmt_dosen->execute();
$result_dosen = $stmt_dosen->get_result();
$dosen_data = $result_dosen->fetch_assoc();
$id_dosen = $dosen_data['id_dosen'];

// Ambil mahasiswa bimbingan (sebagai promotor dan co-promotor)
$sql_bimbingan = "SELECT 
    m.id_mahasiswa, m.nama_lengkap, m.nim, m.program_studi, m.email,
    r.jenis_ujian, r.judul_disertasi, r.status as status_registrasi,
    CASE 
        WHEN r.promotor = ? THEN 'Promotor'
        WHEN r.co_promotor = ? THEN 'Co-Promotor'
        WHEN r.co_promotor2 = ? THEN 'Co-Promotor 2'
    END as peran_bimbingan,
    MAX(r.tanggal_pengajuan) as tanggal_terakhir
    FROM registrasi r
    JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
    WHERE (r.promotor = ? OR r.co_promotor = ? OR r.co_promotor2 = ?)
    AND r.status = 'Diterima'
    GROUP BY m.id_mahasiswa, m.nama_lengkap, m.nim, m.program_studi, m.email, 
             r.jenis_ujian, r.judul_disertasi, r.status, peran_bimbingan
    ORDER BY tanggal_terakhir DESC";

$stmt_bimbingan = $conn->prepare($sql_bimbingan);
$stmt_bimbingan->bind_param("iiiiii", $id_dosen, $id_dosen, $id_dosen, $id_dosen, $id_dosen, $id_dosen);
$stmt_bimbingan->execute();
$bimbingan_result = $stmt_bimbingan->get_result();

$page_title = "Mahasiswa Bimbingan - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_penguji.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">👥 Mahasiswa Bimbingan</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($bimbingan_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>NIM</th>
                                            <th>Program Studi</th>
                                            <th>Peran Bimbingan</th>
                                            <th>Jenis Ujian</th>
                                            <th>Judul Disertasi</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($bimbingan = $bimbingan_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($bimbingan['nama_lengkap']) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($bimbingan['email']) ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($bimbingan['nim']) ?></td>
                                                <td><?= htmlspecialchars($bimbingan['program_studi']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $bimbingan['peran_bimbingan'] == 'Promotor' ? 'success' : 'info' ?>">
                                                        <?= $bimbingan['peran_bimbingan'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= ucfirst($bimbingan['jenis_ujian']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($bimbingan['judul_disertasi']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">Aktif</span>
                                                </td>
                                                <td>
                                                    <a href="daftar_ujian.php" class="btn btn-sm btn-outline-primary">
                                                        Lihat Ujian
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div style="font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;">👨‍🏫</div>
                                <h5 class="text-muted">Belum ada mahasiswa bimbingan</h5>
                                <p class="text-muted">Mahasiswa bimbingan akan muncul ketika Anda ditetapkan sebagai promotor atau co-promotor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php
/**
 * File: mahasiswa/lihat_nilai.php
 * Halaman untuk mahasiswa melihat nilai ujian - DIPERBAIKI
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];

// Ambil nilai ujian dengan query yang lebih komprehensif
$sql = "SELECT DISTINCT p.id_penilaian, p.*, r.*, m.nama_lengkap, m.nim, m.program_studi, 
               d.nama_lengkap as nama_dosen, d.bidang_keahlian,
               r.jenis_ujian, r.judul_disertasi
        FROM penilaian_ujian p
        JOIN registrasi r ON p.id_registrasi = r.id_registrasi
        JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
        JOIN dosen d ON p.id_dosen = d.id_dosen
        WHERE r.id_mahasiswa = ? AND r.status = 'Diterima' 
        ORDER BY p.tanggal_penilaian DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();

$nilai_list = [];
while ($row = $result->fetch_assoc()) {
    $nilai_list[] = $row;
}

$page_title = "Lihat Nilai - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_mahasiswa.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📊 Nilai Ujian Saya</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($nilai_list)): ?>
                            <div class="alert alert-info">
                                Belum ada nilai ujian yang tersedia.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Jenis Ujian</th>
                                            <th>Judul Disertasi</th>
                                            <th>Penilai</th>
                                            <th>Jenis Penilai</th>
                                            <th>Nilai Total</th>
                                            <th>Grade</th>
                                            <th>Catatan</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nilai_list as $nilai): 
                                            $grade = calculateGrade($nilai['nilai_total']);
                                            $grade_class = $grade == 'TIDAK LULUS' ? 'danger' : 
                                                          ($grade == 'A' ? 'success' : 
                                                          ($grade == 'AB' ? 'warning' : 'info'));
                                        ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= ucfirst($nilai['jenis_ujian']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                                         title="<?= htmlspecialchars($nilai['judul_disertasi']) ?>">
                                                        <?= htmlspecialchars($nilai['judul_disertasi']) ?>
                                                    </div>
                                                </td>
                                                <td><?= $nilai['nama_dosen'] ?></td>
                                                <td>
                                                    <span class="badge <?= $nilai['jenis_nilai'] == 'promotor' ? 'bg-info' : 'bg-warning' ?>">
                                                        <?= ucfirst($nilai['jenis_nilai']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong class="h5 <?= $nilai['nilai_total'] >= 70 ? 'text-success' : 'text-danger' ?>">
                                                        <?= number_format($nilai['nilai_total'], 2) ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $grade_class ?>">
                                                        <?= $grade ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($nilai['catatan']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                data-bs-toggle="popover" 
                                                                data-bs-title="Catatan Penilaian"
                                                                data-bs-content="<?= htmlspecialchars($nilai['catatan']) ?>">
                                                            Lihat Catatan
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d/m/Y H:i', strtotime($nilai['tanggal_penilaian'])) ?></td>
                                                <td>
                                                    <a href="detail_nilai.php?id_penilaian=<?= $nilai['id_penilaian'] ?>" 
                                                       class="btn btn-sm btn-info" title="Lihat detail penilaian">
                                                        <i class="fas fa-eye me-1"></i>Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Inisialisasi popover Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    })
});
</script>

<?php include '../includes/footer.php'; ?>
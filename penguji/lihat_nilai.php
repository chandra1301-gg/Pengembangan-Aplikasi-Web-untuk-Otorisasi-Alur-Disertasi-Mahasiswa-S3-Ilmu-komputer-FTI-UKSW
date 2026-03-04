<?php
/**
 * File: penguji/lihat_nilai.php
 * Halaman untuk melihat detail penilaian yang sudah diberikan - DIPERBAIKI UNTUK HINDARI DOUBLE DATA
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/template_penilaian.php';

require_dosen();

$id_penilaian = $_GET['id_penilaian'] ?? 0;

// Ambil data penilaian dengan query yang lebih spesifik
$sql = "SELECT DISTINCT p.*, r.*, m.nama_lengkap, m.nim, m.program_studi, 
               d.nama_lengkap as nama_dosen, d.bidang_keahlian,
               r.jenis_ujian, r.judul_disertasi
        FROM penilaian_ujian p
        JOIN registrasi r ON p.id_registrasi = r.id_registrasi
        JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
        JOIN dosen d ON p.id_dosen = d.id_dosen
        WHERE p.id_penilaian = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_penilaian);
$stmt->execute();
$result = $stmt->get_result();
$penilaian = $result->fetch_assoc();

if (!$penilaian) {
    die("Data penilaian tidak ditemukan.");
}

// Ambil detail penilaian dengan GROUP BY untuk hindari duplikat
$sql_detail = "SELECT dp.* 
               FROM detail_penilaian dp
               WHERE dp.id_penilaian = ? 
               GROUP BY dp.id_detail, dp.aspek_penilaian
               ORDER BY dp.id_detail";
$stmt_detail = $conn->prepare($sql_detail);
$stmt_detail->bind_param("i", $id_penilaian);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();
$detail_penilaian = [];
while ($row = $result_detail->fetch_assoc()) {
    $detail_penilaian[] = $row;
}

// Ambil template untuk menampilkan aspek penilaian
$template = getTemplatePenilaian($penilaian['jenis_ujian']);
$ujian_title = getUjianTitle($penilaian['jenis_ujian']);

$page_title = "Detail Penilaian {$ujian_title} - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_penguji.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📊 Detail Penilaian</h4>
                    </div>
                    <div class="card-body">
                        <!-- Informasi Penilaian -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">📝 Informasi Penilaian</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Mahasiswa:</strong><br>
                                        <?= $penilaian['nama_lengkap'] ?> (<?= $penilaian['nim'] ?>)
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Dosen Penilai:</strong><br>
                                        <?= $penilaian['nama_dosen'] ?>
                                        <?php if ($penilaian['bidang_keahlian']): ?>
                                            <br><small class="text-muted"><?= $penilaian['bidang_keahlian'] ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Jenis Penilaian:</strong><br>
                                        <span class="badge bg-info"><?= ucfirst($penilaian['jenis_nilai']) ?></span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <strong>Jenis Ujian:</strong><br>
                                        <span class="badge bg-secondary"><?= ucfirst($penilaian['jenis_ujian']) ?></span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Nilai Total:</strong><br>
                                        <span class="h5 text-success"><?= number_format($penilaian['nilai_total'], 2) ?></span>
                                        <br>
                                        <span class="badge bg-<?= $penilaian['nilai_total'] >= 70 ? 'success' : 'danger' ?>">
                                            <?= calculateGrade($penilaian['nilai_total']) ?>
                                        </span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Tanggal Penilaian:</strong><br>
                                        <?= date('d F Y H:i', strtotime($penilaian['tanggal_penilaian'])) ?>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <strong>Judul Disertasi:</strong><br>
                                        <div class="alert alert-secondary mt-1">
                                            <?= htmlspecialchars($penilaian['judul_disertasi']) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($penilaian['catatan']): ?>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <strong>Catatan:</strong><br>
                                        <div class="alert alert-info mt-1">
                                            <?= nl2br(htmlspecialchars($penilaian['catatan'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Detail Aspek Penilaian -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">📋 Detail Aspek Penilaian</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Aspek Penilaian</th>
                                                <th width="100">Bobot</th>
                                                <th width="120">Nilai</th>
                                                <th width="120">Nilai Terbobot</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_nilai_terbobot = 0;
                                            $no = 1;
                                            foreach ($detail_penilaian as $detail): 
                                                // Pastikan tidak ada duplikat dengan memeriksa aspek yang unik
                                                $nilai_terbobot = ($detail['nilai'] * $detail['bobot']) / 100;
                                                $total_nilai_terbobot += $nilai_terbobot;
                                            ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= $detail['aspek_penilaian'] ?></td>
                                                    <td><?= number_format($detail['bobot'], 1) ?>%</td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?= number_format($detail['nilai'], 1) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <?= number_format($nilai_terbobot, 2) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-success">
                                                <td colspan="4" class="text-end"><strong>Total Nilai Akhir:</strong></td>
                                                <td>
                                                    <strong class="h5 text-success">
                                                        <?= number_format($total_nilai_terbobot, 2) ?>
                                                    </strong>
                                                    <br>
                                                    <span class="badge bg-<?= $total_nilai_terbobot >= 70 ? 'success' : 'danger' ?>">
                                                        <?= calculateGrade($total_nilai_terbobot) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="daftar_ujian.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Ujian
                            </a>
                            <?php if ($penilaian['status_revisi'] == 'belum'): ?>
                                <a href="form_penilaian.php?id_registrasi=<?= $penilaian['id_registrasi'] ?>&jenis=<?= $penilaian['jenis_nilai'] ?>" 
                                   class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>Edit Penilaian
                                </a>
                            <?php endif; ?>
                            
                            <!-- Tombol cetak penilaian -->
                            <button onclick="window.print()" class="btn btn-info">
                                <i class="fas fa-print me-2"></i>Cetak Penilaian
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .btn {
        display: none !important;
    }
    .sidebar, .header {
        display: none !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
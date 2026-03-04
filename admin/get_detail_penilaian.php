<?php
/**
 * File: admin/get_detail_penilaian.php
 * AJAX endpoint untuk mendapatkan detail penilaian
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

$id_penilaian = isset($_GET['id_penilaian']) ? intval($_GET['id_penilaian']) : 0;

if ($id_penilaian <= 0) {
    echo '<div class="alert alert-danger">ID Penilaian tidak valid</div>';
    exit;
}

// Query untuk mendapatkan detail penilaian
$query = "SELECT p.*, d.nama_lengkap as nama_dosen, p.jenis_nilai,
                 m.nama_lengkap as nama_mahasiswa, m.nim,
                 r.jenis_ujian, r.judul_disertasi
          FROM penilaian_ujian p
          JOIN dosen d ON p.id_dosen = d.id_dosen
          JOIN registrasi r ON p.id_registrasi = r.id_registrasi
          JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
          WHERE p.id_penilaian = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_penilaian);
$stmt->execute();
$result = $stmt->get_result();
$penilaian = $result->fetch_assoc();

if (!$penilaian) {
    echo '<div class="alert alert-danger">Data penilaian tidak ditemukan</div>';
    exit;
}

// Query untuk mendapatkan detail aspek penilaian
$detail_query = "SELECT * FROM detail_penilaian 
                WHERE id_penilaian = ? 
                ORDER BY id_detail";
$detail_stmt = $conn->prepare($detail_query);
$detail_stmt->bind_param("i", $id_penilaian);
$detail_stmt->execute();
$detail_result = $detail_stmt->get_result();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="text-primary">Detail Penilaian</h5>
            <hr>
        </div>
    </div>

    <!-- Informasi Umum -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">👤 Informasi Mahasiswa</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="120"><strong>Nama:</strong></td>
                            <td><?= htmlspecialchars($penilaian['nama_mahasiswa']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>NIM:</strong></td>
                            <td><?= $penilaian['nim'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Ujian:</strong></td>
                            <td><span class="badge bg-info"><?= ucfirst($penilaian['jenis_ujian']) ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">👨‍🏫 Informasi Dosen</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="120"><strong>Nama Dosen:</strong></td>
                            <td><?= htmlspecialchars($penilaian['nama_dosen']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Posisi:</strong></td>
                            <td>
                                <span class="badge <?= $penilaian['jenis_nilai'] == 'promotor' ? 'bg-success' : 'bg-primary' ?>">
                                    <?= ucfirst($penilaian['jenis_nilai']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal:</strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($penilaian['tanggal_penilaian'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Aspek Penilaian -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">📊 Detail Aspek Penilaian</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Aspek Penilaian</th>
                                    <th width="100" class="text-center">Bobot</th>
                                    <th width="120" class="text-center">Nilai</th>
                                    <th width="120" class="text-center">Nilai Terbobot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_bobot = 0;
                                $total_nilai_terbobot = 0;
                                
                                while ($detail = $detail_result->fetch_assoc()):
                                    $nilai_terbobot = ($detail['nilai'] * $detail['bobot']) / 100;
                                    $total_bobot += $detail['bobot'];
                                    $total_nilai_terbobot += $nilai_terbobot;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($detail['aspek_penilaian']) ?></td>
                                    <td class="text-center"><?= number_format($detail['bobot'], 1) ?>%</td>
                                    <td class="text-center"><?= number_format($detail['nilai'], 1) ?></td>
                                    <td class="text-center"><?= number_format($nilai_terbobot, 2) ?></td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <!-- Total -->
                                <tr class="table-primary">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-center"><strong><?= number_format($total_bobot, 1) ?>%</strong></td>
                                    <td class="text-center">-</td>
                                    <td class="text-center"><strong><?= number_format($total_nilai_terbobot, 2) ?></strong></td>
                                </tr>
                                
                                <!-- Nilai Akhir -->
                                <tr class="table-success">
                                    <td colspan="3"><strong>NILAI AKHIR</strong></td>
                                    <td class="text-center">
                                        <strong style="font-size: 1.2em;">
                                            <?= number_format($penilaian['nilai_total'], 2) ?>
                                        </strong>
                                        <br>
                                        <small>
                                            Grade: 
                                            <?php
                                            $nilai = $penilaian['nilai_total'];
                                            $grade = ($nilai > 85) ? 'A' : (($nilai > 80) ? 'AB' : (($nilai >= 70) ? 'B' : 'TIDAK LULUS'));
                                            echo '<span class="badge ' . ($grade == 'TIDAK LULUS' ? 'bg-danger' : 'bg-success') . '">' . $grade . '</span>';
                                            ?>
                                        </small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Catatan -->
    <?php if (!empty($penilaian['catatan'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">💬 Catatan Dosen</h6>
                </div>
                <div class="card-body">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;">
                        <?= nl2br(htmlspecialchars($penilaian['catatan'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Tutup koneksi
$stmt->close();
$detail_stmt->close();
?>
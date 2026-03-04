<?php
/**
 * File: mahasiswa/detail_nilai.php
 * Halaman detail penilaian dari dosen untuk mahasiswa
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

// Validasi parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID registrasi tidak valid!";
    header("Location: lihat_nilai.php");
    exit();
}

$id_registrasi = intval($_GET['id']);
$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];

// **QUERY: Ambil data registrasi dan validasi kepemilikan**
$sql_registrasi = "SELECT r.*, m.nama_lengkap, m.nim, m.program_studi
                   FROM registrasi r 
                   JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
                   WHERE r.id_registrasi = ? AND r.id_mahasiswa = ?";
$stmt_registrasi = $conn->prepare($sql_registrasi);
$stmt_registrasi->bind_param("ii", $id_registrasi, $id_mahasiswa);
$stmt_registrasi->execute();
$result_registrasi = $stmt_registrasi->get_result();
$registrasi = $result_registrasi->fetch_assoc();

if (!$registrasi) {
    $_SESSION['error_message'] = "Data registrasi tidak ditemukan atau tidak memiliki akses!";
    header("Location: lihat_nilai.php");
    exit();
}

// **QUERY: Ambil data tim dosen lengkap**
$sql_tim_dosen = "SELECT 
                    -- Data dari registrasi
                    r.promotor, r.co_promotor, r.co_promotor2,
                    d_promotor.nama_lengkap as nama_promotor,
                    d_promotor.bidang_keahlian as keahlian_promotor,
                    d_copromotor.nama_lengkap as nama_copromotor,
                    d_copromotor.bidang_keahlian as keahlian_copromotor,
                    d_copromotor2.nama_lengkap as nama_copromotor2,
                    d_copromotor2.bidang_keahlian as keahlian_copromotor2,
                    -- Data dari jadwal ujian
                    j.promotor as promotor_jadwal, 
                    j.co_promotor, j.co_promotor2,
                    j.penguji_1, j.penguji_2, j.penguji_3,
                    j.tanggal_ujian, j.tempat,
                    j_promotor.nama_lengkap as nama_promotor_jadwal,
                    j_copromotor.nama_lengkap as nama_copromotor_jadwal,
                    j_copromotor2.nama_lengkap as nama_copromotor2_jadwal,
                    j_penguji1.nama_lengkap as nama_penguji1,
                    j_penguji1.bidang_keahlian as keahlian_penguji1,
                    j_penguji2.nama_lengkap as nama_penguji2,
                    j_penguji2.bidang_keahlian as keahlian_penguji2,
                    j_penguji3.nama_lengkap as nama_penguji3,
                    j_penguji3.bidang_keahlian as keahlian_penguji3
                FROM registrasi r
                LEFT JOIN dosen d_promotor ON r.promotor = d_promotor.id_dosen
                LEFT JOIN dosen d_copromotor ON r.co_promotor = d_copromotor.id_dosen
                LEFT JOIN dosen d_copromotor2 ON r.co_promotor2 = d_copromotor2.id_dosen
                LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi
                LEFT JOIN dosen j_promotor ON j.promotor = j_promotor.id_dosen
                LEFT JOIN dosen j_copromotor ON j.co_promotor = j_copromotor.id_dosen
                LEFT JOIN dosen j_copromotor2 ON j.co_promotor2 = j_copromotor2.id_dosen
                LEFT JOIN dosen j_penguji1 ON j.penguji_1 = j_penguji1.id_dosen
                LEFT JOIN dosen j_penguji2 ON j.penguji_2 = j_penguji2.id_dosen
                LEFT JOIN dosen j_penguji3 ON j.penguji_3 = j_penguji3.id_dosen
                WHERE r.id_registrasi = ?";
$stmt_tim_dosen = $conn->prepare($sql_tim_dosen);
$stmt_tim_dosen->bind_param("i", $id_registrasi);
$stmt_tim_dosen->execute();
$result_tim_dosen = $stmt_tim_dosen->get_result();
$tim_dosen = $result_tim_dosen->fetch_assoc();

// **QUERY: Ambil semua penilaian untuk registrasi ini dengan detail**
$sql_penilaian = "SELECT p.*, d.nama_lengkap, d.bidang_keahlian,
                         CASE 
                             WHEN p.id_dosen = r.promotor THEN 'Promotor'
                             WHEN p.id_dosen = r.co_promotor THEN 'Co-Promotor 1'
                             WHEN p.id_dosen = r.co_promotor2 THEN 'Co-Promotor 2'
                             WHEN p.id_dosen = j.penguji_1 THEN 'Penguji 1'
                             WHEN p.id_dosen = j.penguji_2 THEN 'Penguji 2'
                             WHEN p.id_dosen = j.penguji_3 THEN 'Penguji 3'
                             ELSE 'Dosen'
                         END as peran_penilai,
                         dp.aspek_penilaian, dp.bobot, dp.nilai as nilai_aspek
                  FROM penilaian_ujian p
                  JOIN dosen d ON p.id_dosen = d.id_dosen
                  JOIN registrasi r ON p.id_registrasi = r.id_registrasi
                  LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi
                  LEFT JOIN detail_penilaian dp ON p.id_penilaian = dp.id_penilaian
                  WHERE p.id_registrasi = ?
                  ORDER BY 
                    CASE 
                        WHEN p.id_dosen = r.promotor THEN 1
                        WHEN p.id_dosen = r.co_promotor THEN 2
                        WHEN p.id_dosen = r.co_promotor2 THEN 3
                        ELSE 4
                    END,
                    p.tanggal_penilaian DESC";
$stmt_penilaian = $conn->prepare($sql_penilaian);
$stmt_penilaian->bind_param("i", $id_registrasi);
$stmt_penilaian->execute();
$result_penilaian = $stmt_penilaian->get_result();

// Reorganize data untuk grouping by penilaian
$penilaian_list = [];
$current_penilaian = null;

while ($row = $result_penilaian->fetch_assoc()) {
    $id_penilaian = $row['id_penilaian'];
    
    if (!$current_penilaian || $current_penilaian['id_penilaian'] != $id_penilaian) {
        // Mulai data penilaian baru
        if ($current_penilaian) {
            $penilaian_list[] = $current_penilaian;
        }
        
        $current_penilaian = [
            'id_penilaian' => $id_penilaian,
            'nama_lengkap' => $row['nama_lengkap'],
            'bidang_keahlian' => $row['bidang_keahlian'],
            'peran_penilai' => $row['peran_penilai'],
            'nilai_total' => $row['nilai_total'],
            'tanggal_penilaian' => $row['tanggal_penilaian'],
            'catatan' => $row['catatan'],
            'detail_aspek' => []
        ];
    }
    
    // Tambahkan detail aspek jika ada
    if ($row['aspek_penilaian']) {
        $current_penilaian['detail_aspek'][] = [
            'aspek_penilaian' => $row['aspek_penilaian'],
            'bobot' => $row['bobot'],
            'nilai_aspek' => $row['nilai_aspek']
        ];
    }
}

// Tambahkan penilaian terakhir
if ($current_penilaian) {
    $penilaian_list[] = $current_penilaian;
}

// Hitung statistik nilai
$total_nilai = 0;
$jumlah_penilai = 0;
$nilai_tertinggi = 0;
$nilai_terendah = 100;
$dosen_tertinggi = '';
$dosen_terendah = '';

foreach ($penilaian_list as $penilaian) {
    if ($penilaian['nilai_total']) {
        $nilai = $penilaian['nilai_total'];
        $total_nilai += $nilai;
        $jumlah_penilai++;
        
        if ($nilai > $nilai_tertinggi) {
            $nilai_tertinggi = $nilai;
            $dosen_tertinggi = $penilaian['nama_lengkap'];
        }
        if ($nilai < $nilai_terendah) {
            $nilai_terendah = $nilai;
            $dosen_terendah = $penilaian['nama_lengkap'];
        }
    }
}

$rata_rata = $jumlah_penilai > 0 ? $total_nilai / $jumlah_penilai : 0;
$grade = $rata_rata > 85 ? 'A' : ($rata_rata > 80 ? 'AB' : ($rata_rata >= 70 ? 'B' : 'T'));

$page_title = "Detail Nilai - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_mahasiswa.php';
?>

<style>
.card-detail {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.card-detail:hover {
    transform: translateY(-2px);
}

.badge-peran {
    font-size: 0.75rem;
    padding: 6px 10px;
}

.nilai-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}

.nilai-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    border-left: 4px solid #5495FF;
}

.nilai-high {
    border-left-color: #28a745;
}

.nilai-medium {
    border-left-color: #ffc107;
}

.nilai-low {
    border-left-color: #dc3545;
}

.stats-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.tim-dosen-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
}

.rating-breakdown {
    background: white;
    border-radius: 10px;
    padding: 15px;
    border: 1px solid #e9ecef;
}

.progress-custom {
    height: 6px;
    border-radius: 3px;
}

.nilai-breakdown-item {
    padding: 8px 12px;
    margin-bottom: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #5495FF;
}
</style>

<div class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">📊 Detail Penilaian</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="lihat_nilai.php">Lihat Nilai</a></li>
                                <li class="breadcrumb-item active">Detail Penilaian</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="lihat_nilai.php" class="btn btn-outline-primary">
                        ← Kembali ke Daftar Nilai
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Ujian -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-detail">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">ℹ️ Informasi Ujian</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Jenis Ujian</th>
                                        <td>
                                            <span class="badge bg-primary fs-6">
                                                <?= strtoupper($registrasi['jenis_ujian']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Mahasiswa</th>
                                        <td><?= $registrasi['nama_lengkap'] ?> (<?= $registrasi['nim'] ?>)</td>
                                    </tr>
                                    <tr>
                                        <th>Program Studi</th>
                                        <td><?= $registrasi['program_studi'] ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Judul Disertasi</th>
                                        <td><?= $registrasi['judul_disertasi'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Ujian</th>
                                        <td>
                                            <?= $tim_dosen['tanggal_ujian'] ? 
                                                date('d F Y H:i', strtotime($tim_dosen['tanggal_ujian'])) : 'Belum dijadwalkan' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tempat</th>
                                        <td><?= $tim_dosen['tempat'] ?: '-' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Nilai -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-detail">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">📈 Ringkasan Nilai</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="display-4 text-primary fw-bold">
                                        <?= number_format($rata_rata, 2) ?>
                                    </div>
                                    <div class="text-muted">Rata-rata Nilai</div>
                                    <span class="badge bg-primary mt-2">Grade: <?= $grade ?></span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="display-4 text-success fw-bold">
                                        <?= number_format($nilai_tertinggi, 2) ?>
                                    </div>
                                    <div class="text-muted">Nilai Tertinggi</div>
                                    <small class="text-muted"><?= $dosen_tertinggi ?></small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="display-4 text-warning fw-bold">
                                        <?= number_format($nilai_terendah, 2) ?>
                                    </div>
                                    <div class="text-muted">Nilai Terendah</div>
                                    <small class="text-muted"><?= $dosen_terendah ?></small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="display-4 text-info fw-bold">
                                        <?= $jumlah_penilai ?>
                                    </div>
                                    <div class="text-muted">Total Penilai</div>
                                    <small class="text-muted">Dosen yang menilai</small>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar untuk Grade -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>Distribusi Nilai:</h6>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: <?= min(100, ($rata_rata/100)*100) ?>%">
                                        <?= number_format($rata_rata, 2) ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>0</span>
                                    <span>25</span>
                                    <span>50</span>
                                    <span>75</span>
                                    <span>100</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tim Dosen -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-detail">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">👨‍🏫 Tim Dosen Penguji</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Dosen Pembimbing -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">🎓 Dosen Pembimbing</h6>
                                <?php if ($tim_dosen['nama_promotor']): ?>
                                <div class="tim-dosen-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= $tim_dosen['nama_promotor'] ?></strong>
                                            <span class="badge badge-peran bg-info ms-2">Promotor</span>
                                            <?php if ($tim_dosen['keahlian_promotor']): ?>
                                                <br><small class="text-muted"><?= $tim_dosen['keahlian_promotor'] ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($tim_dosen['nama_copromotor']): ?>
                                <div class="tim-dosen-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= $tim_dosen['nama_copromotor'] ?></strong>
                                            <span class="badge badge-peran bg-primary ms-2">Co-Promotor</span>
                                            <?php if ($tim_dosen['keahlian_copromotor']): ?>
                                                <br><small class="text-muted"><?= $tim_dosen['keahlian_copromotor'] ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($tim_dosen['nama_copromotor2']): ?>
                                <div class="tim-dosen-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= $tim_dosen['nama_copromotor2'] ?></strong>
                                            <span class="badge badge-peran bg-secondary ms-2">Co-Promotor 2</span>
                                            <?php if ($tim_dosen['keahlian_copromotor2']): ?>
                                                <br><small class="text-muted"><?= $tim_dosen['keahlian_copromotor2'] ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Dosen Penguji -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">📝 Dosen Penguji</h6>
                                <?php if ($tim_dosen['nama_penguji1']): ?>
                                <div class="tim-dosen-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= $tim_dosen['nama_penguji1'] ?></strong>
                                            <span class="badge badge-peran bg-warning ms-2">Penguji 1</span>
                                            <?php if ($tim_dosen['keahlian_penguji1']): ?>
                                                <br><small class="text-muted"><?= $tim_dosen['keahlian_penguji1'] ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($tim_dosen['nama_penguji2']): ?>
                                <div class="tim-dosen-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= $tim_dosen['nama_penguji2'] ?></strong>
                                            <span class="badge badge-peran bg-warning ms-2">Penguji 2</span>
                                            <?php if ($tim_dosen['keahlian_penguji2']): ?>
                                                <br><small class="text-muted"><?= $tim_dosen['keahlian_penguji2'] ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($tim_dosen['nama_penguji3']): ?>
                                <div class="tim-dosen-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= $tim_dosen['nama_penguji3'] ?></strong>
                                            <span class="badge badge-peran bg-warning ms-2">Penguji 3</span>
                                            <?php if ($tim_dosen['keahlian_penguji3']): ?>
                                                <br><small class="text-muted"><?= $tim_dosen['keahlian_penguji3'] ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!$tim_dosen['nama_penguji1'] && !$tim_dosen['nama_penguji2'] && !$tim_dosen['nama_penguji3']): ?>
                                <div class="text-center py-4 text-muted">
                                    <div style="font-size: 2rem;">👥</div>
                                    <p class="mb-0">Belum ada penguji yang ditetapkan</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Penilaian per Dosen -->
        <div class="row">
            <div class="col-12">
                <div class="card card-detail">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">📋 Detail Penilaian per Dosen</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($penilaian_list)): ?>
                            <div class="row">
                                <?php foreach ($penilaian_list as $index => $penilaian): 
                                    $nilai_class = $penilaian['nilai_total'] >= 80 ? 'nilai-high' : 
                                                ($penilaian['nilai_total'] >= 70 ? 'nilai-medium' : 'nilai-low');
                                ?>
                                <div class="col-lg-6 mb-4">
                                    <div class="nilai-item <?= $nilai_class ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="mb-1"><?= $penilaian['nama_lengkap'] ?></h6>
                                                <span class="badge <?= $penilaian['peran_penilai'] == 'Promotor' ? 'bg-info' : 
                                                                ($penilaian['peran_penilai'] == 'Co-Promotor 1' ? 'bg-primary' :
                                                                ($penilaian['peran_penilai'] == 'Co-Promotor 2' ? 'bg-secondary' : 'bg-warning')) ?>">
                                                    <?= $penilaian['peran_penilai'] ?>
                                                </span>
                                                <?php if ($penilaian['bidang_keahlian']): ?>
                                                    <br><small class="text-muted"><?= $penilaian['bidang_keahlian'] ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <div class="h4 mb-0 text-primary">
                                                    <?= number_format($penilaian['nilai_total'], 2) ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($penilaian['tanggal_penilaian'])) ?>
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Breakdown Nilai dari detail_penilaian -->
                                        <?php if (!empty($penilaian['detail_aspek'])): ?>
                                        <div class="rating-breakdown mt-3">
                                            <h6 class="mb-3">📊 Breakdown Penilaian:</h6>
                                            <?php foreach ($penilaian['detail_aspek'] as $aspek): ?>
                                            <div class="row align-items-center mb-2">
                                                <div class="col-6">
                                                    <small class="text-muted"><?= $aspek['aspek_penilaian'] ?></small>
                                                </div>
                                                <div class="col-3">
                                                    <small class="text-muted">Bobot: <?= $aspek['bobot'] ?>%</small>
                                                </div>
                                                <div class="col-3 text-end">
                                                    <strong><?= number_format($aspek['nilai_aspek'], 2) ?></strong>
                                                </div>
                                            </div>
                                            <div class="progress progress-custom mb-3">
                                                <div class="progress-bar bg-info" 
                                                    style="width: <?= min(100, ($aspek['nilai_aspek']/$aspek['bobot'])*100) ?>%"
                                                    title="Nilai: <?= $aspek['nilai_aspek'] ?> dari bobot <?= $aspek['bobot'] ?>">
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php else: ?>
                                        <!-- Fallback ke nilai lama jika detail tidak ada -->
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <small class="text-muted d-block">Presentasi</small>
                                                <strong><?= number_format($penilaian['nilai_presentasi'] ?? 0, 2) ?></strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Materi</small>
                                                <strong><?= number_format($penilaian['nilai_materi'] ?? 0, 2) ?></strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Diskusi</small>
                                                <strong><?= number_format($penilaian['nilai_diskusi'] ?? 0, 2) ?></strong>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Catatan -->
                                        <?php if ($penilaian['catatan']): ?>
                                        <div class="mt-3">
                                            <small class="text-muted d-block">Catatan:</small>
                                            <div class="bg-light p-2 rounded small">
                                                <?= nl2br(htmlspecialchars($penilaian['catatan'])) ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div style="font-size: 4rem; opacity: 0.5; margin-bottom: 1rem;">📭</div>
                                <h5 class="text-muted">Belum ada penilaian</h5>
                                <p class="text-muted">Nilai akan muncul setelah dosen selesai melakukan penilaian</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="lihat_nilai.php" class="btn btn-outline-primary me-2">
                    ← Kembali ke Daftar Nilai
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    🖨️ Cetak Detail Nilai
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Animasi untuk cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card-detail');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 200);
    });
});

// Print styling
function printDetail() {
    window.print();
}
</script>

<style>
@media print {
    .sidebar, .main-content .container-fluid > .row:first-child,
    .main-content .container-fluid > .row:last-child {
        display: none !important;
    }
    
    .card-detail {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .btn {
        display: none !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
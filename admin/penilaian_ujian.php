<?php
/**
 * File: admin/penilaian_ujian.php
 * Sistem penilaian ujian untuk admin, penguji, dan promotor
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin(); // Atau require_dosen() untuk penguji

$page_title = "Sistem Penilaian Ujian - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_admin.php';

// Handle penilaian
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_nilai'])) {
    $id_registrasi = $_POST['id_registrasi'];
    $id_dosen = $_POST['id_dosen'];
    $jenis_nilai = $_POST['jenis_nilai'];
    $nilai_presentasi = $_POST['nilai_presentasi'];
    $nilai_materi = $_POST['nilai_materi'];
    $nilai_diskusi = $_POST['nilai_diskusi'];
    $catatan = clean_input($_POST['catatan']);
    
    $nilai_total = ($nilai_presentasi + $nilai_materi + $nilai_diskusi) / 3;
    
    $sql = "INSERT INTO penilaian_ujian (id_registrasi, id_dosen, jenis_nilai, nilai_presentasi, nilai_materi, nilai_diskusi, nilai_total, catatan) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisdddds", $id_registrasi, $id_dosen, $jenis_nilai, $nilai_presentasi, $nilai_materi, $nilai_diskusi, $nilai_total, $catatan);
    
    if ($stmt->execute()) {
        $success = "Penilaian berhasil disimpan!";
    } else {
        $error = "Gagal menyimpan penilaian: " . $stmt->error;
    }
}

// Ambil data ujian yang perlu dinilai
$sql = "SELECT r.*, m.nama_lengkap, m.nim, m.program_studi, 
               j.tanggal_ujian, j.tempat, j.status as status_jadwal
        FROM registrasi r 
        JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
        LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi 
        WHERE r.status = 'Diterima' 
        ORDER BY r.tanggal_pengajuan DESC";
$result = db_query($sql);
$ujian_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ujian_list[] = $row;
}
?>
<link href="../assets/css/admin-styles.css" rel="stylesheet">

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📊 Informasi Tanggal Ujian</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <!-- Filter Jenis Ujian -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Filter Jenis Ujian:</label>
                                <select class="form-select" onchange="window.location.href='?jenis_ujian='+this.value">
                                    <option value="">Semua Jenis Ujian</option>
                                    <option value="kelayakan" <?= (isset($_GET['jenis_ujian']) && $_GET['jenis_ujian'] == 'kelayakan') ? 'selected' : '' ?>>Ujian Kelayakan</option>
                                    <option value="proposal" <?= (isset($_GET['jenis_ujian']) && $_GET['jenis_ujian'] == 'proposal') ? 'selected' : '' ?>>Ujian Proposal</option>
                                    <option value="kualifikasi" <?= (isset($_GET['jenis_ujian']) && $_GET['jenis_ujian'] == 'kualifikasi') ? 'selected' : '' ?>>Ujian Kualifikasi</option>
                                    <option value="tertutup" <?= (isset($_GET['jenis_ujian']) && $_GET['jenis_ujian'] == 'tertutup') ? 'selected' : '' ?>>Ujian Tertutup</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mahasiswa</th>
                                        <th>NIM</th>
                                        <th>Jenis Ujian</th>
                                        <th>Judul Disertasi</th>
                                        <th>Tanggal Ujian</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ujian_list as $ujian): 
                                        // Filter berdasarkan jenis ujian jika dipilih
                                        if (isset($_GET['jenis_ujian']) && $_GET['jenis_ujian'] != '' && $ujian['jenis_ujian'] != $_GET['jenis_ujian']) {
                                            continue;
                                        }
                                        
                                        // Tentukan form penilaian berdasarkan jenis ujian
                                        $form_penilaian = '';
                                        switch ($ujian['jenis_ujian']) {
                                            case 'proposal':
                                                $form_penilaian = 'form_penilaian.php';
                                                break;
                                            case 'kualifikasi':
                                                $form_penilaian = 'form_penilaian_kualifikasi.php';
                                                break;
                                            case 'kelayakan':
                                                $form_penilaian = 'form_penilaian_kelayakan.php';
                                                break;
                                            case 'tertutup':
                                                $form_penilaian = 'form_penilaian_tertutup.php';
                                                break;
                                            default:
                                                $form_penilaian = 'form_penilaian.php';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $ujian['nama_lengkap'] ?></td>
                                            <td><?= $ujian['nim'] ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= ucfirst(str_replace('_', ' ', $ujian['jenis_ujian'])) ?>
                                                </span>
                                            </td>
                                            <td><?= $ujian['judul_disertasi'] ?></td>
                                            <td><?= $ujian['tanggal_ujian'] ? date('d/m/Y H:i', strtotime($ujian['tanggal_ujian'])) : 'Belum dijadwalkan' ?></td>
                                            <td>
                                                <span class="badge <?= $ujian['status_jadwal'] == 'selesai' ? 'bg-success' : 'bg-warning' ?>">
                                                    <?= $ujian['status_jadwal'] ? ucfirst($ujian['status_jadwal']) : 'Terjadwal' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <!-- Tombol Beri Nilai dengan routing yang sesuai -->
                                                    <!-- <a href="../penguji/<?= $form_penilaian ?>?id=<?= $ujian['id_registrasi'] ?>" class="btn btn-sm btn-primary">
                                                        Beri Nilai
                                                    </a> -->
                                                    
                                                    <!-- Tombol khusus untuk form kelayakan -->
                                                    <!-- <?php if ($ujian['jenis_ujian'] == 'kelayakan'): ?>
                                                        <a href="../penguji/form_penilaian_kelayakan.php?id=<?= $ujian['id_registrasi'] ?>" class="btn btn-sm btn-success">
                                                            Form Kelayakan
                                                        </a>
                                                    <?php endif; ?> -->
                                                    
                                                    <!-- Tombol untuk melihat detail -->
                                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $ujian['id_registrasi'] ?>">
                                                        Detail
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Modal Detail Ujian -->
                                        <div class="modal fade" id="modalDetail<?= $ujian['id_registrasi'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Ujian <?= ucfirst(str_replace('_', ' ', $ujian['jenis_ujian'])) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Informasi Mahasiswa</h6>
                                                                <p><strong>Nama:</strong> <?= $ujian['nama_lengkap'] ?></p>
                                                                <p><strong>NIM:</strong> <?= $ujian['nim'] ?></p>
                                                                <p><strong>Program Studi:</strong> <?= $ujian['program_studi'] ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Informasi Ujian</h6>
                                                                <p><strong>Jenis Ujian:</strong> <?= ucfirst(str_replace('_', ' ', $ujian['jenis_ujian'])) ?></p>
                                                                <p><strong>Tanggal Ujian:</strong> <?= $ujian['tanggal_ujian'] ? date('d/m/Y H:i', strtotime($ujian['tanggal_ujian'])) : 'Belum dijadwalkan' ?></p>
                                                                <p><strong>Tempat:</strong> <?= $ujian['tempat'] ?: 'Belum ditentukan' ?></p>
                                                                <p><strong>Status:</strong> 
                                                                    <span class="badge <?= $ujian['status_jadwal'] == 'selesai' ? 'bg-success' : 'bg-warning' ?>">
                                                                        <?= $ujian['status_jadwal'] ? ucfirst($ujian['status_jadwal']) : 'Terjadwal' ?>
                                                                    </span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-12">
                                                                <h6>Judul Disertasi</h6>
                                                                <p><?= $ujian['judul_disertasi'] ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        <a href="../penguji/<?= $form_penilaian ?>?id=<?= $ujian['id_registrasi'] ?>" class="btn btn-primary">
                                                            Lanjut ke Penilaian
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($ujian_list)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fas fa-info-circle"></i> Tidak ada data ujian yang perlu dinilai
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php
/**
 * File: admin/detail_monev.php
 * Detail monitoring evaluasi mahasiswa
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: monev.php');
    exit();
}

$id_monev = (int)$_GET['id'];

// Query untuk mendapatkan data monev
$query_monev = "SELECT mv.*, m.nama_lengkap, m.nim, m.program_studi, m.email, m.angkatan
               FROM monev mv 
               JOIN mahasiswa m ON mv.id_mahasiswa = m.id_mahasiswa 
               WHERE mv.id_monev = $id_monev";
$result_monev = mysqli_query($conn, $query_monev);

if (mysqli_num_rows($result_monev) === 0) {
    header('Location: monev.php');
    exit();
}

$monev = mysqli_fetch_assoc($result_monev);

// Decode JSON deskripsi
$deskripsi_data = [];
if (!empty($monev['deskripsi'])) {
    $deskripsi_data = json_decode($monev['deskripsi'], true);
}

$page_title = "Detail Monitoring Evaluasi - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 bg-success text-white">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-2">
                                        <li class="breadcrumb-item"><a href="monev.php" class="text-white-50">Monitoring Evaluasi</a></li>
                                        <li class="breadcrumb-item active text-white">Detail Monev</li>
                                    </ol>
                                </nav>
                                <h3 class="mb-2"><i class="bi bi-clipboard-data me-2"></i>Detail Monitoring Evaluasi</h3>
                                <p class="mb-0">Laporan periode: <?php echo htmlspecialchars($monev['periode']); ?></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center p-3">
                                    <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informasi Mahasiswa -->
            <div class="col-md-4 mb-4">
                <div class="card border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Informasi Mahasiswa</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-5 fw-bold">Nama</div>
                            <div class="col-sm-7"><?php echo htmlspecialchars($monev['nama_lengkap']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5 fw-bold">NIM</div>
                            <div class="col-sm-7">
                                <code><?php echo htmlspecialchars($monev['nim']); ?></code>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5 fw-bold">Program Studi</div>
                            <div class="col-sm-7">
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <?php echo htmlspecialchars($monev['program_studi']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5 fw-bold">Angkatan</div>
                            <div class="col-sm-7">
                                <?php echo !empty($monev['angkatan']) ? htmlspecialchars($monev['angkatan']) : '<span class="text-muted">-</span>'; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 fw-bold">Email</div>
                            <div class="col-sm-7">
                                <small><?php echo htmlspecialchars($monev['email']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Laporan -->
            <div class="col-md-8 mb-4">
                <div class="card border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Informasi Laporan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold">Periode</div>
                            <div class="col-sm-9">
                                <span class="badge bg-success"><?php echo htmlspecialchars($monev['periode']); ?></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold">Tanggal Upload</div>
                            <div class="col-sm-9">
                                <i class="bi bi-calendar me-1"></i>
                                <?php echo date('d F Y H:i', strtotime($monev['tanggal_upload'])); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold">Status</div>
                            <div class="col-sm-9">
                                <?php if (!empty($monev['status'])): ?>
                                    <span class="badge bg-<?php 
                                        switch($monev['status']) {
                                            case 'disetujui': echo 'success'; break;
                                            case 'ditolak': echo 'danger'; break;
                                            case 'revisi': echo 'warning'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst($monev['status']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum Ditinjau</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deskripsi Laporan dalam Tabel -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Detail Laporan Kemajuan Penelitian</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($deskripsi_data) && is_array($deskripsi_data)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 10%;" class="text-center">No</th>
                                            <th style="width: 25%;">Pertanyaan</th>
                                            <th style="width: 65%;">Jawaban Mahasiswa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Question 1 -->
                                        <tr>
                                            <td class="text-center fw-bold align-middle">I</td>
                                            <td class="fw-semibold align-middle">
                                                Jabarkan kegiatan yang sudah dapat direalisasikan (4 bulan yang lalu).
                                            </td>
                                            <td>
                                                <div class="answer-content">
                                                    <?php if (!empty($deskripsi_data['jawaban1'])): ?>
                                                        <?php echo nl2br(htmlspecialchars($deskripsi_data['jawaban1'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">Tidak ada jawaban</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Question 2 -->
                                        <tr>
                                            <td class="text-center fw-bold align-middle">II</td>
                                            <td class="fw-semibold align-middle">
                                                Kegiatan yang seharusnya selesai, tetapi tidak/belum dapat direalisasikan. 
                                                Sebutkan hambatannya dan rencana penyelesaiannya.
                                            </td>
                                            <td>
                                                <div class="answer-content">
                                                    <?php if (!empty($deskripsi_data['jawaban2'])): ?>
                                                        <?php echo nl2br(htmlspecialchars($deskripsi_data['jawaban2'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">Tidak ada jawaban</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Question 3 -->
                                        <tr>
                                            <td class="text-center fw-bold align-middle">III</td>
                                            <td class="fw-semibold align-middle">
                                                Kegiatan penelitian yang direncanakan dalam 4 bulan yang akan datang.
                                            </td>
                                            <td>
                                                <div class="answer-content">
                                                    <?php if (!empty($deskripsi_data['jawaban3'])): ?>
                                                        <?php echo nl2br(htmlspecialchars($deskripsi_data['jawaban3'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">Tidak ada jawaban</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Question 4 -->
                                        <tr>
                                            <td class="text-center fw-bold align-middle">IV</td>
                                            <td class="fw-semibold align-middle">
                                                Garis besar kegiatan penelitian selanjutnya.
                                            </td>
                                            <td>
                                                <div class="answer-content">
                                                    <?php if (!empty($deskripsi_data['jawaban4'])): ?>
                                                        <?php echo nl2br(htmlspecialchars($deskripsi_data['jawaban4'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">Tidak ada jawaban</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Question 5 -->
                                        <tr>
                                            <td class="text-center fw-bold align-middle">V</td>
                                            <td class="fw-semibold align-middle">
                                                Saran/masukan/komentar untuk perbaikan (dari reviewer).
                                                <small class="text-muted d-block mt-1">
                                                    Dari hasil klarifikasi paparan mahasiswa dan mencermati poin I s/d IV di atas, 
                                                    reviewer memberi masukan, saran, dan komentar.
                                                </small>
                                            </td>
                                            <td>
                                                <div class="answer-content">
                                                    <?php if (!empty($deskripsi_data['jawaban5'])): ?>
                                                        <?php echo nl2br(htmlspecialchars($deskripsi_data['jawaban5'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">Belum ada saran dari reviewer</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-journal-x text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">Tidak ada data laporan yang tersedia</p>
                                <small class="text-muted">Data deskripsi laporan tidak ditemukan atau format tidak valid</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan Reviewer -->
        <?php if (!empty($monev['catatan_reviewer'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning bg-opacity-10 py-3">
                        <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Catatan Reviewer</h5>
                    </div>
                    <div class="card-body">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <?php echo nl2br(htmlspecialchars($monev['catatan_reviewer'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="monev.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $monev['id_monev']; ?>)">
                            <i class="bi bi-trash me-1"></i>Hapus Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Catatan Reviewer -->
<div class="modal fade" id="catatanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Beri Catatan Reviewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="update_catatan.php">
                <div class="modal-body">
                    <input type="hidden" name="id_monev" value="<?php echo $monev['id_monev']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Catatan untuk Mahasiswa</label>
                        <textarea name="catatan_reviewer" class="form-control" rows="5" 
                                  placeholder="Masukkan catatan, saran, atau masukan untuk perbaikan laporan..."><?php 
                            echo !empty($monev['catatan_reviewer']) ? htmlspecialchars($monev['catatan_reviewer']) : ''; 
                        ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.answer-content {
    max-height: 200px;
    overflow-y: auto;
    padding: 8px;
    line-height: 1.6;
    font-size: 0.95rem;
}

.answer-content::-webkit-scrollbar {
    width: 6px;
}

.answer-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.answer-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.answer-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.table th {
    background-color: #f8f9fa !important;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}

.table td {
    vertical-align: top;
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
</style>

<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus laporan monitoring evaluasi ini?')) {
        window.location.href = 'delete_monev.php?id=' + id;
    }
}

function setStatus(id, status) {
    if (confirm('Apakah Anda yakin ingin mengubah status laporan menjadi ' + status + '?')) {
        window.location.href = 'update_status.php?id=' + id + '&status=' + status;
    }
}

// Auto-resize textarea di modal
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="catatan_reviewer"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        // Trigger initial resize
        textarea.dispatchEvent(new Event('input'));
    }
});
</script>

<?php include '../includes/footer.php'; ?>
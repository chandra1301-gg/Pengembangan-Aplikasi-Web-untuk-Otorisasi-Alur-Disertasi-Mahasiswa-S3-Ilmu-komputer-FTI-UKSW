<?php
/**
 * File: mahasiswa/lihat_nilai.php
 * Halaman untuk mahasiswa melihat nilai ujian - SUDAH DITAMBAH DATA PROMOTOR & PENGUJI
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];

// **QUERY BARU: Ambil data lengkap termasuk promotor, co-promotor, dan penguji**
$sql = "SELECT r.id_registrasi, r.jenis_ujian, r.judul_disertasi, 
               -- Data dosen pembimbing dari registrasi
               d_promotor.nama_lengkap as nama_promotor,
               d_copromotor.nama_lengkap as nama_copromotor,
               d_copromotor2.nama_lengkap as nama_copromotor2,
               -- Data dosen dari jadwal ujian
               j_promotor.nama_lengkap as nama_promotor_jadwal,
               j_copromotor.nama_lengkap as nama_copromotor_jadwal,
               j_copromotor2.nama_lengkap as nama_copromotor2_jadwal,
               j_penguji1.nama_lengkap as nama_penguji1,
               j_penguji2.nama_lengkap as nama_penguji2,
               j_penguji3.nama_lengkap as nama_penguji3,
               -- Data penilaian
               p.nilai_presentasi, p.nilai_materi, p.nilai_diskusi, 
               p.nilai_total, p.catatan, p.tanggal_penilaian, 
               p_penilai.nama_lengkap as nama_penilai, 
               p.jenis_nilai,
               -- Tentukan peran penilai
               CASE 
                   WHEN p.id_dosen = r.promotor THEN 'Promotor'
                   WHEN p.id_dosen = r.co_promotor THEN 'Co-Promotor'
                   WHEN p.id_dosen = r.co_promotor2 THEN 'Co-Promotor 2'
                   WHEN p.id_dosen = j.penguji_1 THEN 'Penguji 1'
                   WHEN p.id_dosen = j.penguji_2 THEN 'Penguji 2'
                   WHEN p.id_dosen = j.penguji_3 THEN 'Penguji 3'
                   ELSE 'Dosen'
               END as peran_penilai
        FROM registrasi r 
        -- Join untuk dosen pembimbing dari registrasi
        LEFT JOIN dosen d_promotor ON r.promotor = d_promotor.id_dosen
        LEFT JOIN dosen d_copromotor ON r.co_promotor = d_copromotor.id_dosen
        LEFT JOIN dosen d_copromotor2 ON r.co_promotor2 = d_copromotor2.id_dosen
        -- Join untuk jadwal ujian dan dosen penguji
        LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi
        LEFT JOIN dosen j_promotor ON j.promotor = j_promotor.id_dosen
        LEFT JOIN dosen j_copromotor ON j.co_promotor = j_copromotor.id_dosen
        LEFT JOIN dosen j_copromotor2 ON j.co_promotor2 = j_copromotor2.id_dosen
        LEFT JOIN dosen j_penguji1 ON j.penguji_1 = j_penguji1.id_dosen
        LEFT JOIN dosen j_penguji2 ON j.penguji_2 = j_penguji2.id_dosen
        LEFT JOIN dosen j_penguji3 ON j.penguji_3 = j_penguji3.id_dosen
        -- Join untuk penilaian
        LEFT JOIN penilaian_ujian p ON r.id_registrasi = p.id_registrasi 
        LEFT JOIN dosen p_penilai ON p.id_dosen = p_penilai.id_dosen 
        WHERE r.id_mahasiswa = ? AND r.status = 'Diterima' 
        ORDER BY r.tanggal_pengajuan DESC, p.tanggal_penilaian DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();

$nilai_list = [];
while ($row = $result->fetch_assoc()) {
    $nilai_list[] = $row;
}

// **FUNGSI BARU: Ambil tim dosen lengkap untuk registrasi tertentu**
function getTimDosenRegistrasi($conn, $id_registrasi) {
    $sql = "SELECT 
                -- Data dari registrasi
                r.promotor, r.co_promotor, r.co_promotor2,
                d_promotor.nama_lengkap as nama_promotor,
                d_copromotor.nama_lengkap as nama_copromotor,
                d_copromotor2.nama_lengkap as nama_copromotor2,
                -- Data dari jadwal ujian
                j.promotor as promotor_jadwal, 
                j.co_promotor, j.co_promotor2,
                j.penguji_1, j.penguji_2, j.penguji_3,
                j_promotor.nama_lengkap as nama_promotor_jadwal,
                j_copromotor.nama_lengkap as nama_copromotor_jadwal,
                j_copromotor2.nama_lengkap as nama_copromotor2_jadwal,
                j_penguji1.nama_lengkap as nama_penguji1,
                j_penguji2.nama_lengkap as nama_penguji2,
                j_penguji3.nama_lengkap as nama_penguji3
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
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_registrasi);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$page_title = "Lihat Nilai - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_mahasiswa.php';
?>

<style>
.card-tim-dosen {
    border-left: 4px solid #5495FF;
    background: #f8f9fa;
}

.badge-peran {
    font-size: 0.75rem;
    padding: 4px 8px;
}

.tim-dosen-item {
    padding: 8px 12px;
    margin-bottom: 6px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.nilai-highlight {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 8px;
}

.nilai-card {
    transition: transform 0.2s ease;
    border: none;
    border-radius: 10px;
}

.nilai-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #0d6efd;
}
</style>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">📊 Nilai Ujian Saya</h4>
                        <span class="badge bg-light text-primary"><?= count($nilai_list) ?> Ujian</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($nilai_list)): ?>
                            <div class="alert alert-info text-center py-4">
                                <div style="font-size: 3rem; opacity: 0.5; margin-bottom: 1rem;">📭</div>
                                <h5>Belum ada nilai ujian yang tersedia</h5>
                                <p class="mb-0">Nilai akan muncul setelah ujian dinilai oleh dosen</p>
                            </div>
                        <?php else: ?>
                            <div class="accordion" id="accordionNilai">
                                <?php 
                                $registrasi_grouped = [];
                                // Group by id_registrasi untuk menampilkan per ujian
                                foreach ($nilai_list as $nilai) {
                                    $id_reg = $nilai['id_registrasi'];
                                    if (!isset($registrasi_grouped[$id_reg])) {
                                        $registrasi_grouped[$id_reg] = [
                                            'info' => $nilai,
                                            'penilaian' => []
                                        ];
                                    }
                                    if ($nilai['nama_penilai']) {
                                        $registrasi_grouped[$id_reg]['penilaian'][] = $nilai;
                                    }
                                }
                                
                                $accordion_count = 0;
                                foreach ($registrasi_grouped as $id_registrasi => $data): 
                                    $info = $data['info'];
                                    $penilaian_list = $data['penilaian'];
                                    $accordion_count++;
                                    
                                    // Ambil tim dosen lengkap
                                    $tim_dosen = getTimDosenRegistrasi($conn, $id_registrasi);
                                ?>
                                <div class="card mb-3 nilai-card">
                                    <div class="card-header" id="heading<?= $accordion_count ?>">
                                        <h5 class="mb-0">
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <button class="btn btn-link text-decoration-none text-start flex-grow-1" 
                                                        type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#collapse<?= $accordion_count ?>" 
                                                        aria-expanded="false" 
                                                        aria-controls="collapse<?= $accordion_count ?>">
                                                    <span>
                                                        <strong><?= strtoupper($info['jenis_ujian']) ?></strong> - 
                                                        <?= $info['judul_disertasi'] ?>
                                                    </span>
                                                </button>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2">
                                                        <?= count($penilaian_list) ?> Penilaian
                                                    </span>
                                                    <!-- Tombol Lihat Detail -->
                                                    <a href="detail_nilai.php?id=<?= $id_registrasi ?>" 
                                                    class="btn btn-sm btn-outline-primary me-2"
                                                    title="Lihat detail lengkap nilai">
                                                        📊 Detail
                                                    </a>
                                                </div>
                                            </div>
                                        </h5>
                                    </div>

                                    <div id="collapse<?= $accordion_count ?>" class="collapse" 
                                         aria-labelledby="heading<?= $accordion_count ?>" 
                                         data-bs-parent="#accordionNilai">
                                        <div class="card-body">
                                            <!-- Tim Dosen -->
                                            <div class="row mb-4">
                                                <div class="col-12">
                                                    <div class="card card-tim-dosen">
                                                        <div class="card-header bg-transparent">
                                                            <h6 class="mb-0">👨‍🏫 Tim Dosen Penguji</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <!-- Dosen Pembimbing -->
                                                                <div class="col-md-6 mb-3">
                                                                    <h6 class="text-primary">Dosen Pembimbing</h6>
                                                                    <?php if ($tim_dosen['nama_promotor']): ?>
                                                                    <div class="tim-dosen-item">
                                                                        <strong><?= $tim_dosen['nama_promotor'] ?></strong>
                                                                        <span class="badge badge-peran bg-info ms-2">Promotor</span>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if ($tim_dosen['nama_copromotor']): ?>
                                                                    <div class="tim-dosen-item">
                                                                        <strong><?= $tim_dosen['nama_copromotor'] ?></strong>
                                                                        <span class="badge badge-peran bg-primary ms-2">Co-Promotor</span>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if ($tim_dosen['nama_copromotor2']): ?>
                                                                    <div class="tim-dosen-item">
                                                                        <strong><?= $tim_dosen['nama_copromotor2'] ?></strong>
                                                                        <span class="badge badge-peran bg-secondary ms-2">Co-Promotor 2</span>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                
                                                                <!-- Dosen Penguji -->
                                                                <div class="col-md-6 mb-3">
                                                                    <h6 class="text-primary">Dosen Penguji</h6>
                                                                    <?php if ($tim_dosen['nama_penguji1']): ?>
                                                                    <div class="tim-dosen-item">
                                                                        <strong><?= $tim_dosen['nama_penguji1'] ?></strong>
                                                                        <span class="badge badge-peran bg-warning ms-2">Penguji 1</span>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if ($tim_dosen['nama_penguji2']): ?>
                                                                    <div class="tim-dosen-item">
                                                                        <strong><?= $tim_dosen['nama_penguji2'] ?></strong>
                                                                        <span class="badge badge-peran bg-warning ms-2">Penguji 2</span>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if ($tim_dosen['nama_penguji3']): ?>
                                                                    <div class="tim-dosen-item">
                                                                        <strong><?= $tim_dosen['nama_penguji3'] ?></strong>
                                                                        <span class="badge badge-peran bg-warning ms-2">Penguji 3</span>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if (!$tim_dosen['nama_penguji1'] && !$tim_dosen['nama_penguji2'] && !$tim_dosen['nama_penguji3']): ?>
                                                                    <div class="text-muted">
                                                                        Belum ada penguji yang ditetapkan
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Daftar Penilaian -->
                                            <h6 class="mb-3">📋 Detail Penilaian</h6>
                                            <?php if (!empty($penilaian_list)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Penilai</th>
                                                                <th>Peran</th>
                                                                <th>Presentasi</th>
                                                                <th>Materi</th>
                                                                <th>Diskusi</th>
                                                                <th>Nilai Total</th>
                                                                <th>Catatan</th>
                                                                <th>Tanggal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php 
                                                            $total_nilai = 0;
                                                            $jumlah_penilai = 0;
                                                            foreach ($penilaian_list as $penilaian): 
                                                                if ($penilaian['nilai_total']) {
                                                                    $total_nilai += $penilaian['nilai_total'];
                                                                    $jumlah_penilai++;
                                                                }
                                                            ?>
                                                                <tr>
                                                                    <td>
                                                                        <strong><?= $penilaian['nama_penilai'] ?: '-' ?></strong>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge <?= $penilaian['peran_penilai'] == 'Promotor' ? 'bg-info' : 
                                                                                           ($penilaian['peran_penilai'] == 'Co-Promotor 1' ? 'bg-primary' :
                                                                                           ($penilaian['peran_penilai'] == 'Co-Promotor 2' ? 'bg-secondary' : 'bg-warning')) ?>">
                                                                            <?= $penilaian['peran_penilai'] ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?= $penilaian['nilai_presentasi'] ? number_format($penilaian['nilai_presentasi'], 2) : '-' ?></td>
                                                                    <td><?= $penilaian['nilai_materi'] ? number_format($penilaian['nilai_materi'], 2) : '-' ?></td>
                                                                    <td><?= $penilaian['nilai_diskusi'] ? number_format($penilaian['nilai_diskusi'], 2) : '-' ?></td>
                                                                    <td>
                                                                        <strong><?= $penilaian['nilai_total'] ? number_format($penilaian['nilai_total'], 2) : '-' ?></strong>
                                                                    </td>
                                                                    <td><?= $penilaian['catatan'] ?: '-' ?></td>
                                                                    <td><?= $penilaian['tanggal_penilaian'] ? date('d/m/Y', strtotime($penilaian['tanggal_penilaian'])) : '-' ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Ringkasan Nilai -->
                                                <?php if ($jumlah_penilai > 0): 
                                                    $rata_rata = $total_nilai / $jumlah_penilai;
                                                    $grade = $rata_rata > 85 ? 'A' : ($rata_rata > 80 ? 'AB' : ($rata_rata >= 70 ? 'B' : 'T'));
                                                    $grade_color = $rata_rata >= 70 ? 'success' : 'danger';
                                                ?>
                                                <?php if ($jumlah_penilai > 0): ?>
                                                <div class="row mt-4">
                                                    <div class="col-12 text-center">
                                                        <a href="detail_nilai.php?id=<?= $id_registrasi ?>" 
                                                        class="btn btn-primary btn-lg">
                                                            📈 Lihat Detail Lengkap Penilaian
                                                        </a>
                                                        <p class="text-muted mt-2 small">
                                                            Tampilkan visualisasi grafik, statistik detail, dan breakdown penilaian per dosen
                                                        </p>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <div class="row mt-4">
                                                    <div class="col-md-6">
                                                        <div class="card bg-light">
                                                            <div class="card-body text-center">
                                                                <h6>Rata-rata Nilai</h6>
                                                                <div class="display-4 text-<?= $grade_color ?>">
                                                                    <?= number_format($rata_rata, 2) ?>
                                                                </div>
                                                                <span class="badge bg-<?= $grade_color ?>">
                                                                    Grade: <?= $grade ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card bg-light">
                                                            <div class="card-body text-center">
                                                                <h6>Status Kelulusan</h6>
                                                                <div class="display-4 text-<?= $rata_rata >= 70 ? 'success' : 'warning' ?>">
                                                                    <?= $rata_rata >= 70 ? '✅' : '⏳' ?>
                                                                </div>
                                                                <span class="badge bg-<?= $rata_rata >= 70 ? 'success' : 'warning' ?>">
                                                                    <?= $rata_rata >= 70 ? 'LULUS' : 'BELUM LULUS' ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="alert alert-warning text-center py-3">
                                                    <div style="font-size: 2rem; margin-bottom: 1rem;">⏳</div>
                                                    <p class="mb-0">Belum ada penilaian dari dosen untuk ujian ini</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto expand first accordion jika hanya ada satu ujian
document.addEventListener('DOMContentLoaded', function() {
    const accordionItems = document.querySelectorAll('.accordion .card');
    if (accordionItems.length === 1) {
        const firstCollapse = document.querySelector('.accordion .collapse');
        if (firstCollapse) {
            new bootstrap.Collapse(firstCollapse, { toggle: true });
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
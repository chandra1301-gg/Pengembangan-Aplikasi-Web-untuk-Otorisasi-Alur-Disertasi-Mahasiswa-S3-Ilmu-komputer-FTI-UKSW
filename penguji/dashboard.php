<?php
/**
 * File: dosen/dashboard.php
 * Dashboard khusus untuk dosen - VERSI FINAL YANG PASTI BERHASIL
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_dosen();

$user_id = $_SESSION['user_id'];

// Ambil data dosen lengkap
$sql_dosen = "SELECT d.*, u.username 
              FROM dosen d 
              JOIN users u ON d.user_id = u.id 
              WHERE d.user_id = ?";
$stmt_dosen = $conn->prepare($sql_dosen);
$stmt_dosen->bind_param("i", $user_id);
$stmt_dosen->execute();
$result_dosen = $stmt_dosen->get_result();
$dosen = $result_dosen->fetch_assoc();

if (!$dosen) {
    die("Data dosen tidak ditemukan.");
}

$id_dosen = $dosen['id_dosen'];

// **STATISTIK SEDERHANA YANG PASTI BERHASIL**
$stats = [
    'menunggu_penilaian' => 0,
    'sudah_dinilai' => 0,
    'revisi_diajukan' => 0,
    'total_ujian' => 0,
    'sebagai_promotor' => 0,
    'sebagai_co_promotor' => 0,
    'sebagai_co_promotor2' => 0,
    'sebagai_penguji' => 0
];

// 1. Hitung total ujian yang ditugaskan (QUERY SEDERHANA)
$sql_total = "SELECT COUNT(DISTINCT r.id_registrasi) as total
              FROM registrasi r
              LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi
              WHERE r.status = 'Diterima'
              AND (r.promotor = ? OR r.co_promotor = ? OR  r.co_promotor2 = ? OR
                   j.promotor = ? OR j.penguji_1 = ? OR j.penguji_2 = ? OR j.penguji_3 = ?)";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("iiiiiii", $id_dosen, $id_dosen, $id_dosen, $id_dosen, $id_dosen, $id_dosen, $id_dosen);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_data = $result_total->fetch_assoc();
$stats['total_ujian'] = $total_data['total'] ?? 0;

// 2. Hitung sudah dinilai
$sql_dinilai = "SELECT COUNT(DISTINCT p.id_penilaian) as total
                FROM penilaian_ujian p
                JOIN registrasi r ON p.id_registrasi = r.id_registrasi
                WHERE p.id_dosen = ? AND r.status = 'Diterima'";
$stmt_dinilai = $conn->prepare($sql_dinilai);
$stmt_dinilai->bind_param("i", $id_dosen);
$stmt_dinilai->execute();
$result_dinilai = $stmt_dinilai->get_result();
$dinilai_data = $result_dinilai->fetch_assoc();
$stats['sudah_dinilai'] = $dinilai_data['total'] ?? 0;

// 3. Hitung menunggu penilaian
$stats['menunggu_penilaian'] = $stats['total_ujian'] - $stats['sudah_dinilai'];

// 4. Hitung revisi diajukan
$sql_revisi = "SELECT COUNT(DISTINCT rev.id_revisi) as total
               FROM revisi_disertasi rev
               JOIN penilaian_ujian p ON rev.id_penilaian = p.id_penilaian
               WHERE p.id_dosen = ? AND rev.status IN ('dikirim', 'diajukan', 'menunggu')";
$stmt_revisi = $conn->prepare($sql_revisi);
$stmt_revisi->bind_param("i", $id_dosen);
$stmt_revisi->execute();
$result_revisi = $stmt_revisi->get_result();
$revisi_data = $result_revisi->fetch_assoc();
$stats['revisi_diajukan'] = $revisi_data['total'] ?? 0;

// 5. Hitung sebagai promotor
$sql_promotor = "SELECT COUNT(DISTINCT r.id_registrasi) as total
                 FROM registrasi r
                 WHERE r.status = 'Diterima' AND r.promotor = ?" ;
$stmt_promotor = $conn->prepare($sql_promotor);
$stmt_promotor->bind_param("i", $id_dosen);
$stmt_promotor->execute();
$result_promotor = $stmt_promotor->get_result();
$promotor_data = $result_promotor->fetch_assoc();
$stats['sebagai_promotor'] = $promotor_data['total'] ?? 0;

// 6. Hitung sebagai co-promotor
$sql_co_promotor = "SELECT COUNT(DISTINCT r.id_registrasi) as total
                    FROM registrasi r
                    WHERE r.status = 'Diterima' AND r.co_promotor = ?";
$stmt_co_promotor = $conn->prepare($sql_co_promotor);
$stmt_co_promotor->bind_param("i", $id_dosen);
$stmt_co_promotor->execute();
$result_co_promotor = $stmt_co_promotor->get_result();
$co_promotor_data = $result_co_promotor->fetch_assoc();
$stats['sebagai_co_promotor'] = $co_promotor_data['total'] ?? 0;

// 7. Hitung sebagai co-promotor 2  
$sql_co_promotor2 = "SELECT COUNT(DISTINCT r.id_registrasi) as total
                    FROM registrasi r
                    WHERE r.status = 'Diterima' AND r.co_promotor2 = ?";
$stmt_co_promotor2 = $conn->prepare($sql_co_promotor2);
$stmt_co_promotor2->bind_param("i", $id_dosen);
$stmt_co_promotor2->execute();
$result_co_promotor2 = $stmt_co_promotor2->get_result();
$co_promotor2_data = $result_co_promotor2->fetch_assoc();
$stats['sebagai_co_promotor2'] = $co_promotor2_data['total'] ?? 0;

// 7. Hitung sebagai penguji
$sql_penguji = "SELECT COUNT(DISTINCT r.id_registrasi) as total
                FROM registrasi r
                LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi
                WHERE r.status = 'Diterima'
                AND (j.penguji_1 = ? OR j.penguji_2 = ? OR j.penguji_3 = ?)";
$stmt_penguji = $conn->prepare($sql_penguji);
$stmt_penguji->bind_param("iii", $id_dosen, $id_dosen, $id_dosen);
$stmt_penguji->execute();
$result_penguji = $stmt_penguji->get_result();
$penguji_data = $result_penguji->fetch_assoc();
$stats['sebagai_penguji'] = $penguji_data['total'] ?? 0;

// **QUERY RECENT UJIAN YANG SANGAT SEDERHANA** - PERBAIKI INI
$sql_recent = "SELECT r.id_registrasi, r.jenis_ujian, r.judul_disertasi,
                      m.nama_lengkap, m.nim, m.program_studi,
                      j.tanggal_ujian, j.tempat,
                      p.id_penilaian, p.nilai_total
               FROM registrasi r
               JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
               LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi
               LEFT JOIN penilaian_ujian p ON (r.id_registrasi = p.id_registrasi AND p.id_dosen = ?)
               WHERE r.status = 'Diterima'
               AND (
                   r.promotor = ? OR r.co_promotor = ? OR r.co_promotor2 = ? OR
                   j.promotor = ? OR j.penguji_1 = ? OR j.penguji_2 = ? OR j.penguji_3 = ?
               )
               ORDER BY COALESCE(j.tanggal_ujian, r.tanggal_pengajuan) DESC
               LIMIT 5";

$stmt_recent = $conn->prepare($sql_recent);
if (!$stmt_recent) {
    die("Error preparing recent query: " . $conn->error);
}

// **7 PARAMETER UNTUK 7 TANDA TANYA** - SESUAI DENGAN QUERY DI ATAS
$stmt_recent->bind_param("iiiiiiii", 
    $id_dosen,  // 1. penilaian
    $id_dosen,  // 2. promotor registrasi
    $id_dosen,  // 3. co-promotor registrasi
    $id_dosen,  // 4. co-promotor2 registrasi
    $id_dosen,  // 5. promotor jadwal
    $id_dosen,  // 6. penguji 1
    $id_dosen,  // 7. penguji 2
    $id_dosen   // 8. penguji 3
);

$stmt_recent->execute();
$recent_ujian = $stmt_recent->get_result();

$page_title = "Dashboard Dosen - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_penguji.php';
?>

<style>
.card-stat {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
    border-radius: 12px;
}

.card-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.stat-link {
    font-size: 0.85rem;
    opacity: 0.8;
    transition: opacity 0.2s ease;
}

.stat-link:hover {
    opacity: 1;
    text-decoration: none;
}

.quick-action-btn {
    transition: all 0.3s ease;
    border: none;
    border-radius: 10px;
    padding: 20px 15px;
    font-weight: 600;
    text-decoration: none;
    display: block;
}

.quick-action-btn:hover {
    transform: translateY(-3px);
    text-decoration: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.recent-ujian-item {
    border-left: 4px solid #5495FF;
    transition: all 0.2s ease;
}

.recent-ujian-item:hover {
    background-color: #f8f9fa;
    border-left-color: #3D7FE8;
}

.badge-peran {
    font-size: 0.75rem;
    padding: 4px 8px;
}

.welcome-card {
    background: linear-gradient(135deg, #5495FF 0%, #3D7FE8 100%);
    border: none;
    border-radius: 15px;
    color: white;
}

.welcome-card h3 {
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.role-badge {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 0.85rem;
    margin-right: 8px;
    margin-bottom: 8px;
    display: inline-block;
}
</style>

<div class="main-content">
    <div class="container-fluid">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card welcome-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3>Selamat Datang, <?= htmlspecialchars($dosen['nama_lengkap']) ?>! 👨‍🏫</h3>
                                <p class="mb-2"><?= htmlspecialchars($dosen['bidang_keahlian'] ?? 'Dosen') ?> - Fakultas Teknologi Informasi UKSW</p>
                                <div class="mb-2">
                                    <span class="role-badge">NIDN: <?= htmlspecialchars($dosen['nidn'] ?? '-') ?></span>
                                    <span class="role-badge">Username: <?= htmlspecialchars($dosen['username']) ?></span>
                                    <?php if ($stats['sebagai_promotor'] > 0): ?>
                                        <span class="role-badge">Promotor: <?= $stats['sebagai_promotor'] ?> mahasiswa</span>
                                    <?php endif; ?>
                                    <?php if ($stats['sebagai_co_promotor'] > 0): ?>
                                        <span class="role-badge">Co-Promotor: <?= $stats['sebagai_co_promotor'] ?> mahasiswa</span>
                                    <?php endif; ?>
                                    <?php if ($stats['sebagai_co_promotor2'] > 0): ?>
                                        <span class="role-badge">Co-Promotor 2: <?= $stats['sebagai_co_promotor2'] ?> mahasiswa</span>  
                                    <?php endif; ?>
                                    <?php if ($stats['sebagai_penguji'] > 0): ?>
                                        <span class="role-badge">Penguji: <?= $stats['sebagai_penguji'] ?> ujian</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div style="font-size: 4rem;">🎓</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stat text-white bg-info">
                    <div class="card-body">
                        <div class="stat-number"><?= $stats['menunggu_penilaian'] ?></div>
                        <div class="stat-label">Menunggu Penilaian</div>
                        <a href="daftar_ujian.php?filter=belum_dinilai" class="text-white stat-link">
                            Lihat Detail →
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stat text-white bg-success">
                    <div class="card-body">
                        <div class="stat-number"><?= $stats['sudah_dinilai'] ?></div>
                        <div class="stat-label">Sudah Dinilai</div>
                        <a href="daftar_ujian.php?filter=sudah_dinilai" class="text-white stat-link">
                            Lihat Detail →
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stat text-white bg-warning">
                    <div class="card-body">
                        <div class="stat-number"><?= $stats['revisi_diajukan'] ?></div>
                        <div class="stat-label">Revisi Diajukan</div>
                        <a href="daftar_ujian.php?filter=revisi_diajukan" class="text-white stat-link">
                            Review →
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-stat text-white bg-primary">
                    <div class="card-body">
                        <div class="stat-number"><?= $stats['total_ujian'] ?></div>
                        <div class="stat-label">Total Ujian</div>
                        <a href="daftar_ujian.php" class="text-white stat-link">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Ujian -->
        <div class="row">
            <!-- Quick Actions -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white border-bottom-0 pb-2">
                        <h5 class="mb-0 text-primary">🚀 Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="daftar_ujian.php" class="btn btn-primary quick-action-btn text-white">
                                    <div style="font-size: 2rem; margin-bottom: 10px;">📋</div>
                                    <div>Lihat Daftar Ujian</div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="daftar_ujian.php?filter=belum_dinilai" class="btn btn-warning quick-action-btn text-white">
                                    <div style="font-size: 2rem; margin-bottom: 10px;">✏️</div>
                                    <div>Beri Penilaian</div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="daftar_ujian.php?filter=revisi_diajukan" class="btn btn-success quick-action-btn text-white">
                                    <div style="font-size: 2rem; margin-bottom: 10px;">✅</div>
                                    <div>Review Revisi</div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="bimbingan.php" class="btn btn-info quick-action-btn text-white">
                                    <div style="font-size: 2rem; margin-bottom: 10px;">👥</div>
                                    <div>Mahasiswa Bimbingan</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Ujian -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white border-bottom-0 pb-2">
                        <h5 class="mb-0 text-primary">📅 Ujian Terbaru</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($recent_ujian && $recent_ujian->num_rows > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while ($ujian = $recent_ujian->fetch_assoc()): ?>
                                    <div class="list-group-item recent-ujian-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= htmlspecialchars($ujian['nama_lengkap']) ?></h6>
                                                <p class="mb-1 text-muted small">
                                                    <?= htmlspecialchars($ujian['nim']) ?> • 
                                                    <?= ucfirst($ujian['jenis_ujian']) ?>
                                                </p>
                                                <div class="d-flex align-items-center gap-2 mt-1">
                                                    <span class="badge badge-peran bg-primary">
                                                        <?= $ujian['jenis_ujian'] ?>
                                                    </span>
                                                    <?php if ($ujian['id_penilaian']): ?>
                                                        <span class="badge badge-peran bg-success">
                                                            ✓ Dinilai: <?= number_format($ujian['nilai_total'] ?? 0, 1) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-peran bg-warning">
                                                            ⏳ Belum Dinilai
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">
                                                    <?= $ujian['tanggal_ujian'] ? date('d/m/Y', strtotime($ujian['tanggal_ujian'])) : 'Belum dijadwalkan' ?>
                                                </small>
                                                <a href="daftar_ujian.php" class="btn btn-sm btn-outline-primary mt-1">
                                                    Detail
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div style="font-size: 3rem; opacity: 0.5; margin-bottom: 1rem;">📭</div>
                                <p class="text-muted mb-0">Belum ada ujian yang ditugaskan</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info Cards -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">👨‍🎓</div>
                        <h5>Mahasiswa Bimbingan</h5>
                        <p class="mb-2">Total: <strong><?= $stats['sebagai_promotor'] + $stats['sebagai_co_promotor'] + $stats['sebagai_co_promotor2'] ?></strong></p>
                        <small class="text-muted">
                            <?= $stats['sebagai_promotor'] ?> Promotor • <?= $stats['sebagai_co_promotor'] ?> Co-Promotor • <?= $stats['sebagai_co_promotor2'] ?> Co-Promotor 2
                        </small>
                        
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">📊</div>
                        <h5>Ujian sebagai Penguji</h5>
                        <p class="mb-2">Total: <strong><?= $stats['sebagai_penguji'] ?></strong></p>
                        <small class="text-muted">
                            Dalam berbagai peran penguji
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎯</div>
                        <h5>Kinerja Penilaian</h5>
                        <p class="mb-2">
                            <?= $stats['total_ujian'] > 0 ? 
                                round(($stats['sudah_dinilai'] / $stats['total_ujian']) * 100) : 0 ?>% Tuntas
                        </p>
                        <small class="text-muted">
                            <?= $stats['sudah_dinilai'] ?> dari <?= $stats['total_ujian'] ?> ujian
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Animasi untuk stat cards
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.card-stat');
    
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
<?php
/**
 * File: admin/verifikasi.php
 * Halaman verifikasi pendaftaran mahasiswa oleh admin - SUDAH DITAMBAH CO-PROMOTOR 2
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

// ==========================
// PROSES UPDATE STATUS
// ==========================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id_registrasi = intval($_POST['id_registrasi']);
    $status = clean_input($_POST['status']);
    $status_kelulusan = clean_input($_POST['status_kelulusan']);
    $catatan = clean_input($_POST['catatan']);
    
    $query = "UPDATE registrasi 
              SET status = '$status', 
                  status_kelulusan = '$status_kelulusan', 
                  catatan_admin = '" . escape_string($catatan) . "' 
              WHERE id_registrasi = $id_registrasi";
    
    if (mysqli_query($conn, $query)) {
        // KIRIM EMAIL NOTIFIKASI KE MAHASISWA
        require_once '../includes/email_sender.php';
        
        // Ambil data mahasiswa dan registrasi
        $detail_query = "SELECT r.*, m.nama_lengkap, m.email, m.no_telp 
                        FROM registrasi r 
                        JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
                        WHERE r.id_registrasi = $id_registrasi";
        $detail_result = mysqli_query($conn, $detail_query);
        $detail_data = mysqli_fetch_assoc($detail_result);
        
        if ($detail_data) {
            $student_email = $detail_data['email'];
            $student_name = $detail_data['nama_lengkap'];
            $exam_type = $detail_data['jenis_ujian'];
            $verification_date = date('d F Y H:i:s');
            
            // Hanya kirim email jika status berubah menjadi Diterima atau Ditolak
            if ($status === 'Diterima' || $status === 'Ditolak') {
                if (testEmailConfiguration()) {
                    $email_sent = sendVerificationNotification(
                        $student_email, 
                        $student_name, 
                        $exam_type, 
                        $status, 
                        $catatan, 
                        $verification_date
                    );
                    
                    if ($email_sent) {
                        error_log("Email verifikasi berhasil dikirim ke: $student_name - Status: $status");
                    } else {
                        error_log("Gagal mengirim email verifikasi ke: $student_name");
                    }
                }
            }
        }
        
        $_SESSION['success_message'] = "Status berhasil diupdate!" . 
            (($status === 'Diterima' || $status === 'Ditolak') ? " Notifikasi telah dikirim ke mahasiswa." : "");
    } else {
        $_SESSION['error_message'] = "Gagal update status!";
    }
    
    header("Location: verifikasi.php");
    exit();
}

// ==========================
// PROSES HAPUS DATA
// ==========================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_registrasi'])) {
    $id_registrasi = intval($_POST['id_registrasi']);

    // Hapus file lampiran (jika ada)
    $q_lampiran = "SELECT path_berkas FROM lampiran WHERE id_registrasi = $id_registrasi";
    $r_lampiran = mysqli_query($conn, $q_lampiran);
    while ($lamp = mysqli_fetch_assoc($r_lampiran)) {
        $file_path = "../uploads/" . $lamp['path_berkas'];
        if (file_exists($file_path)) unlink($file_path);
    }

    // Hapus data lampiran dari DB
    mysqli_query($conn, "DELETE FROM lampiran WHERE id_registrasi = $id_registrasi");

    // Hapus data registrasi
    $hapus_query = "DELETE FROM registrasi WHERE id_registrasi = $id_registrasi";
    if (mysqli_query($conn, $hapus_query)) {
        $_SESSION['success_message'] = "Data pendaftaran berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus data pendaftaran!";
    }

    header("Location: verifikasi.php");
    exit();
}

// ==========================
// FILTER
// ==========================
$filter_status = $_GET['status'] ?? 'all';
$filter_jenis = $_GET['jenis'] ?? 'all';

// QUERY DATA DENGAN REVISI DAN DATA DOSEN - UPDATE UNTUK CO-PROMOTOR 2
// ==========================
$query = "SELECT r.*, m.nama_lengkap, m.nim, m.email,
                 -- Data dosen promotor dan co-promotor dari registrasi
                 d_promotor_reg.nama_lengkap as nama_promotor_registrasi,
                 d_promotor_reg.bidang_keahlian as keahlian_promotor_registrasi,
                 d_copromotor_reg.nama_lengkap as nama_copromotor_registrasi,
                 d_copromotor_reg.bidang_keahlian as keahlian_copromotor_registrasi,
                 d_copromotor2_reg.nama_lengkap as nama_copromotor2_registrasi,
                 d_copromotor2_reg.bidang_keahlian as keahlian_copromotor2_registrasi,
                 -- Data dosen dari jadwal ujian
                 d_promotor.nama_lengkap as nama_promotor_jadwal,
                 d_copromotor.nama_lengkap as nama_copromotor_jadwal,  -- TAMBAH
                 d_copromotor2.nama_lengkap as nama_copromotor2_jadwal,  -- TAMBAH
                 d_penguji1.nama_lengkap as nama_penguji1,
                 d_penguji2.nama_lengkap as nama_penguji2,
                 d_penguji3.nama_lengkap as nama_penguji3,
                 -- Hitung jumlah revisi
                 (SELECT COUNT(*) FROM revisi_disertasi rev2 
                  WHERE rev2.id_registrasi = r.id_registrasi 
                  AND rev2.status = 'disetujui') as jumlah_revisi_disetujui,
                 (SELECT COUNT(*) FROM revisi_disertasi rev3 
                  WHERE rev3.id_registrasi = r.id_registrasi) as total_revisi
          FROM registrasi r 
          JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
          -- JOIN untuk promotor dan co-promotor dari tabel registrasi
          LEFT JOIN dosen d_promotor_reg ON r.promotor = d_promotor_reg.id_dosen
          LEFT JOIN dosen d_copromotor_reg ON r.co_promotor = d_copromotor_reg.id_dosen
          LEFT JOIN dosen d_copromotor2_reg ON r.co_promotor2 = d_copromotor2_reg.id_dosen
          -- JOIN untuk data dari jadwal ujian
          LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi
          LEFT JOIN dosen d_promotor ON j.promotor = d_promotor.id_dosen
          LEFT JOIN dosen d_copromotor ON j.co_promotor = d_copromotor.id_dosen  -- TAMBAH
          LEFT JOIN dosen d_copromotor2 ON j.co_promotor2 = d_copromotor2.id_dosen  -- TAMBAH
          LEFT JOIN dosen d_penguji1 ON j.penguji_1 = d_penguji1.id_dosen
          LEFT JOIN dosen d_penguji2 ON j.penguji_2 = d_penguji2.id_dosen
          LEFT JOIN dosen d_penguji3 ON j.penguji_3 = d_penguji3.id_dosen
          WHERE 1=1";

if ($filter_status != 'all') $query .= " AND r.status = '" . escape_string($filter_status) . "'";
if ($filter_jenis != 'all') $query .= " AND r.jenis_ujian = '" . escape_string($filter_jenis) . "'";

$query .= " ORDER BY r.tanggal_pengajuan DESC";
$result = mysqli_query($conn, $query);

$page_title = "Verifikasi Pendaftaran - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<link href="../assets/css/admin-styles.css" rel="stylesheet">

<div class="content-wrapper">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Verifikasi Pendaftaran</h1>
            <p class="hero-breadcrumb">Dashboard<span class="separator">›</span>Verifikasi</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <h2 class="page-title">Verifikasi Pendaftaran Ujian</h2>
        <hr class="title-divider">

        <!-- Notifikasi -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert-custom alert-success">
            <span class="alert-icon">✓</span>
            <span><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
            <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert-custom alert-danger">
            <span class="alert-icon">⚠</span>
            <span><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
            <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Filter Status</label>
                        <select name="status" class="filter-select">
                            <option value="all" <?= $filter_status == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="Menunggu" <?= $filter_status == 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="Diterima" <?= $filter_status == 'Diterima' ? 'selected' : ''; ?>>Diterima</option>
                            <option value="Ditolak" <?= $filter_status == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Filter Jenis Ujian</label>
                        <select name="jenis" class="filter-select">
                            <option value="all" <?= $filter_jenis == 'all' ? 'selected' : ''; ?>>Semua Jenis</option>
                            <option value="proposal" <?= $filter_jenis == 'proposal' ? 'selected' : ''; ?>>Proposal</option>
                            <option value="kualifikasi" <?= $filter_jenis == 'kualifikasi' ? 'selected' : ''; ?>>Kualifikasi</option>
                            <option value="kelayakan" <?= $filter_jenis == 'kelayakan' ? 'selected' : ''; ?>>Kelayakan</option>
                            <option value="tertutup" <?= $filter_jenis == 'tertutup' ? 'selected' : ''; ?>>Tertutup</option>
                        </select>
                    </div>
                    <div class="filter-button-group">
                        <button type="submit" class="btn-filter">
                            🔍 Filter
                        </button>
                        <a href="verifikasi.php" class="btn-reset">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title">Daftar Pendaftaran (<?= mysqli_num_rows($result); ?> data)</h5>
            </div>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-wrapper">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>Jenis Ujian</th>
                                <th>Judul Disertasi</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Kelulusan</th>
                                <th>Revisi</th>
                                <th>Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): 
                            
                            // QUERY BARU: Ambil semua penilaian untuk registrasi ini
                            $penilaian_query = "SELECT p.*, d.nama_lengkap as nama_dosen, d.id_dosen,
                                                       CASE 
                                                           WHEN r.promotor = d.id_dosen THEN 'promotor'
                                                           WHEN r.co_promotor = d.id_dosen THEN 'co_promotor' 
                                                           WHEN r.co_promotor2 = d.id_dosen THEN 'co_promotor2'
                                                           ELSE 'penguji'
                                                       END as peran_dosen
                                                FROM penilaian_ujian p
                                                JOIN dosen d ON p.id_dosen = d.id_dosen
                                                JOIN registrasi r ON p.id_registrasi = r.id_registrasi
                                                WHERE p.id_registrasi = " . $row['id_registrasi'];
                            $penilaian_result = mysqli_query($conn, $penilaian_query);
                            
                            // PERBAIKAN: Hitung nilai dengan bobot yang jelas - Semua Promotor & Co-Promotor 60%, Semua Penguji 40%
                            $nilai_promotor = [];
                            $nilai_co_promotor = [];
                            $nilai_co_promotor2 = [];
                            $nilai_penguji = [];

                            // Kumpulkan semua nilai berdasarkan peran
                            while ($penilaian = mysqli_fetch_assoc($penilaian_result)) {
                                if ($penilaian['peran_dosen'] == 'promotor') {
                                    $nilai_promotor[] = $penilaian['nilai_total'];
                                } elseif ($penilaian['peran_dosen'] == 'co_promotor') {
                                    $nilai_co_promotor[] = $penilaian['nilai_total'];
                                } elseif ($penilaian['peran_dosen'] == 'co_promotor2') {
                                    $nilai_co_promotor2[] = $penilaian['nilai_total'];
                                } else {
                                    $nilai_penguji[] = $penilaian['nilai_total'];
                                }
                            }

                            // Hitung rata-rata untuk setiap kelompok
                            $rata_promotor = !empty($nilai_promotor) ? array_sum($nilai_promotor) / count($nilai_promotor) : 0;
                            $rata_co_promotor = !empty($nilai_co_promotor) ? array_sum($nilai_co_promotor) / count($nilai_co_promotor) : 0;
                            $rata_co_promotor2 = !empty($nilai_co_promotor2) ? array_sum($nilai_co_promotor2) / count($nilai_co_promotor2) : 0;
                            $rata_penguji = !empty($nilai_penguji) ? array_sum($nilai_penguji) / count($nilai_penguji) : 0;

                            // **PERHITUNGAN BOBOT YANG BENAR:**
                            // 1. Hitung rata-rata SEMUA promotor & co-promotor (60%)
                            $total_nilai_promotor_co = 0;
                            $jumlah_promotor_co = 0;

                            // Promotor utama
                            if ($rata_promotor > 0) {
                                $total_nilai_promotor_co += $rata_promotor;
                                $jumlah_promotor_co++;
                            }

                            // Co-promotor 1
                            if ($rata_co_promotor > 0) {
                                $total_nilai_promotor_co += $rata_co_promotor;
                                $jumlah_promotor_co++;
                            }

                            // Co-promotor 2
                            if ($rata_co_promotor2 > 0) {
                                $total_nilai_promotor_co += $rata_co_promotor2;
                                $jumlah_promotor_co++;
                            }

                            // Rata-rata tim promotor & co-promotor
                            $rata_tim_promotor_co = $jumlah_promotor_co > 0 ? $total_nilai_promotor_co / $jumlah_promotor_co : 0;

                            // 2. Hitung nilai akhir dengan bobot 60% promotor-co-promotor dan 40% penguji
                            $nilai_akhir = 0;

                            if ($rata_tim_promotor_co > 0 && $rata_penguji > 0) {
                                // Kasus ideal: ada penilaian dari tim promotor-co-promotor DAN penguji
                                $nilai_akhir = ($rata_tim_promotor_co * 0.6) + ($rata_penguji * 0.4);
                                
                            } elseif ($rata_tim_promotor_co > 0 && $rata_penguji == 0) {
                                // Hanya tim promotor-co-promotor yang menilai
                                $nilai_akhir = $rata_tim_promotor_co;
                                
                            } elseif ($rata_tim_promotor_co == 0 && $rata_penguji > 0) {
                                // Hanya penguji yang menilai (kasus khusus)
                                $nilai_akhir = $rata_penguji;
                                
                            } else {
                                // Tidak ada penilaian sama sekali
                                $nilai_akhir = 0;
                            }

                            // Tentukan grade berdasarkan nilai akhir
                            $grade = '';
                            $grade_class = '';
                            if ($nilai_akhir > 0) {
                                if ($nilai_akhir > 85) {
                                    $grade = 'A';
                                    $grade_class = 'nilai-a';
                                } elseif ($nilai_akhir > 80) {
                                    $grade = 'AB';
                                    $grade_class = 'nilai-ab';
                                } elseif ($nilai_akhir >= 70) {
                                    $grade = 'B';
                                    $grade_class = 'nilai-b';
                                } else {
                                    $grade = 'T';
                                    $grade_class = 'nilai-t';
                                }
                            }
                            
                            // QUERY BARU: Ambil semua revisi untuk registrasi ini
                            $revisi_query = "SELECT rev.*, p.id_dosen, d.nama_lengkap as nama_dosen, p.jenis_nilai,
                                                    CASE 
                                                        WHEN r.promotor = d.id_dosen THEN 'Promotor'
                                                        WHEN r.co_promotor = d.id_dosen THEN 'Co-Promotor'
                                                        WHEN r.co_promotor2 = d.id_dosen THEN 'Co-Promotor 2'
                                                        ELSE 'Penguji'
                                                    END as posisi_dosen
                                             FROM revisi_disertasi rev 
                                             LEFT JOIN penilaian_ujian p ON rev.id_penilaian = p.id_penilaian
                                             LEFT JOIN dosen d ON p.id_dosen = d.id_dosen
                                             LEFT JOIN registrasi r ON rev.id_registrasi = r.id_registrasi
                                             WHERE rev.id_registrasi = " . $row['id_registrasi'] . " 
                                             ORDER BY rev.tanggal_kirim DESC";
                            $revisi_result = mysqli_query($conn, $revisi_query);
                            $total_revisi = mysqli_num_rows($revisi_result);
                            $revisi_disetujui = 0;
                            
                            // Hitung revisi yang disetujui
                            mysqli_data_seek($revisi_result, 0);
                            while ($revisi = mysqli_fetch_assoc($revisi_result)) {
                                if ($revisi['status'] == 'disetujui') {
                                    $revisi_disetujui++;
                                }
                            }
                            
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <span class="student-name"><?= $row['nama_lengkap']; ?></span>
                                    <span class="student-nim"><?= $row['nim']; ?></span>
                                </td>
                                <td><span class="exam-type-badge"><?= ucfirst($row['jenis_ujian']); ?></span></td>
                                <td><div class="dissertation-title" title="<?= htmlspecialchars($row['judul_disertasi']); ?>"><?= $row['judul_disertasi']; ?></div></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                <td>
                                    <?php 
                                    $badge_class = match($row['status']) {
                                        'Menunggu' => 'badge-menunggu',
                                        'Diterima' => 'badge-diterima',
                                        default => 'badge-ditolak'
                                    };
                                    ?>
                                    <span class="status-badge <?= $badge_class; ?>"><?= $row['status']; ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $kelulusan_badge = match($row['status_kelulusan'] ?? 'belum_ujian') {
                                        'lulus' => '<span class="status-badge badge-diterima">Lulus</span>',
                                        'tidak_lulus' => '<span class="status-badge badge-ditolak">Tidak Lulus</span>',
                                        default => '<span class="status-badge badge-menunggu">Belum Ujian</span>'
                                    };
                                    echo $kelulusan_badge;
                                    ?>
                                </td>
                                <td>
                                    <div class="revisi-info">
                                        <?php if ($total_revisi > 0): ?>
                                            <span class="status-badge <?= $revisi_disetujui == $total_revisi ? 'badge-diterima' : 'badge-menunggu'; ?>" style="font-size: 10px;">
                                                <?= $revisi_disetujui ?>/<?= $total_revisi ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge badge-menunggu" style="font-size: 10px;">
                                                -
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($nilai_akhir > 0): ?>
                                        <div style="text-align: center;">
                                            <span class="nilai-badge <?= $grade_class; ?>"><?= number_format($nilai_akhir, 1); ?></span>
                                            <br>
                                            <small style="color: #6B7280; font-size: 9px;"><?= $grade; ?></small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 11px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button class="action-btn" data-bs-toggle="modal" data-bs-target="#modalVerifikasi<?= $row['id_registrasi']; ?>" title="Detail & Verifikasi">
                                            ✏️
                                        </button>
                                        <button class="action-btn action-btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $row['id_registrasi']; ?>" title="Hapus">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Verifikasi -->
                            <div class="modal fade" id="modalVerifikasi<?= $row['id_registrasi']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary">
                                            <h5 class="modal-title">ℹ️ Detail & Verifikasi Pendaftaran - <?= $row['nama_lengkap']; ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Data Mahasiswa -->
                                            <div class="info-card">
                                                <h6 class="modal-section-title">👤 Data Mahasiswa</h6>
                                                <div class="info-grid">
                                                    <div class="info-item">
                                                        <span class="info-label">Nama Lengkap</span>
                                                        <span class="info-value"><?= $row['nama_lengkap']; ?></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">NIM</span>
                                                        <span class="info-value"><?= $row['nim']; ?></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Email</span>
                                                        <span class="info-value"><?= $row['email']; ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Data Pendaftaran -->
                                            <div class="info-card">
                                                <h6 class="modal-section-title">📋 Data Pendaftaran</h6>
                                                <div class="info-grid">
                                                    <div class="info-item">
                                                        <span class="info-label">Jenis Ujian</span>
                                                        <span class="info-value"><span class="exam-type-badge"><?= ucfirst($row['jenis_ujian']); ?></span></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Promotor</span>
                                                        <span class="info-value">
                                                            <span class="promotor">
                                                                <?= htmlspecialchars($row['nama_promotor_registrasi'] ?? 'Belum ditentukan'); ?>
                                                                <?php if (!empty($row['keahlian_promotor_registrasi'])): ?>
                                                                    <small style="color: #666; font-size: 0.9em;">
                                                                        (<?= htmlspecialchars($row['keahlian_promotor_registrasi']); ?>)
                                                                    </small>
                                                                <?php endif; ?>
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Co-Promotor 1</span>
                                                        <span class="info-value">
                                                            <span class="promotor">
                                                                <?= htmlspecialchars($row['nama_copromotor_registrasi'] ?? 'Belum ditentukan'); ?>
                                                                <?php if (!empty($row['keahlian_copromotor_registrasi'])): ?>
                                                                    <small style="color: #666; font-size: 0.9em;">
                                                                        (<?= htmlspecialchars($row['keahlian_copromotor_registrasi']); ?>)
                                                                    </small>
                                                                <?php endif; ?>
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Co-Promotor 2</span>
                                                        <span class="info-value">
                                                            <span class="promotor">
                                                                <?= htmlspecialchars($row['nama_copromotor2_registrasi'] ?? 'Belum ditentukan'); ?>
                                                                <?php if (!empty($row['keahlian_copromotor2_registrasi'])): ?>
                                                                    <small style="color: #666; font-size: 0.9em;">
                                                                        (<?= htmlspecialchars($row['keahlian_copromotor2_registrasi']); ?>)
                                                                    </small>
                                                                <?php endif; ?>
                                                            </span>
                                                        </span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Tanggal Pengajuan</span>
                                                        <span class="info-value"><?= date('d F Y', strtotime($row['tanggal_pengajuan'])); ?></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Status Saat Ini</span>
                                                        <span class="info-value"><span class="status-badge <?= $badge_class; ?>"><?= $row['status']; ?></span></span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Status Kelulusan</span>
                                                        <span class="info-value">
                                                            <?php 
                                                            $kelulusan_badge = match($row['status_kelulusan'] ?? 'belum_ujian') {
                                                                'lulus' => '<span class="status-badge badge-diterima">Lulus</span>',
                                                                'tidak_lulus' => '<span class="status-badge badge-ditolak">Tidak Lulus</span>',
                                                                default => '<span class="status-badge badge-menunggu">Belum Ujian</span>'
                                                            };
                                                            echo $kelulusan_badge;
                                                            ?>
                                                        </span>
                                                    </div>
                                                    <div class="info-item" style="grid-column: 1 / -1;">
                                                        <span class="info-label">Judul Disertasi</span>
                                                        <span class="info-value"><?= $row['judul_disertasi']; ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tim Penguji -->
                                            <div class="info-card">
                                                <h6 class="modal-section-title">👨‍🏫 Tim Penguji (Dari Jadwal Ujian)</h6>
                                                <div class="info-grid">
                                                    <?php if (!empty($row['nama_promotor_jadwal'])): ?>
                                                    <div class="info-item">
                                                        <span class="info-label">Promotor</span>
                                                        <span class="info-value">
                                                            <?= $row['nama_promotor_jadwal']; ?>
                                                            <span style="color: #059669; font-size: 10px; margin-left: 4px;">✓</span>
                                                        </span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($row['nama_copromotor_jadwal'])): ?>
                                                    <div class="info-item">
                                                        <span class="info-label">Co-Promotor 1</span>
                                                        <span class="info-value"><?= $row['nama_copromotor_jadwal']; ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($row['nama_copromotor2_jadwal'])): ?>
                                                    <div class="info-item">
                                                        <span class="info-label">Co-Promotor 2</span>
                                                        <span class="info-value"><?= $row['nama_copromotor2_jadwal']; ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($row['nama_penguji1'])): ?>
                                                    <div class="info-item">
                                                        <span class="info-label">Penguji 1</span>
                                                        <span class="info-value"><?= $row['nama_penguji1']; ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($row['nama_penguji2'])): ?>
                                                    <div class="info-item">
                                                        <span class="info-label">Penguji 2</span>
                                                        <span class="info-value"><?= $row['nama_penguji2']; ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($row['nama_penguji3'])): ?>
                                                    <div class="info-item">
                                                        <span class="info-label">Penguji 3</span>
                                                        <span class="info-value"><?= $row['nama_penguji3']; ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if (empty($row['nama_promotor_jadwal']) && empty($row['nama_copromotor_jadwal']) && empty($row['nama_copromotor2_jadwal']) && empty($row['nama_penguji1']) && empty($row['nama_penguji2']) && empty($row['nama_penguji3'])): ?>
                                                <div style="text-align: center; padding: 10px; color: #6B7280; font-style: italic;">
                                                    Belum ada tim penguji yang ditugaskan dari jadwal ujian
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Lampiran -->
                                            <div class="info-card">
                                                <h6 class="modal-section-title">📎 Lampiran Berkas</h6>
                                                <?php
                                                $lampiran_q = mysqli_query($conn, "SELECT * FROM lampiran WHERE id_registrasi = " . $row['id_registrasi']);
                                                if (mysqli_num_rows($lampiran_q) > 0): ?>
                                                    <div class="attachment-list">
                                                        <?php 
                                                        $lamp_no = 1;
                                                        while ($lamp = mysqli_fetch_assoc($lampiran_q)): 
                                                            $path = "../uploads/" . $lamp['path_berkas'];
                                                            $ext = strtolower(pathinfo($lamp['path_berkas'], PATHINFO_EXTENSION));
                                                            $file_id = 'file_' . $row['id_registrasi'] . '_' . $lamp_no;
                                                        ?>
                                                        <div class="attachment-wrapper">
                                                            <div class="attachment-item-v2" onclick="togglePreview('<?= $file_id; ?>', '<?= $path; ?>', '<?= $ext; ?>')">
                                                                <div class="attachment-info">
                                                                    <div class="attachment-number"><?= $lamp_no++; ?></div>
                                                                    <div class="attachment-details">
                                                                        <div class="attachment-name-v2"><?= $lamp['nama_berkas']; ?></div>
                                                                        <div class="attachment-meta">Format: <?= strtoupper($ext); ?></div>
                                                                    </div>
                                                                </div>
                                                                <div class="attachment-actions">
                                                                    <a href="<?= $path; ?>" download class="btn-download-v3" onclick="event.stopPropagation();">
                                                                        Download
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- Preview Area (hidden by default) -->
                                                            <div id="preview_<?= $file_id; ?>" class="file-preview-inline" style="display: none;">
                                                                <div class="preview-content" id="content_<?= $file_id; ?>">
                                                                    <div class="preview-loading">⏳ Memuat preview...</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endwhile; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="empty-attachment">
                                                        <span class="empty-icon">📭</span>
                                                        <span class="empty-text">Tidak ada lampiran</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- HASIL PENILAIAN KOMPREHENSIF -->
                                            <div class="info-card">
                                                <h6 class="modal-section-title">📊 Hasil Penilaian Komprehensif</h6>
                                                <?php
                                                    // Query ulang untuk mendapatkan semua penilaian dengan detail
                                                    $penilaian_detail_query = "SELECT p.*, d.nama_lengkap as nama_dosen, d.id_dosen,
                                                                                    CASE 
                                                                                        WHEN r.promotor = d.id_dosen THEN 'Promotor'
                                                                                        WHEN r.co_promotor = d.id_dosen THEN 'Co-Promotor' 
                                                                                        WHEN r.co_promotor2 = d.id_dosen THEN 'Co-Promotor 2'
                                                                                        ELSE 'Penguji'
                                                                                    END as peran_dosen
                                                                            FROM penilaian_ujian p
                                                                            JOIN dosen d ON p.id_dosen = d.id_dosen
                                                                            JOIN registrasi r ON p.id_registrasi = r.id_registrasi
                                                                            WHERE p.id_registrasi = " . $row['id_registrasi'];
                                                    $penilaian_detail_result = mysqli_query($conn, $penilaian_detail_query);
                                                
                                                    if (mysqli_num_rows($penilaian_detail_result) > 0): 
                                                ?>
                                                    <div class="attachment-list">
                                                        <?php 
                                                        $total_nilai_promotor = 0;
                                                        $jumlah_promotor = 0;
                                                        $total_nilai_co_promotor = 0;
                                                        $total_nilai_co_promotor2 = 0;
                                                        $jumlah_co_promotor = 0;
                                                        $total_nilai_penguji = 0;
                                                        $jumlah_penguji = 0;
                                                        $penilaian_no = 1;
                                                        
                                                        while ($penilaian = mysqli_fetch_assoc($penilaian_detail_result)): 
                                                            $grade = ($penilaian['nilai_total'] > 85) ? 'A' : (($penilaian['nilai_total'] > 80) ? 'AB' : (($penilaian['nilai_total'] >= 70) ? 'B' : 'T'));
                                                            
                                                            if ($penilaian['peran_dosen'] == 'Promotor') {
                                                                $total_nilai_promotor += $penilaian['nilai_total'];
                                                                $jumlah_promotor++;
                                                            } elseif ($penilaian['peran_dosen'] == 'Co-Promotor') {
                                                                $total_nilai_co_promotor += $penilaian['nilai_total'];
                                                                $jumlah_co_promotor++;
                                                            } elseif ($penilaian['peran_dosen'] == 'Co-Promotor 2') {
                                                                $total_nilai_co_promotor2 += $penilaian['nilai_total'];
                                                                $jumlah_co_promotor++;
                                                            } else {
                                                                $total_nilai_penguji += $penilaian['nilai_total'];
                                                                $jumlah_penguji++;
                                                            }
                                                            
                                                            $nama_dosen = $penilaian['nama_dosen'] ?? 'Belum ditentukan';
                                                            $posisi_dosen = $penilaian['peran_dosen'] ?? 'Dosen';
                                                            $badge_color = match($posisi_dosen) {
                                                                'Promotor' => '#059669',
                                                                'Co-Promotor 1' => '#0ea5e9',
                                                                'Co-Promotor 2' => '#2563EB',
                                                                default => '#6366F1'
                                                            };
                                                            
                                                            $grade_badge_color = match($grade) {
                                                                'A' => '#059669',
                                                                'AB' => '#0ea5e9',
                                                                'B' => '#2563EB',
                                                                default => '#dc2626'
                                                            };
                                                        ?>
                                                        <div class="attachment-wrapper">
                                                            <div class="attachment-item-v2">
                                                                <div class="attachment-info">
                                                                    <div class="attachment-number"><?= $penilaian_no++; ?></div>
                                                                    <div class="attachment-details">
                                                                        <div class="attachment-name-v2">
                                                                            Penilaian <?= $penilaian_no-1 ?> 
                                                                            <span class="status-badge" style="font-size: 10px; background: <?= $grade_badge_color; ?>; color: white;">
                                                                                Grade: <?= $grade; ?>
                                                                            </span>
                                                                            <span class="status-badge" style="font-size: 9px; background: <?= $badge_color; ?>; color: white; margin-left: 6px;">
                                                                                <?= $posisi_dosen; ?>
                                                                            </span>
                                                                        </div>
                                                                        
                                                                        <!-- Informasi Dosen -->
                                                                        <div style="margin-top: 4px;">
                                                                            <strong style="font-size: 10px; color: #374151;">Dosen Penilai:</strong>
                                                                            <span style="font-size: 11px; color: #6B7280; margin-left: 4px;">
                                                                                <?= $nama_dosen; ?>
                                                                            </span>
                                                                        </div>
                                                                        
                                                                        <div class="attachment-meta">
                                                                            Tanggal Penilaian: <?= date('d/m/Y', strtotime($penilaian['tanggal_penilaian'])) ?>
                                                                        </div>
                                                                        
                                                                        <!-- Detail Nilai -->
                                                                        <div style="margin-top: 8px;">
                                                                            <strong style="font-size: 11px; color: #374151;">📊 Detail Nilai:</strong>
                                                                            <div style="font-size: 11px; color: #6B7280; background: #F0F9FF; padding: 8px; border-radius: 6px; margin-top: 4px; border-left: 3px solid #0ea5e9;">
                                                                                <strong>Nilai Total: <?= number_format($penilaian['nilai_total'], 2); ?></strong>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="attachment-actions">
                                                                    <span class="btn-download-v3" style="background: <?= $grade_badge_color; ?>;">
                                                                        Grade: <?= $grade; ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endwhile; ?>
                                                        
                                                        <!-- Ringkasan Nilai Komprehensif -->
                                                        <div class="attachment-wrapper">
                                                            <div class="attachment-item-v2" style="background: #f0f9ff; border: 1px solid #bae6fd;">
                                                                <div class="attachment-info">
                                                                    <div class="attachment-number" style="background: #0369a1;">📈</div>
                                                                    <div class="attachment-details">
                                                                        <div class="attachment-name-v2">
                                                                            Ringkasan Nilai Komprehensif - Sistem Bobot 60/40
                                                                        </div>
                                                                        
                                                                        <div style="margin-top: 8px;">
                                                                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                                                                <span style="font-size: 11px; color: #374151;">
                                                                                    <strong>Rata-rata Tim Promotor & Co-Promotor (60%):</strong><br>
                                                                                    <?php 
                                                                                    $nilai_promotor_co_display = number_format($rata_tim_promotor_co, 2);
                                                                                    echo $nilai_promotor_co_display . " (" . $jumlah_promotor_co . ' dosen' . ")";
                                                                                    ?>
                                                                                    <?php if ($jumlah_promotor_co > 0): ?>
                                                                                        <br>
                                                                                        <small style="color: #6B7280;">
                                                                                            <?php 
                                                                                            $detail_promotor = [];
                                                                                            if ($rata_promotor > 0) $detail_promotor[] = "Promotor: " . number_format($rata_promotor, 2);
                                                                                            if ($rata_co_promotor > 0) $detail_promotor[] = "Co-Promotor: " . number_format($rata_co_promotor, 2);
                                                                                            if ($rata_co_promotor2 > 0) $detail_promotor[] = "Co-Promotor 2: " . number_format($rata_co_promotor2, 2);
                                                                                            echo implode(" | ", $detail_promotor);
                                                                                            ?>
                                                                                        </small>
                                                                                    <?php endif; ?>
                                                                                </span>
                                                                                <span style="font-size: 11px; color: #374151;">
                                                                                    <strong>Rata-rata Penguji (40%):</strong><br>
                                                                                    <?php 
                                                                                    $jumlah_penguji_aktif = count($nilai_penguji);
                                                                                    $nilai_penguji_display = number_format($rata_penguji, 2);
                                                                                    echo $nilai_penguji_display . " (" . $jumlah_penguji_aktif . ' penguji' . ")";
                                                                                    ?>
                                                                                </span>
                                                                            </div>
                                                                            
                                                                            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #bae6fd;">
                                                                                <strong style="font-size: 11px; color: #0369a1;">Nilai Akhir (Bobot: Promotor & Co-Promotor 60% + Penguji 40%):</strong>
                                                                                <div style="font-size: 14px; font-weight: bold; color: #059669; margin-top: 4px;">
                                                                                    <?= number_format($nilai_akhir, 2); ?>
                                                                                    <span class="status-badge" style="font-size: 10px; background: <?= $nilai_akhir >= 70 ? '#059669' : '#dc2626'; ?>; color: white; margin-left: 8px;">
                                                                                        Grade: <?= $grade ?>
                                                                                    </span>
                                                                                </div>
                                                                                
                                                                                <!-- Detail Perhitungan -->
                                                                                <div style="margin-top: 6px; font-size: 10px; color: #6B7280;">
                                                                                    <strong>Detail Perhitungan:</strong><br>
                                                                                    (<?= $nilai_promotor_co_display ?> × 60%) + (<?= $nilai_penguji_display ?> × 40%) = 
                                                                                    (<?= number_format($rata_tim_promotor_co * 0.6, 2) ?>) + (<?= number_format($rata_penguji * 0.4, 2) ?>) = 
                                                                                    <?= number_format($nilai_akhir, 2) ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="empty-attachment">
                                                        <span class="empty-icon">📊</span>
                                                        <span class="empty-text">Belum ada penilaian</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- HASIL REVISI DARI SEMUA DOSEN -->
                                            <div class="info-card">
                                                <h6 class="modal-section-title">📝 Hasil Revisi dari Semua Dosen</h6>
                                                <?php
                                                // Query ulang untuk mendapatkan semua revisi
                                                mysqli_data_seek($revisi_result, 0);
                                                if (mysqli_num_rows($revisi_result) > 0): 
                                                ?>
                                                    <div class="attachment-list">
                                                        <?php 
                                                        $revisi_no = 1;
                                                        while ($revisi = mysqli_fetch_assoc($revisi_result)): 
                                                            $revisi_status = $revisi['status'] ?? 'belum';
                                                            $revisi_badge = match($revisi_status) {
                                                                'disetujui' => '<span class="status-badge badge-diterima" style="font-size: 10px;">✅ Disetujui</span>',
                                                                'perlu_perbaikan' => '<span class="status-badge badge-ditolak" style="font-size: 10px;">❌ Perlu Perbaikan</span>',
                                                                'dikirim' => '<span class="status-badge badge-menunggu" style="font-size: 10px;">📤 Dikirim</span>',
                                                                default => '<span class="status-badge badge-menunggu" style="font-size: 10px;">⏳ Menunggu</span>'
                                                            };
                                                            
                                                            $nama_dosen = $revisi['nama_dosen'] ?? 'Belum ditentukan';
                                                            $posisi_dosen = $revisi['posisi_dosen'] ?? 'Dosen';
                                                            $badge_color = match($posisi_dosen) {
                                                                'Promotor' => '#059669',
                                                                'Co-Promotor 1' => '#0ea5e9',
                                                                'Co-Promotor 2' => '#2563EB',
                                                                default => '#6366F1'
                                                            };
                                                        ?>
                                                        <div class="attachment-wrapper">
                                                            <div class="attachment-item-v2">
                                                                <div class="attachment-info">
                                                                    <div class="attachment-number"><?= $revisi_no++; ?></div>
                                                                    <div class="attachment-details">
                                                                        <div class="attachment-name-v2">
                                                                            Revisi <?= $revisi_no-1 ?> 
                                                                            <?= $revisi_badge; ?>
                                                                            <span class="status-badge" style="font-size: 9px; background: <?= $badge_color; ?>; color: white; margin-left: 6px;">
                                                                                <?= $posisi_dosen; ?>
                                                                            </span>
                                                                        </div>
                                                                        
                                                                        <!-- Informasi Dosen -->
                                                                        <div style="margin-top: 4px;">
                                                                            <strong style="font-size: 10px; color: #374151;">Dosen Penilai:</strong>
                                                                            <span style="font-size: 11px; color: #6B7280; margin-left: 4px;">
                                                                                <?= $nama_dosen; ?>
                                                                            </span>
                                                                        </div>
                                                                        
                                                                        <div class="attachment-meta">
                                                                            Dikirim: <?= date('d/m/Y H:i', strtotime($revisi['tanggal_kirim'])) ?>
                                                                            <?php if ($revisi['tanggal_approve']): ?>
                                                                                | Disetujui: <?= date('d/m/Y H:i', strtotime($revisi['tanggal_approve'])) ?>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        
                                                                        <!-- Catatan dari Mahasiswa -->
                                                                        <?php if (!empty($revisi['catatan_revisi'])): ?>
                                                                        <div style="margin-top: 8px;">
                                                                            <strong style="font-size: 11px; color: #374151;">📝 Catatan Mahasiswa:</strong>
                                                                            <div style="font-size: 11px; color: #6B7280; background: #F3F4F6; padding: 8px; border-radius: 6px; margin-top: 4px; border-left: 3px solid #9CA3AF;">
                                                                                <?= nl2br(htmlspecialchars($revisi['catatan_revisi'])) ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <!-- Catatan dari Dosen -->
                                                                        <?php if (!empty($revisi['catatan_dosen'])): ?>
                                                                        <div style="margin-top: 8px;">
                                                                            <strong style="font-size: 11px; color: #374151;">💬 Catatan <?= $posisi_dosen; ?>:</strong>
                                                                            <div style="font-size: 11px; color: #6B7280; background: #EFF6FF; padding: 8px; border-radius: 6px; margin-top: 4px; border-left: 3px solid #3B82F6;">
                                                                                <?= nl2br(htmlspecialchars($revisi['catatan_dosen'])) ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="attachment-actions">
                                                                    <?php if (!empty($revisi['file_revisi'])): ?>
                                                                    <a href="../uploads/revisi/<?= $revisi['file_revisi']; ?>" 
                                                                    download 
                                                                    class="btn-download-v3" 
                                                                    style="background: #5495FF;">
                                                                        📄 File Revisi
                                                                    </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endwhile; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="empty-attachment">
                                                        <span class="empty-icon">📝</span>
                                                        <span class="empty-text">Belum ada revisi</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Form Verifikasi -->
                                            <div class="verification-card">
                                                <h6 class="modal-section-title">✅ Form Verifikasi</h6>
                                                <form method="POST">
                                                    <input type="hidden" name="id_registrasi" value="<?= $row['id_registrasi']; ?>">
                                                    <div class="form-group-v2">
                                                        <label class="form-label-v2">Status Verifikasi <span style="color: #EF4444;">*</span></label>
                                                        <select name="status" class="form-control-v2" required>
                                                            <option value="Menunggu" <?= $row['status']=='Menunggu'?'selected':''; ?>>⏳ Menunggu</option>
                                                            <option value="Diterima" <?= $row['status']=='Diterima'?'selected':''; ?>>✅ Diterima</option>
                                                            <option value="Ditolak" <?= $row['status']=='Ditolak'?'selected':''; ?>>❌ Ditolak</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="form-group-v2">
                                                        <label class="form-label-v2">Status Kelulusan <span style="color: #EF4444;">*</span></label>
                                                        <select name="status_kelulusan" class="form-control-v2" required>
                                                            <option value="belum_ujian" <?= ($row['status_kelulusan']??'belum_ujian')=='belum_ujian'?'selected':''; ?>>⏳ Belum Ujian</option>
                                                            <option value="lulus" <?= ($row['status_kelulusan']??'belum_ujian')=='lulus'?'selected':''; ?>>✅ Lulus</option>
                                                            <option value="tidak_lulus" <?= ($row['status_kelulusan']??'belum_ujian')=='tidak_lulus'?'selected':''; ?>>❌ Tidak Lulus</option>
                                                        </select>
                                                        <small style="font-size: 11px; color: #6B7280; margin-top: 4px; display: block;">
                                                            Status ini menentukan apakah mahasiswa bisa lanjut ke tahap berikutnya
                                                        </small>
                                                    </div>
                                                    
                                                    <div class="form-group-v2">
                                                        <label class="form-label-v2">Catatan Admin</label>
                                                        <textarea name="catatan" class="form-control-v2" rows="4" placeholder="Tambahkan catatan atau keterangan..."><?= $row['catatan_admin']; ?></textarea>
                                                    </div>
                                                    <button type="submit" name="update_status" class="btn-submit-v2">
                                                        💾 Simpan Verifikasi
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Hapus -->
                            <div class="modal fade" id="modalDelete<?= $row['id_registrasi']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger">
                                            <h5 class="modal-title">🗑️ Hapus Data</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p style="font-family: 'Poppins', sans-serif; font-size: 14px; color: #374151; margin: 0;">
                                                Apakah Anda yakin ingin menghapus pendaftaran milik <strong><?= $row['nama_lengkap']; ?></strong>?
                                            </p>
                                        </div>
                                        <div class="modal-footer" style="border-top: 1px solid #E5E7EB; padding: 16px 24px;">
                                            <form method="POST" style="display: flex; gap: 8px; width: 100%;">
                                                <input type="hidden" name="id_registrasi" value="<?= $row['id_registrasi']; ?>">
                                                <button type="button" class="btn-reset" data-bs-dismiss="modal" style="flex: 1;">Batal</button>
                                                <button type="submit" name="delete_registrasi" style="flex: 1; height: 42px; background: #EF4444; color: #FFFFFF; border: none; border-radius: 6px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s ease;">
                                                    🗑️ Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p class="empty-state-title">Tidak ada data pendaftaran</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript untuk preview file -->
<script>
// Track currently open preview
let currentOpenPreview = null;

function togglePreview(fileId, filePath, fileExtension) {
    const previewElement = document.getElementById('preview_' + fileId);
    const contentElement = document.getElementById('content_' + fileId);
    
    // If clicking the same item, toggle close
    if (currentOpenPreview === fileId) {
        previewElement.style.display = 'none';
        currentOpenPreview = null;
        return;
    }
    
    // Close previously open preview
    if (currentOpenPreview) {
        document.getElementById('preview_' + currentOpenPreview).style.display = 'none';
    }
    
    // Open new preview
    previewElement.style.display = 'block';
    currentOpenPreview = fileId;
    
    // Show loading
    contentElement.innerHTML = '<div class="preview-loading">⏳ Memuat preview...</div>';
    
    // Load preview based on file type
    setTimeout(() => {
        loadPreview(contentElement, filePath, fileExtension);
    }, 300);
}

function loadPreview(container, filePath, fileExtension) {
    if (fileExtension === 'pdf') {
        // PDF files
        container.innerHTML = `
            <embed src="${filePath}" type="application/pdf" width="100%" style="min-height: 500px;" />
        `;
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(fileExtension)) {
        // Image files
        container.innerHTML = `
            <img src="${filePath}" alt="Preview" style="max-width: 100%; height: auto; display: block; margin: 0 auto;" />
        `;
    } else if (['doc', 'docx'].includes(fileExtension)) {
        // Word documents - use Google Docs Viewer
        const fullUrl = window.location.origin + '/' + filePath;
        container.innerHTML = `
            <iframe src="https://docs.google.com/viewer?url=${encodeURIComponent(fullUrl)}&embedded=true" width="100%" style="min-height: 500px;"></iframe>
        `;
    } else {
        // Other file types - show download option
        container.innerHTML = `
            <div class="preview-error">
                <span class="preview-error-icon">📄</span>
                <span class="preview-error-text">Preview tidak tersedia untuk tipe file ini</span>
                <a href="${filePath}" download class="preview-download-btn">⬇️ Download File</a>
            </div>
        `;
    }
}

// Close all previews when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function () {
            if (currentOpenPreview) {
                document.getElementById('preview_' + currentOpenPreview).style.display = 'none';
                currentOpenPreview = null;
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
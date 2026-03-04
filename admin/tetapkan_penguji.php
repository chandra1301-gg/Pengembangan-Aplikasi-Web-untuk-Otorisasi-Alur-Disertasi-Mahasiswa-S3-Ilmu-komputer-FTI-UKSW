<?php
/**
 * File: admin/tetapkan_penguji.php
 * Halaman untuk menetapkan penguji untuk ujian yang sudah di-approve - SUDAH DITAMBAH CO-PROMOTOR 2
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

$success = '';
$error = '';

// Handle penunjukkan penguji - DITAMBAH CO_PROMOTOR dan CO_PROMOTOR2
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tetapkan_penguji'])) {
    $id_registrasi = $_POST['id_registrasi'];
    
    // Handle NULL values untuk promotor & co-promotor
    $promotor = !empty($_POST['promotor']) ? (int)$_POST['promotor'] : NULL;
    $co_promotor = !empty($_POST['co_promotor']) ? (int)$_POST['co_promotor'] : NULL;
    $co_promotor2 = !empty($_POST['co_promotor2']) ? (int)$_POST['co_promotor2'] : NULL;
    
    $tanggal_ujian = $_POST['tanggal_ujian'];
    $tempat = clean_input($_POST['tempat']);
    
    // Collect penguji data
    $penguji_data = [];
    for ($i = 1; $i <= 5; $i++) {
        $penguji_field = 'penguji_' . $i;
        $penguji_data[$penguji_field] = !empty($_POST[$penguji_field]) ? (int)$_POST[$penguji_field] : NULL;
    }
    
    // Validasi minimal 2 penguji
    $penguji_terisi = 0;
    foreach ($penguji_data as $penguji) {
        if (!empty($penguji)) {
            $penguji_terisi++;
        }
    }
    
    if ($penguji_terisi < 2) {
        $error = "Minimal harus memilih 2 penguji!";
    } else {
        // Cek apakah sudah ada jadwal
        $check_sql = "SELECT * FROM jadwal_ujian WHERE id_registrasi = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id_registrasi);
        $check_stmt->execute();
        $existing = $check_stmt->get_result();
        
        if ($existing->num_rows > 0) {
            // Update existing - TAMBAH co_promotor dan co_promotor2
            $sql = "UPDATE jadwal_ujian SET promotor = ?, co_promotor = ?, co_promotor2 = ?,
                    penguji_1 = ?, penguji_2 = ?, penguji_3 = ?, penguji_4 = ?, penguji_5 = ?,
                    tanggal_ujian = ?, tempat = ?, status = 'terjadwal' 
                    WHERE id_registrasi = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiiiiiissi", 
                $promotor, $co_promotor, $co_promotor2,
                $penguji_data['penguji_1'], $penguji_data['penguji_2'], $penguji_data['penguji_3'],
                $penguji_data['penguji_4'], $penguji_data['penguji_5'],
                $tanggal_ujian, $tempat, $id_registrasi
            );
        } else {
            // Insert new - TAMBAH co_promotor dan co_promotor2
            $sql = "INSERT INTO jadwal_ujian (id_registrasi, promotor, co_promotor, co_promotor2,
                    penguji_1, penguji_2, penguji_3, penguji_4, penguji_5, 
                    tanggal_ujian, tempat, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'terjadwal')";
            $stmt = $conn->prepare($sql);
            
            $stmt->bind_param("iiiiiiiiiss", 
                $id_registrasi, $promotor, $co_promotor, $co_promotor2,
                $penguji_data['penguji_1'], $penguji_data['penguji_2'], $penguji_data['penguji_3'],
                $penguji_data['penguji_4'], $penguji_data['penguji_5'],
                $tanggal_ujian, $tempat
            );
        }
        
        if ($stmt->execute()) {
            // ================ KIRIM EMAIL NOTIFIKASI KE DOSEN ================
            require_once '../includes/email_sender.php';
            
            // Ambil data lengkap registrasi
            $detail_query = "SELECT r.*, m.nama_lengkap, m.nim, m.email as student_email, 
                                    r.judul_disertasi, r.jenis_ujian
                            FROM registrasi r 
                            JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
                            WHERE r.id_registrasi = ?";
            $detail_stmt = $conn->prepare($detail_query);
            $detail_stmt->bind_param("i", $id_registrasi);
            $detail_stmt->execute();
            $detail_result = $detail_stmt->get_result();
            $registrasi_data = $detail_result->fetch_assoc();
            
            if ($registrasi_data && testEmailConfiguration()) {
                $student_name = $registrasi_data['nama_lengkap'];
                $student_nim = $registrasi_data['nim'];
                $exam_type = $registrasi_data['jenis_ujian'];
                $judul_disertasi = $registrasi_data['judul_disertasi'];
                $exam_date = $tanggal_ujian;
                $exam_place = $tempat;
                
                // Array untuk melacak dosen yang sudah dikirimi email
                $emails_sent = [];
                
                // 1. Kirim email ke PROMOTOR (jika ada)
                if (!empty($promotor)) {
                    $dosen_query = "SELECT nama_lengkap, email FROM dosen WHERE id_dosen = ?";
                    $dosen_stmt = $conn->prepare($dosen_query);
                    $dosen_stmt->bind_param("i", $promotor);
                    $dosen_stmt->execute();
                    $dosen_result = $dosen_stmt->get_result();
                    $promotor_data = $dosen_result->fetch_assoc();
                    
                    if ($promotor_data && !empty($promotor_data['email'])) {
                        $email_sent = sendDosenAssignmentNotification(
                            $promotor_data['email'],
                            $promotor_data['nama_lengkap'],
                            'promotor',
                            $student_name,
                            $student_nim,
                            $exam_type,
                            $exam_date,
                            $exam_place,
                            $judul_disertasi
                        );
                        
                        if ($email_sent) {
                            $emails_sent[] = "Promotor: {$promotor_data['nama_lengkap']}";
                            error_log("Email penugasan berhasil dikirim ke promotor: {$promotor_data['nama_lengkap']}");
                        }
                    }
                }
                
                // 2. Kirim email ke CO-PROMOTOR 1 (jika ada)
                if (!empty($co_promotor)) {
                    $dosen_query = "SELECT nama_lengkap, email FROM dosen WHERE id_dosen = ?";
                    $dosen_stmt = $conn->prepare($dosen_query);
                    $dosen_stmt->bind_param("i", $co_promotor);
                    $dosen_stmt->execute();
                    $dosen_result = $dosen_stmt->get_result();
                    $co_promotor_data = $dosen_result->fetch_assoc();
                    
                    if ($co_promotor_data && !empty($co_promotor_data['email'])) {
                        $email_sent = sendDosenAssignmentNotification(
                            $co_promotor_data['email'],
                            $co_promotor_data['nama_lengkap'],
                            'co_promotor',
                            $student_name,
                            $student_nim,
                            $exam_type,
                            $exam_date,
                            $exam_place,
                            $judul_disertasi
                        );
                        
                        if ($email_sent) {
                            $emails_sent[] = "Co-Promotor: {$co_promotor_data['nama_lengkap']}";
                            error_log("Email penugasan berhasil dikirim ke co-promotor: {$co_promotor_data['nama_lengkap']}");
                        }
                    }
                }
                
                // 3. Kirim email ke CO-PROMOTOR 2 (jika ada)
                if (!empty($co_promotor2)) {
                    $dosen_query = "SELECT nama_lengkap, email FROM dosen WHERE id_dosen = ?";
                    $dosen_stmt = $conn->prepare($dosen_query);
                    $dosen_stmt->bind_param("i", $co_promotor2);
                    $dosen_stmt->execute();
                    $dosen_result = $dosen_stmt->get_result();
                    $co_promotor2_data = $dosen_result->fetch_assoc();
                    
                    if ($co_promotor2_data && !empty($co_promotor2_data['email'])) {
                        $email_sent = sendDosenAssignmentNotification(
                            $co_promotor2_data['email'],
                            $co_promotor2_data['nama_lengkap'],
                            'co_promotor2',
                            $student_name,
                            $student_nim,
                            $exam_type,
                            $exam_date,
                            $exam_place,
                            $judul_disertasi
                        );
                        
                        if ($email_sent) {
                            $emails_sent[] = "Co-Promotor 2: {$co_promotor2_data['nama_lengkap']}";
                            error_log("Email penugasan berhasil dikirim ke co-promotor2: {$co_promotor2_data['nama_lengkap']}");
                        }
                    }
                }
                
                // 4. Kirim email ke semua PENGGUJI (jika ada)
                foreach ($penguji_data as $penguji_field => $penguji_id) {
                    if (!empty($penguji_id)) {
                        $dosen_query = "SELECT nama_lengkap, email FROM dosen WHERE id_dosen = ?";
                        $dosen_stmt = $conn->prepare($dosen_query);
                        $dosen_stmt->bind_param("i", $penguji_id);
                        $dosen_stmt->execute();
                        $dosen_result = $dosen_stmt->get_result();
                        $penguji_data_row = $dosen_result->fetch_assoc();
                        
                        if ($penguji_data_row && !empty($penguji_data_row['email'])) {
                            $penguji_number = str_replace('penguji_', '', $penguji_field);
                            
                            $email_sent = sendDosenAssignmentNotification(
                                $penguji_data_row['email'],
                                $penguji_data_row['nama_lengkap'],
                                'penguji',
                                $student_name,
                                $student_nim,
                                $exam_type,
                                $exam_date,
                                $exam_place,
                                $judul_disertasi
                            );
                            
                            if ($email_sent) {
                                $emails_sent[] = "Penguji $penguji_number: {$penguji_data_row['nama_lengkap']}";
                                error_log("Email penugasan berhasil dikirim ke penguji: {$penguji_data_row['nama_lengkap']}");
                            }
                        }
                    }
                }
                
                // 5. Kirim email notifikasi ke MAHASISWA bahwa penguji telah ditetapkan
                if (!empty($registrasi_data['student_email'])) {
                    require_once '../includes/email_sender.php';
                    
                    $student_email_sent = sendStudentNotificationPenguji(
                        $registrasi_data['student_email'],
                        $student_name,
                        $exam_type,
                        $exam_date,
                        $exam_place
                    );
                    
                    if ($student_email_sent) {
                        error_log("Email notifikasi penguji berhasil dikirim ke mahasiswa: $student_name");
                    }
                }
                
                // Tambahkan info email yang terkirim ke pesan success
                if (!empty($emails_sent)) {
                    $email_count = count($emails_sent);
                    $success = "Penguji berhasil ditetapkan! Notifikasi telah dikirim ke $email_count dosen.";
                } else {
                    $success = "Penguji berhasil ditetapkan! (Email notifikasi tidak terkirim)";
                }
                
                $detail_stmt->close();
            }
        } else {
            $error = "Gagal menetapkan penguji: " . $stmt->error;
        }
    }
}

// Ambil data ujian yang sudah di-approve dengan data promotor - UPDATE QUERY
$sql_ujian = "SELECT r.*, m.nama_lengkap, m.nim, m.program_studi, 
                     d1.nama_lengkap as nama_promotor, d1.id_dosen as id_promotor,
                     d2.nama_lengkap as nama_co_promotor, d2.id_dosen as id_co_promotor,
                     d3.nama_lengkap as nama_co_promotor2, d3.id_dosen as id_co_promotor2
              FROM registrasi r 
              JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
              LEFT JOIN dosen d1 ON r.promotor = d1.id_dosen
              LEFT JOIN dosen d2 ON r.co_promotor = d2.id_dosen
              LEFT JOIN dosen d3 ON r.co_promotor2 = d3.id_dosen
              WHERE r.status = 'Diterima' 
              ORDER BY r.tanggal_pengajuan DESC";
$result_ujian = db_query($sql_ujian);
$ujian_list = [];
while ($row = mysqli_fetch_assoc($result_ujian)) {
    $ujian_list[] = $row;
}

// Ambil data dosen
$sql_dosen = "SELECT * FROM dosen WHERE status = 'active' OR status IS NULL ORDER BY jabatan, nama_lengkap";
$result_dosen = db_query($sql_dosen);
$dosen_list = [];
while ($row = mysqli_fetch_assoc($result_dosen)) {
    $dosen_list[] = $row;
}

// Ambil data jadwal ujian yang sudah ditetapkan - UPDATE QUERY
$sql_jadwal = "SELECT j.*, 
               d1.nama_lengkap as nama_promotor,
               d2.nama_lengkap as nama_co_promotor,
               d3.nama_lengkap as nama_co_promotor2,
               d4.nama_lengkap as nama_penguji1,
               d5.nama_lengkap as nama_penguji2,
               d6.nama_lengkap as nama_penguji3,
               d7.nama_lengkap as nama_penguji4,
               d8.nama_lengkap as nama_penguji5
               FROM jadwal_ujian j
               LEFT JOIN dosen d1 ON j.promotor = d1.id_dosen
               LEFT JOIN dosen d2 ON j.co_promotor = d2.id_dosen
               LEFT JOIN dosen d3 ON j.co_promotor2 = d3.id_dosen
               LEFT JOIN dosen d4 ON j.penguji_1 = d4.id_dosen
               LEFT JOIN dosen d5 ON j.penguji_2 = d5.id_dosen
               LEFT JOIN dosen d6 ON j.penguji_3 = d6.id_dosen
               LEFT JOIN dosen d7 ON j.penguji_4 = d7.id_dosen
               LEFT JOIN dosen d8 ON j.penguji_5 = d8.id_dosen";
$result_jadwal = db_query($sql_jadwal);
$jadwal_list = [];
while ($row = mysqli_fetch_assoc($result_jadwal)) {
    $jadwal_list[$row['id_registrasi']] = $row;
}

$page_title = "Tetapkan Penguji - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>
<link href="../assets/css/admin-styles.css" rel="stylesheet">
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">👥 Tetapkan Penguji Ujian</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <?php if (empty($ujian_list)): ?>
                            <div class="alert alert-info">
                                Tidak ada ujian yang menunggu penetapan penguji.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>NIM</th>
                                            <th>Jenis Ujian</th>
                                            <th>Judul Disertasi</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Penguji yang Ditugaskan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ujian_list as $ujian): 
                                            $jadwal = isset($jadwal_list[$ujian['id_registrasi']]) ? $jadwal_list[$ujian['id_registrasi']] : null;
                                        ?>
                                            <tr>
                                                <td><?= $ujian['nama_lengkap'] ?></td>
                                                <td><?= $ujian['nim'] ?></td>
                                                <td><?= ucfirst($ujian['jenis_ujian']) ?></td>
                                                <td><?= $ujian['judul_disertasi'] ?></td>
                                                <td><?= $ujian['tanggal_pengajuan'] ?></td>
                                                <td>
                                                    <?php if ($jadwal): ?>
                                                        <div class="penguji-info">
                                                            <small>
                                                                <?php if (!empty($jadwal['nama_promotor'])): ?>
                                                                    <strong>Promotor:</strong> <?= $jadwal['nama_promotor'] ?><br>
                                                                <?php endif; ?>
                                                                <?php if (!empty($jadwal['nama_co_promotor'])): ?>
                                                                    <strong>Co-Promotor:</strong> <?= $jadwal['nama_co_promotor'] ?><br>
                                                                <?php endif; ?>
                                                                <?php if (!empty($jadwal['nama_co_promotor2'])): ?>
                                                                    <strong>Co-Promotor 2:</strong> <?= $jadwal['nama_co_promotor2'] ?><br>
                                                                <?php endif; ?>
                                                                
                                                                <?php for ($i = 1; $i <= 9; $i++): ?>
                                                                    <?php $nama_penguji = $jadwal['nama_penguji' . $i] ?? ''; ?>
                                                                    <?php if (!empty($nama_penguji)): ?>
                                                                        <strong>Penguji <?= $i ?>:</strong> <?= $nama_penguji ?><br>
                                                                    <?php endif; ?>
                                                                <?php endfor; ?>
                                                                
                                                                <strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($jadwal['tanggal_ujian'])) ?><br>
                                                                <strong>Tempat:</strong> <?= $jadwal['tempat'] ?>
                                                            </small>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Belum ditetapkan</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalPenguji<?= $ujian['id_registrasi'] ?>">
                                                        <?= $jadwal ? 'Edit Penguji' : 'Tetapkan Penguji' ?>
                                                    </button>
                                                </td>
                                            </tr>
                                            
                                            <!-- Modal Tetapkan Penguji -->
                                            <div class="modal fade" id="modalPenguji<?= $ujian['id_registrasi'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"><?= $jadwal ? 'Edit' : 'Tetapkan' ?> Penguji - <?= $ujian['nama_lengkap'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST" onsubmit="return validatePengujiForm(<?= $ujian['id_registrasi'] ?>)">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id_registrasi" value="<?= $ujian['id_registrasi'] ?>">
                                                                
                                                                <?php if ($jadwal): ?>
                                                                    <div class="alert alert-info">
                                                                        <small>
                                                                            <strong>Info:</strong> Penguji sudah ditetapkan sebelumnya. 
                                                                            Anda dapat mengubahnya melalui form ini.
                                                                        </small>
                                                                    </div>
                                                                <?php endif; ?>
                                                                
                                                                <!-- Info Promotor & Co-Promotor (Readonly) -->
                                                                <div class="row mb-3">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Promotor</label>
                                                                        <input type="text" class="form-control" 
                                                                               value="<?= !empty($ujian['nama_promotor']) ? $ujian['nama_promotor'] : 'Tidak ada' ?>" 
                                                                               readonly>
                                                                        <input type="hidden" name="promotor" 
                                                                               value="<?= !empty($ujian['id_promotor']) ? $ujian['id_promotor'] : '' ?>">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Co-Promotor</label>
                                                                        <input type="text" class="form-control" 
                                                                               value="<?= !empty($ujian['nama_co_promotor']) ? $ujian['nama_co_promotor'] : 'Tidak ada' ?>" 
                                                                               readonly>
                                                                        <input type="hidden" name="co_promotor" 
                                                                               value="<?= !empty($ujian['id_co_promotor']) ? $ujian['id_co_promotor'] : '' ?>">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Co-Promotor 2</label>
                                                                        <input type="text" class="form-control" 
                                                                               value="<?= !empty($ujian['nama_co_promotor2']) ? $ujian['nama_co_promotor2'] : 'Tidak ada' ?>" 
                                                                               readonly>
                                                                        <input type="hidden" name="co_promotor2" 
                                                                               value="<?= !empty($ujian['id_co_promotor2']) ? $ujian['id_co_promotor2'] : '' ?>">
                                                                    </div>
                                                                </div>
                                                                
                                                                <hr>
                                                                <h6 class="mb-3">Pilih Penguji (Minimal 2)</h6>
                                                                
                                                                <!-- Penguji 1-5 (Optional) -->
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <div class="row mb-3">
                                                                    <div class="col-md-12">
                                                                        <label class="form-label">Penguji <?= $i ?> <small class="text-muted">(Opsional)</small></label>
                                                                        <select name="penguji_<?= $i ?>" class="form-select penguji-select" data-registrasi="<?= $ujian['id_registrasi'] ?>">
                                                                            <option value="">Pilih Penguji <?= $i ?> (Opsional)</option>
                                                                            <?php foreach ($dosen_list as $dosen): ?>
                                                                                <?php 
                                                                                // Cek apakah dosen ini adalah promotor/co-promotor yang aktif
                                                                                $is_promotor = (!empty($ujian['id_promotor']) && $dosen['id_dosen'] == $ujian['id_promotor']);
                                                                                $is_co_promotor = (!empty($ujian['id_co_promotor']) && $dosen['id_dosen'] == $ujian['id_co_promotor']);
                                                                                $is_co_promotor2 = (!empty($ujian['id_co_promotor2']) && $dosen['id_dosen'] == $ujian['id_co_promotor2']);
                                                                                
                                                                                // Hanya tampilkan jika bukan promotor/co-promotor yang aktif
                                                                                if (!$is_promotor && !$is_co_promotor && !$is_co_promotor2):
                                                                                ?>
                                                                                <option value="<?= $dosen['id_dosen'] ?>" 
                                                                                    <?= ($jadwal && isset($jadwal['penguji_'.$i]) && $jadwal['penguji_'.$i] == $dosen['id_dosen']) ? 'selected' : '' ?>>
                                                                                    <?= $dosen['nama_lengkap'] ?> 
                                                                                    <?php if (!empty($dosen['bidang_keahlian'])): ?>
                                                                                        (<?= $dosen['bidang_keahlian'] ?>)
                                                                                    <?php endif; ?>
                                                                                </option>
                                                                                <?php endif; ?>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <?php endfor; ?>
                                                                
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Tanggal Ujian <span class="text-danger">*</span></label>
                                                                        <input type="datetime-local" name="tanggal_ujian" class="form-control" 
                                                                            value="<?= $jadwal ? date('Y-m-d\TH:i', strtotime($jadwal['tanggal_ujian'])) : '' ?>" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Tempat <span class="text-danger">*</span></label>
                                                                        <input type="text" name="tempat" class="form-control" placeholder="Gedung TI UKSW Ruang 101" 
                                                                            value="<?= $jadwal ? $jadwal['tempat'] : '' ?>" required>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="alert alert-info">
                                                                    <small>
                                                                        <strong>Catatan:</strong><br>
                                                                        - Promotor dan Co-Promotor sudah ditetapkan oleh mahasiswa<br>
                                                                        - Penguji 1-5 bersifat opsional (boleh dikosongkan)<br>
                                                                        - <strong>Minimal isi 2 penguji</strong> untuk bisa menyimpan
                                                                    </small>
                                                                </div>
                                                                
                                                                <div id="validationAlert<?= $ujian['id_registrasi'] ?>" class="alert alert-warning d-none">
                                                                    <small>Minimal harus memilih 2 penguji!</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" name="tetapkan_penguji" class="btn btn-primary">Simpan Penetapan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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
function validatePengujiForm(registrasiId) {
    const selects = document.querySelectorAll(`#modalPenguji${registrasiId} .penguji-select`);
    let filledCount = 0;
    
    selects.forEach(select => {
        if (select.value !== '') {
            filledCount++;
        }
    });
    
    const alertDiv = document.getElementById(`validationAlert${registrasiId}`);
    
    if (filledCount < 2) {
        alertDiv.classList.remove('d-none');
        return false;
    } else {
        alertDiv.classList.add('d-none');
        return true;
    }
}

// Real-time validation
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const selects = modal.querySelectorAll('.penguji-select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                const registrasiId = this.getAttribute('data-registrasi');
                validatePengujiForm(registrasiId);
            });
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
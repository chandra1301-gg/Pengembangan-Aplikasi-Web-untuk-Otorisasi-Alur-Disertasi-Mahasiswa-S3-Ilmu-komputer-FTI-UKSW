<?php
/**
 * File: mahasiswa/revisi_disertasi.php
 * Sistem terpadu untuk upload dan konfirmasi revisi disertasi
 * Terintegrasi dengan sistem review revisi penguji
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/email_config.php';
require_once '../includes/email_sender.php';
require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
$id_mahasiswa = $mahasiswa['id_mahasiswa'];

$success_message = '';
$error_message = '';

// Handle upload revisi - GABUNGAN DARI KEDUA FILE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_revisi'])) {
    $id_registrasi = intval($_POST['id_registrasi']);
    $id_penilaian = intval($_POST['id_penilaian']);
    $catatan_mahasiswa = clean_input($_POST['catatan_mahasiswa']);
    
    // Validasi input
    if (empty($id_registrasi) || empty($id_penilaian)) {
        $_SESSION['error_message'] = "Data registrasi atau penilaian tidak valid.";
        header("Location: revisi_disertasi.php");
        exit();
    }
    
    // Upload file revisi
    $upload_dir = '../uploads/revisi/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $file_revisi = '';
    if (isset($_FILES['file_revisi']) && $_FILES['file_revisi']['error'] == 0) {
        // Validasi tipe file
        $allowed_types = ['pdf', 'doc', 'docx'];
        $file_ext = strtolower(pathinfo($_FILES['file_revisi']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            $_SESSION['error_message'] = "Format file tidak didukung. Hanya PDF, DOC, DOCX yang diizinkan.";
            header("Location: revisi_disertasi.php");
            exit();
        }
        
        // Validasi ukuran file (max 10MB)
        if ($_FILES['file_revisi']['size'] > 10 * 1024 * 1024) {
            $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal 10MB.";
            header("Location: revisi_disertasi.php");
            exit();
        }
        
        $file_revisi = 'revisi_' . $id_mahasiswa . '_' . time() . '.' . $file_ext;
        
        if (!move_uploaded_file($_FILES['file_revisi']['tmp_name'], $upload_dir . $file_revisi)) {
            $_SESSION['error_message'] = "Gagal mengupload file revisi.";
            header("Location: revisi_disertasi.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "File revisi harus diupload.";
        header("Location: revisi_disertasi.php");
        exit();
    }
    
    // Insert ke tabel revisi_disertasi (dari konfirmasi_revisi.php)
    $sql = "INSERT INTO revisi_disertasi (id_registrasi, id_penilaian, catatan_revisi, file_revisi, status, tanggal_kirim) 
            VALUES (?, ?, ?, ?, 'dikirim', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $id_registrasi, $id_penilaian, $catatan_mahasiswa, $file_revisi);
    
    if ($stmt->execute()) {
        // Update status di penilaian_ujian (dari revisi.php)
        $sql_update = "UPDATE penilaian_ujian 
                      SET status_revisi = 'diajukan', 
                          file_revisi = ?,
                          tanggal_revisi = NOW()
                      WHERE id_penilaian = ?";
        
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $file_revisi, $id_penilaian);
        $stmt_update->execute();
        
        $_SESSION['success_message'] = "Revisi berhasil dikirim! Menunggu persetujuan dosen.";
        // KIRIM EMAIL NOTIFIKASI HANYA KE DOSEN SPESIFIK yang memberikan catatan
        require_once '../includes/email_sender.php';

        // Ambil data dosen yang memberikan penilaian/catatan ini
        $sql_dosen_penilai = "SELECT d.id_dosen, d.nama_lengkap, d.email, p.catatan as catatan_asli
                            FROM penilaian_ujian p
                            JOIN dosen d ON p.id_dosen = d.id_dosen
                            WHERE p.id_penilaian = ?";
        $stmt_dosen_penilai = $conn->prepare($sql_dosen_penilai);
        $stmt_dosen_penilai->bind_param("i", $id_penilaian);
        $stmt_dosen_penilai->execute();
        $result_dosen_penilai = $stmt_dosen_penilai->get_result();
        $dosen_penilai = $result_dosen_penilai->fetch_assoc();

        // Ambil data mahasiswa
        $sql_mahasiswa = "SELECT m.nama_lengkap, m.nim 
                        FROM registrasi r 
                        JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
                        WHERE r.id_registrasi = ?";
        $stmt_mhs = $conn->prepare($sql_mahasiswa);
        $stmt_mhs->bind_param("i", $id_registrasi);
        $stmt_mhs->execute();
        $result_mhs = $stmt_mhs->get_result();
        $mahasiswa = $result_mhs->fetch_assoc();

        // Ambil data ujian
        $sql_ujian = "SELECT jenis_ujian FROM registrasi WHERE id_registrasi = ?";
        $stmt_ujian = $conn->prepare($sql_ujian);
        $stmt_ujian->bind_param("i", $id_registrasi);
        $stmt_ujian->execute();
        $result_ujian = $stmt_ujian->get_result();
        $ujian = $result_ujian->fetch_assoc();

        if ($dosen_penilai && $mahasiswa && $ujian && testEmailConfiguration()) {
            $dosen_name = $dosen_penilai['nama_lengkap'];
            $dosen_email = $dosen_penilai['email'];
            $student_name = $mahasiswa['nama_lengkap'];
            $student_nim = $mahasiswa['nim'];
            $exam_type = $ujian['jenis_ujian'];
            $original_notes = $dosen_penilai['catatan_asli'] ?? '';
            
            // Kirim email hanya ke dosen yang memberikan catatan
            $email_sent = sendRevisionToSpecificDosen(
                $dosen_email,
                $dosen_name,
                $student_name,
                $student_nim,
                $exam_type,
                $catatan_mahasiswa,
                $file_revisi,
                $original_notes
            );
            
            if ($email_sent) {
                error_log("Email notifikasi revisi spesifik berhasil dikirim ke dosen: " . $dosen_name);
                $_SESSION['success_message'] = "Revisi berhasil dikirim! Notifikasi telah dikirim ke " . $dosen_name . ".";
            } else {
                error_log("Gagal mengirim email notifikasi revisi ke dosen: " . $dosen_name);
                $_SESSION['success_message'] = "Revisi berhasil dikirim! (Email notifikasi gagal dikirim)";
            }
        } else {
            $_SESSION['success_message'] = "Revisi berhasil dikirim!";
        }
    } else {
        $_SESSION['error_message'] = "Gagal mengirim revisi: " . $stmt->error;
        
        // Hapus file yang sudah diupload jika gagal insert database
        if ($file_revisi && file_exists($upload_dir . $file_revisi)) {
            unlink($upload_dir . $file_revisi);
        }
    }
    
    header("Location: revisi_disertasi.php");
    exit();
}

// Ambil data penilaian yang perlu direvisi - QUERY YANG DIOPTIMALKAN
$sql = "SELECT 
            p.*, 
            r.jenis_ujian, 
            r.judul_disertasi, 
            d.nama_lengkap as penilai,
            d.id_dosen,
            p.status_revisi,
            p.tanggal_revisi,
            p.catatan_approval,
            (SELECT COUNT(*) FROM revisi_disertasi rd WHERE rd.id_penilaian = p.id_penilaian) as jumlah_revisi,
            (SELECT MAX(tanggal_kirim) FROM revisi_disertasi rd WHERE rd.id_penilaian = p.id_penilaian) as tanggal_revisi_terakhir,
            (SELECT status FROM revisi_disertasi rd WHERE rd.id_penilaian = p.id_penilaian ORDER BY id_revisi DESC LIMIT 1) as status_revisi_terakhir
        FROM penilaian_ujian p
        JOIN registrasi r ON p.id_registrasi = r.id_registrasi
        JOIN dosen d ON p.id_dosen = d.id_dosen
        WHERE r.id_mahasiswa = ? 
        AND (p.catatan IS NOT NULL AND p.catatan != '')
        AND p.nilai_total IS NOT NULL
        ORDER BY p.tanggal_penilaian DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();

$penilaian_list = [];
while ($row = $result->fetch_assoc()) {
    $penilaian_list[] = $row;
}

// Ambil riwayat revisi untuk setiap penilaian
$riwayat_revisi = [];
foreach ($penilaian_list as $penilaian) {
    $sql_riwayat = "SELECT * FROM revisi_disertasi 
                   WHERE id_penilaian = ? 
                   ORDER BY tanggal_kirim DESC";
    $stmt_riwayat = $conn->prepare($sql_riwayat);
    $stmt_riwayat->bind_param("i", $penilaian['id_penilaian']);
    $stmt_riwayat->execute();
    $result_riwayat = $stmt_riwayat->get_result();
    
    $riwayat_revisi[$penilaian['id_penilaian']] = $result_riwayat->fetch_all(MYSQLI_ASSOC);
}

$page_title = "Revisi Disertasi - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_mahasiswa.php';

// Tampilkan pesan sukses/error
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📝 Revisi Disertasi</h4>
                        <p class="mb-0 mt-2 small opacity-75">
                            Upload file revisi dan konfirmasi revisi yang telah dilakukan berdasarkan catatan dari dosen penguji.
                        </p>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= $success_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($penilaian_list)): ?>
                            <div class="alert alert-info text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-check-circle fa-3x text-muted"></i>
                                </div>
                                <h5>Tidak ada revisi yang perlu dikonfirmasi</h5>
                                <p class="mb-0">Semua penilaian telah selesai atau belum memerlukan revisi.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($penilaian_list as $penilaian): 
                                $status_revisi = $penilaian['status_revisi_terakhir'] ?? $penilaian['status_revisi'];
                                $can_upload = in_array($status_revisi, ['belum', 'ditolak', 'perlu_perbaikan']);
                            ?>
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <?= ucfirst($penilaian['jenis_ujian']); ?> - 
                                            Dinilai oleh: <?= $penilaian['penilai']; ?>
                                        </h5>
                                        <div>
                                            <?php
                                            $status_badge = [
                                                'belum' => 'bg-secondary',
                                                'dikirim' => 'bg-warning',
                                                'diajukan' => 'bg-warning',
                                                'menunggu' => 'bg-warning',
                                                'disetujui' => 'bg-success',
                                                'diterima' => 'bg-success',
                                                'perlu_perbaikan' => 'bg-danger',
                                                'ditolak' => 'bg-danger'
                                            ][$status_revisi] ?? 'bg-secondary';
                                            
                                            $status_text = [
                                                'belum' => 'Belum Revisi',
                                                'dikirim' => 'Revisi Dikirim',
                                                'diajukan' => 'Revisi Diajukan',
                                                'menunggu' => 'Menunggu Review',
                                                'disetujui' => 'Disetujui',
                                                'diterima' => 'Diterima',
                                                'perlu_perbaikan' => 'Perlu Perbaikan',
                                                'ditolak' => 'Ditolak'
                                            ][$status_revisi] ?? ucfirst($status_revisi);
                                            ?>
                                            <span class="badge <?= $status_badge ?>">
                                                <?= $status_text ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Catatan dari Dosen -->
                                        <div class="col-md-6 mb-4">
                                            <h6 class="text-primary mb-3">
                                                <i class="fas fa-sticky-note me-2"></i>Catatan Revisi dari Dosen:
                                            </h6>
                                            <div class="alert alert-warning border-warning">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-exclamation-circle mt-1 me-2 text-warning"></i>
                                                    <div>
                                                        <?= nl2br(htmlspecialchars($penilaian['catatan'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Info Nilai -->
                                            <div class="row mt-3">
                                                <div class="col-6">
                                                    <small class="text-muted">Tanggal Penilaian:</small>
                                                    <div class="fw-bold">
                                                        <?= date('d/m/Y', strtotime($penilaian['tanggal_penilaian'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Form Upload Revisi -->
                                        <div class="col-md-6">
                                            <?php if ($can_upload): ?>
                                                <h6 class="text-primary mb-3">
                                                    <i class="fas fa-upload me-2"></i>Form Upload Revisi:
                                                </h6>
                                                <form method="POST" enctype="multipart/form-data" id="formRevisi<?= $penilaian['id_penilaian'] ?>">
                                                    <input type="hidden" name="id_registrasi" value="<?= $penilaian['id_registrasi']; ?>">
                                                    <input type="hidden" name="id_penilaian" value="<?= $penilaian['id_penilaian']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">
                                                            File Revisi <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="file" name="file_revisi" class="form-control" 
                                                               accept=".pdf,.doc,.docx" required
                                                               onchange="validateFile(this, <?= $penilaian['id_penilaian'] ?>)">
                                                        <div class="form-text">
                                                            Format: PDF, DOC, DOCX (Maks. 10MB)
                                                        </div>
                                                        <div class="invalid-feedback" id="fileError<?= $penilaian['id_penilaian'] ?>"></div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Catatan untuk Dosen</label>
                                                        <textarea name="catatan_mahasiswa" class="form-control" rows="4" 
                                                                  placeholder="Jelaskan revisi yang telah dilakukan, perubahan yang dibuat, atau hal-hal yang ingin dikonfirmasi..."><?= 
                                                            htmlspecialchars($penilaian['catatan_approval'] ? "Revisi berdasarkan catatan: " . $penilaian['catatan_approval'] : '') 
                                                        ?></textarea>
                                                        <div class="form-text">
                                                            Jelaskan secara singkat perubahan yang telah dilakukan
                                                        </div>
                                                    </div>
                                                    
                                                    <button type="submit" name="submit_revisi" class="btn btn-success w-100 py-2">
                                                        <i class="fas fa-paper-plane me-2"></i>Kirim Revisi
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <!-- Status saat revisi sudah diajukan/diterima -->
                                                <div class="text-center py-4">
                                                    <?php if (in_array($status_revisi, ['dikirim', 'diajukan', 'menunggu'])): ?>
                                                        <div class="mb-3">
                                                            <i class="fas fa-clock fa-2x text-warning"></i>
                                                        </div>
                                                        <h6 class="text-warning">Revisi Menunggu Review</h6>
                                                        <p class="text-muted small">
                                                            Revisi Anda telah dikirim dan sedang menunggu review dari dosen.
                                                            <?php if ($penilaian['tanggal_revisi_terakhir']): ?>
                                                                <br>Dikirim pada: <?= date('d/m/Y H:i', strtotime($penilaian['tanggal_revisi_terakhir'])) ?>
                                                            <?php endif; ?>
                                                        </p>
                                                    <?php elseif (in_array($status_revisi, ['disetujui', 'diterima'])): ?>
                                                        <div class="mb-3">
                                                            <i class="fas fa-check-circle fa-2x text-success"></i>
                                                        </div>
                                                        <h6 class="text-success">Revisi Disetujui</h6>
                                                        <p class="text-muted small">
                                                            Revisi Anda telah disetujui oleh dosen.
                                                            <?php if ($penilaian['tanggal_revisi']): ?>
                                                                <br>Disetujui pada: <?= date('d/m/Y H:i', strtotime($penilaian['tanggal_revisi'])) ?>
                                                            <?php endif; ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($penilaian['file_revisi']): ?>
                                                        <a href="../uploads/revisi/<?= $penilaian['file_revisi'] ?>" 
                                                           target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                                            <i class="fas fa-eye me-1"></i>Lihat File Revisi
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Riwayat Revisi -->
                                    <?php if (!empty($riwayat_revisi[$penilaian['id_penilaian']])): ?>
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-history me-2"></i>Riwayat Revisi
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>File</th>
                                                        <th>Catatan</th>
                                                        <th>Status</th>
                                                        <th>Catatan Dosen</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($riwayat_revisi[$penilaian['id_penilaian']] as $riwayat): ?>
                                                    <tr>
                                                        <td class="text-nowrap">
                                                            <?= date('d/m/Y H:i', strtotime($riwayat['tanggal_kirim'])) ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($riwayat['file_revisi']): ?>
                                                                <a href="../uploads/revisi/<?= $riwayat['file_revisi'] ?>" 
                                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-download me-1"></i>Download
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?= $riwayat['catatan_revisi'] ? nl2br(htmlspecialchars($riwayat['catatan_revisi'])) : '<span class="text-muted">-</span>' ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $status_badge_riwayat = [
                                                                'dikirim' => 'bg-warning',
                                                                'disetujui' => 'bg-success',
                                                                'perlu_perbaikan' => 'bg-danger'
                                                            ][$riwayat['status']] ?? 'bg-secondary';
                                                            
                                                            $status_text_riwayat = [
                                                                'dikirim' => 'Dikirim',
                                                                'disetujui' => 'Disetujui',
                                                                'perlu_perbaikan' => 'Perlu Perbaikan'
                                                            ][$riwayat['status']] ?? ucfirst($riwayat['status']);
                                                            ?>
                                                            <span class="badge <?= $status_badge_riwayat ?>">
                                                                <?= $status_text_riwayat ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?= $riwayat['catatan_dosen'] ? nl2br(htmlspecialchars($riwayat['catatan_dosen'])) : '<span class="text-muted">-</span>' ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Informasi Sistem -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Sistem Revisi</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Alur Revisi:</h6>
                                <ol class="small">
                                    <li>Baca catatan revisi dari dosen</li>
                                    <li>Lakukan perbaikan pada disertasi</li>
                                    <li>Upload file revisi dan beri catatan</li>
                                    <li>Tunggu review dari dosen</li>
                                    <li>Jika perlu perbaikan, ulangi proses</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6>Status Revisi:</h6>
                                <ul class="small list-unstyled">
                                    <li><span class="badge bg-secondary">Belum Revisi</span> - Belum mengupload revisi</li>
                                    <li><span class="badge bg-warning">Menunggu Review</span> - Revisi telah dikirim</li>
                                    <li><span class="badge bg-success">Disetujui</span> - Revisi telah diterima</li>
                                    <li><span class="badge bg-danger">Perlu Perbaikan</span> - Revisi perlu diperbaiki</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validasi file sebelum upload
function validateFile(input, idPenilaian) {
    const file = input.files[0];
    const errorDiv = document.getElementById('fileError' + idPenilaian);
    const form = document.getElementById('formRevisi' + idPenilaian);
    
    if (file) {
        // Validasi ukuran file (10MB)
        if (file.size > 10 * 1024 * 1024) {
            errorDiv.textContent = 'Ukuran file terlalu besar. Maksimal 10MB.';
            input.classList.add('is-invalid');
            return false;
        }
        
        // Validasi tipe file
        const allowedTypes = ['application/pdf', 'application/msword', 
                             'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const allowedExtensions = ['pdf', 'doc', 'docx'];
        
        if (!allowedExtensions.includes(fileExtension)) {
            errorDiv.textContent = 'Format file tidak didukung. Hanya PDF, DOC, DOCX yang diizinkan.';
            input.classList.add('is-invalid');
            return false;
        }
        
        input.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }
    
    return false;
}

// Konfirmasi sebelum submit
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[id^="formRevisi"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const fileInput = this.querySelector('input[type="file"]');
            if (fileInput && !fileInput.files[0]) {
                e.preventDefault();
                alert('Silakan pilih file revisi sebelum mengirim.');
                return false;
            }
            
            if (!confirm('Apakah Anda yakin ingin mengirim revisi? Pastikan file dan catatan sudah benar.')) {
                e.preventDefault();
                return false;
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
<?php
/**
 * File: penguji/form_penilaian.php
 * Form penilaian universal untuk semua jenis ujian - SUPPORT CO-PROMOTOR
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/template_penilaian.php';

require_once '../includes/email_config.php';
require_once '../includes/email_sender.php';

require_dosen();

// PERBAIKAN: Ambil parameter dengan nama yang benar
$id_registrasi = $_GET['id_registrasi'] ?? $_GET['id'] ?? 0;
$jenis_penilaian = $_GET['jenis'] ?? 'penguji';

// Validasi parameter
if (!$id_registrasi) {
    die("Error: Parameter id_registrasi tidak ditemukan.");
}

// Ambil ID dosen
$user_id = $_SESSION['user_id'];
$sql_dosen = "SELECT id_dosen FROM dosen WHERE user_id = ?";
$stmt_dosen = $conn->prepare($sql_dosen);
$stmt_dosen->bind_param("i", $user_id);
$stmt_dosen->execute();
$result_dosen = $stmt_dosen->get_result();
$dosen_data = $result_dosen->fetch_assoc();

if (!$dosen_data) {
    die("Data dosen tidak ditemukan untuk user ini.");
}

$id_dosen = $dosen_data['id_dosen'];

// Ambil data registrasi
$sql_registrasi = "SELECT r.*, m.nama_lengkap, m.nim, m.program_studi, 
                          j.tanggal_ujian, j.tempat, d1.nama_lengkap as nama_promotor,
                          d2.nama_lengkap as nama_co_promotor,d3.nama_lengkap as nama_co_promotor2,
                          d4.nama_lengkap as nama_penguji,
                          j.promotor as id_promotor_jadwal, j.penguji_1, j.penguji_2, j.penguji_3
                   FROM registrasi r
                   JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa
                   LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi
                   LEFT JOIN dosen d1 ON r.promotor = d1.id_dosen
                   LEFT JOIN dosen d2 ON r.co_promotor = d2.id_dosen
                     LEFT JOIN dosen d3 ON r.co_promotor2 = d3.id_dosen
                     LEFT JOIN dosen d4 ON (j.penguji_1 = d4.id_dosen OR j.penguji_2 = d4.id_dosen OR j.penguji_3 = d4.id_dosen)
                   WHERE r.id_registrasi = ? AND r.status = 'Diterima'";
$stmt_reg = $conn->prepare($sql_registrasi);
$stmt_reg->bind_param("i", $id_registrasi);
$stmt_reg->execute();
$result_reg = $stmt_reg->get_result();
$registrasi = $result_reg->fetch_assoc();

if (!$registrasi) {
    die("Data registrasi tidak ditemukan.");
}

// PERBAIKAN: Cek apakah dosen berhak menilai ujian ini (termasuk co-promotor)
$sql_cek = "SELECT COUNT(*) as boleh_nilai 
            FROM jadwal_ujian j
            WHERE j.id_registrasi = ? AND 
                  (j.promotor = ? OR j.penguji_1 = ? OR j.penguji_2 = ? OR j.penguji_3 = ?)";
$stmt_cek = $conn->prepare($sql_cek);
$stmt_cek->bind_param("iiiii", $id_registrasi, $id_dosen, $id_dosen, $id_dosen, $id_dosen);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();
$cek_data = $result_cek->fetch_assoc();

if (!$cek_data['boleh_nilai']) {
    // PERBAIKAN: Cek juga dari tabel registrasi (promotor/co-promotor) - co-promotor sekarang bisa menilai
    $sql_cek_reg = "SELECT COUNT(*) as boleh_nilai 
                    FROM registrasi 
                    WHERE id_registrasi = ? AND (promotor = ? OR co_promotor = ? OR co_promotor2 = ?)";
    $stmt_cek_reg = $conn->prepare($sql_cek_reg);
    $stmt_cek_reg->bind_param("iiii", $id_registrasi, $id_dosen, $id_dosen, $id_dosen);
    $stmt_cek_reg->execute();
    $result_cek_reg = $stmt_cek_reg->get_result();
    $cek_data_reg = $result_cek_reg->fetch_assoc();
    
    if (!$cek_data_reg['boleh_nilai']) {
        die("Anda tidak berhak menilai ujian ini.");
    }
}

// Tentukan peran dosen saat ini untuk menentukan jenis penilaian
$peran_dosen = '';
if ($registrasi['promotor'] == $id_dosen || $registrasi['id_promotor_jadwal'] == $id_dosen) {
    $peran_dosen = 'promotor';
    $jenis_penilaian = 'promotor'; // Force jenis penilaian untuk promotor
} elseif ($registrasi['co_promotor'] == $id_dosen) {
    $peran_dosen = 'co_promotor';
    $jenis_penilaian = 'promotor'; // Co-promotor juga menggunakan jenis penilaian promotor
} elseif ($registrasi['co_promotor2'] == $id_dosen) {
    $peran_dosen = 'co_promotor2';
    $jenis_penilaian = 'promotor'; // Co-promotor2 juga menggunakan jenis penilaian promotor
} elseif (in_array($id_dosen, [$registrasi['penguji_1'], $registrasi['penguji_2'], $registrasi['penguji_3']])) {
    $peran_dosen = 'penguji';
    $jenis_penilaian = 'penguji'; // Force jenis penilaian untuk penguji
}

// Ambil template penilaian berdasarkan jenis ujian
$template = getTemplatePenilaian($registrasi['jenis_ujian']);
$ujian_title = getUjianTitle($registrasi['jenis_ujian']);
$ujian_description = getUjianDescription($registrasi['jenis_ujian']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_nilai'])) {
    $catatan = clean_input($_POST['catatan']);
    $jenis_penilaian = clean_input($_POST['jenis_nilai']); // Ambil dari form
    
    // Hitung total nilai
    $total_nilai = 0;
    foreach ($template as $index => $aspek) {
        $nilai_aspek = $_POST['nilai_' . $index] ?? 0;
        $bobot = $aspek['bobot'];
        $total_nilai += ($nilai_aspek * $bobot) / 100;
    }
    
    // Pastikan tipe data benar
    $id_registrasi_int = (int)$id_registrasi;
    $id_dosen_int = (int)$id_dosen;
    $total_nilai_float = (float)$total_nilai;
    
    // Cek apakah sudah ada penilaian
    $sql_cek_penilaian = "SELECT id_penilaian FROM penilaian_ujian 
                          WHERE id_registrasi = ? AND id_dosen = ?";
    $stmt_cek_pen = $conn->prepare($sql_cek_penilaian);
    $stmt_cek_pen->bind_param("ii", $id_registrasi_int, $id_dosen_int);
    $stmt_cek_pen->execute();
    $result_cek_pen = $stmt_cek_pen->get_result();
    $existing_penilaian = $result_cek_pen->fetch_assoc();
    
    // Mulai transaction
    mysqli_begin_transaction($conn);
    
    try {
        if ($existing_penilaian) {
            // Update penilaian existing
            $id_penilaian = $existing_penilaian['id_penilaian'];
            $sql_penilaian = "UPDATE penilaian_ujian 
                             SET nilai_total = ?, catatan = ?, tanggal_penilaian = NOW(),
                                 jenis_nilai = ?
                             WHERE id_penilaian = ?";
            $stmt_penilaian = $conn->prepare($sql_penilaian);
            
            $stmt_penilaian->bind_param("dssi", $total_nilai_float, $catatan, $jenis_penilaian, $id_penilaian);
            $stmt_penilaian->execute();
            
            // Hapus detail penilaian lama
            $sql_delete_detail = "DELETE FROM detail_penilaian WHERE id_penilaian = ?";
            $stmt_delete = $conn->prepare($sql_delete_detail);
            $stmt_delete->bind_param("i", $id_penilaian);
            $stmt_delete->execute();
        } else {
            // Insert penilaian baru
            $sql_penilaian = "INSERT INTO penilaian_ujian (id_registrasi, id_dosen, jenis_nilai, nilai_total, catatan, tanggal_penilaian)
                             VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt_penilaian = $conn->prepare($sql_penilaian);
            
            $stmt_penilaian->bind_param("iisds", $id_registrasi_int, $id_dosen_int, $jenis_penilaian, $total_nilai_float, $catatan);
            $stmt_penilaian->execute();
            $id_penilaian = $stmt_penilaian->insert_id;
        }
        
        // Insert detail penilaian
        $sql_detail = "INSERT INTO detail_penilaian (id_penilaian, aspek_penilaian, bobot, nilai) 
                      VALUES (?, ?, ?, ?)";
        $stmt_detail = $conn->prepare($sql_detail);
        
        foreach ($template as $index => $aspek) {
            $nilai_aspek = $_POST['nilai_' . $index] ?? 0;
            $nilai_aspek_float = (float)$nilai_aspek;
            $bobot_float = (float)$aspek['bobot'];
            
            $stmt_detail->bind_param("isdd", $id_penilaian, $aspek['aspek'], $bobot_float, $nilai_aspek_float);
            $stmt_detail->execute();
        }
        
        // Commit transaction
        mysqli_commit($conn);
        // Kirim email notifikasi revisi ke mahasiswa jika ada catatan
        if (!empty($catatan)) {
            // Ambil data mahasiswa untuk email
            $sql_mahasiswa = "SELECT m.email, m.nama_lengkap 
                            FROM registrasi r 
                            JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
                            WHERE r.id_registrasi = ?";
            $stmt_mhs = $conn->prepare($sql_mahasiswa);
            $stmt_mhs->bind_param("i", $id_registrasi_int);
            $stmt_mhs->execute();
            $result_mhs = $stmt_mhs->get_result();
            $mahasiswa_data = $result_mhs->fetch_assoc();
            
            // Ambil data dosen
            $sql_dosen_info = "SELECT nama_lengkap FROM dosen WHERE id_dosen = ?";
            $stmt_dosen_info = $conn->prepare($sql_dosen_info);
            $stmt_dosen_info->bind_param("i", $id_dosen_int);
            $stmt_dosen_info->execute();
            $result_dosen_info = $stmt_dosen_info->get_result();
            $dosen_data = $result_dosen_info->fetch_assoc();
            
            if ($mahasiswa_data && $dosen_data && testEmailConfiguration()) {
                $email_sent = sendRevisionNotification(
                    $mahasiswa_data['email'],
                    $mahasiswa_data['nama_lengkap'],
                    $dosen_data['nama_lengkap'],
                    $registrasi['jenis_ujian'],
                    $catatan,
                    7 // deadline 7 hari
                );
                
                if ($email_sent) {
                    error_log("Email notifikasi revisi berhasil dikirim ke mahasiswa: " . $mahasiswa_data['nama_lengkap']);
                    $_SESSION['success_message'] = "Penilaian berhasil disimpan! Notifikasi revisi telah dikirim ke mahasiswa.";
                }
            }
        }
        
        $_SESSION['success_message'] = "Penilaian {$ujian_title} berhasil disimpan! Nilai Total: " . number_format($total_nilai, 2) . " (" . calculateGrade($total_nilai) . ")";
        header("Location: daftar_ujian.php");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Gagal menyimpan penilaian: " . $e->getMessage();
    }
}

$page_title = "Form Penilaian {$ujian_title} - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_penguji.php';
?>

<style>
.form-penilaian-container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.header-penilaian {
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 3px solid #2c3e50;
    padding-bottom: 20px;
}

.header-penilaian h1 {
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 24px;
}

.header-penilaian h2 {
    color: #3498db;
    margin-bottom: 5px;
    font-size: 18px;
}

.info-mahasiswa {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border-left: 4px solid #3498db;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    font-weight: bold;
    color: #2c3e50;
    margin-right: 10px;
}

.info-value {
    color: #34495e;
}

.table-penilaian {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
    background: white;
}

.table-penilaian th {
    background: #2c3e50;
    color: white;
    padding: 12px;
    text-align: left;
    border: 1px solid #34495e;
}

.table-penilaian td {
    padding: 12px;
    border: 1px solid #ddd;
}

.table-penilaian tr:nth-child(even) {
    background: #f8f9fa;
}

.table-penilaian tr:hover {
    background: #e8f4fd;
}

.total-row {
    background: #2c3e50 !important;
    color: white;
    font-weight: bold;
}

.total-nilai {
    text-align: center;
    font-size: 18px;
}

.input-nilai {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
}

.input-nilai:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #2c3e50;
}

.form-control-custom {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-control-custom:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}

.keterangan-nilai {
    background: #e8f4fd;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #3498db;
}

.keterangan-nilai h5 {
    margin-bottom: 10px;
    color: #2c3e50;
}

.keterangan-nilai p {
    margin: 5px 0;
    color: #34495e;
}

.btn-submit {
    background: #27ae60;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-submit:hover {
    background: #219a52;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>
<div class="main-content">
    <div class="container-fluid">
        <div class="form-penilaian-container">
            <div class="header-penilaian">
                <h1>LEMBAR PENILAIAN <?= $ujian_title ?></h1>
                <h2>PROGRAM STUDI DOKTOR ILMU KOMPUTER - FAKULTAS TEKNOLOGI INFORMASI UKSW</h2>
                <p style="color: #666; font-size: 14px; margin-top: 10px;">
                    <?= $ujian_description ?>
                </p>
                <!-- Tampilkan peran dosen -->
                <?php if ($peran_dosen): ?>
                <div style="margin-top: 10px;">
                    <span class="badge bg-<?= $peran_dosen == 'promotor' ? 'success' : ($peran_dosen == 'co_promotor' ? 'info' : 'primary') ?>">
                        Anda bertugas sebagai: <?= ucfirst(str_replace('_', ' ', $peran_dosen)) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <!-- Info Mahasiswa -->
            <div class="info-mahasiswa">
                <div class="info-row">
                    <div>
                        <span class="info-label">Nama Mahasiswa:</span>
                        <span class="info-value"><?= $registrasi['nama_lengkap'] ?></span>
                    </div>
                    <div>
                        <span class="info-label">NIM:</span>
                        <span class="info-value"><?= $registrasi['nim'] ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div>
                        <span class="info-label">Program Studi:</span>
                        <span class="info-value"><?= $registrasi['program_studi'] ?></span>
                    </div>
                    <div>
                        <span class="info-label">Penguji:</span>
                        <span class="info-value"><?= $registrasi['nama_penguji'] ?? 'Belum ditetapkan' ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div>
                        <span class="info-label">Promotor:</span>
                        <span class="info-value"><?= $registrasi['nama_promotor'] ?? 'Belum ditetapkan' ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div>
                        <span class="info-label">Co-Promotor:</span>
                        <span class="info-value"><?= $registrasi['nama_co_promotor'] ?? 'Belum ditetapkan' ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div>
                        <span class="info-label">Co-Promotor 2:</span>
                        <span class="info-value"><?= $registrasi['nama_co_promotor2'] ?? 'Belum ditetapkan' ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div style="width: 100%;">
                        <span class="info-label">Judul Disertasi:</span>
                        <span class="info-value"><?= $registrasi['judul_disertasi'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Form Penilaian -->
            <form method="POST">
                <!-- PERBAIKAN: Jenis penilaian disesuaikan dengan peran -->
                <div class="form-group">
                    <label class="form-label">Jenis Penilaian:</label>
                    <select name="jenis_nilai" class="form-control-custom" required style="width: 200px;" 
                            <?= $peran_dosen ? 'disabled' : '' ?>>
                        <option value="promotor" <?= $jenis_penilaian == 'promotor' ? 'selected' : '' ?>>Promotor</option>
                        <option value="penguji" <?= $jenis_penilaian == 'penguji' ? 'selected' : '' ?>>Penguji</option>
                    </select>
                    <?php if ($peran_dosen): ?>
                        <input type="hidden" name="jenis_nilai" value="<?= $jenis_penilaian ?>">
                        <small class="text-muted">Jenis penilaian ditentukan otomatis berdasarkan peran Anda</small>
                    <?php endif; ?>
                </div>

                <table class="table-penilaian">
                    <thead>
                        <tr>
                            <th width="60%">ASPEK PENILAIAN</th>
                            <th width="15%">BOBOT (%)</th>
                            <th width="25%">NILAI (0-100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_bobot = 0;
                        foreach ($template as $index => $aspek): 
                            $total_bobot += $aspek['bobot'];
                        ?>
                        <tr>
                            <td><?= $aspek['aspek'] ?></td>
                            <td style="text-align: center;"><?= number_format($aspek['bobot'], 1) ?></td>
                            <td>
                                <input type="number" 
                                    name="nilai_<?= $index ?>" 
                                    class="input-nilai" 
                                    min="0" 
                                    max="100" 
                                    step="0.1" 
                                    required
                                    placeholder="0-100"
                                    onchange="validateInput(this)">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td><strong>TOTAL NILAI AKHIR</strong></td>
                            <td style="text-align: center;"><strong><?= number_format($total_bobot, 1) ?></strong></td>
                            <td class="total-nilai">
                                <span id="total-nilai-display">0.00</span>
                                <div id="grade-display" style="font-size: 12px; margin-top: 5px;"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-group">
                    <label class="form-label">CATATAN DAN SARAN PERBAIKAN:</label>
                    <textarea name="catatan" class="form-control-custom" rows="4" placeholder="Berikan catatan konstruktif, saran perbaikan, dan apresiasi untuk mahasiswa..."></textarea>
                </div>

                <!-- Keterangan Nilai -->
                <div class="keterangan-nilai">
                    <h5>PEDOMAN PENILAIAN</h5>
                    <p>> 85 &nbsp;&nbsp;&nbsp;&nbsp;: A (Sangat Memuaskan)</p>
                    <p>80 – 85 : AB (Memuaskan)</p>
                    <p>70 – 80 &nbsp;&nbsp;: B (Cukup Memuaskan)</p>
                    <p>< 70 &nbsp;&nbsp;&nbsp;&nbsp;: TIDAK LULUS (Perlu Perbaikan Signifikan)</p>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" name="submit_nilai" class="btn-submit">
                        💾 SIMPAN PENILAIAN
                    </button>
                    <a href="daftar_ujian.php" style="margin-left: 15px; padding: 12px 25px; background: #95a5a6; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">
                        ↩️ KEMBALI
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript sama seperti sebelumnya
function validateInput(input) {
    if (input.value < 0) input.value = 0;
    if (input.value > 100) input.value = 100;
    calculateTotal();
}

function calculateGrade(nilai) {
    if (nilai > 85) return { grade: 'A', color: '#27ae60', text: 'Sangat Memuaskan' };
    if (nilai > 80) return { grade: 'AB', color: '#3498db', text: 'Memuaskan' };
    if (nilai >= 70) return { grade: 'B', color: '#f39c12', text: 'Cukup Memuaskan' };
    return { grade: 'TIDAK LULUS', color: '#e74c3c', text: 'Perlu Perbaikan Signifikan' };
}

function calculateTotal() {
    let total = 0;
    
    <?php foreach ($template as $index => $aspek): ?>
        const nilai<?= $index ?> = parseFloat(document.querySelector('[name="nilai_<?= $index ?>"]').value) || 0;
        total += (nilai<?= $index ?> * <?= $aspek['bobot'] ?>) / 100;
    <?php endforeach; ?>
    
    const totalDisplay = document.getElementById('total-nilai-display');
    const gradeDisplay = document.getElementById('grade-display');
    
    totalDisplay.textContent = total.toFixed(2);
    
    const gradeInfo = calculateGrade(total);
    gradeDisplay.innerHTML = `<strong style="color: ${gradeInfo.color}">${gradeInfo.grade} - ${gradeInfo.text}</strong>`;
}

// Hitung total saat halaman dimuat dan setiap input berubah
document.addEventListener('DOMContentLoaded', function() {
    const inputNilais = document.querySelectorAll('.input-nilai');
    inputNilais.forEach(input => {
        input.addEventListener('input', calculateTotal);
        input.addEventListener('change', function() {
            validateInput(this);
        });
    });
    calculateTotal();
});
</script>

<?php include '../includes/footer.php'; ?>
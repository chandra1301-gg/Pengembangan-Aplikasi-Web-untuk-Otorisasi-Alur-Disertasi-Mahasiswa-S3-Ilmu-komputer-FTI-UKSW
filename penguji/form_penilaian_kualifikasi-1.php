<?php
/**
 * File: penguji/form_penilaian_kualifikasi.php
 * Form penilaian khusus ujian kualifikasi
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/template_penilaian.php';

// Cek role dosen/penguji
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'dosen') {
    header('Location: ../login.php');
    exit;
}

$id_registrasi = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data ujian kualifikasi
$sql = "SELECT r.*, m.nama_lengkap, m.nim, m.program_studi, 
               j.tanggal_ujian, j.tempat, d1.nama_lengkap as promotor, 
               d2.nama_lengkap as co_promotor
        FROM registrasi r 
        JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
        LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi 
        LEFT JOIN dosen d1 ON j.promotor = d1.id_dosen 
        LEFT JOIN dosen d2 ON j.penguji_1 = d2.id_dosen 
        WHERE r.id_registrasi = ? AND r.jenis_ujian = 'kualifikasi'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_registrasi);
$stmt->execute();
$result = $stmt->get_result();
$ujian = $result->fetch_assoc();

if (!$ujian) {
    die("Data ujian kualifikasi tidak ditemukan");
}

// Ambil template penilaian kualifikasi
$template = getTemplatePenilaian('kualifikasi');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_nilai'])) {
    $id_dosen = $_SESSION['user_id'];
    $jenis_nilai = $_POST['jenis_nilai'];
    $catatan = clean_input($_POST['catatan']);
    
    // Hitung total nilai
    $total_nilai = 0;
    
    foreach ($template as $index => $aspek) {
        $nilai_aspek = $_POST['nilai_' . $index];
        $bobot = $aspek['bobot'];
        $total_nilai += ($nilai_aspek * $bobot) / 100;
    }
    
    // Simpan ke database
    $sql = "INSERT INTO penilaian_ujian (id_registrasi, id_dosen, jenis_nilai, nilai_total, catatan) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisds", $id_registrasi, $id_dosen, $jenis_nilai, $total_nilai, $catatan);
    
    if ($stmt->execute()) {
        $id_penilaian = $stmt->insert_id;
        
        // Simpan detail penilaian
        foreach ($template as $index => $aspek) {
            $nilai_aspek = $_POST['nilai_' . $index];
            $sql_detail = "INSERT INTO detail_penilaian (id_penilaian, aspek_penilaian, bobot, nilai) 
                          VALUES (?, ?, ?, ?)";
            $stmt_detail = $conn->prepare($sql_detail);
            $stmt_detail->bind_param("isdd", $id_penilaian, $aspek['aspek'], $aspek['bobot'], $nilai_aspek);
            $stmt_detail->execute();
        }
        
        $success = "Penilaian ujian kualifikasi berhasil disimpan! Nilai Total: " . number_format($total_nilai, 2) . " (" . calculateGrade($total_nilai) . ")";
    } else {
        $error = "Gagal menyimpan penilaian: " . $stmt->error;
    }
}

$page_title = "Form Penilaian Ujian - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    background: #f8f9fa;
}

.form-penilaian-container {
    max-width: 1000px;
    margin: 20px auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.header-penilaian {
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #1C5EBC;
    padding-bottom: 20px;
}

.header-penilaian h1 {
    color: #1C5EBC;
    font-size: 24px;
    margin-bottom: 10px;
}

.header-penilaian h2 {
    color: #333;
    font-size: 18px;
    font-weight: 500;
}

.info-mahasiswa {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border-left: 4px solid #1C5EBC;
}

.info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 10px;
}

.info-label {
    font-weight: 600;
    color: #555;
}

.info-value {
    color: #333;
}

.table-penilaian {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

.table-penilaian th,
.table-penilaian td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

.table-penilaian th {
    background: #1C5EBC;
    color: white;
    font-weight: 600;
}

.table-penilaian tr:nth-child(even) {
    background: #f8f9fa;
}

.input-nilai {
    width: 80px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
}

.input-nilai:focus {
    border-color: #1C5EBC;
    outline: none;
}

.total-row {
    background: #e8f4fd !important;
    font-weight: 600;
}

.total-nilai {
    font-size: 16px;
    color: #1C5EBC;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.btn-submit {
    background: #1C5EBC;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-submit:hover {
    background: #154a8a;
}

.keterangan-nilai {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 5px;
    padding: 15px;
    margin-top: 20px;
}

.keterangan-nilai h5 {
    color: #856404;
    margin-bottom: 10px;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
</style>

<!-- HTML form yang sama dengan form_penilaian.php, tapi dengan header khusus -->
<div class="form-penilaian-container">
    <div class="header-penilaian">
        <h1>LEMBAR PENILAIAN UJIAN KUALIFIKASI</h1>
        <h2>PROGRAM STUDI DOKTOR ILMU KOMPUTER</h2>
        <p style="color: #666; font-size: 14px; margin-top: 10px;">
            Penilaian Kemampuan Dasar dan Metodologi Penelitian
        </p>
    </div>
    
    <!-- Sisanya sama dengan form_penilaian.php -->
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
                <span class="info-label">Nama:</span>
                <span class="info-value"><?= $ujian['nama_lengkap'] ?></span>
            </div>
            <div>
                <span class="info-label">NIM:</span>
                <span class="info-value"><?= $ujian['nim'] ?></span>
            </div>
        </div>
        <div class="info-row">
            <div>
                <span class="info-label">Program Studi:</span>
                <span class="info-value"><?= $ujian['program_studi'] ?></span>
            </div>
            <div>
                <span class="info-label">Tanggal Ujian:</span>
                <span class="info-value"><?= $ujian['tanggal_ujian'] ? date('d F Y', strtotime($ujian['tanggal_ujian'])) : 'Belum dijadwalkan' ?></span>
            </div>
        </div>
        <div class="info-row">
            <div>
                <span class="info-label">Judul Disertasi:</span>
                <span class="info-value"><?= $ujian['judul_disertasi'] ?></span>
            </div>
        </div>
    </div>

    <!-- Form Penilaian -->
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Jenis Penilaian:</label>
            <select name="jenis_nilai" class="form-control-custom" required style="width: 200px;">
                <option value="promotor">Promotor</option>
                <option value="penguji">Penguji</option>
            </select>
        </div>

        <table class="table-penilaian">
            <thead>
                <tr>
                    <th width="60%">MATERI YANG DINILAI</th>
                    <th width="15%">BOBOT (%)</th>
                    <th width="25%">NILAI 0-100</th>
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
                    <td><?= $aspek['bobot'] ?></td>
                    <td>
                        <input type="number" 
                               name="nilai_<?= $index ?>" 
                               class="input-nilai" 
                               min="0" 
                               max="100" 
                               step="0.1" 
                               required
                               placeholder="0-100">
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td><strong>TOTAL NILAI</strong></td>
                    <td><strong><?= $total_bobot ?></strong></td>
                    <td class="total-nilai">
                        <span id="total-nilai-display">0.00</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="form-group">
            <label class="form-label">CATATAN:</label>
            <textarea name="catatan" class="form-control-custom" rows="4" placeholder="Berikan catatan untuk mahasiswa..."></textarea>
        </div>

        <!-- Keterangan Nilai -->
        <div class="keterangan-nilai">
            <h5>KETERANGAN NILAI</h5>
            <p>> 85 &nbsp;&nbsp;&nbsp;&nbsp;: A</p>
            <p>> 80 – 85 : AB</p>
            <p>70 – 80 &nbsp;&nbsp;: B</p>
            <p>< 70 &nbsp;&nbsp;&nbsp;&nbsp;: TIDAK LULUS</p>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" name="submit_nilai" class="btn-submit">
                SIMPAN PENILAIAN
            </button>
        </div>
    </form>
</div>

<script>
// Hitung total nilai secara real-time
document.addEventListener('DOMContentLoaded', function() {
    const inputNilais = document.querySelectorAll('.input-nilai');
    const totalDisplay = document.getElementById('total-nilai-display');
    
    function calculateTotal() {
        let total = 0;
        let totalBobot = 0;
        
        <?php foreach ($template as $index => $aspek): ?>
            const nilai<?= $index ?> = parseFloat(document.querySelector('[name="nilai_<?= $index ?>"]').value) || 0;
            total += (nilai<?= $index ?> * <?= $aspek['bobot'] ?>) / 100;
            totalBobot += <?= $aspek['bobot'] ?>;
        <?php endforeach; ?>
        
        totalDisplay.textContent = total.toFixed(2);
    }
    
    inputNilais.forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
    
    // Validasi input nilai
    inputNilais.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 0) this.value = 0;
            if (this.value > 100) this.value = 100;
            calculateTotal();
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
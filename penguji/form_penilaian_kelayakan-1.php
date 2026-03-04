<?php
/**
 * File: penguji/form_penilaian_kelayakan.php
 * Form penilaian khusus ujian kelayakan
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

// Ambil data ujian kelayakan
$sql = "SELECT r.*, m.nama_lengkap, m.nim, m.program_studi, 
               j.tanggal_ujian, j.tempat, d1.nama_lengkap as promotor, 
               d2.nama_lengkap as co_promotor
        FROM registrasi r 
        JOIN mahasiswa m ON r.id_mahasiswa = m.id_mahasiswa 
        LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi 
        LEFT JOIN dosen d1 ON j.promotor = d1.id_dosen 
        LEFT JOIN dosen d2 ON j.penguji_1 = d2.id_dosen 
        WHERE r.id_registrasi = ? AND r.jenis_ujian = 'kelayakan'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_registrasi);
$stmt->execute();
$result = $stmt->get_result();
$ujian = $result->fetch_assoc();

if (!$ujian) {
    die("Data ujian kelayakan tidak ditemukan");
}

// Ambil template penilaian kelayakan
$template = getTemplatePenilaian('kelayakan');

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
        
        $success = "Penilaian ujian kelayakan berhasil disimpan! Nilai Total: " . number_format($total_nilai, 2) . " (" . calculateGrade($total_nilai) . ")";
    } else {
        $error = "Gagal menyimpan penilaian: " . $stmt->error;
    }
}
?>

<div class="form-penilaian-container">
    <div class="header-penilaian">
        <h1>LEMBAR PENILAIAN UJIAN KELAYAKAN</h1>
        <h2>PROGRAM STUDI DOKTOR ILMU KOMPUTER</h2>
        <p style="color: #666; font-size: 14px; margin-top: 10px;">
            Penilaian Kelayakan Disertasi untuk Persiapan Ujian Tertutup
        </p>
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
        
        <?php foreach ($template as $index => $aspek): ?>
            const nilai<?= $index ?> = parseFloat(document.querySelector('[name="nilai_<?= $index ?>"]').value) || 0;
            total += (nilai<?= $index ?> * <?= $aspek['bobot'] ?>) / 100;
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
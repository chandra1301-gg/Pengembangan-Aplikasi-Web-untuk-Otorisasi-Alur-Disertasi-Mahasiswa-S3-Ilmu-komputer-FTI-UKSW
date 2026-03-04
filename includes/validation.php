<?php
/**
 * File: includes/validation.php
 * Fungsi validasi untuk sistem registrasi bertahap
 */

/**
 * Validasi apakah mahasiswa boleh mendaftar ujian tertentu
 */
function validateTahapanUjian($conn, $id_mahasiswa, $jenis_ujian) {
    $tahapan = [
        'proposal' => 1,
        'kualifikasi' => 2, 
        'kelayakan' => 3,
        'tertutup' => 4
    ];
    
    $current_tahap = $tahapan[$jenis_ujian];
    
    // Ujian proposal selalu boleh
    if ($current_tahap == 1) {
        return ['boleh' => true, 'alasan' => ''];
    }
    
    // Cek semua tahap sebelumnya harus lulus
    foreach ($tahapan as $ujian => $tahap) {
        if ($tahap < $current_tahap) {
            $sql = "SELECT status_kelulusan FROM registrasi 
                    WHERE id_mahasiswa = ? AND jenis_ujian = ? 
                    ORDER BY id_registrasi DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $id_mahasiswa, $ujian);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if (!$row || $row['status_kelulusan'] != 'lulus') {
                return [
                    'boleh' => false, 
                    'alasan' => 'Anda harus lulus ujian ' . ucfirst($ujian) . ' terlebih dahulu sebelum mendaftar ujian ' . ucfirst($jenis_ujian)
                ];
            }
        }
    }
    
    return ['boleh' => true, 'alasan' => ''];
}

/**
 * Cek apakah ada registrasi pending untuk ujian tertentu
 */
function hasPendingUjian($conn, $id_mahasiswa, $jenis_ujian) {
    $sql = "SELECT COUNT(*) as total FROM registrasi 
            WHERE id_mahasiswa = ? AND jenis_ujian = ? AND status = 'Menunggu'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_mahasiswa, $jenis_ujian);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] > 0;
}

/**
 * Dapatkan riwayat ujian mahasiswa
 */
function getRiwayatUjian($conn, $id_mahasiswa) {
    $sql = "SELECT r.*, 
                   CASE 
                       WHEN r.status = 'Diterima' AND r.status_kelulusan = 'lulus' THEN 'Lulus'
                       WHEN r.status = 'Diterima' AND r.status_kelulusan = 'tidak_lulus' THEN 'Tidak Lulus'
                       WHEN r.status = 'Diterima' AND r.status_kelulusan = 'belum_ujian' THEN 'Belum Ujian'
                       WHEN r.status = 'Menunggu' THEN 'Menunggu Approval'
                       WHEN r.status = 'Ditolak' THEN 'Ditolak'
                   END as status_detail
            FROM registrasi r 
            WHERE r.id_mahasiswa = ? 
            ORDER BY 
                CASE r.jenis_ujian 
                    WHEN 'proposal' THEN 1
                    WHEN 'kualifikasi' THEN 2
                    WHEN 'kelayakan' THEN 3
                    WHEN 'tertutup' THEN 4
                END,
                r.tanggal_pengajuan DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_mahasiswa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $riwayat = [];
    while ($row = $result->fetch_assoc()) {
        $riwayat[] = $row;
    }
    
    return $riwayat;
}

?>
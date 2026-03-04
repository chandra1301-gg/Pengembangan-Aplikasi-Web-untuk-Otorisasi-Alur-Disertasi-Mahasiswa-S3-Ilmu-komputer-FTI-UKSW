<?php
/**
 * File: includes/auto_approval.php - DIPERBAIKI UNTUK CO-PROMOTOR 2
 * Sistem otomatis untuk membuka akses ujian berikutnya - SUDAH DITAMBAH CO-PROMOTOR 2
 */

function checkAndOpenNextUjian($conn, $id_mahasiswa, $jenis_ujian_sekarang) {
    $tahapan = [
        'proposal' => 1,
        'kualifikasi' => 2, 
        'kelayakan' => 3,
        'tertutup' => 4
    ];
    
    $current_tahap = $tahapan[$jenis_ujian_sekarang];
    $next_tahap = $current_tahap + 1;
    
    // **PERBAIKAN: TETAP update status kelulusan meskipun ini ujian terakhir**
    $is_final_exam = ($next_tahap > 4);
    
    // Ambil id_registrasi saat ini
    $sql_current = "SELECT id_registrasi FROM registrasi 
                   WHERE id_mahasiswa = ? AND jenis_ujian = ? 
                   ORDER BY id_registrasi DESC LIMIT 1";
    $stmt_current = $conn->prepare($sql_current);
    $stmt_current->bind_param("is", $id_mahasiswa, $jenis_ujian_sekarang);
    $stmt_current->execute();
    $result_current = $stmt_current->get_result();
    $current_data = $result_current->fetch_assoc();
    
    if (!$current_data) {
        error_log("Sistem: Data registrasi tidak ditemukan untuk mahasiswa $id_mahasiswa, ujian $jenis_ujian_sekarang");
        $result_current->close();
        $stmt_current->close();
        return false;
    }
    
    $id_registrasi_sekarang = $current_data['id_registrasi'];
    $result_current->close();
    $stmt_current->close();
    
    // **PERBAIKAN: Hitung total dosen yang terlibat dan yang sudah approve - HANYA yang SUDAH MENILAI**
    $sql_dosen = "SELECT 
                    -- Hitung dari jadwal_ujian (bukan registrasi)
                    (SELECT COUNT(*) FROM jadwal_ujian j2 
                     WHERE j2.id_registrasi = ? AND j2.promotor IS NOT NULL) as ada_promotor,
                    
                    (SELECT COUNT(*) FROM jadwal_ujian j3 
                     WHERE j3.id_registrasi = ? AND j3.co_promotor IS NOT NULL) as ada_co_promotor,
                    
                    (SELECT COUNT(*) FROM jadwal_ujian j4 
                     WHERE j4.id_registrasi = ? AND j4.co_promotor2 IS NOT NULL) as ada_co_promotor2,
                    
                    -- **PERBAIKAN: Hitung penguji yang SUDAH MENILAI (bukan semua di jadwal)**
                    (SELECT COUNT(DISTINCT p.id_dosen) 
                     FROM penilaian_ujian p 
                     JOIN jadwal_ujian j ON p.id_registrasi = j.id_registrasi
                     WHERE p.id_registrasi = ? 
                       AND p.nilai_total IS NOT NULL
                       AND (p.id_dosen = j.penguji_1 
                            OR p.id_dosen = j.penguji_2 
                            OR p.id_dosen = j.penguji_3)) as total_penguji_menilai,
                    
                    -- Hitung yang sudah approve revisi (promotor + co-promotor1 + co-promotor2 + penguji)
                    (SELECT COUNT(DISTINCT p.id_dosen) 
                     FROM penilaian_ujian p 
                     WHERE p.id_registrasi = ? AND p.status_revisi = 'diterima') as total_approved";
    
    $stmt_dosen = $conn->prepare($sql_dosen);
    $stmt_dosen->bind_param("iiiii", $id_registrasi_sekarang, $id_registrasi_sekarang, $id_registrasi_sekarang, $id_registrasi_sekarang, $id_registrasi_sekarang);
    $stmt_dosen->execute();
    $result_dosen = $stmt_dosen->get_result();
    $data_dosen = $result_dosen->fetch_assoc();
    
    if (!$data_dosen) {
        error_log("Sistem: Gagal mengambil data dosen untuk registrasi $id_registrasi_sekarang");
        $result_dosen->close();
        $stmt_dosen->close();
        return false;
    }
    
    // Hitung total dosen yang seharusnya memberikan penilaian
    $total_dosen_seharusnya = 0;
    
    // Promotor
    if ($data_dosen['ada_promotor'] > 0) {
        $total_dosen_seharusnya += 1;
    }
    
    // Co-Promotor 1  
    if ($data_dosen['ada_co_promotor'] > 0) {
        $total_dosen_seharusnya += 1;
    }
    
    // Co-Promotor 2
    if ($data_dosen['ada_co_promotor2'] > 0) {
        $total_dosen_seharusnya += 1;
    }
    
    // **PERBAIKAN: Penguji yang SUDAH MENILAI**
    $total_dosen_seharusnya += $data_dosen['total_penguji_menilai'];
    
    $total_approved = $data_dosen['total_approved'];
    
    $result_dosen->close();
    $stmt_dosen->close();
    
    error_log("Sistem: Check ujian $jenis_ujian_sekarang - Dosen: $total_dosen_seharusnya, Approved: $total_approved, Is Final: " . ($is_final_exam ? 'Yes' : 'No'));
    
    // **PERBAIKAN: Jika semua dosen yang terlibat sudah approve revisi**
    if ($total_dosen_seharusnya > 0 && $total_dosen_seharusnya == $total_approved) {
        
        // 1. Update status kelulusan ujian saat ini menjadi 'lulus'
        $update_sql = "UPDATE registrasi 
                      SET status_kelulusan = 'lulus' 
                      WHERE id_registrasi = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $id_registrasi_sekarang);
        
        if ($update_stmt->execute()) {
            error_log("Sistem: Mahasiswa ID $id_mahasiswa lulus ujian $jenis_ujian_sekarang - Semua $total_dosen_seharusnya dosen telah approve");
            
            if ($is_final_exam) {
                // **PERBAIKAN: Untuk ujian tertutup (final), kirim email penyelesaian**
                sendFinalCompletionNotification($conn, $id_mahasiswa, $jenis_ujian_sekarang);
                error_log("Sistem: Mahasiswa ID $id_mahasiswa TELAH MENYELESAIKAN SEMUA TAHAP UJIAN");
            } else {
                // Untuk ujian non-final, kirim email kelulusan biasa
                $next_ujian = array_search($next_tahap, $tahapan);
                sendGraduationNotificationToStudent($conn, $id_mahasiswa, $jenis_ujian_sekarang, $next_ujian);
            }
            
            $update_stmt->close();
            return true;
        }
        
        $update_stmt->close();
    } else {
        error_log("Sistem: Belum semua dosen approve - Dosen: $total_dosen_seharusnya, Approved: $total_approved");
    }
    
    return false;
}

/**
 * Fungsi untuk mengirim notifikasi kelulusan ke mahasiswa
 */
function sendGraduationNotificationToStudent($conn, $id_mahasiswa, $completed_exam, $next_exam_available) {
    require_once 'email_sender.php';
    
    // Ambil data mahasiswa
    $sql = "SELECT nama_lengkap, email FROM mahasiswa WHERE id_mahasiswa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_mahasiswa);
    $stmt->execute();
    $result = $stmt->get_result();
    $student_data = $result->fetch_assoc();
    
    if ($student_data && testEmailConfiguration()) {
        $student_email = $student_data['email'];
        $student_name = $student_data['nama_lengkap'];
        $graduation_date = date('d F Y H:i:s');
        
        $email_sent = sendGraduationNotification(
            $student_email,
            $student_name,
            $completed_exam,
            $next_exam_available,
            $graduation_date
        );
        
        if ($email_sent) {
            error_log("Email kelulusan berhasil dikirim ke: $student_name - $completed_exam -> $next_exam_available");
        } else {
            error_log("Gagal mengirim email kelulusan ke: $student_name");
        }
        
        $result->close();
        $stmt->close();
        return $email_sent;
    }
    
    if ($result) $result->close();
    $stmt->close();
    return false;
}

/**
 * Fungsi untuk mengirim notifikasi penyelesaian semua tahap
 */
function sendFinalCompletionNotification($conn, $id_mahasiswa, $final_exam) {
    require_once 'email_sender.php';
    
    // Ambil data mahasiswa
    $sql = "SELECT nama_lengkap, email FROM mahasiswa WHERE id_mahasiswa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_mahasiswa);
    $stmt->execute();
    $result = $stmt->get_result();
    $student_data = $result->fetch_assoc();
    
    if ($student_data && testEmailConfiguration()) {
        $student_email = $student_data['email'];
        $student_name = $student_data['nama_lengkap'];
        $completion_date = date('d F Y H:i:s');
        
        // Untuk ujian tertutup yang selesai, kirim email khusus
        $email_sent = sendGraduationNotification(
            $student_email,
            $student_name,
            $final_exam,
            'completed', // Menandakan semua tahap selesai
            $completion_date
        );
        
        if ($email_sent) {
            error_log("Email penyelesaian semua tahap berhasil dikirim ke: $student_name");
        } else {
            error_log("Gagal mengirim email penyelesaian semua tahap ke: $student_name");
        }
        
        $result->close();
        $stmt->close();
        return $email_sent;
    }
    
    if ($result) $result->close();
    $stmt->close();
    return false;
}

/**
 * **FUNGSI BARU: Cek apakah mahasiswa boleh mendaftar ujian tertentu (untuk frontend)**
 */
function bolehDaftarUjian($conn, $id_mahasiswa, $jenis_ujian) {
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
                $result->close();
                $stmt->close();
                return [
                    'boleh' => false, 
                    'alasan' => 'Anda harus lulus ujian ' . ucfirst($ujian) . ' terlebih dahulu sebelum mendaftar ujian ' . ucfirst($jenis_ujian)
                ];
            }
            
            $result->close();
            $stmt->close();
        }
    }
    
    return ['boleh' => true, 'alasan' => ''];
}

/**
 * **FUNGSI BARU: Get ujian yang tersedia untuk mahasiswa (untuk dropdown)**
 */
function getUjianTersedia($conn, $id_mahasiswa) {
    $tahapan = ['proposal', 'kualifikasi', 'kelayakan', 'tertutup'];
    $ujian_tersedia = [];
    
    foreach ($tahapan as $ujian) {
        $boleh = bolehDaftarUjian($conn, $id_mahasiswa, $ujian);
        if ($boleh['boleh']) {
            $ujian_tersedia[] = $ujian;
        }
    }
    
    return $ujian_tersedia;
}

/**
 * Dipanggil setelah dosen approve revisi - VERSION PENGUJI & CO-PROMOTOR 2
 */
function afterApproveRevisi($conn, $id_penilaian) {
    // Ambil data penilaian dengan join ke registrasi
    $sql = "SELECT r.id_mahasiswa, r.jenis_ujian, r.id_registrasi, p.id_dosen, d.nama_lengkap as nama_dosen
            FROM penilaian_ujian p
            JOIN registrasi r ON p.id_registrasi = r.id_registrasi
            JOIN dosen d ON p.id_dosen = d.id_dosen
            WHERE p.id_penilaian = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_penilaian);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if ($data) {
        error_log("Sistem: AfterApproveRevisi dipanggil - Penilaian: $id_penilaian, Dosen: {$data['nama_dosen']}, Mahasiswa: {$data['id_mahasiswa']}, Ujian: {$data['jenis_ujian']}");
        
        // Update status revisi di tabel penilaian_ujian
        $update_sql = "UPDATE penilaian_ujian SET status_revisi = 'diterima', tanggal_revisi = NOW() WHERE id_penilaian = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $id_penilaian);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Juga update status di tabel revisi_disertasi jika ada
        $update_revisi_sql = "UPDATE revisi_disertasi 
                             SET status = 'disetujui', tanggal_approve = NOW() 
                             WHERE id_penilaian = ? AND status != 'disetujui'";
        $update_revisi_stmt = $conn->prepare($update_revisi_sql);
        $update_revisi_stmt->bind_param("i", $id_penilaian);
        $update_revisi_stmt->execute();
        $update_revisi_stmt->close();
        
        // **PERUBAHAN: Cek dan update status kelulusan (TIDAK buat registrasi otomatis)**
        $result_auto = checkAndOpenNextUjian($conn, $data['id_mahasiswa'], $data['jenis_ujian']);

        if ($result_auto) {
            error_log("Sistem: Auto-approval BERHASIL - Mahasiswa: {$data['id_mahasiswa']}, Ujian: {$data['jenis_ujian']}");
        } else {
            error_log("Sistem: Auto-approval BELUM - Mahasiswa: {$data['id_mahasiswa']}, Ujian: {$data['jenis_ujian']}");
        }
        
        $result->close();
        $stmt->close();
        return $result_auto;
    }
    
    $result->close();
    $stmt->close();
    return false;
}

/**
 * **UPDATE: Fungsi untuk mendapatkan detail status approval per dosen - SUDAH DITAMBAH CO-PROMOTOR 2**
 */
function getDetailStatusApproval($conn, $id_registrasi) {
    $sql = "SELECT 
                -- Data dari jadwal_ujian
                j.promotor, j.co_promotor, j.co_promotor2, j.penguji_1, j.penguji_2, j.penguji_3,
                
                -- Data nama dosen
                (SELECT nama_lengkap FROM dosen d WHERE d.id_dosen = j.promotor) as nama_promotor,
                (SELECT nama_lengkap FROM dosen d WHERE d.id_dosen = j.co_promotor) as nama_co_promotor,
                (SELECT nama_lengkap FROM dosen d WHERE d.id_dosen = j.co_promotor2) as nama_co_promotor2,
                (SELECT nama_lengkap FROM dosen d WHERE d.id_dosen = j.penguji_1) as nama_penguji1,
                (SELECT nama_lengkap FROM dosen d WHERE d.id_dosen = j.penguji_2) as nama_penguji2,
                (SELECT nama_lengkap FROM dosen d WHERE d.id_dosen = j.penguji_3) as nama_penguji3,
                
                -- Status approval
                (SELECT p.status_revisi FROM penilaian_ujian p 
                 WHERE p.id_registrasi = r.id_registrasi AND p.id_dosen = j.promotor) as status_promotor,
                
                (SELECT p.status_revisi FROM penilaian_ujian p 
                 WHERE p.id_registrasi = r.id_registrasi AND p.id_dosen = j.co_promotor) as status_co_promotor,
                
                (SELECT p.status_revisi FROM penilaian_ujian p 
                 WHERE p.id_registrasi = r.id_registrasi AND p.id_dosen = j.co_promotor2) as status_co_promotor2,
                
                (SELECT p.status_revisi FROM penilaian_ujian p 
                 WHERE p.id_registrasi = r.id_registrasi AND p.id_dosen = j.penguji_1) as status_penguji1,
                
                (SELECT p.status_revisi FROM penilaian_ujian p 
                 WHERE p.id_registrasi = r.id_registrasi AND p.id_dosen = j.penguji_2) as status_penguji2,
                
                (SELECT p.status_revisi FROM penilaian_ujian p 
                 WHERE p.id_registrasi = r.id_registrasi AND p.id_dosen = j.penguji_3) as status_penguji3
                
            FROM registrasi r
            LEFT JOIN jadwal_ujian j ON r.id_registrasi = j.id_registrasi
            WHERE r.id_registrasi = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_registrasi);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $detail = [
        'promotor' => [
            'id' => $data['promotor'],
            'nama' => $data['nama_promotor'],
            'status' => $data['status_promotor']
        ],
        'co_promotor' => [
            'id' => $data['co_promotor'],
            'nama' => $data['nama_co_promotor'], 
            'status' => $data['status_co_promotor']
        ],
        'co_promotor2' => [
            'id' => $data['co_promotor2'],
            'nama' => $data['nama_co_promotor2'], 
            'status' => $data['status_co_promotor2']
        ],
        'penguji' => []
    ];
    
    // Tambahkan penguji yang ada
    for ($i = 1; $i <= 3; $i++) {
        if (!empty($data["penguji_$i"])) {
            $detail['penguji'][] = [
                'id' => $data["penguji_$i"],
                'nama' => $data["nama_penguji$i"] ?? 'Penguji ' . $i,
                'status' => $data["status_penguji$i"]
            ];
        }
    }
    
    $result->close();
    $stmt->close();
    
    return $detail;
}

/**
 * **UPDATE: Cek status auto approval untuk debugging - SUDAH DITAMBAH CO-PROMOTOR 2**
 */
function getAutoApprovalStatus($conn, $id_registrasi) {
    $sql_dosen = "SELECT 
                    -- Hitung total dosen dari jadwal_ujian
                    (SELECT COUNT(*) FROM jadwal_ujian j2 
                     WHERE j2.id_registrasi = ? AND j2.promotor IS NOT NULL) +
                    (SELECT COUNT(*) FROM jadwal_ujian j3 
                     WHERE j3.id_registrasi = ? AND j3.co_promotor IS NOT NULL) +
                    (SELECT COUNT(*) FROM jadwal_ujian j4 
                     WHERE j4.id_registrasi = ? AND j4.co_promotor2 IS NOT NULL) +
                    (SELECT COUNT(*) FROM jadwal_ujian j5 
                     WHERE j5.id_registrasi = ? AND 
                           (j5.penguji_1 IS NOT NULL OR j5.penguji_2 IS NOT NULL OR j5.penguji_3 IS NOT NULL)) as total_dosen,
                    
                    -- Hitung yang sudah approve
                    (SELECT COUNT(DISTINCT p.id_dosen) 
                     FROM penilaian_ujian p 
                     WHERE p.id_registrasi = ? AND p.status_revisi = 'diterima') as total_approved";
    
    $stmt = $conn->prepare($sql_dosen);
    $stmt->bind_param("iiiii", $id_registrasi, $id_registrasi, $id_registrasi, $id_registrasi, $id_registrasi);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $status = [
        'total_dosen' => $data['total_dosen'] ?? 0,
        'total_approved' => $data['total_approved'] ?? 0,
        'progress' => $data['total_dosen'] > 0 ? round(($data['total_approved'] / $data['total_dosen']) * 100, 2) : 0,
        'is_complete' => ($data['total_dosen'] > 0 && $data['total_dosen'] == $data['total_approved'])
    ];
    
    $result->close();
    $stmt->close();
    
    return $status;
}

/**
 * Fungsi: Cek progress mahasiswa
 */
function getProgressMahasiswa($conn, $id_mahasiswa) {
    $tahapan = ['proposal', 'kualifikasi', 'kelayakan', 'tertutup'];
    $progress = [];
    
    foreach ($tahapan as $ujian) {
        $sql = "SELECT status_kelulusan, tanggal_pengajuan 
                FROM registrasi 
                WHERE id_mahasiswa = ? AND jenis_ujian = ? 
                ORDER BY id_registrasi DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_mahasiswa, $ujian);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $progress[$ujian] = [
            'status' => $row ? $row['status_kelulusan'] : 'belum',
            'tanggal' => $row ? $row['tanggal_pengajuan'] : null,
            'completed' => $row && $row['status_kelulusan'] == 'lulus'
        ];
        
        $result->close();
        $stmt->close();
    }
    
    return $progress;
}

/**
 * **FUNGSI BARU: Dapatkan detail tim dosen untuk ujian tertentu**
 */
function getTimDosenUjian($conn, $id_registrasi) {
    $sql = "SELECT 
                j.promotor, j.co_promotor, j.co_promotor2, 
                j.penguji_1, j.penguji_2, j.penguji_3,
                
                d1.nama_lengkap as nama_promotor,
                d2.nama_lengkap as nama_co_promotor,
                d3.nama_lengkap as nama_co_promotor2,
                d4.nama_lengkap as nama_penguji1,
                d5.nama_lengkap as nama_penguji2,
                d6.nama_lengkap as nama_penguji3
                
            FROM jadwal_ujian j
            LEFT JOIN dosen d1 ON j.promotor = d1.id_dosen
            LEFT JOIN dosen d2 ON j.co_promotor = d2.id_dosen
            LEFT JOIN dosen d3 ON j.co_promotor2 = d3.id_dosen
            LEFT JOIN dosen d4 ON j.penguji_1 = d4.id_dosen
            LEFT JOIN dosen d5 ON j.penguji_2 = d5.id_dosen
            LEFT JOIN dosen d6 ON j.penguji_3 = d6.id_dosen
            WHERE j.id_registrasi = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_registrasi);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $tim_dosen = [
        'promotor' => [
            'id' => $data['promotor'],
            'nama' => $data['nama_promotor']
        ],
        'co_promotor' => [
            'id' => $data['co_promotor'],
            'nama' => $data['nama_co_promotor']
        ],
        'co_promotor2' => [
            'id' => $data['co_promotor2'],
            'nama' => $data['nama_co_promotor2']
        ],
        'penguji' => []
    ];
    
    // Tambahkan penguji yang ada
    for ($i = 1; $i <= 3; $i++) {
        if (!empty($data["penguji_$i"])) {
            $tim_dosen['penguji'][] = [
                'id' => $data["penguji_$i"],
                'nama' => $data["nama_penguji$i"] ?? 'Penguji ' . $i
            ];
        }
    }
    
    $result->close();
    $stmt->close();
    
    return $tim_dosen;
}

?>
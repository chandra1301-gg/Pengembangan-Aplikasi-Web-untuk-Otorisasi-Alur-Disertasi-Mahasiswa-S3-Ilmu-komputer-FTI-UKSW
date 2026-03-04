<?php
/**
 * File: api_test_registration.php - FIXED VERSION dengan valid mahasiswa
 */

require_once 'enable_cors.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/email_sender.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // ✅ DAPATKAN MAHASISWA YANG VALID DARI DATABASE
        $mahasiswa_query = "SELECT id_mahasiswa, nama_lengkap, nim FROM mahasiswa LIMIT 1";
        $mahasiswa_result = mysqli_query($conn, $mahasiswa_query);
        
        if (mysqli_num_rows($mahasiswa_result) === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Tidak ada data mahasiswa di database. Silakan tambah data mahasiswa terlebih dahulu.'
            ]);
            exit;
        }
        
        $mahasiswa = mysqli_fetch_assoc($mahasiswa_result);
        
        // Gunakan data mahasiswa yang valid
        $id_mahasiswa = $mahasiswa['id_mahasiswa'];
        $nama_lengkap = $input['nama_lengkap'] ?? $mahasiswa['nama_lengkap'];
        $nim = $input['nim'] ?? $mahasiswa['nim'];
        $judul_disertasi = $input['judul_disertasi'] ?? 'Judul Test Disertasi API';
        $promotor = $input['promotor'] ?? 1;
        $co_promotor = $input['co_promotor'] ?? 2;
        $co_promotor2 = $input['co_promotor2'] ?? 3;
        
        error_log("🔍 [API TEST] Menggunakan mahasiswa: $nama_lengkap (ID: $id_mahasiswa)");
        
        // Simulate database insert
        $query = "INSERT INTO registrasi (id_mahasiswa, jenis_ujian, judul_disertasi, promotor, co_promotor, co_promotor2, tanggal_pengajuan, status) 
                 VALUES ($id_mahasiswa, 'proposal', '" . mysqli_real_escape_string($conn, $judul_disertasi) . "', 
                         $promotor, $co_promotor, $co_promotor2, NOW(), 'Menunggu')";
        
        if (mysqli_query($conn, $query)) {
            $id_registrasi = mysqli_insert_id($conn);
            
            // KIRIM EMAIL NOTIFIKASI
            $email_sent = sendRegistrationNotification($nama_lengkap, $nim, 'proposal', date('d F Y H:i:s'));
            
            // Response untuk Postman
            echo json_encode([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'id_registrasi' => $id_registrasi,
                    'mahasiswa_used' => [
                        'id' => $id_mahasiswa,
                        'nama' => $nama_lengkap,
                        'nim' => $nim
                    ],
                    'email_sent' => $email_sent,
                    'environment' => isDevelopment() ? 'development' : 'production',
                    'note' => isDevelopment() ? 'EMAIL TIDAK AKAN TERKIRIM - DEVELOPMENT MODE' : 'EMAIL AKAN TERKIRIM - PRODUCTION MODE'
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . mysqli_error($conn)
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST method.'
    ]);
}
?>
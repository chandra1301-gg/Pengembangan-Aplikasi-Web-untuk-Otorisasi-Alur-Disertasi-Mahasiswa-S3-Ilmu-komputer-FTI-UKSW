<?php
/**
 * File: includes/auth.php
 * Middleware untuk cek autentikasi dan role user - SUDAH DIUPDATE
 */

// Mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah user sudah login
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['role']) && 
           isset($_SESSION['logged_in']) && 
           $_SESSION['logged_in'] === true;
}

/**
 * Redirect jika belum login
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Silakan login terlebih dahulu!';
        header("Location: ../login.php");
        exit();
    }
}

/**
 * Cek role mahasiswa dan redirect jika bukan
 */
function require_mahasiswa() {
    require_login();
    if ($_SESSION['role'] !== 'mahasiswa') {
        $_SESSION['error_message'] = 'Akses ditolak! Halaman ini hanya untuk mahasiswa.';
        header("Location: ../unauthorized.php");
        exit();
    }
}

/**
 * Cek role admin dan redirect jika bukan
 */
function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        $_SESSION['error_message'] = 'Akses ditolak! Halaman ini hanya untuk administrator.';
        header("Location: ../unauthorized.php");
        exit();
    }
}

/**
 * Cek role dosen/penguji (admin juga bisa akses)
 */
function require_dosen() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'dosen') {
        header('Location: ../login.php');
        exit;
    }
    
    // Ambil data dosen lengkap dan simpan di session jika belum ada
    if (!isset($_SESSION['id_dosen'])) {
        global $conn;
        $sql = "SELECT d.* FROM dosen d JOIN users u ON d.user_id = u.id WHERE u.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $dosen = $result->fetch_assoc();
        
        if ($dosen) {
            $_SESSION['id_dosen'] = $dosen['id_dosen'];
            $_SESSION['nama_lengkap'] = $dosen['nama_lengkap'];
            $_SESSION['nidn'] = $dosen['nidn'];
            $_SESSION['bidang_keahlian'] = $dosen['bidang_keahlian'];
        }
    }
}

/**
 * Get data mahasiswa lengkap dengan join ke tabel users
 */
function get_mahasiswa_data($conn, $user_id) {
    $query = "SELECT m.*, u.username, u.nim, u.status as user_status 
              FROM mahasiswa m 
              JOIN users u ON m.user_id = u.id 
              WHERE u.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Get data user lengkap
 */
function get_user_data($conn, $user_id) {
    $query = "SELECT u.*, m.id_mahasiswa, m.nama_lengkap, m.program_studi, m.angkatan, m.email as mahasiswa_email
              FROM users u 
              LEFT JOIN mahasiswa m ON u.id = m.user_id 
              WHERE u.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Cek status akun user (approved, pending, rejected)
 */
function is_account_approved($user_data) {
    return isset($user_data['status']) && $user_data['status'] === 'approved';
}

/**
 * Fungsi logout yang aman
 */
function logout() {
    // Hapus semua data session
    $_SESSION = array();

    // Hapus session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Hancurkan session
    session_destroy();

    // Redirect ke login
    header("Location: ../disertasi_s3/public/beranda.php");
    exit();
}

/**
 * Redirect berdasarkan role setelah login
 */
function redirect_by_role() {
    if (!is_logged_in()) {
        return;
    }

    $role = $_SESSION['role'];
    switch ($role) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'mahasiswa':
            header("Location: mahasiswa/dashboard.php");
            break;
        case 'dosen':
            header("Location: dosen/dashboard.php");
            break;
        default:
            header("Location: index.php");
            break;
    }
    exit();
}

/**
 * Cek apakah user memiliki akses ke resource tertentu
 */
function has_access($allowed_roles) {
    if (!is_logged_in()) {
        return false;
    }
    
    return in_array($_SESSION['role'], (array)$allowed_roles);
}

/**
 * Set flash message untuk notifikasi
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get dan clear flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
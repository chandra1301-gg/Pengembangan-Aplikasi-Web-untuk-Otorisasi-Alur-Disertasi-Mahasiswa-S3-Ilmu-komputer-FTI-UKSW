<?php
/**
 * File: admin/delete_monev.php
 * Hapus data monitoring evaluasi
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_admin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: monev.php');
    exit();
}

$id_monev = (int)$_GET['id'];

// Get file name before deleting
$query_file = "SELECT file_laporan FROM monev WHERE id_monev = $id_monev";
$result_file = mysqli_query($conn, $query_file);
$file_data = mysqli_fetch_assoc($result_file);

// Delete record from database
$query_delete = "DELETE FROM monev WHERE id_monev = $id_monev";
$result_delete = mysqli_query($conn, $query_delete);

if ($result_delete) {
    // Delete file if exists
    if (!empty($file_data['file_laporan'])) {
        $file_path = "../uploads/monev/" . $file_data['file_laporan'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $_SESSION['success_message'] = "Data monitoring evaluasi berhasil dihapus";
} else {
    $_SESSION['error_message'] = "Gagal menghapus data monitoring evaluasi";
}

header('Location: monev.php');
exit();
?>
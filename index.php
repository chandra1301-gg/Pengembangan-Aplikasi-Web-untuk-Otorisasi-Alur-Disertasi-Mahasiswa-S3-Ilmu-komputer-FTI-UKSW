<?php
/**
 * File: index.php
 * Landing page - redirect ke login atau dashboard
 */

session_start();

// Jika sudah login, redirect ke dashboard sesuai role
// if (isset($_SESSION['user_id'])) {
//     if ($_SESSION['role'] == 'admin') {
//         header("Location: admin/dashboard.php");
//     } else {
//         header("Location: mahasiswa/dashboard.php");
//     }
//     exit();
// } else {
//     Jika belum login, redirect ke halaman login
//     header("Location: login.php");
//     exit();
// }

header("Location: public/beranda.php");
exit();
?>
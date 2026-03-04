<?php
/**
 * File: debug_user_data.php
 * Debugging untuk cek data user dan email
 */

require_once 'includes/db_connect.php';

$page_title = "Debug User Data - Sistem Disertasi S3 UKSW";
include 'includes/header.php';

echo "<div class='container mt-4'>";
echo "<h1>🐛 Debug User Data & Email</h1>";

// Test dengan user ID tertentu
$test_user_id = 1; // Ganti dengan ID user yang ada di database Anda

echo "<h3>1. Testing getUserData() untuk User ID: $test_user_id</h3>";

$user_data = getUserData($test_user_id);

if ($user_data) {
    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Data User Ditemukan:</h4>";
    echo "<pre>" . print_r($user_data, true) . "</pre>";
    
    // Cek email
    if (!empty($user_data['email'])) {
        echo "<p>📧 <strong>Email:</strong> " . htmlspecialchars($user_data['email']) . "</p>";
    } else {
        echo "<p>❌ <strong>Email:</strong> TIDAK ADA / KOSONG</p>";
    }
    
    echo "</div>";
} else {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Data User TIDAK Ditemukan untuk ID: $test_user_id</h4>";
    echo "<p>Pastikan user dengan ID tersebut ada di database.</p>";
    echo "</div>";
}

// Test query langsung
echo "<h3>2. Testing Query Langsung ke Database</h3>";

$direct_query = "SELECT 
                    u.id,
                    u.username, 
                    u.role, 
                    u.status,
                    u.created_at,
                    m.nama_lengkap, 
                    m.nim, 
                    m.email, 
                    m.program_studi, 
                    m.angkatan 
                 FROM users u 
                 LEFT JOIN mahasiswa m ON u.id = m.user_id 
                 WHERE u.id = $test_user_id";

$result = mysqli_query($conn, $direct_query);

if ($result && mysqli_num_rows($result) > 0) {
    $direct_data = mysqli_fetch_assoc($result);
    echo "<div class='alert alert-info'>";
    echo "<h4>📊 Hasil Query Langsung:</h4>";
    echo "<pre>" . print_r($direct_data, true) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='alert alert-warning'>";
    echo "<h4>⚠️ Query Langsung Tidak Mengembalikan Data</h4>";
    echo "<p>Error: " . mysqli_error($conn) . "</p>";
    echo "</div>";
}

// Test semua user yang pending
echo "<h3>3. Testing Semua User dengan Status Pending</h3>";

$pending_query = "SELECT 
                    u.id,
                    u.username, 
                    m.nama_lengkap, 
                    m.email 
                 FROM users u 
                 LEFT JOIN mahasiswa m ON u.id = m.user_id 
                 WHERE u.status = 'pending' 
                 LIMIT 5";

$pending_result = mysqli_query($conn, $pending_query);

if ($pending_result && mysqli_num_rows($pending_result) > 0) {
    echo "<div class='alert alert-success'>";
    echo "<h4>✅ User Pending Ditemukan:</h4>";
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>ID</th><th>Username</th><th>Nama</th><th>Email</th></tr></thead>";
    echo "<tbody>";
    while ($user = mysqli_fetch_assoc($pending_result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['nama_lengkap']) . "</td>";
        echo "<td>" . (!empty($user['email']) ? htmlspecialchars($user['email']) : '❌ KOSONG') . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    echo "</div>";
} else {
    echo "<div class='alert alert-warning'>";
    echo "<h4>⚠️ Tidak Ada User dengan Status Pending</h4>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li>Pastikan user memiliki data di tabel <code>mahasiswa</code></li>";
echo "<li>Pastikan kolom <code>email</code> di tabel <code>mahasiswa</code> tidak NULL</li>";
echo "<li>Pastikan relasi <code>users.id = mahasiswa.user_id</code> benar</li>";
echo "<li>Test dengan user yang sudah terdaftar lengkap</li>";
echo "</ol>";

echo "<div class='mt-3'>";
echo "<a href='test_email_localhost.php' class='btn btn-primary'>🧪 Test Email System</a>";
echo "<a href='admin/manage_users.php' class='btn btn-secondary'>⚙️ Admin Panel</a>";
echo "</div>";

echo "</div>";

include 'includes/footer.php';
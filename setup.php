<?php
/**
 * File: setup.php
 * Installer otomatis untuk sistem
 * Jalankan file ini sekali untuk setup database
 */

// Konfigurasi database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'disertasi_s3';
$db_port = 3306; // Ubah ke 3307 jika perlu

$errors = [];
$success = [];

// Proses setup jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_host = $_POST['db_host'];
    $db_port = $_POST['db_port'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];
    
    // Try koneksi
    $conn = @mysqli_connect($db_host, $db_user, $db_pass, '', $db_port);
    
    if (!$conn) {
        $errors[] = "Koneksi gagal: " . mysqli_connect_error();
    } else {
        $success[] = "✓ Koneksi ke MySQL berhasil!";
        
        // Cek apakah database sudah ada
        $db_check = mysqli_query($conn, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
        
        if (mysqli_num_rows($db_check) > 0) {
            $errors[] = "Database '$db_name' sudah ada. Gunakan database yang sudah ada atau hapus terlebih dahulu.";
        } else {
            // Buat database
            if (mysqli_query($conn, "CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                $success[] = "✓ Database '$db_name' berhasil dibuat!";
                
                // Pilih database
                mysqli_select_db($conn, $db_name);
                
                // Baca file SQL
                $sql_file = __DIR__ . '/disertasi_s3.sql';
                
                if (file_exists($sql_file)) {
                    $sql_content = file_get_contents($sql_file);
                    
                    // Remove database creation commands
                    $sql_content = preg_replace('/CREATE DATABASE.*?;/i', '', $sql_content);
                    $sql_content = preg_replace('/USE.*?;/i', '', $sql_content);
                    
                    // Split queries
                    $queries = array_filter(array_map('trim', explode(';', $sql_content)));
                    
                    $query_success = 0;
                    $query_failed = 0;
                    
                    foreach ($queries as $query) {
                        if (!empty($query)) {
                            if (mysqli_query($conn, $query)) {
                                $query_success++;
                            } else {
                                $query_failed++;
                                $errors[] = "Query gagal: " . mysqli_error($conn);
                            }
                        }
                    }
                    
                    $success[] = "✓ Berhasil menjalankan $query_success query";
                    
                    if ($query_failed > 0) {
                        $errors[] = "⚠ $query_failed query gagal dijalankan";
                    }
                    
                    // Update file config
                    $config_content = "<?php
/**
 * File: includes/db_connect.php (Auto-generated)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

\$host = \"$db_host\";
\$port = \"$db_port\";
\$user = \"$db_user\";
\$pass = \"$db_pass\";
\$db   = \"$db_name\";

try {
    \$conn = @mysqli_connect(\$host, \$user, \$pass, \$db, \$port);
    
    if (!\$conn) {
        throw new Exception(\"Koneksi database gagal: \" . mysqli_connect_error());
    }
    
    mysqli_set_charset(\$conn, \"utf8\");
    
} catch (Exception \$e) {
    die(\"<div style='padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;'>
        <h3>Database Error</h3>
        <p>\" . \$e->getMessage() . \"</p>
        <p>Silakan periksa konfigurasi database Anda.</p>
    </div>\");
}

function escape_string(\$data) {
    global \$conn;
    return mysqli_real_escape_string(\$conn, \$data);
}

function clean_input(\$data) {
    return htmlspecialchars(stripslashes(trim(\$data)));
}
?>";
                    
                    file_put_contents(__DIR__ . '/includes/db_connect.php', $config_content);
                    $success[] = "✓ File konfigurasi database telah diupdate!";
                    
                    if (empty($errors)) {
                        $success[] = "<strong>Setup selesai!</strong> Silakan <a href='login.php'>login ke sistem</a>";
                    }
                    
                } else {
                    $errors[] = "File disertasi_s3.sql tidak ditemukan!";
                }
            } else {
                $errors[] = "Gagal membuat database: " . mysqli_error($conn);
            }
        }
        
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Sistem Disertasi S3 UKSW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .setup-card {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .setup-header {
            background: #0d6efd;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .setup-body {
            padding: 30px;
        }
        .alert ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-card">
            <div class="setup-header">
                <div class="mb-3">
                    <i class="bi bi-gear-fill" style="font-size: 3rem;"></i>
                </div>
                <h3 class="mb-1">Setup Sistem</h3>
                <p class="mb-0">Sistem Disertasi S3 UKSW</p>
            </div>
            
            <div class="setup-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h6><i class="bi bi-exclamation-triangle"></i> Error:</h6>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <h6><i class="bi bi-check-circle"></i> Success:</h6>
                    <ul>
                        <?php foreach ($success as $msg): ?>
                        <li><?php echo $msg; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (empty($success) || !empty($errors)): ?>
                <h5 class="mb-3">Konfigurasi Database</h5>
                <p class="text-muted">Masukkan informasi database Anda</p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Database Host</label>
                        <input type="text" class="form-control" name="db_host" value="<?php echo $db_host; ?>" required>
                        <small class="text-muted">Default: localhost</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Database Port</label>
                        <input type="number" class="form-control" name="db_port" value="<?php echo $db_port; ?>" required>
                        <small class="text-muted">Default: 3306 (atau 3307 untuk XAMPP alternatif)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Database Username</label>
                        <input type="text" class="form-control" name="db_user" value="<?php echo $db_user; ?>" required>
                        <small class="text-muted">Default: root</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Database Password</label>
                        <input type="password" class="form-control" name="db_pass" value="<?php echo $db_pass; ?>">
                        <small class="text-muted">Kosongkan jika tidak ada password</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Database Name</label>
                        <input type="text" class="form-control" name="db_name" value="<?php echo $db_name; ?>" required>
                        <small class="text-muted">Nama database yang akan dibuat</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-play-circle me-2"></i>Install Sekarang
                    </button>
                </form>
                <?php else: ?>
                <div class="text-center">
                    <a href="login.php" class="btn btn-primary">
                        <i class="bi bi-arrow-right-circle me-2"></i>Ke Halaman Login
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-white">
                <i class="bi bi-info-circle"></i>
                Jalankan file ini <strong>sekali saja</strong> untuk setup database.
                Setelah selesai, hapus file ini untuk keamanan.
            </p>
        </div>
    </div>
</body>
</html>
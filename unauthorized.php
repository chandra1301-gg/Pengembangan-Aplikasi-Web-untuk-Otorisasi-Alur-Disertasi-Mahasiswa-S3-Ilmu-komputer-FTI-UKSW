<?php
/**
 * File: unauthorized.php
 * Halaman untuk akses ditolak
 */
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Sistem Disertasi S3 UKSW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .error-icon {
            font-size: 80px;
            color: #ff4757;
            margin-bottom: 20px;
        }
        .error-title {
            color: #2f3542;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .error-message {
            color: #747d8c;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #1C5EBC;
            color: white;
            border: none;
        }
        .btn-outline {
            background: transparent;
            color: #1C5EBC;
            border: 2px solid #1C5EBC;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🚫</div>
        <h1 class="error-title">Akses Ditolak</h1>
        
        <div class="error-message">
            <?php 
            if (isset($_SESSION['error_message'])) {
                echo htmlspecialchars($_SESSION['error_message']);
                unset($_SESSION['error_message']);
            } else {
                echo "Anda tidak memiliki izin untuk mengakses halaman ini.";
            }
            ?>
        </div>
        
        <div class="btn-group">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php 
                    if (isset($_SESSION['role'])) {
                        switch ($_SESSION['role']) {
                            case 'admin': echo 'admin/dashboard.php'; break;
                            case 'dosen': echo 'dosen/dashboard.php'; break;
                            case 'mahasiswa': echo 'mahasiswa/dashboard.php'; break;
                            default: echo 'login.php';
                        }
                    } else {
                        echo 'login.php';
                    }
                ?>" class="btn btn-primary">
                    Kembali ke Dashboard
                </a>
            <?php endif; ?>
            
            <a href="login.php" class="btn btn-outline">
                Login dengan Akun Lain
            </a>
            
            <a href="javascript:history.back()" class="btn btn-outline">
                Kembali ke Halaman Sebelumnya
            </a>
        </div>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="mt-4">
                <p class="text-muted">Silakan login dengan akun yang sesuai untuk mengakses halaman ini.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto redirect setelah 10 detik
        setTimeout(function() {
            <?php if (isset($_SESSION['user_id'])): ?>
                window.location.href = '<?php 
                    if (isset($_SESSION['role'])) {
                        switch ($_SESSION['role']) {
                            case 'admin': echo 'admin/dashboard.php'; break;
                            case 'dosen': echo 'dosen/dashboard.php'; break;
                            case 'mahasiswa': echo 'mahasiswa/dashboard.php'; break;
                            default: echo 'login.php';
                        }
                    } else {
                        echo 'login.php';
                    }
                ?>';
            <?php else: ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        }, 10000);
    </script>
</body>
</html>
<?php
/**
 * File: login.php
 * Halaman login untuk mahasiswa dan admin - SUPPORT LOGIN DENGAN NIM
 */

session_start();
require_once 'includes/db_connect.php';

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = clean_input($_POST['identifier']);
    $password = $_POST['password'];

    // Cari user berdasarkan username ATAU NIM ATAU NIDN
    $user = get_user_by_identifier($conn, $identifier);

    if ($user) {
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            
            // Cek status akun
            if ($user['status'] === 'pending') {
                $error = 'Akun Anda menunggu persetujuan admin. Silakan tunggu konfirmasi via email.';
            } elseif ($user['status'] === 'rejected') {
                $error = 'Akun Anda ditolak. Silakan hubungi administrator untuk informasi lebih lanjut.';
            } elseif ($user['status'] === 'approved' || $user['status'] === 'active') {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                // Tambahkan data berdasarkan role
                if ($user['role'] === 'mahasiswa') {
                    $_SESSION['id_mahasiswa'] = $user['id_mahasiswa'];
                    $_SESSION['nama_lengkap'] = $user['nama_mahasiswa'];
                    $_SESSION['nim'] = $user['nim'];
                    $_SESSION['program_studi'] = $user['program_studi'];
                    $_SESSION['angkatan'] = $user['angkatan'];
                    $_SESSION['email'] = $user['mahasiswa_email'];
                } elseif ($user['role'] === 'dosen') {
                    $_SESSION['id_dosen'] = $user['id_dosen'];
                    $_SESSION['nama_lengkap'] = $user['nama_dosen'];
                    $_SESSION['nidn'] = $user['nidn'];
                    $_SESSION['jabatan'] = $user['jabatan'];
                    $_SESSION['bidang_keahlian'] = $user['bidang_keahlian'];
                    $_SESSION['email'] = $user['email_dosen'];
                }

                // Redirect berdasarkan role
                switch ($user['role']) {
                    case 'admin':
                        header("Location: admin/dashboard.php");
                        exit();
                    case 'dosen':
                        header("Location: penguji/dashboard.php");
                        exit();
                    case 'mahasiswa':
                        header("Location: mahasiswa/dashboard.php");
                        exit();
                    default:
                        header("Location: index.php");
                        exit();
                }
            }
        } else {
            $error = 'NIM/Username/NIDN atau password salah!';
        }
    } else {
        $error = 'NIM/Username/NIDN tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Disertasi S3 UKSW</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(123.95deg, #EEF2FF 0%, #F8F9FC 85.12%);
            min-height: 100vh;
        }

        .login-container {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5rem;
            gap: 2rem;
            background: linear-gradient(113.85deg, rgba(255, 255, 255, 0.3) 5.03%, rgba(215, 230, 250, 0.3) 82.21%);
        }

        /* Logo UKSW */
        .logo-section {
            flex: 0 0 auto;
            width: 419px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-box {
            text-align: center;
        }

        .logo-box img {
            max-width: 300px;
            height: auto;
        }

        /* Welcome Card Section */
        .card-section {
            flex: 0 0 auto;
            width: 623px;
        }

        .welcome-card {
            width: 623px;
            background: linear-gradient(180deg, #FFFFFF 0%, #F4F9FF 100%);
            box-shadow: 0.728671px 0.728671px 11.8773px -3.64335px rgba(0, 0, 0, 0.25);
            border-radius: 19.6741px;
            padding: 77px 65px;
        }

        .welcome-title {
            font-weight: 700;
            font-size: 25px;
            line-height: 38px;
            letter-spacing: 0.03em;
            color: #000000;
            margin-bottom: 40px;
            text-align: center;
        }

        /* Info Box */
        .info-box {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 12px;
            color: #1565c0;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 30px;
        }

        .info-icon {
            flex-shrink: 0;
            font-size: 16px;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 33px;
            position: relative;
        }

        .form-label {
            font-weight: 300;
            font-size: 15px;
            line-height: 22px;
            letter-spacing: 0.03em;
            color: rgba(0, 0, 0, 0.5);
            margin-bottom: 5px;
            display: block;
        }

        .form-input {
            width: 100%;
            border: none;
            border-bottom: 0.728671px solid #000000;
            outline: none;
            padding: 8px 0;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            background: transparent;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            border-bottom-color: #1C5EBC;
        }

        .form-input::placeholder {
            color: rgba(0, 0, 0, 0.3);
            font-size: 14px;
        }

        /* Password Field */
        .password-wrapper {
            position: relative;
        }

        .forget-password {
            position: absolute;
            right: 0;
            top: -25px;
            font-weight: 500;
            font-size: 10.9301px;
            line-height: 16px;
            letter-spacing: 0.03em;
            color: #1C5EBC;
            text-decoration: none;
            cursor: pointer;
        }

        .forget-password:hover {
            text-decoration: underline;
        }

        .eye-icon {
            position: absolute;
            right: 5px;
            top: 8px;
            cursor: pointer;
            font-size: 18px;
            opacity: 0.6;
            user-select: none;
        }

        .eye-icon:hover {
            opacity: 1;
        }

        /* Sign In Button */
        .sign-in-button {
            width: 100%;
            height: 34.25px;
            background: rgba(215, 230, 250, 0.5);
            border: 0.145734px solid rgba(0, 0, 0, 0.4);
            border-radius: 14.5734px;
            font-weight: 600;
            font-size: 16.0308px;
            line-height: 24px;
            letter-spacing: 0.03em;
            color: #000000;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            margin-top: 40px;
        }

        .sign-in-button:hover {
            background: rgba(215, 230, 250, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(28, 94, 188, 0.2);
        }

        /* Register Link */
        .register-link {
            text-align: center;
            font-size: 14px;
            color: #333;
            margin-top: 25px;
        }

        .register-link a {
            color: #1C5EBC;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Alert */
        .alert-custom {
            background-color: #fce4ec;
            border: 1px solid #f8bbd0;
            color: #c62828;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 12px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
            position: relative;
        }

        .alert-icon {
            flex-shrink: 0;
            font-size: 16px;
        }

        .btn-close {
            position: absolute;
            right: 10px;
            top: 10px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #c62828;
            opacity: 0.7;
            line-height: 1;
        }

        .btn-close:hover {
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .login-container {
                padding: 0 3rem;
            }

            .logo-section {
                width: 350px;
            }

            .card-section {
                width: 550px;
            }

            .welcome-card {
                width: 550px;
                padding: 60px 50px;
            }
        }

        @media (max-width: 1024px) {
            .login-container {
                flex-direction: column;
                justify-content: center;
                padding: 3rem 2rem;
                gap: 3rem;
            }

            .logo-section,
            .card-section {
                width: 100%;
                max-width: 500px;
            }

            .logo-box img,
            .welcome-card {
                width: 100%;
            }
        }

        @media (max-width: 600px) {
            .login-container {
                padding: 2rem 1rem;
            }

            .welcome-card {
                padding: 40px 30px;
            }

            .welcome-title {
                font-size: 22px;
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo Section -->
        <div class="logo-section">
            <div class="logo-box">
                <img src="assets/LOGO UKSW CM.png" alt="Logo UKSW">
            </div>
        </div>

        <!-- Card Section -->
        <div class="card-section">
            <div class="welcome-card">
                <h2 class="welcome-title">Welcome Back!</h2>

                <!-- Info Box -->
                <div class="info-box">
                    <span class="info-icon">ℹ️</span>
                    <span>
                        <strong>Informasi Login:</strong><br>
                        • Mahasiswa: Login menggunakan <strong>NIM</strong> atau username<br>
                        • Dosen/Penguji: Login menggunakan <strong>username</strong> atau <strong>NIDN</strong><br>
                        • Admin: Login menggunakan username<br>
                        • Akun baru membutuhkan persetujuan admin
                    </span>
                </div>

                <!-- Alert Error (show only when there's an error) -->
                <?php if ($error): ?>
                <div class="alert-custom">
                    <span class="alert-icon">⚠️</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">×</button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Identifier Field - Bisa NIM atau Username -->
                    <div class="form-group">
                        <label class="form-label">NIM atau Username</label>
                        <input type="text" name="identifier" class="form-input" required autofocus 
                               placeholder="Masukkan NIM atau username">
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <div class="password-wrapper">
                            <label class="form-label">Password</label>
                            <a href="#" class="forget-password">Lupa Password?</a>
                            <input type="password" name="password" id="password" class="form-input" required 
                                   placeholder="Masukkan password">
                            <span class="eye-icon" onclick="togglePassword()">👁️</span>
                        </div>
                    </div>

                    <!-- Sign In Button -->
                    <button type="submit" class="sign-in-button">Sign In</button>
                </form>

                <!-- Register Link -->
                <div class="register-link">
                    Belum punya akun? <a href="register.php">Daftar di sini</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.querySelector('.eye-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                eyeIcon.textContent = '👁️';
            }
        }

        // Auto-focus pada password field setelah identifier diisi
        document.addEventListener('DOMContentLoaded', function() {
            const identifierInput = document.querySelector('input[name="identifier"]');
            
            identifierInput.addEventListener('blur', function() {
                if (this.value.trim() !== '') {
                    document.getElementById('password').focus();
                }
            });
            
            // Enter key navigation
            identifierInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('password').focus();
                }
            });
            
            document.getElementById('password').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.querySelector('.sign-in-button').click();
                }
            });
        });
    </script>
</body>
</html>
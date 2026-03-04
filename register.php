<?php
/**
 * File: register.php
 * Halaman registrasi akun mahasiswa baru - SUDAH SUPPORT NIM
 */

session_start();
require_once 'includes/db_connect.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $nim = clean_input($_POST['nim']);
    $email = clean_input($_POST['email']);
    $program_studi = clean_input($_POST['program_studi']);
    $angkatan = clean_input($_POST['angkatan']);
    
    // Validasi
    $errors = [];
    
    if ($password !== $confirm_password) {
        $errors[] = 'Password dan konfirmasi password tidak sama!';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter!';
    }
    
    if (!validate_nim($nim)) {
        $errors[] = 'NIM harus berupa angka (9-15 digit)!';
    }
    
    // Cek duplikasi username
    if (get_user_by_username($conn, $username)) {
        $errors[] = 'Username sudah digunakan!';
    }
    
    // Cek duplikasi NIM
    if (get_user_by_nim($conn, $nim)) {
        $errors[] = 'NIM sudah terdaftar!';
    }
    
    // Cek duplikasi email di tabel mahasiswa
    $check_email = "SELECT * FROM mahasiswa WHERE email = ?";
    $stmt_email = $conn->prepare($check_email);
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $result_email = $stmt_email->get_result();
    if ($result_email->num_rows > 0) {
        $errors[] = 'Email sudah terdaftar!';
    }
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert ke tabel users dengan status pending DAN NIM
        $insert_user = "INSERT INTO users (username, password, role, nim, status) VALUES (?, ?, 'mahasiswa', ?, 'pending')";
        $stmt_user = $conn->prepare($insert_user);
        $stmt_user->bind_param("sss", $username, $hashed_password, $nim);
        
        if ($stmt_user->execute()) {
            $user_id = $stmt_user->insert_id;
            
            // Insert ke tabel mahasiswa
            $insert_mahasiswa = "INSERT INTO mahasiswa (user_id, nama_lengkap, nim, email, program_studi, angkatan) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_mahasiswa = $conn->prepare($insert_mahasiswa);
            $stmt_mahasiswa->bind_param("isssss", $user_id, $nama_lengkap, $nim, $email, $program_studi, $angkatan);
            
            if ($stmt_mahasiswa->execute()) {
                $success = 'Registrasi berhasil! Akun Anda sedang menunggu persetujuan admin. Anda akan mendapat notifikasi via email setelah disetujui.';
                
                // Clear form setelah sukses
                $_POST = array();
            } else {
                $error = 'Terjadi kesalahan saat menyimpan data mahasiswa.';
                // Rollback: hapus user yang sudah dibuat
                $conn->query("DELETE FROM users WHERE id = $user_id");
            }
        } else {
            $error = 'Terjadi kesalahan saat membuat akun.';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Disertasi S3 UKSW</title>
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
            padding: 40px 20px;
        }

        .register-container {
            position: relative;
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5rem;
            gap: 2rem;
            background: linear-gradient(113.85deg, rgba(255, 255, 255, 0.3) 5.03%, rgba(215, 230, 250, 0.3) 82.21%);
            border-radius: 20px;
            max-width: 1440px;
            margin: 0 auto;
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

        /* Register Card Section */
        .card-section {
            flex: 0 0 auto;
            width: 623px;
            max-height: 85vh;
            overflow-y: auto;
            padding-right: 10px;
        }

        /* Scrollbar styling */
        .card-section::-webkit-scrollbar {
            width: 6px;
        }

        .card-section::-webkit-scrollbar-track {
            background: transparent;
        }

        .card-section::-webkit-scrollbar-thumb {
            background: #1C5EBC;
            border-radius: 10px;
        }

        .card-section::-webkit-scrollbar-thumb:hover {
            background: #1565c0;
        }

        .register-card {
            width: 623px;
            background: linear-gradient(180deg, #FFFFFF 0%, #F4F9FF 100%);
            box-shadow: 0.728671px 0.728671px 11.8773px -3.64335px rgba(0, 0, 0, 0.25);
            border-radius: 19.6741px;
            padding: 50px 65px;
        }

        .register-title {
            font-weight: 700;
            font-size: 25px;
            line-height: 38px;
            letter-spacing: 0.03em;
            color: #000000;
            margin-bottom: 30px;
            text-align: center;
        }

        /* Info Box */
        .info-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 12px;
            color: #856404;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 25px;
        }

        .info-icon {
            flex-shrink: 0;
            font-size: 16px;
        }

        /* Section Header */
        .section-header {
            font-weight: 600;
            font-size: 14px;
            color: #1C5EBC;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #e3f2fd;
        }

        /* Form Groups */
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }

        .form-group-full {
            width: 100%;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 300;
            font-size: 13px;
            line-height: 20px;
            letter-spacing: 0.03em;
            color: rgba(0, 0, 0, 0.5);
            margin-bottom: 5px;
            display: block;
        }

        .required {
            color: #c62828;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            border: none;
            border-bottom: 0.728671px solid #000000;
            outline: none;
            padding: 8px 0;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            background: transparent;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            border-bottom-color: #1C5EBC;
        }

        .form-hint {
            font-size: 11px;
            color: rgba(0, 0, 0, 0.4);
            margin-top: 3px;
        }

        /* Password Toggle */
        .password-wrapper {
            position: relative;
        }

        .eye-icon {
            position: absolute;
            right: 5px;
            top: 8px;
            cursor: pointer;
            font-size: 16px;
            opacity: 0.6;
            user-select: none;
        }

        .eye-icon:hover {
            opacity: 1;
        }

        /* Submit Button */
        .submit-button {
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
            margin-top: 30px;
        }

        .submit-button:hover {
            background: rgba(215, 230, 250, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(28, 94, 188, 0.2);
        }

        /* Login Link */
        .login-link {
            text-align: center;
            font-size: 14px;
            color: #333;
            margin-top: 20px;
        }

        .login-link a {
            color: #1C5EBC;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Alert Error */
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

        /* Alert Success */
        .alert-success {
            background-color: #e8f5e9;
            border: 1px solid #a5d6a7;
            color: #2e7d32;
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
            opacity: 0.7;
            line-height: 1;
        }

        .btn-close:hover {
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .register-container {
                padding: 0 3rem;
            }

            .logo-section {
                width: 350px;
            }

            .card-section {
                width: 550px;
            }

            .register-card {
                width: 550px;
                padding: 45px 50px;
            }
        }

        @media (max-width: 1024px) {
            .register-container {
                flex-direction: column;
                justify-content: center;
                padding: 3rem 2rem;
                gap: 3rem;
                min-height: auto;
            }

            .logo-section,
            .card-section {
                width: 100%;
                max-width: 500px;
                max-height: none;
            }

            .register-card {
                width: 100%;
            }
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }

            .register-container {
                padding: 2rem 1rem;
            }

            .register-card {
                padding: 35px 25px;
            }

            .register-title {
                font-size: 22px;
                margin-bottom: 25px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Logo Section -->
        <div class="logo-section">
            <div class="logo-box">
                <img src="assets/LOGO UKSW CM.png" alt="Logo UKSW">
            </div>
        </div>

        <!-- Card Section -->
        <div class="card-section">
            <div class="register-card">
                <h2 class="register-title">Daftar Akun Baru</h2>

                <!-- Info Box -->
                <div class="info-box">
                    <span class="info-icon">ℹ️</span>
                    <span>
                        <strong>Informasi Penting:</strong><br>
                        • Akun Anda akan aktif setelah disetujui oleh admin (1x24 jam)<br>
                        • Setelah disetujui, Anda bisa login menggunakan <strong>NIM</strong> atau username<br>
                        • Pastikan NIM yang Anda input sudah benar
                    </span>
                </div>

                <!-- Success Alert -->
                <?php if ($success): ?>
                <div class="alert-success">
                    <span class="alert-icon">✓</span>
                    <span><?php echo htmlspecialchars($success); ?></span>
                    <button type="button" class="btn-close" style="color: #2e7d32;" onclick="this.parentElement.style.display='none'">×</button>
                </div>
                <?php endif; ?>

                <!-- Error Alert -->
                <?php if ($error): ?>
                <div class="alert-custom">
                    <span class="alert-icon">⚠️</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                    <button type="button" class="btn-close" style="color: #c62828;" onclick="this.parentElement.style.display='none'">×</button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Section: Data Akun -->
                    <div class="section-header">📋 Data Akun</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Username <span class="required">*</span></label>
                            <input type="text" name="username" class="form-input" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" class="form-input" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Password <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="password" class="form-input" required>
                                <span class="eye-icon" onclick="togglePassword('password')">👁️</span>
                            </div>
                            <div class="form-hint">Minimal 6 karakter</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" id="confirm_password" class="form-input" required>
                                <span class="eye-icon" onclick="togglePassword('confirm_password')">👁️</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Data Mahasiswa -->
                    <div class="section-header">👤 Data Mahasiswa</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-input" value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">NIM <span class="required">*</span></label>
                            <input type="text" name="nim" class="form-input" value="<?php echo isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : ''; ?>" required placeholder="Contoh: 672022337">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label class="form-label">Program Studi <span class="required">*</span></label>
                            <input type="text" name="program_studi" class="form-input" value="<?php echo isset($_POST['program_studi']) ? htmlspecialchars($_POST['program_studi']) : ''; ?>" required placeholder="Contoh: Program Doktor Ilmu Komputer">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label">Angkatan <span class="required">*</span></label>
                            <input type="number" name="angkatan" class="form-input" value="<?php echo isset($_POST['angkatan']) ? htmlspecialchars($_POST['angkatan']) : ''; ?>" required min="2000" max="2030" placeholder="2025">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="submit-button">Daftar Sekarang</button>
                </form>

                <!-- Login Link -->
                <div class="login-link">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = passwordField.nextElementSibling;
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                eyeIcon.textContent = '👁️';
            }
        }

        // Validasi NIM (hanya angka)
        document.addEventListener('DOMContentLoaded', function() {
            const nimInput = document.querySelector('input[name="nim"]');
            
            nimInput.addEventListener('input', function() {
                // Hanya allow angka
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Validasi panjang NIM
                if (this.value.length < 9) {
                    this.style.borderBottomColor = '#ff4444';
                } else if (this.value.length > 15) {
                    this.value = this.value.slice(0, 15);
                    this.style.borderBottomColor = '#ff4444';
                } else {
                    this.style.borderBottomColor = '#1C5EBC';
                }
            });
            
            // Validasi form sebelum submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const nim = nimInput.value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                // Validasi NIM
                if (nim.length < 9 || nim.length > 15) {
                    e.preventDefault();
                    alert('NIM harus antara 9-15 digit angka!');
                    nimInput.focus();
                    return;
                }
                
                // Validasi password
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password minimal 6 karakter!');
                    document.getElementById('password').focus();
                    return;
                }
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Password dan konfirmasi password tidak sama!');
                    document.getElementById('confirm_password').focus();
                    return;
                }
            });
        });
    </script>
</body>
</html>
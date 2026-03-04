<?php
/**
 * File: dosen/profil.php
 * Halaman profil dan edit data dosen
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_dosen();

$dosen = get_dosen_data($conn, $_SESSION['user_id']);
$success = '';
$error = '';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    $nidn = clean_input($_POST['nidn']);
    $jabatan = clean_input($_POST['jabatan']);
    $bidang_keahlian = clean_input($_POST['bidang_keahlian']);
    
    $query = "UPDATE dosen SET 
              nama_lengkap = '" . escape_string($nama_lengkap) . "',
              email = '" . escape_string($email) . "',
              nidn = '" . escape_string($nidn) . "',
              jabatan = '" . escape_string($jabatan) . "',
              bidang_keahlian = '" . escape_string($bidang_keahlian) . "'
              WHERE user_id = " . $_SESSION['user_id'];
    
    if (mysqli_query($conn, $query)) {
        $success = "Profil berhasil diupdate!";
        $dosen = get_dosen_data($conn, $_SESSION['user_id']);
    } else {
        $error = "Gagal mengupdate profil!";
    }
}

// Proses ubah password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current password
    $query_user = "SELECT password FROM users WHERE id = " . $_SESSION['user_id'];
    $result_user = mysqli_query($conn, $query_user);
    $user = mysqli_fetch_assoc($result_user);
    
    if (password_verify($old_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query_update = "UPDATE users SET password = '$hashed_password' WHERE id = " . $_SESSION['user_id'];
                
                if (mysqli_query($conn, $query_update)) {
                    $success = "Password berhasil diubah!";
                    // Tidak perlu kirim email notifikasi
                } else {
                    $error = "Gagal mengubah password!";
                }
            } else {
                $error = "Password baru minimal 6 karakter!";
            }
        } else {
            $error = "Konfirmasi password tidak cocok!";
        }
    } else {
        $error = "Password lama tidak sesuai!";
    }
}

$page_title = "Profil Dosen - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_penguji.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="bi bi-person-circle me-2"></i>Profil Saya</h2>
                <p class="text-muted">Kelola informasi profil dan keamanan akun Anda</p>
            </div>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Profil Card -->
            <div class="col-md-4 mb-4">
                <div class="card border-0">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                            <i class="bi bi-person-fill text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="mb-1"><?php echo $dosen['nama_lengkap']; ?></h5>
                        <p class="text-muted mb-2"><?php echo $dosen['nidn']; ?></p>
                        <span class="badge bg-success">Dosen <?php echo ucfirst($dosen['jabatan']); ?></span>
                        
                        <hr class="my-4">
                        
                        <div class="text-start">
                            <p class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?php echo $dosen['email']; ?></p>
                            <p class="mb-2"><i class="bi bi-award me-2 text-primary"></i><?php echo ucfirst($dosen['jabatan']); ?></p>
                            <p class="mb-2"><i class="bi bi-book me-2 text-primary"></i><?php echo $dosen['bidang_keahlian'] ?: 'Belum diisi'; ?></p>
                            <p class="mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Status: <?php echo ucfirst($dosen['status']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profil -->
            <div class="col-md-8 mb-4">
                <div class="card border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Profil</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_lengkap" value="<?php echo $dosen['nama_lengkap']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NIDN <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nidn" value="<?php echo $dosen['nidn']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?php echo $dosen['email']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                    <select class="form-control" name="jabatan" required>
                                        <option value="promotor" <?php echo $dosen['jabatan'] == 'promotor' ? 'selected' : ''; ?>>Promotor</option>
                                        <option value="kopromotor" <?php echo $dosen['jabatan'] == 'kopromotor' ? 'selected' : ''; ?>>Kopromotor</option>
                                        <option value="penguji" <?php echo $dosen['jabatan'] == 'penguji' ? 'selected' : ''; ?>>Penguji</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Bidang Keahlian</label>
                                <input type="text" class="form-control" name="bidang_keahlian" value="<?php echo $dosen['bidang_keahlian']; ?>" placeholder="Contoh: Artificial Intelligence, Data Science, dll.">
                            </div>
                            
                            <button type="submit" name="update_profil" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Ubah Password -->
                <div class="card border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Ubah Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Password Lama <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="old_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="new_password" required>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="bi bi-key me-2"></i>Ubah Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
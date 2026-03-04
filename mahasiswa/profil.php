<?php
/**
 * File: mahasiswa/profil.php
 * Halaman profil dan edit data mahasiswa
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_mahasiswa();

$mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
$success = '';
$error = '';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    $no_telp = clean_input($_POST['no_telp']);
    $alamat = clean_input($_POST['alamat']);
    $pembimbing_1 = clean_input($_POST['pembimbing_1']);
    $pembimbing_2 = clean_input($_POST['pembimbing_2']);
    
    $query = "UPDATE mahasiswa SET 
              nama_lengkap = '" . escape_string($nama_lengkap) . "',
              email = '" . escape_string($email) . "',
              no_telp = '" . escape_string($no_telp) . "',
              alamat = '" . escape_string($alamat) . "',
              pembimbing_1 = '" . escape_string($pembimbing_1) . "',
              pembimbing_2 = '" . escape_string($pembimbing_2) . "'
              WHERE id_mahasiswa = " . $mahasiswa['id_mahasiswa'];
    
    if (mysqli_query($conn, $query)) {
        $success = "Profil berhasil diupdate!";
        $mahasiswa = get_mahasiswa_data($conn, $_SESSION['user_id']);
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

$page_title = "Profil Mahasiswa - Sistem Disertasi S3 UKSW";
include '../includes/header.php';
include '../includes/sidebar_mahasiswa.php';
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
                        <h5 class="mb-1"><?php echo $mahasiswa['nama_lengkap']; ?></h5>
                        <p class="text-muted mb-2"><?php echo $mahasiswa['nim']; ?></p>
                        <span class="badge bg-success">Mahasiswa Aktif</span>
                        
                        <hr class="my-4">
                        
                        <div class="text-start">
                            <p class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?php echo $mahasiswa['email']; ?></p>
                            <p class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i><?php echo $mahasiswa['no_telp'] ?: 'Belum diisi'; ?></p>
                            <p class="mb-2"><i class="bi bi-calendar3 me-2 text-primary"></i>Angkatan <?php echo $mahasiswa['angkatan']; ?></p>
                            <p class="mb-0"><i class="bi bi-book me-2 text-primary"></i><?php echo $mahasiswa['program_studi']; ?></p>
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
                                    <input type="text" class="form-control" name="nama_lengkap" value="<?php echo $mahasiswa['nama_lengkap']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NIM</label>
                                    <input type="text" class="form-control" value="<?php echo $mahasiswa['nim']; ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?php echo $mahasiswa['email']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" name="no_telp" value="<?php echo $mahasiswa['no_telp']; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Program Studi</label>
                                <input type="text" class="form-control" value="<?php echo $mahasiswa['program_studi']; ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat" rows="3"><?php echo $mahasiswa['alamat']; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pembimbing 1</label>
                                    <input type="text" class="form-control" name="pembimbing_1" value="<?php echo $mahasiswa['pembimbing_1']; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pembimbing 2</label>
                                    <input type="text" class="form-control" name="pembimbing_2" value="<?php echo $mahasiswa['pembimbing_2']; ?>">
                                </div>
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
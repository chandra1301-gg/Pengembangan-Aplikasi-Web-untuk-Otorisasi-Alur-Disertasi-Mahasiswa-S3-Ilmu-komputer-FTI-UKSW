<?php
session_start();

// Koneksi database
require_once '../includes/db_connect.php';

$edit_mode = false;
$edit_data = [];

// Tambah pengumuman
if (isset($_POST['add_announcement'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $publish_date = $_POST['publish_date'];
    $author = $_POST['author'];
    $status = $_POST['status'];
    
    // Upload gambar
    $image = '';
    if ($_FILES['image']['name']) {
        $target_dir = "../assets/uploads/announcements/";
        $image = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }
    
    $sql = "INSERT INTO announcements (title, description, image, publish_date, author, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $title, $description, $image, $publish_date, $author, $status);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Pengumuman berhasil diupdate!";
        header("Location: announcements.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal mengupdate pengumuman!";
        header("Location: announcements.php");
        exit();
    }
}

// Edit pengumuman - tampilkan form
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $edit_mode = true;
}

// Update pengumuman
if (isset($_POST['update_announcement'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $publish_date = $_POST['publish_date'];
    $author = $_POST['author'];
    $status = $_POST['status'];
    
    // Cek apakah ada gambar baru diupload
    if ($_FILES['image']['name']) {
        // Hapus gambar lama jika ada
        if ($_POST['old_image']) {
            $old_image_path = "../assets/uploads/announcements/" . $_POST['old_image'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
        
        // Upload gambar baru
        $target_dir = "../assets/uploads/announcements/";
        $image = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    } else {
        // Gunakan gambar lama
        $image = $_POST['old_image'];
    }
    
    $sql = "UPDATE announcements SET title = ?, description = ?, image = ?, publish_date = ?, author = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $title, $description, $image, $publish_date, $author, $status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Pengumuman berhasil diupdate!";
        header("Location: announcements.php");
        exit();
    }
}

// Hapus pengumuman
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Hapus gambar dari server
    $sql_select = "SELECT image FROM announcements WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['image']) {
        $image_path = "../assets/uploads/announcements/" . $row['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $sql = "DELETE FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Pengumuman berhasil dihapus!";
        header("Location: announcements.php");
        exit();
    }
}

// Ambil data pengumuman
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($sql);

include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <h2>Management Pengumuman</h2>
        
        <!-- Notifikasi -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <!-- Form Tambah/Edit Pengumuman -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><?php echo $edit_mode ? 'Edit Pengumuman' : 'Tambah Pengumuman Baru'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <input type="hidden" name="old_image" value="<?php echo $edit_data['image']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Judul Pengumuman</label>
                                <input type="text" name="title" class="form-control" 
                                       value="<?php echo $edit_mode ? $edit_data['title'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="4" required><?php echo $edit_mode ? $edit_data['description'] : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Publikasi</label>
                                <input type="date" name="publish_date" class="form-control" 
                                       value="<?php echo $edit_mode ? $edit_data['publish_date'] : date('Y-m-d'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Author</label>
                                <input type="text" name="author" class="form-control" 
                                       value="<?php echo $edit_mode ? $edit_data['author'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="active" <?php echo ($edit_mode && $edit_data['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($edit_mode && $edit_data['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gambar</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <?php if ($edit_mode && $edit_data['image']): ?>
                                    <div class="mt-2">
                                        <img src="../assets/uploads/announcements/<?php echo $edit_data['image']; ?>" 
                                             width="100" height="80" style="object-fit: cover;" class="rounded">
                                        <small class="text-muted d-block">Gambar saat ini</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($edit_mode): ?>
                        <button type="submit" name="update_announcement" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i> Update Pengumuman
                        </button>
                        <a href="announcements.php" class="btn btn-secondary">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="add_announcement" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Pengumuman
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Daftar Pengumuman -->
        <div class="card">
            <div class="card-header">
                <h5>Daftar Pengumuman</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['image']): ?>
                                        <img src="../assets/uploads/announcements/<?php echo $row['image']; ?>" 
                                             width="60" height="60" style="object-fit: cover;" class="rounded">
                                    <?php else: ?>
                                        <img src="../assets/images/announcement.jpg" 
                                             width="60" height="60" style="object-fit: cover;" class="rounded">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo $row['title']; ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo substr($row['description'], 0, 80); ?>...
                                    </small>
                                </td>
                                <td><?php echo date('d M Y', strtotime($row['publish_date'])); ?></td>
                                <td><?php echo $row['author']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Yakin hapus pengumuman ini?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
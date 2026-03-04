<?php
/**
 * File: mahasiswa/views/messages.php
 * View untuk halaman pesan mahasiswa - DIUPDATE
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pesan - Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .message-item {
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
        .message-item.unread {
            border-left-color: #007bff;
            background-color: #f8f9fa;
        }
        .message-item:hover {
            background-color: #e9ecef;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #007bff;
        }
        .badge-notification {
            font-size: 0.7em;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../includes/header.php'; ?>
            <?php include '../includes/sidebar_mahasiswa.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-envelope"></i> Sistem Pesan
                        <?php if ($unread_count > 0): ?>
                            <span class="badge bg-danger badge-notification"><?php echo $unread_count; ?> baru</span>
                        <?php endif; ?>
                    </h1>
                </div>

                <!-- Notifikasi -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tab == 'inbox') ? 'active' : ''; ?>" 
                           href="messages.php?tab=inbox">
                            <i class="fas fa-inbox"></i> Kotak Masuk
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tab == 'outbox') ? 'active' : ''; ?>" 
                           href="messages.php?tab=outbox">
                            <i class="fas fa-paper-plane"></i> Terkirim
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($tab == 'compose') ? 'active' : ''; ?>" 
                           href="messages.php?tab=compose">
                            <i class="fas fa-edit"></i> Tulis Pesan
                        </a>
                    </li>
                </ul>

                <!-- Content berdasarkan tab -->
                <?php if ($tab == 'compose'): ?>
                    <!-- Form Tulis Pesan -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-edit"></i> Tulis Pesan Baru</h5>
                        </div>
                        <div class="card-body">
                            <form action="messages.php?action=send" method="POST">
                                <div class="mb-3">
                                    <label for="receiver_id" class="form-label">Kepada: <span class="text-danger">*</span></label>
                                    <select class="form-select" id="receiver_id" name="receiver_id" required>
                                        <option value="">Pilih Admin</option>
                                        <?php foreach ($admins as $admin): ?>
                                            <option value="<?php echo $admin['id']; ?>"
                                                <?php echo (isset($reply_to) && $reply_to == $admin['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($admin['username']); ?> (Admin)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           value="<?php echo isset($reply_subject) ? htmlspecialchars($reply_subject) : ''; ?>" 
                                           required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Pesan: <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="8" 
                                              placeholder="Tulis pesan Anda di sini..." required></textarea>
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="messages.php" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Daftar Pesan (Inbox/Outbox) -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-<?php echo ($tab == 'inbox') ? 'inbox' : 'paper-plane'; ?>"></i>
                                <?php echo ($tab == 'inbox') ? 'Pesan Masuk' : 'Pesan Terkirim'; ?>
                                <span class="badge bg-secondary"><?php echo count($messages); ?></span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($messages)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada pesan</p>
                                    <?php if ($tab == 'inbox'): ?>
                                        <a href="messages.php?tab=compose" class="btn btn-primary">
                                            <i class="fas fa-edit"></i> Tulis Pesan Pertama
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($messages as $msg): ?>
                                        <a href="messages.php?action=view&id=<?php echo $msg['id']; ?>" 
                                           class="list-group-item list-group-item-action message-item <?php echo ($tab == 'inbox' && !$msg['is_read']) ? 'unread' : ''; ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php if ($tab == 'inbox'): ?>
                                                        <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                                                        <small class="text-muted">(<?php echo ucfirst($msg['sender_role']); ?>)</small>
                                                    <?php else: ?>
                                                        <strong>Kepada: <?php echo htmlspecialchars($msg['receiver_name']); ?></strong>
                                                        <small class="text-muted">(<?php echo ucfirst($msg['receiver_role']); ?>)</small>
                                                    <?php endif; ?>
                                                    <?php if ($tab == 'inbox' && !$msg['is_read']): ?>
                                                        <span class="badge bg-primary">Baru</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <small class="text-muted"><?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-1 fw-bold"><?php echo htmlspecialchars($msg['subject']); ?></p>
                                            <p class="mb-1 text-muted small">
                                                <?php 
                                                $preview = strip_tags($msg['message']);
                                                echo strlen($preview) > 150 ? 
                                                    substr($preview, 0, 150) . '...' : 
                                                    $preview; 
                                                ?>
                                            </p>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
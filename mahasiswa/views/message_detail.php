<?php
/**
 * File: mahasiswa/views/message_detail.php
 * View untuk detail pesan mahasiswa
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesan - Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .message-content {
            white-space: pre-wrap;
            line-height: 1.6;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
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
                    <h1 class="h2"><i class="fas fa-envelope-open"></i> Detail Pesan</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="messages.php" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="messages.php?action=delete&id=<?php echo $message['id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus pesan ini?')">
                            <i class="fas fa-trash"></i> Hapus
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['subject']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong><i class="fas fa-user"></i> Dari:</strong> 
                                    <?php echo htmlspecialchars($message['sender_name']); ?>
                                    <span class="badge bg-info"><?php echo ucfirst($message['sender_role']); ?></span>
                                </p>
                                <p class="mb-1">
                                    <strong><i class="fas fa-user"></i> Kepada:</strong> 
                                    <?php echo htmlspecialchars($message['receiver_name']); ?>
                                    <span class="badge bg-info"><?php echo ucfirst($message['receiver_role']); ?></span>
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="mb-1">
                                    <strong><i class="fas fa-clock"></i> Tanggal:</strong> 
                                    <?php echo date('d F Y H:i', strtotime($message['created_at'])); ?>
                                </p>
                                <p class="mb-1">
                                    <strong><i class="fas fa-eye"></i> Status:</strong> 
                                    <span class="badge bg-<?php echo $message['is_read'] ? 'success' : 'warning'; ?>">
                                        <?php echo $message['is_read'] ? 'Telah Dibaca' : 'Belum Dibaca'; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="border-top pt-3">
                            <h6 class="mb-3"><i class="fas fa-file-text"></i> Isi Pesan:</h6>
                            <div class="message-content"><?php echo htmlspecialchars($message['message']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Balas -->
                <?php if ($message['sender_id'] != $_SESSION['user_id']): ?>
                    <div class="mt-3 d-flex justify-content-end">
                        <a href="messages.php?tab=compose&reply_to=<?php echo $message['sender_id']; ?>&subject=Re: <?php echo urlencode($message['subject']); ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-reply"></i> Balas Pesan
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
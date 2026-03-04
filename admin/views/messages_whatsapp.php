<?php
/**
 * File: admin/views/messages_whatsapp.php
 * View untuk halaman pesan dengan tampilan WhatsApp - TAMBAH TOMBOL HAPUS
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan - WhatsApp Style</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .chat-container {
            height: 80vh;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .conversations-list {
            background: white;
            border-right: 1px solid #ddd;
            height: 100%;
            overflow-y: auto;
        }
        
        .chat-area {
            background: #f8f9fa;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.3s;
            position: relative;
        }
        
        .conversation-item:hover, .conversation-item.active {
            background: #f0f0f0;
        }
        
        .conversation-item.unread {
            background: #e3f2fd;
        }
        
        .chat-header {
            background: #075e54;
            color: white;
            padding: 15px;
        }
        
        .messages-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #e5ddd5;
        }
        
        .message {
            max-width: 70%;
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 7.5px;
            position: relative;
            word-wrap: break-word;
        }
        
        .message.sent {
            background: #dcf8c6;
            margin-left: auto;
            border-bottom-right-radius: 0;
        }
        
        .message.received {
            background: white;
            margin-right: auto;
            border-bottom-left-radius: 0;
        }
        
        .message-time {
            font-size: 0.75em;
            color: #666;
            text-align: right;
            margin-top: 5px;
        }
        
        .message-input {
            background: white;
            padding: 15px;
            border-top: 1px solid #ddd;
        }
        
        .unread-badge {
            background: #25d366;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7em;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .last-message-preview {
            font-size: 0.9em;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .no-conversation {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            text-align: center;
        }
        
        .new-chat-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #075e54;
            color: white;
            border: none;
            font-size: 1.5em;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .conversation-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .conversation-item:hover .conversation-actions {
            opacity: 1;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .message-actions {
            position: absolute;
            top: 5px;
            right: 5px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .message:hover .message-actions {
            opacity: 1;
        }
        
        .dropdown-menu {
            min-width: 100px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../includes/header.php'; ?>
            <?php include '../includes/sidebar_admin.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-comments"></i> Percakapan
                        <?php if ($unread_count > 0): ?>
                            <span class="badge bg-danger"><?php echo $unread_count; ?> baru</span>
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

                <div class="chat-container">
                    <div class="row h-100">
                        <!-- Daftar Percakapan -->
                        <div class="col-md-4 conversations-list">
                            <div class="p-3 border-bottom">
                                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Percakapan</h5>
                            </div>
                            
                            <?php if (empty($conversations)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada percakapan</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($conversations as $conv): ?>
                                    <div class="conversation-item <?php echo ($conv['unread_count'] > 0) ? 'unread' : ''; ?> <?php echo (isset($other_user) && $other_user['id'] == $conv['other_user_id']) ? 'active' : ''; ?>">
                                        <div class="conversation-actions">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item text-danger" 
                                                           href="#" 
                                                           onclick="confirmDeleteConversation(<?php echo $conv['other_user_id']; ?>)">
                                                            <i class="fas fa-trash me-2"></i>Hapus Percakapan
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <div onclick="location.href='messages.php?action=conversation&with=<?php echo $conv['other_user_id']; ?>'">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <?php echo htmlspecialchars($conv['other_user_name']); ?>
                                                        <small class="text-muted">(<?php echo ucfirst($conv['other_user_role']); ?>)</small>
                                                    </h6>
                                                    <p class="last-message-preview mb-1">
                                                        <?php 
                                                        $preview = strip_tags($conv['last_message']);
                                                        echo strlen($preview) > 40 ? 
                                                            substr($preview, 0, 40) . '...' : 
                                                            $preview; 
                                                        ?>
                                                    </p>
                                                    <small class="text-muted">
                                                        <?php echo date('H:i', strtotime($conv['last_message_time'])); ?>
                                                    </small>
                                                </div>
                                                <?php if ($conv['unread_count'] > 0): ?>
                                                    <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Area Chat -->
                        <div class="col-md-8 chat-area">
                            <?php if ($action == 'conversation' && isset($other_user)): ?>
                                <!-- Header Chat -->
                                <div class="chat-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <a href="messages.php" class="text-white me-3">
                                                <i class="fas fa-arrow-left"></i>
                                            </a>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($other_user['username']); ?></h6>
                                                <small class="opacity-75"><?php echo ucfirst($other_user['role']); ?></small>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-light dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item text-danger" 
                                                       href="#" 
                                                       onclick="confirmDeleteConversation(<?php echo $other_user['id']; ?>)">
                                                        <i class="fas fa-trash me-2"></i>Hapus Percakapan
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Messages -->
                                <div class="messages-container" id="messagesContainer">
                                    <?php if (empty($conversation)): ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-comment-slash fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada pesan dalam percakapan ini</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($conversation as $msg): ?>
                                            <div class="message <?php echo ($msg['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                                                <div class="message-actions">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item text-danger" 
                                                                   href="#" 
                                                                   onclick="confirmDeleteMessage(<?php echo $msg['id']; ?>, <?php echo $other_user['id']; ?>)">
                                                                    <i class="fas fa-trash me-2"></i>Hapus Pesan
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                
                                                <div class="message-text">
                                                    <?php echo htmlspecialchars($msg['message']); ?>
                                                </div>
                                                <div class="message-time">
                                                    <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                                    <?php if ($msg['sender_id'] == $user_id && $msg['is_read']): ?>
                                                        <i class="fas fa-check-double text-primary ms-1"></i>
                                                    <?php elseif ($msg['sender_id'] == $user_id): ?>
                                                        <i class="fas fa-check text-muted ms-1"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Input Message -->
                                <div class="message-input">
                                    <form action="messages.php?action=send" method="POST" class="d-flex">
                                        <input type="hidden" name="receiver_id" value="<?php echo $other_user['id']; ?>">
                                        <input type="hidden" name="parent_id" value="<?php echo isset($conversation[0]) ? $conversation[0]['id'] : ''; ?>">
                                        <div class="flex-grow-1 me-2">
                                            <textarea name="message" class="form-control" rows="1" 
                                                      placeholder="Ketik pesan..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>

                            <?php else: ?>
                                <!-- No Conversation Selected -->
                                <div class="no-conversation">
                                    <div>
                                        <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                                        <h5 class="text-muted">Pilih percakapan untuk memulai chat</h5>
                                        <p class="text-muted">atau mulai percakapan baru</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Floating Button untuk Chat Baru -->
                <button type="button" class="new-chat-btn" data-bs-toggle="modal" data-bs-target="#newChatModal">
                    <i class="fas fa-edit"></i>
                </button>
            </main>
        </div>
    </div>

    <!-- Modal untuk Chat Baru -->
    <div class="modal fade" id="newChatModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Percakapan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="messages.php?action=send" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Pilih Mahasiswa:</label>
                            <select class="form-select" name="receiver_id" required>
                                <option value="">Pilih Mahasiswa</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars($student['username']); ?> (Mahasiswa)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pesan Pertama:</label>
                            <textarea class="form-control" name="message" rows="3" 
                                      placeholder="Tulis pesan pertama..." required></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Mulai Percakapan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto scroll to bottom of messages
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.getElementById('messagesContainer');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });

        // Auto-resize textarea
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });

        // Konfirmasi hapus percakapan
        function confirmDeleteConversation(otherUserId) {
            if (confirm('Apakah Anda yakin ingin menghapus percakapan ini? Tindakan ini tidak dapat dibatalkan.')) {
                window.location.href = 'messages.php?action=delete_conversation&with=' + otherUserId;
            }
        }

        // Konfirmasi hapus pesan
        function confirmDeleteMessage(messageId, conversationWith) {
            if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
                window.location.href = 'messages.php?action=delete_message&id=' + messageId + '&with=' + conversationWith;
            }
        }
    </script>
</body>
</html>
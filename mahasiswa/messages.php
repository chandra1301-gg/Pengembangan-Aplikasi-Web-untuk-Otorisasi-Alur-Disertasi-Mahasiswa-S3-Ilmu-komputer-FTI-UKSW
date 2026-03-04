<?php
/**
 * File: mahasiswa/messages.php
 * Controller untuk mengelola pesan mahasiswa - VERSI TERBARU RESPONSIF
 */

require_once '../includes/db_connect.php';
require_once '../includes/message_model.php';

// Cek session mahasiswa
if (!isset($_SESSION['user_id']) || MessageModel::getUserRole($_SESSION['user_id']) != 'mahasiswa') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : 'conversations';
$conversation_with = isset($_GET['with']) ? $_GET['with'] : null;

// Handle actions
switch ($action) {
    case 'conversation':
        if (!$conversation_with) {
            $_SESSION['error'] = "Pilih percakapan terlebih dahulu.";
            header('Location: messages.php');
            exit;
        }
        
        // Cek apakah percakapan sudah dihapus
        if (MessageModel::isConversationFullyDeleted($user_id, $conversation_with)) {
            $_SESSION['error'] = "Percakapan tidak ditemukan atau sudah dihapus.";
            header('Location: messages.php');
            exit;
        }
        
        // Mark all messages as read when opening conversation
        $query = "UPDATE messages SET is_read = 1 
                  WHERE receiver_id = '$user_id' AND sender_id = '$conversation_with' 
                  AND is_read = 0 AND deleted_by_receiver = 0";
        db_execute($query);
        
        $conversation = MessageModel::getConversation($user_id, $conversation_with);
        $other_user = MessageModel::getUserById($conversation_with);
        break;
        
    case 'send':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $sender_id = $user_id;
            $receiver_id = $_POST['receiver_id'];
            $message_content = $_POST['message'];
            $parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : null;
            
            if (empty($message_content)) {
                $_SESSION['error'] = "Pesan tidak boleh kosong.";
                header('Location: messages.php?action=conversation&with=' . $receiver_id);
                exit;
            }
            
            $original_message = null;
            if ($parent_id) {
                $original_message = MessageModel::getMessage($parent_id, $user_id);
            }
            
            $subject = $original_message ? $original_message['subject'] : 'Percakapan';
            
            $result = MessageModel::sendMessage($sender_id, $receiver_id, $subject, $message_content, $parent_id);
            
            if ($result) {
                $_SESSION['success'] = "Pesan berhasil dikirim.";
            } else {
                $_SESSION['error'] = "Gagal mengirim pesan.";
            }
            
            header('Location: messages.php?action=conversation&with=' . $receiver_id);
            exit;
        }
        break;
        
    case 'delete_conversation':
        $other_user_id = isset($_GET['with']) ? $_GET['with'] : null;
        
        if (!$other_user_id) {
            $_SESSION['error'] = "Percakapan tidak valid.";
            header('Location: messages.php');
            exit;
        }
        
        $result = MessageModel::deleteConversation($user_id, $other_user_id);
        
        if ($result) {
            $_SESSION['success'] = "Percakapan berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus percakapan.";
        }
        
        header('Location: messages.php');
        exit;
        
    case 'delete_message':
        $message_id = isset($_GET['id']) ? $_GET['id'] : 0;
        $conversation_with = isset($_GET['with']) ? $_GET['with'] : null;
        
        $result = MessageModel::deleteMessage($message_id, $user_id);
        
        if ($result) {
            $_SESSION['success'] = "Pesan berhasil dihapus.";
            
            // Cek apakah setelah hapus pesan, percakapan menjadi kosong
            if ($conversation_with && MessageModel::isConversationFullyDeleted($user_id, $conversation_with)) {
                $_SESSION['info'] = "Pesan dihapus. Percakapan telah dihapus karena tidak ada pesan lagi.";
                header('Location: messages.php');
                exit;
            }
        } else {
            $_SESSION['error'] = "Gagal menghapus pesan.";
        }
        
        if ($conversation_with) {
            header('Location: messages.php?action=conversation&with=' . $conversation_with);
        } else {
            header('Location: messages.php');
        }
        exit;
        
    default:
        // Get conversations list - ini akan otomatis exclude yang sudah dihapus
        $conversations = MessageModel::getConversations($user_id);
        $admins = MessageModel::getAdmins();
        break;
}

$unread_count = MessageModel::getUnreadCount($user_id);

// Include view
include 'views/messages_whatsapp.php';
?>
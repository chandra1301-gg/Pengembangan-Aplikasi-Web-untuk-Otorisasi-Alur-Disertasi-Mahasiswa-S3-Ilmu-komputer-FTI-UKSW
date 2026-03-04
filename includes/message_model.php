<?php
/**
 * File: includes/message_model.php
 * Model untuk mengelola pesan - VERSI TERBARU DENGAN HAPUS PERCAKAPAN
 */

require_once 'db_connect.php';

class MessageModel {
    
    /**
     * Mengirim pesan baru
     */
    public static function sendMessage($sender_id, $receiver_id, $subject, $message, $parent_id = null) {
        $sender_id = escape_string($sender_id);
        $receiver_id = escape_string($receiver_id);
        $subject = escape_string(clean_input($subject));
        $message = escape_string(clean_input($message));
        $parent_id = $parent_id ? escape_string($parent_id) : 'NULL';
        
        $query = "INSERT INTO messages (sender_id, receiver_id, subject, message, parent_id) 
                  VALUES ('$sender_id', '$receiver_id', '$subject', '$message', $parent_id)";
        
        return db_execute($query);
    }
    
    /**
     * Mendapatkan semua percakapan dengan user tertentu - DIUPDATE untuk exclude yang dihapus
     */
    public static function getConversation($user1_id, $user2_id) {
        $user1_id = escape_string($user1_id);
        $user2_id = escape_string($user2_id);
        
        $query = "SELECT m.*, u.username as sender_name, u.role as sender_role
                  FROM messages m
                  JOIN users u ON m.sender_id = u.id
                  WHERE ((m.sender_id = '$user1_id' AND m.receiver_id = '$user2_id' AND m.deleted_by_sender = 0)
                     OR (m.sender_id = '$user2_id' AND m.receiver_id = '$user1_id' AND m.deleted_by_receiver = 0))
                  ORDER BY m.created_at ASC";
        
        return db_fetch_all($query);
    }
    
    /**
     * Mendapatkan list percakapan terbaru - DIUPDATE untuk exclude yang dihapus
     */
    public static function getConversations($user_id) {
        $user_id = escape_string($user_id);
        
        $query = "SELECT 
                    u.id as other_user_id,
                    u.username as other_user_name,
                    u.role as other_user_role,
                    m.message as last_message,
                    m.created_at as last_message_time,
                    m.is_read,
                    (SELECT COUNT(*) FROM messages m2 
                     WHERE ((m2.sender_id = u.id AND m2.receiver_id = '$user_id') 
                         OR (m2.sender_id = '$user_id' AND m2.receiver_id = u.id))
                     AND m2.is_read = 0 
                     AND m2.receiver_id = '$user_id'
                     AND (m2.deleted_by_receiver = 0 OR m2.receiver_id != '$user_id')
                     AND (m2.deleted_by_sender = 0 OR m2.sender_id != '$user_id')) as unread_count
                  FROM users u
                  INNER JOIN messages m ON (
                      (m.sender_id = u.id AND m.receiver_id = '$user_id' AND m.deleted_by_receiver = 0)
                      OR (m.sender_id = '$user_id' AND m.receiver_id = u.id AND m.deleted_by_sender = 0)
                  )
                  WHERE u.id != '$user_id'
                  AND m.id = (
                      SELECT MAX(m2.id) 
                      FROM messages m2 
                      WHERE (m2.sender_id = u.id AND m2.receiver_id = '$user_id' AND m2.deleted_by_receiver = 0)
                         OR (m2.sender_id = '$user_id' AND m2.receiver_id = u.id AND m2.deleted_by_sender = 0)
                  )
                  GROUP BY u.id, u.username, u.role
                  HAVING COUNT(m.id) > 0  -- Pastikan ada pesan yang tidak dihapus
                  ORDER BY m.created_at DESC";
        
        return db_fetch_all($query);
    }
    
    /**
     * Mendapatkan pesan masuk untuk user
     */
    public static function getInbox($user_id) {
        $user_id = escape_string($user_id);
        
        $query = "SELECT m.*, u.username as sender_name, u.role as sender_role
                  FROM messages m
                  JOIN users u ON m.sender_id = u.id
                  WHERE m.receiver_id = '$user_id' 
                  AND m.deleted_by_receiver = 0
                  ORDER BY m.created_at DESC";
        
        return db_fetch_all($query);
    }
    
    /**
     * Mendapatkan detail pesan
     */
    public static function getMessage($message_id, $user_id) {
        $message_id = escape_string($message_id);
        $user_id = escape_string($user_id);
        
        // Update status baca jika penerima yang membuka
        $query = "UPDATE messages SET is_read = 1 
                  WHERE id = '$message_id' AND receiver_id = '$user_id' 
                  AND deleted_by_receiver = 0";
        db_execute($query);
        
        $query = "SELECT m.*, 
                         s.username as sender_name, s.role as sender_role,
                         r.username as receiver_name, r.role as receiver_role
                  FROM messages m
                  JOIN users s ON m.sender_id = s.id
                  JOIN users r ON m.receiver_id = r.id
                  WHERE m.id = '$message_id' 
                  AND (m.sender_id = '$user_id' OR m.receiver_id = '$user_id')
                  AND (m.deleted_by_sender = 0 OR m.sender_id != '$user_id')
                  AND (m.deleted_by_receiver = 0 OR m.receiver_id != '$user_id')";
        
        return db_fetch($query);
    }
    
    /**
     * Menghapus pesan (soft delete)
     */
    public static function deleteMessage($message_id, $user_id) {
        $message_id = escape_string($message_id);
        $user_id = escape_string($user_id);
        
        // Cek apakah user adalah pengirim atau penerima
        $message = self::getMessage($message_id, $user_id);
        
        if (!$message) {
            return false;
        }
        
        if ($message['sender_id'] == $user_id) {
            $query = "UPDATE messages SET deleted_by_sender = 1 WHERE id = '$message_id'";
        } else {
            $query = "UPDATE messages SET deleted_by_receiver = 1 WHERE id = '$message_id'";
        }
        
        return db_execute($query);
    }
    
    /**
     * Menghapus seluruh percakapan dengan user tertentu (soft delete) - DIUPDATE
     */
    public static function deleteConversation($user1_id, $user2_id) {
        $user1_id = escape_string($user1_id);
        $user2_id = escape_string($user2_id);
        
        // Hapus pesan yang dikirim oleh user1 ke user2
        $query1 = "UPDATE messages SET deleted_by_sender = 1 
                  WHERE sender_id = '$user1_id' AND receiver_id = '$user2_id'";
        $result1 = db_execute($query1);
        
        // Hapus pesan yang dikirim oleh user2 ke user1 (dari perspektif user1 sebagai receiver)
        $query2 = "UPDATE messages SET deleted_by_receiver = 1 
                  WHERE sender_id = '$user2_id' AND receiver_id = '$user1_id'";
        $result2 = db_execute($query2);
        
        return $result1 && $result2;
    }
    
    /**
     * Cek apakah percakapan sudah dihapus sepenuhnya - DIUPDATE
     */
    public static function isConversationFullyDeleted($user_id, $other_user_id) {
        $user_id = escape_string($user_id);
        $other_user_id = escape_string($other_user_id);
        
        // Cek apakah masih ada pesan yang tidak dihapus dari kedua sisi
        $query = "SELECT COUNT(*) as count 
                  FROM messages 
                  WHERE ((sender_id = '$user_id' AND receiver_id = '$other_user_id' AND deleted_by_sender = 0)
                     OR (sender_id = '$other_user_id' AND receiver_id = '$user_id' AND deleted_by_receiver = 0))";
        
        $result = db_fetch($query);
        return $result['count'] == 0;
    }
    
    /**
     * Mendapatkan jumlah pesan belum dibaca
     */
    public static function getUnreadCount($user_id) {
        $user_id = escape_string($user_id);
        
        $query = "SELECT COUNT(*) as count 
                  FROM messages 
                  WHERE receiver_id = '$user_id' 
                  AND is_read = 0 
                  AND deleted_by_receiver = 0";
        
        $result = db_fetch($query);
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Mendapatkan daftar mahasiswa untuk dropdown (admin)
     */
    public static function getStudents() {
        $query = "SELECT id, username FROM users WHERE role = 'mahasiswa' ORDER BY username";
        return db_fetch_all($query);
    }
    
    /**
     * Mendapatkan daftar admin untuk dropdown (mahasiswa)
     */
    public static function getAdmins() {
        $query = "SELECT id, username FROM users WHERE role = 'admin' OR role = 'administrator' ORDER BY username";
        return db_fetch_all($query);
    }
    
    /**
     * Mendapatkan informasi user by ID
     */
    public static function getUserById($user_id) {
        $user_id = escape_string($user_id);
        $query = "SELECT id, username, role FROM users WHERE id = '$user_id'";
        return db_fetch($query);
    }
    
    /**
     * Mendapatkan role user saat ini
     */
    public static function getUserRole($user_id) {
        $user_id = escape_string($user_id);
        $query = "SELECT role FROM users WHERE id = '$user_id'";
        $result = db_fetch($query);
        return $result ? $result['role'] : null;
    }
}
?>
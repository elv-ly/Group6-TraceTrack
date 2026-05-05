<?php

class Notification {

    private $conn;
    private $table = "NOTIFICATIONS";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ── GET ALL FOR USER ──────────────────────────────────────────
    public function getAll($user_id) {
        try {
            $query = "SELECT * FROM " . $this->table . "
                      WHERE user_id = :user_id
                      ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    // ── GET UNREAD COUNT ──────────────────────────────────────────
    public function getUnreadCount($user_id) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table . "
                      WHERE user_id = :user_id AND is_read = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    // ── MARK ONE AS READ ──────────────────────────────────────────
    public function markRead($notification_id, $user_id) {
        try {
            $query = "UPDATE " . $this->table . "
                      SET is_read = 1
                      WHERE notification_id = :id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $notification_id, ':user_id' => $user_id]);
            return ["status" => true];
        } catch (Throwable $e) {
            return ["status" => false];
        }
    }

    // ── MARK ALL AS READ ──────────────────────────────────────────
    public function markAllRead($user_id) {
        try {
            $query = "UPDATE " . $this->table . " SET is_read = 1 WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            return ["status" => true];
        } catch (Throwable $e) {
            return ["status" => false];
        }
    }

    // ── SEND NOTIFICATION ─────────────────────────────────────────
    public static function send($conn, $user_id, $type, $message, $reference_id = null, $reference_type = null) {
    try {
        $query = "INSERT INTO NOTIFICATIONS (user_id, type, message, reference_id, reference_type)
                  VALUES (:user_id, :type, :message, :ref_id, :ref_type)";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id'  => $user_id,
            ':type'     => $type,
            ':message'  => $message,
            ':ref_id'   => $reference_id,
            ':ref_type' => $reference_type,
        ]);
        return true;
    } catch (Throwable $e) {
        error_log("Notification send failed: " . $e->getMessage());
        return false;
    }
}

    // ── GET ADMIN USER ID ─────────────────────────────────────────
    public static function getAdminId($conn) {
        try {
            $stmt = $conn->prepare("SELECT user_id FROM USERS WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (Throwable $e) {
            return null;
        }
    }
}

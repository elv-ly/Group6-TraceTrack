<?php

class Item {

    private $conn;
    private $table = "ITEMS";

    public $item_id;
    public $user_id;
    public $report_type;
    public $item_name;
    public $category;
    public $description;
    public $location;
    public $date_occured;
    public $photo;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ── CREATE REPORT ─────────────────────────────────────────────
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table . "
                        (user_id, report_type, item_name, category, description, location, date_occured, photo)
                      VALUES
                        (:user_id, :report_type, :item_name, :category, :description, :location, :date_occured, :photo)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id'      => $this->user_id,
                ':report_type'  => $this->report_type,
                ':item_name'    => htmlspecialchars(strip_tags($this->item_name)),
                ':category'     => $this->category,
                ':description'  => htmlspecialchars(strip_tags($this->description)),
                ':location'     => htmlspecialchars(strip_tags($this->location)),
                ':date_occured' => $this->date_occured,
                ':photo'        => $this->photo,
            ]);

            $new_id = $this->conn->lastInsertId();

            // Notify admin
            $this->notifyAdmin($new_id, $this->report_type);

            return ["status" => true, "message" => "Report submitted successfully! Awaiting admin review."];

        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── READ ALL (public active items) ────────────────────────────
    public function readAll($type = null) {
        try {
            $sql = "SELECT i.*, u.full_name, u.contact
                    FROM " . $this->table . " i
                    JOIN USERS u ON i.user_id = u.user_id
                    WHERE i.status = 'active'";
            if ($type) $sql .= " AND i.report_type = :type";
            $sql .= " ORDER BY i.created_at DESC";

            $stmt = $this->conn->prepare($sql);
            if ($type) $stmt->bindValue(':type', $type);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    // ── READ MY REPORTS ───────────────────────────────────────────
    public function readMyReports($user_id) {
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

    // ── READ ONE ──────────────────────────────────────────────────
    public function readOne($item_id) {
        try {
            $query = "SELECT i.*, u.full_name, u.contact, u.email
                      FROM " . $this->table . " i
                      JOIN USERS u ON i.user_id = u.user_id
                      WHERE i.item_id = :item_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':item_id' => $item_id]);
            return $stmt->fetch();
        } catch (Throwable $e) {
            return false;
        }
    }

    // ── READ ALL FOR ADMIN ────────────────────────────────────────
    public function readAllAdmin() {
        try {
            $query = "SELECT i.*, u.full_name
                      FROM " . $this->table . " i
                      JOIN USERS u ON i.user_id = u.user_id
                      ORDER BY i.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    // ── REQUEST DELETION ──────────────────────────────────────────
    public function requestDeletion($item_id, $user_id, $reason) {
        try {
            // Check if already pending
            $check = $this->conn->prepare("SELECT deletion_id FROM DELETION_REQUESTS WHERE item_id = :item_id AND status = 'pending' LIMIT 1");
            $check->execute([':item_id' => $item_id]);
            if ($check->rowCount() > 0) {
                return ["status" => false, "message" => "A deletion request is already pending for this item."];
            }

            $query = "INSERT INTO DELETION_REQUESTS (item_id, user_id, reason) VALUES (:item_id, :user_id, :reason)";
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([
                ':item_id' => $item_id,
                ':user_id' => $user_id,
                ':reason'  => htmlspecialchars(strip_tags($reason)),
            ]);

            // Notify admin
            $this->notifyAdminDeletion($item_id);

            return ["status" => true, "message" => "Deletion request submitted. Awaiting admin approval."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── UPLOAD PHOTO ──────────────────────────────────────────────
    public static function uploadPhoto($file) {
        $allowed     = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size    = 5 * 1024 * 1024; // 5MB
        $upload_dir  = $_SERVER['DOCUMENT_ROOT'] . '/uploads/items/';

        if (!in_array($file['type'], $allowed)) {
            return ["status" => false, "message" => "Only JPG, PNG, and WEBP images are allowed."];
        }

        if ($file['size'] > $max_size) {
            return ["status" => false, "message" => "Image must be 5MB or less."];
        }

        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('item_', true) . '.' . $ext;
        $dest     = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ["status" => false, "message" => "Failed to save the image. Please try again."];
        }

        return ["status" => true, "path" => '/uploads/items/' . $filename];
    }

    // ── NOTIFY ADMIN (new report) ─────────────────────────────────
    private function notifyAdmin($item_id, $report_type) {
        try {
            $admin = $this->conn->prepare("SELECT user_id FROM USERS WHERE role = 'admin' LIMIT 1");
            $admin->execute();
            $admin_id = $admin->fetchColumn();
            if (!$admin_id) return;

            $label = $report_type === 'lost' ? 'Lost Item' : 'Found Item';
            $msg   = "A new $label report has been submitted and is awaiting your review.";

            $stmt = $this->conn->prepare("INSERT INTO NOTIFICATIONS (user_id, type, message, reference_id, reference_type)
                                          VALUES (:user_id, 'new_report', :message, :ref_id, 'item')");
            $stmt->execute([':user_id' => $admin_id, ':message' => $msg, ':ref_id' => $item_id]);
        } catch (Throwable $e) {}
    }

    // ── NOTIFY ADMIN (deletion request) ──────────────────────────
    private function notifyAdminDeletion($item_id) {
        try {
            $admin = $this->conn->prepare("SELECT user_id FROM USERS WHERE role = 'admin' LIMIT 1");
            $admin->execute();
            $admin_id = $admin->fetchColumn();
            if (!$admin_id) return;

            $stmt = $this->conn->prepare("INSERT INTO NOTIFICATIONS (user_id, type, message, reference_id, reference_type)
                                          VALUES (:user_id, 'new_deletion_request', 'A user has requested deletion of an item report.', :ref_id, 'item')");
            $stmt->execute([':user_id' => $admin_id, ':ref_id' => $item_id]);
        } catch (Throwable $e) {}
    }
}

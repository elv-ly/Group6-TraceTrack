<?php

class Claim {

    private $conn;
    private $table = "CLAIMS";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ── FILE A CLAIM ──────────────────────────────────────────────
    public function create($item_id, $user_id, $description, $proof_photo, $additional_info) {
        try {
            // Prevent claiming own item
            $check = $this->conn->prepare("SELECT user_id FROM ITEMS WHERE item_id = :id LIMIT 1");
            $check->execute([':id' => $item_id]);
            $owner = $check->fetchColumn();
            if ($owner == $user_id) return ["status" => false, "message" => "You cannot claim your own reported item."];

            // Prevent duplicate claim
            $dup = $this->conn->prepare("SELECT claim_id FROM CLAIMS WHERE item_id = :item_id AND user_id = :user_id LIMIT 1");
            $dup->execute([':item_id' => $item_id, ':user_id' => $user_id]);
            if ($dup->rowCount() > 0) return ["status" => false, "message" => "You have already filed a claim on this item."];

            $query = "INSERT INTO " . $this->table . "
                        (item_id, user_id, description, proof_photo, additional_info)
                      VALUES (:item_id, :user_id, :description, :proof_photo, :additional_info)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':item_id'         => $item_id,
                ':user_id'         => $user_id,
                ':description'     => htmlspecialchars(strip_tags($description)),
                ':proof_photo'     => $proof_photo,
                ':additional_info' => htmlspecialchars(strip_tags($additional_info)),
            ]);
            $claim_id = $this->conn->lastInsertId();

            // Get item info
            $item = $this->conn->prepare("SELECT item_name, user_id FROM ITEMS WHERE item_id = :id LIMIT 1");
            $item->execute([':id' => $item_id]);
            $item_row = $item->fetch();

            // Notify admin
            $admin_id = Notification::getAdminId($this->conn);
            if ($admin_id) {
                Notification::send($this->conn, $admin_id, 'new_claim',
                    "A new claim has been filed on '{$item_row['item_name']}'. Please review.",
                    $claim_id, 'claim');
            }

            // Notify finder
            Notification::send($this->conn, $item_row['user_id'], 'claim_submitted',
                "Someone has filed a claim on your found item '{$item_row['item_name']}'. Awaiting admin review.",
                $claim_id, 'claim');

            return ["status" => true, "message" => "Claim submitted! Awaiting admin review."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── GET MY CLAIMS ─────────────────────────────────────────────
    public function getMyClaims($user_id) {
        try {
            $query = "SELECT c.*, i.item_name, i.category, i.location, i.report_type, i.photo,
                             u.full_name AS reporter_name, u.contact AS reporter_contact
                      FROM " . $this->table . " c
                      JOIN ITEMS i ON c.item_id = i.item_id
                      LEFT JOIN USERS u ON i.user_id = u.user_id
                      WHERE c.user_id = :user_id
                      ORDER BY c.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    // ── GET ONE ───────────────────────────────────────────────────
    public function getOne($claim_id, $user_id = null) {
        try {
            $sql = "SELECT c.*, i.item_name, i.user_id AS item_owner_id,
                           u.full_name AS claimant_name, u.contact AS claimant_contact, u.email AS claimant_email
                    FROM " . $this->table . " c
                    JOIN ITEMS i ON c.item_id = i.item_id
                    LEFT JOIN USERS u ON c.user_id = u.user_id
                    WHERE c.claim_id = :claim_id";
            if ($user_id) $sql .= " AND c.user_id = :user_id";
            $sql .= " LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $params = [':claim_id' => $claim_id];
            if ($user_id) $params[':user_id'] = $user_id;
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (Throwable $e) {
            return false;
        }
    }

    // ── GET ALL FOR ADMIN ─────────────────────────────────────────
    public function getAllAdmin($status = '') {
        try {
            $sql = "SELECT c.*, i.item_name, i.report_type,
                           u.full_name AS claimant_name, u.contact AS claimant_contact
                    FROM " . $this->table . " c
                    JOIN ITEMS i ON c.item_id = i.item_id
                    LEFT JOIN USERS u ON c.user_id = u.user_id";
            $params = [];
            if ($status) { $sql .= " WHERE c.status = :status"; $params[':status'] = $status; }
            $sql .= " ORDER BY c.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    // ── ADMIN APPROVE CLAIM ───────────────────────────────────────
    public function adminApprove($claim_id, $admin_id) {
        try {
            $claim = $this->getOne($claim_id);
            if (!$claim) return ["status" => false, "message" => "Claim not found."];
            $this->conn->prepare("UPDATE " . $this->table . " SET status='approved', reviewed_by=:admin, reviewed_at=NOW() WHERE claim_id=:id")
                ->execute([':admin' => $admin_id, ':id' => $claim_id]);
            $this->conn->prepare("UPDATE ITEMS SET status='claimed' WHERE item_id=:id")
                ->execute([':id' => $claim['item_id']]);
            Notification::send($this->conn, $claim['user_id'], 'claim_approved',
                "Your claim on '{$claim['item_name']}' has been approved! Please coordinate with the finder for handover.",
                $claim_id, 'claim');
            Notification::send($this->conn, $claim['item_owner_id'], 'claim_approved',
                "A claim on your found item '{$claim['item_name']}' has been approved. Please coordinate handover.",
                $claim_id, 'claim');
            return ["status" => true, "message" => "Claim approved."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── ADMIN REJECT CLAIM ────────────────────────────────────────
    public function adminReject($claim_id, $admin_id, $reason) {
        try {
            $claim = $this->getOne($claim_id);
            if (!$claim) return ["status" => false, "message" => "Claim not found."];
            $this->conn->prepare("UPDATE " . $this->table . " SET status='rejected', admin_note=:reason, reviewed_by=:admin, reviewed_at=NOW() WHERE claim_id=:id")
                ->execute([':reason' => $reason, ':admin' => $admin_id, ':id' => $claim_id]);
            Notification::send($this->conn, $claim['user_id'], 'claim_rejected',
                "Your claim on '{$claim['item_name']}' was rejected. Reason: $reason",
                $claim_id, 'claim');
            return ["status" => true, "message" => "Claim rejected."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── MARK AS RETURNED ──────────────────────────────────────────
    public function markReturned($claim_id, $admin_id) {
        try {
            $claim = $this->getOne($claim_id);
            if (!$claim) return ["status" => false, "message" => "Claim not found."];
            $this->conn->prepare("UPDATE " . $this->table . " SET status='returned', reviewed_by=:admin WHERE claim_id=:id")
                ->execute([':admin' => $admin_id, ':id' => $claim_id]);
            $this->conn->prepare("UPDATE ITEMS SET status='returned' WHERE item_id=:id")
                ->execute([':id' => $claim['item_id']]);
            Notification::send($this->conn, $claim['user_id'], 'item_returned',
                "The item '{$claim['item_name']}' has been marked as returned. Thank you!",
                $claim_id, 'claim');
            Notification::send($this->conn, $claim['item_owner_id'], 'item_returned',
                "Your found item '{$claim['item_name']}' has been marked as returned.",
                $claim_id, 'claim');
            return ["status" => true, "message" => "Item marked as returned."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── REQUEST CANCEL ────────────────────────────────────────────
    public function requestCancel($claim_id, $user_id) {
        try {
            $claim = $this->getOne($claim_id, $user_id);
            if (!$claim) return ["status" => false, "message" => "Claim not found."];
            if ($claim['status'] !== 'pending') return ["status" => false, "message" => "Only pending claims can be cancelled."];
            $this->conn->prepare("UPDATE " . $this->table . " SET status='cancel_requested' WHERE claim_id=:id AND user_id=:uid")
                ->execute([':id' => $claim_id, ':uid' => $user_id]);
            $admin_id = Notification::getAdminId($this->conn);
            if ($admin_id) {
                Notification::send($this->conn, $admin_id, 'new_claim',
                    "A user requested to cancel their claim on '{$claim['item_name']}'.",
                    $claim_id, 'claim');
            }
            return ["status" => true, "message" => "Cancel request submitted. Awaiting admin approval."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── ADMIN APPROVE CANCEL ──────────────────────────────────────
    public function adminApproveCancel($claim_id, $admin_id) {
        try {
            $claim = $this->getOne($claim_id);
            if (!$claim) return ["status" => false, "message" => "Claim not found."];
            $this->conn->prepare("UPDATE " . $this->table . " SET status='rejected', reviewed_by=:admin, reviewed_at=NOW() WHERE claim_id=:id")
                ->execute([':admin' => $admin_id, ':id' => $claim_id]);
            $this->conn->prepare("UPDATE ITEMS SET status='active' WHERE item_id=:id")
                ->execute([':id' => $claim['item_id']]);
            Notification::send($this->conn, $claim['user_id'], 'claim_rejected',
                "Your request to cancel your claim on '{$claim['item_name']}' has been approved.",
                $claim_id, 'claim');
            return ["status" => true, "message" => "Claim cancellation approved."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── ADMIN REJECT CANCEL ───────────────────────────────────────
    public function adminRejectCancel($claim_id, $admin_id, $reason) {
        try {
            $claim = $this->getOne($claim_id);
            if (!$claim) return ["status" => false, "message" => "Claim not found."];
            $this->conn->prepare("UPDATE " . $this->table . " SET status='pending', admin_note=:reason, reviewed_by=:admin, reviewed_at=NOW() WHERE claim_id=:id")
                ->execute([':reason' => $reason, ':admin' => $admin_id, ':id' => $claim_id]);
            Notification::send($this->conn, $claim['user_id'], 'claim_approved',
                "Your request to cancel your claim on '{$claim['item_name']}' was denied. Reason: $reason",
                $claim_id, 'claim');
            return ["status" => true, "message" => "Claim cancellation denied."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── UPLOAD PROOF PHOTO ────────────────────────────────────────
    public static function uploadProof($file) {
        $allowed    = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size   = 5 * 1024 * 1024;
        $upload_dir = dirname(__DIR__) . '/uploads/proofs/';
        if (!in_array($file['type'], $allowed)) return ["status" => false, "message" => "Only JPG, PNG, WEBP allowed."];
        if ($file['size'] > $max_size) return ["status" => false, "message" => "Image must be 5MB or less."];
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('proof_', true) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) return ["status" => false, "message" => "Failed to save image."];
        return ["status" => true, "path" => '/uploads/proofs/' . $filename];
    }
}

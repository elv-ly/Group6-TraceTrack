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

    // ── CREATE ────────────────────────────────────────────────────
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
            $new_id   = $this->conn->lastInsertId();
            $admin_id = Notification::getAdminId($this->conn);
            if ($admin_id) {
                $label = $this->report_type === 'lost' ? 'Lost Item' : 'Found Item';
                Notification::send($this->conn, $admin_id, 'new_report',
                    "A new $label report '{$this->item_name}' has been submitted and awaits your review.",
                    $new_id, 'item');
            }
            return ["status" => true, "message" => "Report submitted! Awaiting admin review."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── BROWSE (active items only, exclude own) ───────────────────
    public function browse($user_id, $search = '', $type = '', $category = '') {
        try {
            $sql = "SELECT i.*, u.full_name, u.contact,
                           CASE WHEN r.return_id IS NOT NULL THEN 1 ELSE 0 END AS has_return_request
                    FROM " . $this->table . " i
                    LEFT JOIN USERS u ON i.user_id = u.user_id
                    LEFT JOIN RETURNS r ON i.item_id = r.item_id AND r.finder_id = :user_id
                    WHERE i.status = 'active'
                    AND i.user_id != :user_id";
            $params = [':user_id' => $user_id];
            if ($search) {
                $sql .= " AND (i.item_name LIKE :search OR i.description LIKE :search OR i.location LIKE :search)";
                $params[':search'] = "%$search%";
            }
            if ($type) { $sql .= " AND i.report_type = :type"; $params[':type'] = $type; }
            if ($category) { $sql .= " AND i.category = :category"; $params[':category'] = $category; }
            $sql .= " ORDER BY i.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    // ── READ MY REPORTS ───────────────────────────────────────────
    public function readMyReports($user_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt  = $this->conn->prepare($query);
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
                      LEFT JOIN USERS u ON i.user_id = u.user_id
                      WHERE i.item_id = :item_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':item_id' => $item_id]);
            return $stmt->fetch();
        } catch (Throwable $e) {
            return false;
        }
    }

    // ── READ ALL FOR ADMIN ────────────────────────────────────────
    public function readAllAdmin($status = '') {
        try {
            $sql = "SELECT i.*, u.full_name FROM " . $this->table . " i
                    LEFT JOIN USERS u ON i.user_id = u.user_id";
            $params = [];
            if ($status) { $sql .= " WHERE i.status = :status"; $params[':status'] = $status; }
            $sql .= " ORDER BY i.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    // ── ADMIN APPROVE REPORT ──────────────────────────────────────
    public function adminApprove($item_id, $admin_id) {
        try {
            $item = $this->readOne($item_id);
            if (!$item) return ["status" => false, "message" => "Item not found."];
            $query = "UPDATE " . $this->table . "
                      SET status = 'active', reviewed_by = :admin_id, reviewed_at = NOW()
                      WHERE item_id = :item_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':admin_id' => $admin_id, ':item_id' => $item_id]);
            Notification::send($this->conn, $item['user_id'], 'report_approved',
                "Your report '{$item['item_name']}' has been approved and is now live.",
                $item_id, 'item');
            return ["status" => true, "message" => "Report approved."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── ADMIN REJECT REPORT ───────────────────────────────────────
    public function adminReject($item_id, $admin_id, $reason) {
        try {
            $item = $this->readOne($item_id);
            if (!$item) return ["status" => false, "message" => "Item not found."];
            $query = "UPDATE " . $this->table . "
                      SET status = 'rejected', admin_note = :reason,
                          reviewed_by = :admin_id, reviewed_at = NOW()
                      WHERE item_id = :item_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':reason' => $reason, ':admin_id' => $admin_id, ':item_id' => $item_id]);
            Notification::send($this->conn, $item['user_id'], 'report_rejected',
                "Your report '{$item['item_name']}' was rejected. Reason: $reason",
                $item_id, 'item');
            return ["status" => true, "message" => "Report rejected."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── REQUEST DELETION ──────────────────────────────────────────
    public function requestDeletion($item_id, $user_id, $reason) {
        try {
            $check = $this->conn->prepare("SELECT deletion_id FROM DELETION_REQUESTS WHERE item_id = :item_id AND status = 'pending' LIMIT 1");
            $check->execute([':item_id' => $item_id]);
            if ($check->rowCount() > 0) return ["status" => false, "message" => "A deletion request is already pending."];
            $query = "INSERT INTO DELETION_REQUESTS (item_id, user_id, reason) VALUES (:item_id, :user_id, :reason)";
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':item_id' => $item_id, ':user_id' => $user_id, ':reason' => htmlspecialchars(strip_tags($reason))]);
            $admin_id = Notification::getAdminId($this->conn);
            if ($admin_id) {
                Notification::send($this->conn, $admin_id, 'new_deletion_request',
                    "A user has requested deletion of a report. Reason: $reason",
                    $item_id, 'item');
            }
            return ["status" => true, "message" => "Deletion request submitted. Awaiting admin approval."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── ADMIN APPROVE DELETION ────────────────────────────────────
    public function adminApproveDeletion($deletion_id, $admin_id) {
        try {
            $dr = $this->conn->prepare("SELECT dr.*, i.item_name, i.user_id AS owner_id FROM DELETION_REQUESTS dr JOIN ITEMS i ON dr.item_id = i.item_id WHERE dr.deletion_id = :id LIMIT 1");
            $dr->execute([':id' => $deletion_id]);
            $row = $dr->fetch();
            if (!$row) return ["status" => false, "message" => "Request not found."];
            $this->conn->prepare("UPDATE ITEMS SET status = 'deleted' WHERE item_id = :id")->execute([':id' => $row['item_id']]);
            $this->conn->prepare("UPDATE DELETION_REQUESTS SET status = 'approved', reviewed_by = :admin, reviewed_at = NOW() WHERE deletion_id = :id")->execute([':admin' => $admin_id, ':id' => $deletion_id]);
            Notification::send($this->conn, $row['owner_id'], 'deletion_approved',
                "Your deletion request for '{$row['item_name']}' has been approved.",
                $row['item_id'], 'item');
            return ["status" => true, "message" => "Deletion approved."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── ADMIN REJECT DELETION ─────────────────────────────────────
    public function adminRejectDeletion($deletion_id, $admin_id, $reason) {
        try {
            $dr = $this->conn->prepare("SELECT dr.*, i.item_name, i.user_id AS owner_id FROM DELETION_REQUESTS dr JOIN ITEMS i ON dr.item_id = i.item_id WHERE dr.deletion_id = :id LIMIT 1");
            $dr->execute([':id' => $deletion_id]);
            $row = $dr->fetch();
            if (!$row) return ["status" => false, "message" => "Request not found."];
            $this->conn->prepare("UPDATE DELETION_REQUESTS SET status = 'rejected', admin_note = :reason, reviewed_by = :admin, reviewed_at = NOW() WHERE deletion_id = :id")->execute([':reason' => $reason, ':admin' => $admin_id, ':id' => $deletion_id]);
            Notification::send($this->conn, $row['owner_id'], 'deletion_rejected',
                "Your deletion request for '{$row['item_name']}' was denied. Reason: $reason",
                $row['item_id'], 'item');
            return ["status" => true, "message" => "Deletion request rejected."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── UPLOAD PHOTO ──────────────────────────────────────────────
    public static function uploadPhoto($file) {
        $allowed    = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size   = 5 * 1024 * 1024;
        $upload_dir = dirname(__DIR__) . '/uploads/items/';
        if (!in_array($file['type'], $allowed)) return ["status" => false, "message" => "Only JPG, PNG, and WEBP allowed."];
        if ($file['size'] > $max_size) return ["status" => false, "message" => "Image must be 5MB or less."];
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('item_', true) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) return ["status" => false, "message" => "Failed to save image."];
        return ["status" => true, "path" => '/uploads/items/' . $filename];
    }

    // ── CHECK IF USER HAS SUBMITTED RETURN REQUEST ───────────────
    public function hasReturnRequest($item_id, $finder_id) {
        try {
            $check = $this->conn->prepare("SELECT return_id FROM RETURNS WHERE item_id = :item_id AND finder_id = :finder_id LIMIT 1");
            $check->execute([':item_id' => $item_id, ':finder_id' => $finder_id]);
            return $check->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function createReturnRequest($item_id, $finder_id, $found_location, $finder_description, $proof_photo) {
    try {
        // Check if there's already any return request for this item by this finder
        $check = $this->conn->prepare("SELECT return_id FROM RETURNS WHERE item_id = :item_id AND finder_id = :finder_id LIMIT 1");
        $check->execute([':item_id' => $item_id, ':finder_id' => $finder_id]);
        if ($check->rowCount() > 0) {
            return ["status" => false, "message" => "You have already submitted a return request for this item. You can only submit one return request per item."];
        }

        $sql = "INSERT INTO RETURNS (item_id, finder_id, found_location, finder_description, proof_photo, status)
                VALUES (:item_id, :finder_id, :found_location, :finder_desc, :proof_photo, 'admin_pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':item_id' => $item_id,
            ':finder_id' => $finder_id,
            ':found_location' => $found_location,
            ':finder_desc' => $finder_description,
            ':proof_photo' => $proof_photo
        ]);

        // Get the newly created return request ID
        $return_id = $this->conn->lastInsertId();

        // Get item details
        $item = $this->readOne($item_id);
        if (!$item) {
            error_log("Return request: Could not find item with ID $item_id");
            return ["status" => false, "message" => "Item not found."];
        }

        // Notify admin for approval
        $admin_id = Notification::getAdminId($this->conn);
        if ($admin_id) {
            Notification::send($this->conn, $admin_id, 'return_request_submitted',
                "A new return request has been submitted for lost item '{$item['item_name']}'. Please review and approve if valid.",
                $return_id, 'item');
        }

        // Notify item owner that someone wants to return their item
        Notification::send($this->conn, $item['user_id'], 'return_request_submitted',
            "Someone has submitted a return request for your lost item '{$item['item_name']}'. The admin will review this request.",
            $return_id, 'item');

        return ["status" => true, "message" => "Return request submitted. The admin will review your request."];
    } catch (Throwable $e) {
        error_log("createReturnRequest error: " . $e->getMessage());
        return ["status" => false, "message" => $e->getMessage()];
    }
}

    public function confirmReturn($return_id, $owner_id) {
    try {
        // Fetch return request
        $stmt = $this->conn->prepare("SELECT r.*, i.user_id AS owner_id, i.item_id, i.item_name, r.finder_id
                                      FROM RETURNS r
                                      JOIN ITEMS i ON r.item_id = i.item_id
                                      WHERE r.return_id = :return_id AND r.status = 'pending' LIMIT 1");
        $stmt->execute([':return_id' => $return_id]);
        $return = $stmt->fetch();

        if (!$return) {
            return ["status" => false, "message" => "Return request not found or already processed."];
        }
        if ($return['owner_id'] != $owner_id) {
            return ["status" => false, "message" => "You are not the owner of this item."];
        }

        // Update returns table
        $update = $this->conn->prepare("UPDATE RETURNS SET status = 'confirmed', owner_confirmed_at = NOW() WHERE return_id = :id");
        $update->execute([':id' => $return_id]);

        // Update item status to 'returned'
        $itemUpdate = $this->conn->prepare("UPDATE ITEMS SET status = 'returned' WHERE item_id = :item_id");
        $itemUpdate->execute([':item_id' => $return['item_id']]);

        // Notify finder
        Notification::send($this->conn, $return['finder_id'], 'return_confirmed',
            "The owner confirmed the return of '{$return['item_name']}'. The item is now marked as returned. Thank you for helping!",
            $return['return_id'], 'item');

        // Also notify owner (optional)
        Notification::send($this->conn, $owner_id, 'return_confirmed',
            "You confirmed the return of '{$return['item_name']}'. The item has been marked as returned.",
            $return['return_id'], 'item');

        return ["status" => true, "message" => "Item marked as returned. The finder has been notified."];
    } catch (Throwable $e) {
        return ["status" => false, "message" => $e->getMessage()];
    }
}

public function rejectReturn($return_id, $owner_id, $reason) {
    try {
        $stmt = $this->conn->prepare("SELECT r.*, i.user_id AS owner_id, i.item_name, r.finder_id
                                      FROM RETURNS r
                                      JOIN ITEMS i ON r.item_id = i.item_id
                                      WHERE r.return_id = :return_id AND r.status = 'pending' LIMIT 1");
        $stmt->execute([':return_id' => $return_id]);
        $return = $stmt->fetch();

        if (!$return) {
            return ["status" => false, "message" => "Return request not found."];
        }
        if ($return['owner_id'] != $owner_id) {
            return ["status" => false, "message" => "Unauthorized."];
        }

        $update = $this->conn->prepare("UPDATE RETURNS SET status = 'rejected', admin_note = :reason WHERE return_id = :id");
        $update->execute([':reason' => $reason, ':id' => $return_id]);

        Notification::send($this->conn, $return['finder_id'], 'return_rejected',
            "The owner rejected your return request for '{$return['item_name']}'. Reason: $reason",
            $return['return_id'], 'item');

        return ["status" => true, "message" => "Return request rejected. Finder notified."];
    } catch (Throwable $e) {
        return ["status" => false, "message" => $e->getMessage()];
    }
}

    // ── ADMIN APPROVE RETURN REQUEST ───────────────────────────────────
    public function adminApproveReturn($return_id, $admin_id, $coordinates, $deadline) {
        try {
            $stmt = $this->conn->prepare("SELECT r.*, i.item_name, i.user_id AS owner_id, r.finder_id
                                          FROM RETURNS r
                                          JOIN ITEMS i ON r.item_id = i.item_id
                                          WHERE r.return_id = :return_id AND r.status = 'admin_pending' LIMIT 1");
            $stmt->execute([':return_id' => $return_id]);
            $return = $stmt->fetch();

            if (!$return) {
                return ["status" => false, "message" => "Return request not found or already processed."];
            }

            // Update return request
            $update = $this->conn->prepare("UPDATE RETURNS SET
                status = 'admin_approved',
                admin_approved = 1,
                admin_reviewed_at = NOW(),
                admin_reviewed_by = :admin_id,
                coordinates = :coordinates,
                deadline = :deadline
                WHERE return_id = :return_id");
            $update->execute([
                ':admin_id' => $admin_id,
                ':coordinates' => $coordinates,
                ':deadline' => $deadline,
                ':return_id' => $return_id
            ]);

            // Notify finder with coordinates and deadline
            Notification::send($this->conn, $return['finder_id'], 'return_request_approved',
                "Your return request for '{$return['item_name']}' has been approved. Please return the item to: {$coordinates} by {$deadline}.",
                $return_id, 'item');

            // Notify owner that return request was approved
            Notification::send($this->conn, $return['owner_id'], 'return_request_approved',
                "The return request for your item '{$return['item_name']}' has been approved by admin. The finder will contact you soon.",
                $return_id, 'item');

            return ["status" => true, "message" => "Return request approved. Finder and owner notified."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── ADMIN REJECT RETURN REQUEST ────────────────────────────────────
    public function adminRejectReturn($return_id, $admin_id, $reason) {
        try {
            $stmt = $this->conn->prepare("SELECT r.*, i.item_name, i.user_id AS owner_id, r.finder_id
                                          FROM RETURNS r
                                          JOIN ITEMS i ON r.item_id = i.item_id
                                          WHERE r.return_id = :return_id AND r.status = 'admin_pending' LIMIT 1");
            $stmt->execute([':return_id' => $return_id]);
            $return = $stmt->fetch();

            if (!$return) {
                return ["status" => false, "message" => "Return request not found."];
            }

            $update = $this->conn->prepare("UPDATE RETURNS SET
                status = 'admin_rejected',
                admin_reviewed_at = NOW(),
                admin_reviewed_by = :admin_id,
                admin_note = :reason
                WHERE return_id = :return_id");
            $update->execute([':admin_id' => $admin_id, ':reason' => $reason, ':return_id' => $return_id]);

            // Notify finder
            Notification::send($this->conn, $return['finder_id'], 'return_request_rejected',
                "Your return request for '{$return['item_name']}' was rejected. Reason: {$reason}",
                $return_id, 'item');

            // Notify owner
            Notification::send($this->conn, $return['owner_id'], 'return_request_rejected',
                "The return request for your item '{$return['item_name']}' was rejected by admin.",
                $return_id, 'item');

            return ["status" => true, "message" => "Return request rejected. Participants notified."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── FINDER MARK AS COMPLETED ──────────────────────────────────────
    public function completeReturn($return_id, $finder_id) {
        try {
            $stmt = $this->conn->prepare("SELECT r.*, i.item_name, i.user_id AS owner_id, r.finder_id
                                          FROM RETURNS r
                                          JOIN ITEMS i ON r.item_id = i.item_id
                                          WHERE r.return_id = :return_id AND r.finder_id = :finder_id
                                          AND r.status = 'admin_approved' LIMIT 1");
            $stmt->execute([':return_id' => $return_id, ':finder_id' => $finder_id]);
            $return = $stmt->fetch();

            if (!$return) {
                return ["status" => false, "message" => "Return request not found or not authorized."];
            }

            // Check if deadline has passed
            if ($return['deadline'] && strtotime($return['deadline']) < time()) {
                return ["status" => false, "message" => "Deadline has passed. Please submit a failure reason instead."];
            }

            // Update return status and item status
            $this->conn->prepare("UPDATE RETURNS SET status = 'completed' WHERE return_id = :id")
                      ->execute([':id' => $return_id]);
            $this->conn->prepare("UPDATE ITEMS SET status = 'returned' WHERE item_id = :item_id")
                      ->execute([':item_id' => $return['item_id']]);

            // Notify finder
            Notification::send($this->conn, $return['finder_id'], 'return_completed',
                "Thank you for returning '{$return['item_name']}'. The item has been marked as returned.",
                $return_id, 'item');

            // Notify owner
            Notification::send($this->conn, $return['owner_id'], 'return_completed',
                "Your item '{$return['item_name']}' has been successfully returned. Thank you to the finder!",
                $return_id, 'item');

            return ["status" => true, "message" => "Return completed successfully."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── FINDER SUBMIT FAILURE REASON ──────────────────────────────────
    public function submitFailureReason($return_id, $finder_id, $failure_reason) {
        try {
            $stmt = $this->conn->prepare("SELECT r.*, i.item_name, r.finder_id
                                          FROM RETURNS r
                                          JOIN ITEMS i ON r.item_id = i.item_id
                                          WHERE r.return_id = :return_id AND r.finder_id = :finder_id
                                          AND r.status = 'admin_approved' LIMIT 1");
            $stmt->execute([':return_id' => $return_id, ':finder_id' => $finder_id]);
            $return = $stmt->fetch();

            if (!$return) {
                return ["status" => false, "message" => "Return request not found or not authorized."];
            }

            // Check if deadline has passed
            if (!$return['deadline'] || strtotime($return['deadline']) >= time()) {
                return ["status" => false, "message" => "Deadline has not passed yet. You can still complete the return."];
            }

            $update = $this->conn->prepare("UPDATE RETURNS SET
                status = 'failed',
                failure_reason = :reason
                WHERE return_id = :return_id");
            $update->execute([':reason' => $failure_reason, ':return_id' => $return_id]);

            // Notify admin for review
            $admin_id = Notification::getAdminId($this->conn);
            if ($admin_id) {
                Notification::send($this->conn, $admin_id, 'return_failed',
                    "Return failed for '{$return['item_name']}'. Finder submitted failure reason: {$failure_reason}. Please review.",
                    $return_id, 'item');
            }

            return ["status" => true, "message" => "Failure reason submitted. Admin will review your case."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── ADMIN ALLOW RE-SUBMISSION ─────────────────────────────────────
    public function allowResubmission($return_id, $admin_id) {
        try {
            $stmt = $this->conn->prepare("SELECT r.*, i.item_name, r.finder_id
                                          FROM RETURNS r
                                          JOIN ITEMS i ON r.item_id = i.item_id
                                          WHERE r.return_id = :return_id AND r.status = 'failed' LIMIT 1");
            $stmt->execute([':return_id' => $return_id]);
            $return = $stmt->fetch();

            if (!$return) {
                return ["status" => false, "message" => "Failed return request not found."];
            }

            // Reset the return request to allow re-submission
            $update = $this->conn->prepare("UPDATE RETURNS SET
                status = 'admin_pending',
                admin_approved = 0,
                admin_reviewed_at = NULL,
                admin_reviewed_by = NULL,
                coordinates = NULL,
                deadline = NULL,
                failure_reason = NULL
                WHERE return_id = :return_id");
            $update->execute([':return_id' => $return_id]);

            // Notify finder
            Notification::send($this->conn, $return['finder_id'], 'return_request_submitted',
                "Admin has allowed you to re-submit your return request for '{$return['item_name']}'. Please update your information.",
                $return_id, 'item');

            return ["status" => true, "message" => "Finder can now re-submit the return request."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── GET RETURN REQUEST DETAILS ────────────────────────────────────
    public function getReturnDetails($return_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT r.*, i.item_name, i.description AS item_description,
                                                 i.photo AS item_photo, i.category, i.location AS item_location,
                                                 i.date_occured, u.full_name AS finder_name, u.contact AS finder_contact,
                                                 u.email AS finder_email, owner.full_name AS owner_name,
                                                 owner.contact AS owner_contact, owner.email AS owner_email
                                          FROM RETURNS r
                                          JOIN ITEMS i ON r.item_id = i.item_id
                                          LEFT JOIN USERS u ON r.finder_id = u.user_id
                                          LEFT JOIN USERS owner ON i.user_id = owner.user_id
                                          WHERE r.return_id = :return_id
                                          AND (r.finder_id = :user_id OR i.user_id = :user_id OR :user_id IN (SELECT user_id FROM USERS WHERE role = 'admin'))
                                          LIMIT 1");
            $stmt->execute([':return_id' => $return_id, ':user_id' => $user_id]);
            return $stmt->fetch();
        } catch (Throwable $e) {
            return false;
        }
    }

    // ── GET MY RETURNED ITEMS ─────────────────────────────────────
    public function getMyReturnedItems($user_id) {
        try {
            $sql = "SELECT i.*, r.finder_id, r.found_location, r.finder_description,
                           u.full_name AS finder_name, u.contact AS finder_contact,
                           r.created_at AS return_completed_at
                    FROM ITEMS i
                    INNER JOIN RETURNS r ON i.item_id = r.item_id AND r.status = 'completed'
                    LEFT JOIN USERS u ON r.finder_id = u.user_id
                    WHERE i.user_id = :user_id
                      AND i.report_type = 'lost'
                      AND i.status = 'returned'
                    ORDER BY r.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log("getMyReturnedItems error: " . $e->getMessage());
            return [];
        }
    }
}

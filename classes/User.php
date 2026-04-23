<?php

class User {

    private $conn;
    private $table = "USERS";

    public $user_id;
    public $full_name;
    public $email;
    public $password;
    public $role;
    public $id_number;
    public $contact;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ── REGISTER ─────────────────────────────────────────────────
    public function register() {
        try {
            if ($this->emailExists()) {
                return ["status" => false, "message" => "Email is already registered."];
            }

            $query = "INSERT INTO " . $this->table . "
                        (full_name, email, password, role, id_number, contact)
                      VALUES
                        (:full_name, :email, :password, :role, :id_number, :contact)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':full_name' => htmlspecialchars(strip_tags($this->full_name)),
                ':email'     => htmlspecialchars(strip_tags($this->email)),
                ':password'  => password_hash($this->password, PASSWORD_BCRYPT),
                ':role'      => $this->role ?? 'student',
                ':id_number' => htmlspecialchars(strip_tags($this->id_number)),
                ':contact'   => htmlspecialchars(strip_tags($this->contact)),
            ]);

            return ["status" => true, "message" => "Account created successfully!"];

        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── LOGIN ─────────────────────────────────────────────────────
    public function login() {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email = :email AND is_active = 1 LIMIT 1";
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':email' => $this->email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ["status" => false, "message" => "No account found with that email."];
            }

            if (!password_verify($this->password, $user['password'])) {
                return ["status" => false, "message" => "Incorrect password."];
            }

            return ["status" => true, "user" => $user];

        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── READ ALL (admin) ──────────────────────────────────────────
    public function read() {
        try {
            $query = "SELECT user_id, full_name, email, role, id_number, contact, is_active, created_at
                      FROM " . $this->table . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // ── READ ONE ──────────────────────────────────────────────────
    public function readOne() {
        try {
            $query = "SELECT user_id, full_name, email, role, id_number, contact FROM " . $this->table . "
                      WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $id   = decryptId($this->user_id);
            $stmt->bindParam(':user_id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // ── UPDATE PROFILE ────────────────────────────────────────────
    public function update() {
        try {
            $query = "UPDATE " . $this->table . "
                      SET full_name = :full_name, contact = :contact, updated_at = NOW()
                      WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':full_name' => htmlspecialchars(strip_tags($this->full_name)),
                ':contact'   => htmlspecialchars(strip_tags($this->contact)),
                ':user_id'   => $this->user_id,
            ]);
            return ["status" => $stmt->rowCount() > 0, "message" => "Profile updated successfully."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── CHANGE PASSWORD ───────────────────────────────────────────
    public function changePassword($current_password, $new_password) {
        try {
            $query = "SELECT password FROM " . $this->table . " WHERE user_id = :user_id LIMIT 1";
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $this->user_id]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($current_password, $row['password'])) {
                return ["status" => false, "message" => "Current password is incorrect."];
            }

            $query2 = "UPDATE " . $this->table . " SET password = :password, updated_at = NOW() WHERE user_id = :user_id";
            $stmt2  = $this->conn->prepare($query2);
            $stmt2->execute([
                ':password' => password_hash($new_password, PASSWORD_BCRYPT),
                ':user_id'  => $this->user_id,
            ]);

            return ["status" => true, "message" => "Password changed successfully."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── TOGGLE ACTIVE (admin) ─────────────────────────────────────
    public function toggleActive($user_id, $status) {
        try {
            $query = "UPDATE " . $this->table . " SET is_active = :status WHERE user_id = :user_id";
            $stmt  = $this->conn->prepare($query);
            $stmt->execute([':status' => $status, ':user_id' => $user_id]);
            return ["status" => true, "message" => $status ? "User activated." : "User deactivated."];
        } catch (Throwable $e) {
            return ["status" => false, "message" => $e->getMessage()];
        }
    }

    // ── HELPERS ───────────────────────────────────────────────────
    public function emailExists() {
        $query = "SELECT user_id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute([':email' => $this->email]);
        return $stmt->rowCount() > 0;
    }

    public function getDashboardStats($user_id, $role) {
        try {
            $stats = [];
            if ($role === 'admin') {
                $queries = [
                    'total_users'       => "SELECT COUNT(*) FROM USERS WHERE role != 'admin'",
                    'total_reports'     => "SELECT COUNT(*) FROM ITEMS",
                    'pending_review'    => "SELECT COUNT(*) FROM ITEMS WHERE status = 'pending_review'",
                    'pending_claims'    => "SELECT COUNT(*) FROM CLAIMS WHERE status = 'pending'",
                    'total_returned'    => "SELECT COUNT(*) FROM ITEMS WHERE status = 'returned'",
                    'pending_deletions' => "SELECT COUNT(*) FROM DELETION_REQUESTS WHERE status = 'pending'",
                ];
            } else {
                $queries = [
                    'my_reports'  => "SELECT COUNT(*) FROM ITEMS WHERE user_id = " . (int)$user_id,
                    'my_claims'   => "SELECT COUNT(*) FROM CLAIMS WHERE claimant_id = " . (int)$user_id,
                    'lost_items'  => "SELECT COUNT(*) FROM ITEMS WHERE report_type = 'lost' AND status = 'active'",
                    'found_items' => "SELECT COUNT(*) FROM ITEMS WHERE report_type = 'found' AND status = 'active'",
                ];
            }
            foreach ($queries as $key => $sql) {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $stats[$key] = $stmt->fetchColumn();
            }
            return $stats;
        } catch (Throwable $e) {
            return [];
        }
    }
}

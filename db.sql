CREATE DATABASE IF NOT EXISTS tracetrack
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tracetrack;

CREATE TABLE USERS
(
  user_id    INT UNSIGNED AUTO_INCREMENT       NOT NULL,
  full_name  VARCHAR(150)                      NOT NULL,
  email      VARCHAR(150)                      NOT NULL,
  password   VARCHAR(255)                      NOT NULL,
  role       ENUM('student','faculty','admin') NOT NULL DEFAULT 'student',
  id_number  VARCHAR(50)                       NOT NULL,
  contact    VARCHAR(20)                       NOT NULL,
  is_active  TINYINT(1)                        NOT NULL DEFAULT 1,
  first_login_seen TINYINT(1)                  NOT NULL DEFAULT 0,
  created_at TIMESTAMP                         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP                         NULL,
  PRIMARY KEY (user_id)
);

CREATE TABLE ITEMS
(
  item_id      INT UNSIGNED AUTO_INCREMENT                                               NOT NULL,
  report_type  ENUM('lost','found')                                                      NOT NULL,
  item_name    VARCHAR(150)                                                              NOT NULL,
  category     ENUM('electronics','clothing','documents','accessories','keys','others')  NOT NULL,
  description  TEXT                                                                      NOT NULL,
  location     VARCHAR(255)                                                              NOT NULL,
  date_occured DATE                                                                      NOT NULL,
  photo        VARCHAR(255)                                                              NULL,
  status       ENUM('pending_review','active','claimed','returned','rejected','deleted') NOT NULL DEFAULT 'pending_review',
  admin_note   TEXT                                                                      NULL,
  reviewed_by  INT UNSIGNED                                                              NULL,
  reviewed_at  TIMESTAMP                                                                 NULL,
  created_at   TIMESTAMP                                                                 NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP                                                                 NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_id      INT UNSIGNED                                                              NOT NULL,
  PRIMARY KEY (item_id)
);

CREATE TABLE CLAIMS
(
  claim_id        INT UNSIGNED AUTO_INCREMENT                      NOT NULL,
  description     TEXT                                             NOT NULL,
  proof_photo     VARCHAR(255)                                     NULL,
  additional_info TEXT                                             NULL,
  status          ENUM('pending','approved','rejected','returned') NOT NULL DEFAULT 'pending',
  admin_note      TEXT                                             NULL,
  reviewed_by     INT UNSIGNED                                     NULL,
  reviewed_at     TIMESTAMP                                        NULL,
  created_at      TIMESTAMP                                        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_id         INT UNSIGNED                                     NOT NULL COMMENT 'claimant_id',
  item_id         INT UNSIGNED                                     NOT NULL,
  PRIMARY KEY (claim_id)
);

CREATE TABLE RETURNS (
    return_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    finder_id INT UNSIGNED NOT NULL,
    found_location VARCHAR(255) NOT NULL,
    finder_description TEXT,
    proof_photo VARCHAR(255),
    status ENUM('admin_pending', 'admin_approved', 'admin_rejected', 'owner_notified', 'completed', 'failed', 'cancelled') DEFAULT 'admin_pending',
    admin_approved TINYINT(1) DEFAULT 0,
    admin_reviewed_at TIMESTAMP NULL,
    admin_reviewed_by INT UNSIGNED NULL,
    coordinates VARCHAR(255) NULL,
    deadline DATETIME NULL,
    failure_reason TEXT NULL,
    admin_note TEXT NULL,
    owner_confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (item_id) REFERENCES ITEMS(item_id) ON DELETE CASCADE,
    FOREIGN KEY (finder_id) REFERENCES USERS(user_id) ON DELETE CASCADE
);
    FOREIGN KEY (finder_id) REFERENCES USERS(user_id) ON DELETE CASCADE
);

CREATE TABLE DELETION_REQUESTS
(
  deletion_id INT UNSIGNED AUTO_INCREMENT           NOT NULL,
  reason      TEXT                                  NOT NULL,
  status      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_note  TEXT                                  NULL,
  reviewed_by INT UNSIGNED                          NULL,
  reviewed_at TIMESTAMP                             NULL,
  created_at  TIMESTAMP                             NOT NULL DEFAULT CURRENT_TIMESTAMP,
  item_id     INT UNSIGNED                          NOT NULL,
  user_id     INT UNSIGNED                          NOT NULL COMMENT 'requested_by fk',
  PRIMARY KEY (deletion_id)
);

CREATE TABLE NOTIFICATIONS
(
  notification_id INT UNSIGNED AUTO_INCREMENT                                                                                                                                                                                      NOT NULL,
  type            ENUM('claim_submitted','claim_approved','claim_rejected','report_approved','report_rejected','deletion_approved','deletion_rejected','item_returned','new_report','new_claim','new_deletion_request','new_user','return_request_submitted','return_request_approved','return_request_rejected','return_deadline_set','return_failed','return_completed') NOT NULL,
  is_read         TINYINT(1)                                                                                                                                                                                                       NOT NULL DEFAULT 0,
  message         TEXT                                                                                                                                                                                                             NOT NULL,
  reference_id    INT UNSIGNED                                                                                                                                                                                                     NULL,
  reference_type  ENUM('item','claim','deletion_request')                                                                                                                                                                          NULL,
  created_at      TIMESTAMP                                                                                                                                                                                                        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_id         INT UNSIGNED                                                                                                                                                                                                     NOT NULL,
  PRIMARY KEY (notification_id)
);

-- Unique & Foreign Keys
ALTER TABLE USERS
  ADD CONSTRAINT UQ_email UNIQUE (email);

ALTER TABLE ITEMS
  ADD CONSTRAINT FK_USERS_TO_ITEMS
    FOREIGN KEY (user_id) REFERENCES USERS (user_id);

ALTER TABLE CLAIMS
  ADD CONSTRAINT FK_USERS_TO_CLAIMS
    FOREIGN KEY (user_id) REFERENCES USERS (user_id);

ALTER TABLE CLAIMS
  ADD CONSTRAINT FK_ITEMS_TO_CLAIMS
    FOREIGN KEY (item_id) REFERENCES ITEMS (item_id);

ALTER TABLE DELETION_REQUESTS
  ADD CONSTRAINT FK_ITEMS_TO_DELETION_REQUESTS
    FOREIGN KEY (item_id) REFERENCES ITEMS (item_id);

ALTER TABLE DELETION_REQUESTS
  ADD CONSTRAINT FK_USERS_TO_DELETION_REQUESTS
    FOREIGN KEY (user_id) REFERENCES USERS (user_id);

ALTER TABLE NOTIFICATIONS
  ADD CONSTRAINT FK_USERS_TO_NOTIFICATIONS
    FOREIGN KEY (user_id) REFERENCES USERS (user_id);
    
ALTER TABLE CLAIMS MODIFY status 
ENUM('pending','approved','rejected','returned','cancel_requested') 
NOT NULL DEFAULT 'pending';

-- Default admin account (password: Admin@1234)
INSERT INTO USERS (full_name, email, password, role, id_number, contact)
VALUES (
  'System Administrator',
  'admin@tracetrack.edu.ph',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin',
  'ADMIN-001',
  '09000000000'
);


UPDATE USERS 
SET password = '$2y$12$Jv4Mj2n.XSf4PRb1YUzd5uLwfxD5b0D2I.jR1KKvWk5DC/FG.bGQS'
WHERE email = 'admin@tracetrack.edu.ph';

ALTER TABLE NOTIFICATIONS 
MODIFY type ENUM('claim_submitted','claim_approved','claim_rejected','report_approved','report_rejected',
                 'deletion_approved','deletion_rejected','item_returned','new_report','new_claim',
                 'new_deletion_request','new_user','return_request','return_confirmed','return_rejected') 
NOT NULL;

-- Add first_login_seen column if it doesn't exist (for existing databases)
ALTER TABLE USERS ADD COLUMN first_login_seen TINYINT(1) NOT NULL DEFAULT 0;



-- 1. USERS table structure confirmed (no super_admin role needed)

-- 2. Create Audit Log Table
CREATE TABLE IF NOT EXISTS AUDIT_LOG (
    log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USERS(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create System Config Table
CREATE TABLE IF NOT EXISTS SYSTEM_CONFIG (
    config_key VARCHAR(100) PRIMARY KEY,
    config_value LONGTEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Insert Default Configuration Values
INSERT IGNORE INTO SYSTEM_CONFIG (config_key, config_value) VALUES
('site_name', 'TraceTrack'),
('contact_email', 'admin@tracetrack.edu.ph'),
('max_upload_size', '5242880'),
('item_expiry_days', '30'),
('maintenance_mode', '0'),
('global_announcement', '');

-- All admin features now consolidated under the 'admin' role
-- No separate super admin account needed

-- 6. Create Maintenance Log (Optional)
CREATE TABLE IF NOT EXISTS MAINTENANCE_LOG (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(100),
    performed_by INT UNSIGNED,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES USERS(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Setup Complete!
-- ============================================================
-- 
-- IMPORTANT STEPS:
-- 1. Log in with: super@tracetrack.edu.ph / password
-- 2. Change the super admin password immediately!
-- 3. Go to Super Admin > Reset Passwords to change other users
-- 4. The super admin menu will appear in the sidebar for super admin role
-- 5. Regular admins will only see the Admin menu
--
-- ============================================================

SELECT * FROM USERS;


-- =====================================================
-- TRACETRACK - Campus Lost & Found System Database
-- =====================================================

-- Create database with proper character encoding
CREATE DATABASE IF NOT EXISTS tracetrack
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tracetrack;

-- =====================================================
-- TABLE: USERS (System users: students, faculty, admin)
-- =====================================================
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
  created_at TIMESTAMP                         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP                         NULL,
  PRIMARY KEY (user_id)
);

-- =====================================================
-- TABLE: ITEMS (Lost/Found item reports)
-- =====================================================
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

-- =====================================================
-- TABLE: CLAIMS (User claims on found items)
-- =====================================================
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

-- =====================================================
-- TABLE: DELETION_REQUESTS (User requests to delete items)
-- =====================================================
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

-- =====================================================
-- TABLE: NOTIFICATIONS (System notifications for users)
-- =====================================================
CREATE TABLE NOTIFICATIONS
(
  notification_id INT UNSIGNED AUTO_INCREMENT                                                                                                                                                                                      NOT NULL,
  type            ENUM('claim_submitted','claim_approved','claim_rejected','report_approved','report_rejected','deletion_approved','deletion_rejected','item_returned','new_report','new_claim','new_deletion_request','new_user') NOT NULL,
  is_read         TINYINT(1)                                                                                                                                                                                                       NOT NULL DEFAULT 0,
  message         TEXT                                                                                                                                                                                                             NOT NULL,
  reference_id    INT UNSIGNED                                                                                                                                                                                                     NULL,
  reference_type  ENUM('item','claim','deletion_request')                                                                                                                                                                          NULL,
  created_at      TIMESTAMP                                                                                                                                                                                                        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_id         INT UNSIGNED                                                                                                                                                                                                     NOT NULL,
  PRIMARY KEY (notification_id)
);

-- =====================================================
-- CONSTRAINTS: Unique & Foreign Keys
-- =====================================================

-- Email must be unique across all users
ALTER TABLE USERS
  ADD CONSTRAINT UQ_email UNIQUE (email);

-- Items belong to users (reporters)
ALTER TABLE ITEMS
  ADD CONSTRAINT FK_USERS_TO_ITEMS
    FOREIGN KEY (user_id) REFERENCES USERS (user_id);

-- Claims belong to users (claimants)
ALTER TABLE CLAIMS
  ADD CONSTRAINT FK_USERS_TO_CLAIMS
    FOREIGN KEY (user_id) REFERENCES USERS (user_id);

-- Claims reference specific items
ALTER TABLE CLAIMS
  ADD CONSTRAINT FK_ITEMS_TO_CLAIMS
    FOREIGN KEY (item_id) REFERENCES ITEMS (item_id);

-- Deletion requests reference items
ALTER TABLE DELETION_REQUESTS
  ADD CONSTRAINT FK_ITEMS_TO_DELETION_REQUESTS
    FOREIGN KEY (item_id) REFERENCES ITEMS (item_id);

-- Deletion requests belong to users who requested them
ALTER TABLE DELETION_REQUESTS
  ADD CONSTRAINT FK_USERS_TO_DELETION_REQUESTS
    FOREIGN KEY (user_id) REFERENCES USERS (user_id);

-- Notifications belong to users
ALTER TABLE NOTIFICATIONS
  ADD CONSTRAINT FK_USERS_TO_NOTIFICATIONS
    FOREIGN KEY (user_id) REFERENCES USERS (user_id);

-- =====================================================
-- ALTERATIONS: Add cancel_requested status to claims
-- =====================================================
ALTER TABLE CLAIMS MODIFY status 
ENUM('pending','approved','rejected','returned','cancel_requested') 
NOT NULL DEFAULT 'pending';

-- =====================================================
-- SEED DATA: Default admin account
-- Password: Admin@1234 (hashed with BCRYPT)
-- =====================================================
INSERT INTO USERS (full_name, email, password, role, id_number, contact)
VALUES (
  'System Administrator',
  'admin@tracetrack.edu.ph',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- 'Admin@1234'
  'admin',
  'ADMIN-001',
  '09000000000'
);

-- =====================================================
-- VERIFICATION: List all users
-- =====================================================
SELECT * FROM USERS;

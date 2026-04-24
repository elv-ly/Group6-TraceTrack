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
  type            ENUM('claim_submitted','claim_approved','claim_rejected','report_approved','report_rejected','deletion_approved','deletion_rejected','item_returned','new_report','new_claim','new_deletion_request','new_user') NOT NULL,
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

SELECT * FROM USERS;
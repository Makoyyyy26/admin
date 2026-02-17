-- =====================================================================
-- MICROFINANCIAL MANAGEMENT SYSTEM I: ADMINISTRATIVE
-- Real-Time QR Integration Powered by OCR for Smart Operations
-- and Automated Document Processing
-- =====================================================================
-- Database: administrative
-- Server: localhost:3306 (XAMPP MySQL)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `administrative`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `administrative`;

-- =====================================================================
-- CORE / SHARED TABLES
-- =====================================================================

-- System users (admin accounts)
CREATE TABLE IF NOT EXISTS `users` (
  `user_id`       INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id`   VARCHAR(20)  NOT NULL UNIQUE,
  `first_name`    VARCHAR(100) NOT NULL,
  `last_name`     VARCHAR(100) NOT NULL,
  `email`         VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('super_admin','admin','manager','staff') NOT NULL DEFAULT 'staff',
  `department`    VARCHAR(100) DEFAULT NULL,
  `avatar_url`    VARCHAR(500) DEFAULT NULL,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `last_login`    DATETIME     DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Audit trail for all modules
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id`      BIGINT AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT          DEFAULT NULL,
  `module`      ENUM('facilities','documents','legal','visitors','system') NOT NULL,
  `action`      VARCHAR(50)  NOT NULL,
  `table_name`  VARCHAR(100) DEFAULT NULL,
  `record_id`   INT          DEFAULT NULL,
  `old_values`  JSON         DEFAULT NULL,
  `new_values`  JSON         DEFAULT NULL,
  `ip_address`  VARCHAR(45)  DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- QR codes master table (shared across modules)
CREATE TABLE IF NOT EXISTS `qr_codes` (
  `qr_id`         INT AUTO_INCREMENT PRIMARY KEY,
  `qr_uuid`       CHAR(36)     NOT NULL UNIQUE,
  `module`         ENUM('facilities','documents','legal','visitors') NOT NULL,
  `reference_table` VARCHAR(100) NOT NULL,
  `reference_id`   INT          NOT NULL,
  `qr_data`        TEXT         NOT NULL,
  `qr_image_path`  VARCHAR(500) DEFAULT NULL,
  `scan_count`     INT          NOT NULL DEFAULT 0,
  `last_scanned`   DATETIME     DEFAULT NULL,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_module_ref` (`module`, `reference_table`, `reference_id`)
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 1: FACILITIES RESERVATION
-- =====================================================================

-- Facilities / Venues
CREATE TABLE IF NOT EXISTS `facilities` (
  `facility_id`   INT AUTO_INCREMENT PRIMARY KEY,
  `facility_code` VARCHAR(20)  NOT NULL UNIQUE,
  `name`          VARCHAR(200) NOT NULL,
  `type`          ENUM('conference_room','meeting_room','training_hall','auditorium','parking','equipment','other') NOT NULL,
  `location`      VARCHAR(200) DEFAULT NULL,
  `capacity`      INT          DEFAULT NULL,
  `description`   TEXT         DEFAULT NULL,
  `amenities`     JSON         DEFAULT NULL,
  `hourly_rate`   DECIMAL(10,2) DEFAULT 0.00,
  `image_url`     VARCHAR(500) DEFAULT NULL,
  `status`        ENUM('available','occupied','maintenance','retired') NOT NULL DEFAULT 'available',
  `created_by`    INT          DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Facility reservations
CREATE TABLE IF NOT EXISTS `facility_reservations` (
  `reservation_id`   INT AUTO_INCREMENT PRIMARY KEY,
  `reservation_code` VARCHAR(20) NOT NULL UNIQUE,
  `facility_id`      INT          NOT NULL,
  `reserved_by`      INT          NOT NULL,
  `department`       VARCHAR(100) DEFAULT NULL,
  `purpose`          VARCHAR(500) NOT NULL,
  `event_title`      VARCHAR(200) DEFAULT NULL,
  `reservation_type` ENUM('regular','vip','emergency') NOT NULL DEFAULT 'regular',
  `priority`         ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `start_datetime`   DATETIME     NOT NULL,
  `end_datetime`     DATETIME     NOT NULL,
  `attendees_count`  INT          DEFAULT NULL,
  `budget`           DECIMAL(12,2) DEFAULT 0.00,
  `equipment_needed` JSON         DEFAULT NULL COMMENT 'Array of equipment names/ids requested',
  `special_requests` TEXT         DEFAULT NULL,
  `is_validated`     TINYINT(1)   NOT NULL DEFAULT 0,
  `validated_by`     INT          DEFAULT NULL,
  `validated_at`     DATETIME     DEFAULT NULL,
  `status`           ENUM('pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `approved_by`      INT          DEFAULT NULL,
  `approved_at`      DATETIME     DEFAULT NULL,
  `qr_code_id`       INT          DEFAULT NULL,
  `remarks`          TEXT         DEFAULT NULL,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`facility_id`) ON DELETE CASCADE,
  FOREIGN KEY (`reserved_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`validated_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes`(`qr_id`) ON DELETE SET NULL,
  INDEX `idx_date_range` (`start_datetime`, `end_datetime`),
  INDEX `idx_status` (`status`),
  INDEX `idx_type` (`reservation_type`),
  INDEX `idx_priority` (`priority`)
) ENGINE=InnoDB;

-- Equipment inventory for facilities
CREATE TABLE IF NOT EXISTS `facility_equipment` (
  `equipment_id`   INT AUTO_INCREMENT PRIMARY KEY,
  `equipment_code` VARCHAR(20)  NOT NULL UNIQUE,
  `name`           VARCHAR(200) NOT NULL,
  `category`       VARCHAR(100) DEFAULT NULL,
  `serial_number`  VARCHAR(100) DEFAULT NULL,
  `facility_id`    INT          DEFAULT NULL,
  `condition_status` ENUM('excellent','good','fair','needs_repair','retired') NOT NULL DEFAULT 'good',
  `quantity`       INT          NOT NULL DEFAULT 1,
  `is_available`   TINYINT(1)   NOT NULL DEFAULT 1,
  `last_maintained` DATE        DEFAULT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`facility_id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Equipment assigned per reservation (junction table)
CREATE TABLE IF NOT EXISTS `reservation_equipment` (
  `id`              INT AUTO_INCREMENT PRIMARY KEY,
  `reservation_id`  INT NOT NULL,
  `equipment_id`    INT NOT NULL,
  `quantity`        INT NOT NULL DEFAULT 1,
  `notes`           VARCHAR(255) DEFAULT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`reservation_id`) REFERENCES `facility_reservations`(`reservation_id`) ON DELETE CASCADE,
  FOREIGN KEY (`equipment_id`) REFERENCES `facility_equipment`(`equipment_id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_res_equip` (`reservation_id`, `equipment_id`)
) ENGINE=InnoDB;

-- Maintenance requests for facilities
CREATE TABLE IF NOT EXISTS `facility_maintenance` (
  `maintenance_id`   INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_number`    VARCHAR(20)  NOT NULL UNIQUE,
  `facility_id`      INT          NOT NULL,
  `equipment_id`     INT          DEFAULT NULL,
  `reported_by`      INT          NOT NULL,
  `issue_type`       ENUM('electrical','plumbing','hvac','structural','equipment','cleaning','other') NOT NULL,
  `priority`         ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `description`      TEXT         NOT NULL,
  `status`           ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `assigned_to`      VARCHAR(200) DEFAULT NULL,
  `resolution_notes` TEXT         DEFAULT NULL,
  `resolved_at`      DATETIME     DEFAULT NULL,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`facility_id`) ON DELETE CASCADE,
  FOREIGN KEY (`equipment_id`) REFERENCES `facility_equipment`(`equipment_id`) ON DELETE SET NULL,
  FOREIGN KEY (`reported_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 2: DOCUMENT MANAGEMENT (ARCHIVING)
-- =====================================================================

-- Document categories
CREATE TABLE IF NOT EXISTS `document_categories` (
  `category_id`   INT AUTO_INCREMENT PRIMARY KEY,
  `name`          VARCHAR(200) NOT NULL,
  `code`          VARCHAR(20)  NOT NULL UNIQUE,
  `parent_id`     INT          DEFAULT NULL,
  `description`   TEXT         DEFAULT NULL,
  `icon`          VARCHAR(50)  DEFAULT NULL,
  `sort_order`    INT          NOT NULL DEFAULT 0,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `document_categories`(`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Documents archive
CREATE TABLE IF NOT EXISTS `documents` (
  `document_id`    INT AUTO_INCREMENT PRIMARY KEY,
  `document_code`  VARCHAR(30)  NOT NULL UNIQUE,
  `title`          VARCHAR(300) NOT NULL,
  `folder_name`    VARCHAR(200) DEFAULT NULL COMMENT 'Folder title for department organization',
  `category_id`    INT          DEFAULT NULL,
  `document_type`  ENUM('memo','contract','report','policy','form','certificate','invoice','receipt','letter','other') NOT NULL,
  `description`    TEXT         DEFAULT NULL,
  `file_path`      VARCHAR(500) NOT NULL,
  `file_name`      VARCHAR(300) NOT NULL,
  `file_size`      BIGINT       DEFAULT NULL,
  `file_type`      VARCHAR(50)  DEFAULT NULL,
  `version`        INT          NOT NULL DEFAULT 1,
  `tags`           JSON         DEFAULT NULL,
  `ocr_text`       LONGTEXT     DEFAULT NULL COMMENT 'Extracted text via OCR processing',
  `ocr_status`     ENUM('pending','processing','completed','failed','not_applicable') NOT NULL DEFAULT 'not_applicable',
  `ocr_processed_at` DATETIME   DEFAULT NULL,
  `qr_code_id`     INT          DEFAULT NULL,
  `uploaded_by`    INT          NOT NULL,
  `department`     VARCHAR(100) DEFAULT NULL,
  `confidentiality` ENUM('public','internal','confidential','restricted') NOT NULL DEFAULT 'internal',
  `status`         ENUM('draft','active','archived','retained') NOT NULL DEFAULT 'active',
  `archived_at`    DATETIME     DEFAULT NULL,
  `retained_at`    DATETIME     DEFAULT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `document_categories`(`category_id`) ON DELETE SET NULL,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes`(`qr_id`) ON DELETE SET NULL,
  FULLTEXT INDEX `idx_fulltext` (`title`, `description`, `ocr_text`),
  INDEX `idx_category` (`category_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- Document version history
CREATE TABLE IF NOT EXISTS `document_versions` (
  `version_id`    INT AUTO_INCREMENT PRIMARY KEY,
  `document_id`   INT          NOT NULL,
  `version_number` INT         NOT NULL,
  `file_path`     VARCHAR(500) NOT NULL,
  `file_name`     VARCHAR(300) NOT NULL,
  `file_size`     BIGINT       DEFAULT NULL,
  `change_notes`  TEXT         DEFAULT NULL,
  `uploaded_by`   INT          NOT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- OCR processing queue
CREATE TABLE IF NOT EXISTS `ocr_queue` (
  `queue_id`      INT AUTO_INCREMENT PRIMARY KEY,
  `document_id`   INT          NOT NULL,
  `priority`      ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `status`        ENUM('queued','processing','completed','failed') NOT NULL DEFAULT 'queued',
  `attempts`      INT          NOT NULL DEFAULT 0,
  `error_message` TEXT         DEFAULT NULL,
  `started_at`    DATETIME     DEFAULT NULL,
  `completed_at`  DATETIME     DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Document sharing / access control
CREATE TABLE IF NOT EXISTS `document_access` (
  `access_id`     INT AUTO_INCREMENT PRIMARY KEY,
  `document_id`   INT          NOT NULL,
  `user_id`       INT          NOT NULL,
  `permission`    ENUM('view','download','edit','admin') NOT NULL DEFAULT 'view',
  `granted_by`    INT          DEFAULT NULL,
  `expires_at`    DATETIME     DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`granted_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  UNIQUE KEY `uk_doc_user` (`document_id`, `user_id`)
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 3: LEGAL MANAGEMENT
-- =====================================================================

-- Legal cases
CREATE TABLE IF NOT EXISTS `legal_cases` (
  `case_id`          INT AUTO_INCREMENT PRIMARY KEY,
  `case_number`      VARCHAR(30)  NOT NULL UNIQUE,
  `title`            VARCHAR(300) NOT NULL,
  `case_type`        ENUM('litigation','arbitration','mediation','regulatory','compliance','internal_investigation','other') NOT NULL,
  `description`      TEXT         NOT NULL,
  `priority`         ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status`           ENUM('open','in_progress','pending_review','resolved','closed','appealed') NOT NULL DEFAULT 'open',
  `filing_date`      DATE         DEFAULT NULL,
  `due_date`         DATE         DEFAULT NULL,
  `resolution_date`  DATE         DEFAULT NULL,
  `opposing_party`   VARCHAR(300) DEFAULT NULL,
  `court_venue`      VARCHAR(300) DEFAULT NULL,
  `assigned_lawyer`  VARCHAR(200) DEFAULT NULL,
  `assigned_to`      INT          DEFAULT NULL,
  `department`       VARCHAR(100) DEFAULT NULL,
  `financial_impact` DECIMAL(15,2) DEFAULT NULL COMMENT 'Estimated financial impact in microfinancial operations',
  `resolution_summary` TEXT      DEFAULT NULL,
  `created_by`       INT          NOT NULL,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_type` (`case_type`)
) ENGINE=InnoDB;

-- Contracts & agreements
CREATE TABLE IF NOT EXISTS `legal_contracts` (
  `contract_id`      INT AUTO_INCREMENT PRIMARY KEY,
  `contract_number`  VARCHAR(30)  NOT NULL UNIQUE,
  `title`            VARCHAR(300) NOT NULL,
  `contract_type`    ENUM('employment','vendor','service','nda','lease','loan','partnership','other') NOT NULL,
  `party_name`       VARCHAR(300) NOT NULL,
  `party_contact`    VARCHAR(200) DEFAULT NULL,
  `description`      TEXT         DEFAULT NULL,
  `start_date`       DATE         NOT NULL,
  `end_date`         DATE         DEFAULT NULL,
  `value`            DECIMAL(15,2) DEFAULT NULL,
  `currency`         VARCHAR(3)   NOT NULL DEFAULT 'PHP',
  `status`           ENUM('draft','active','expired','terminated','renewed','under_review') NOT NULL DEFAULT 'draft',
  `auto_renew`       TINYINT(1)   NOT NULL DEFAULT 0,
  `renewal_notice_days` INT       DEFAULT 30,
  `document_id`      INT          DEFAULT NULL COMMENT 'Link to archived document',
  `qr_code_id`       INT          DEFAULT NULL,
  `assigned_to`      INT          DEFAULT NULL,
  `created_by`       INT          NOT NULL,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE SET NULL,
  FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes`(`qr_id`) ON DELETE SET NULL,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_end_date` (`end_date`)
) ENGINE=InnoDB;

-- Compliance tracking
CREATE TABLE IF NOT EXISTS `legal_compliance` (
  `compliance_id`    INT AUTO_INCREMENT PRIMARY KEY,
  `reference_code`   VARCHAR(30)  NOT NULL UNIQUE,
  `requirement`      VARCHAR(300) NOT NULL,
  `regulatory_body`  VARCHAR(200) DEFAULT NULL,
  `category`         ENUM('banking_regulation','data_privacy','labor_law','tax','anti_money_laundering','consumer_protection','other') NOT NULL,
  `description`      TEXT         DEFAULT NULL,
  `deadline`         DATE         DEFAULT NULL,
  `status`           ENUM('compliant','non_compliant','in_progress','pending_review','exempted') NOT NULL DEFAULT 'pending_review',
  `risk_level`       ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `evidence_document_id` INT     DEFAULT NULL,
  `assigned_to`      INT          DEFAULT NULL,
  `last_reviewed`    DATE         DEFAULT NULL,
  `next_review_date` DATE         DEFAULT NULL,
  `notes`            TEXT         DEFAULT NULL,
  `created_by`       INT          NOT NULL,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`evidence_document_id`) REFERENCES `documents`(`document_id`) ON DELETE SET NULL,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Legal case documents (linking legal to document management)
CREATE TABLE IF NOT EXISTS `legal_case_documents` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `case_id`       INT DEFAULT NULL,
  `contract_id`   INT DEFAULT NULL,
  `compliance_id` INT DEFAULT NULL,
  `document_id`   INT NOT NULL,
  `doc_label`     VARCHAR(200) DEFAULT NULL,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`case_id`) REFERENCES `legal_cases`(`case_id`) ON DELETE CASCADE,
  FOREIGN KEY (`contract_id`) REFERENCES `legal_contracts`(`contract_id`) ON DELETE CASCADE,
  FOREIGN KEY (`compliance_id`) REFERENCES `legal_compliance`(`compliance_id`) ON DELETE CASCADE,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Loan documentation & contracts
CREATE TABLE IF NOT EXISTS `loan_documentation` (
  `loan_doc_id`       INT AUTO_INCREMENT PRIMARY KEY,
  `loan_doc_code`     VARCHAR(30) NOT NULL UNIQUE,
  `borrower_name`     VARCHAR(300) NOT NULL,
  `borrower_address`  VARCHAR(500) DEFAULT NULL,
  `loan_amount`       DECIMAL(15,2) NOT NULL,
  `interest_rate`     DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `loan_term_months`  INT NOT NULL DEFAULT 12,
  `repayment_schedule` ENUM('monthly','quarterly','semi_annual','annual','lump_sum') NOT NULL DEFAULT 'monthly',
  `purpose`           VARCHAR(500) DEFAULT NULL,
  `contract_body`     LONGTEXT DEFAULT NULL COMMENT 'Full contract text with terms & conditions',
  `attorney_name`     VARCHAR(200) DEFAULT NULL,
  `attorney_prc`      VARCHAR(50) DEFAULT NULL COMMENT 'PRC license number',
  `attorney_ptr`      VARCHAR(50) DEFAULT NULL COMMENT 'PTR number',
  `attorney_ibp`      VARCHAR(50) DEFAULT NULL COMMENT 'IBP number',
  `attorney_roll`     VARCHAR(50) DEFAULT NULL COMMENT 'Roll of Attorneys number',
  `attorney_mcle`     VARCHAR(50) DEFAULT NULL COMMENT 'MCLE compliance number',
  `attorney_signature` TEXT DEFAULT NULL COMMENT 'Base64 encoded signature image',
  `notary_name`       VARCHAR(200) DEFAULT NULL,
  `notary_commission` VARCHAR(100) DEFAULT NULL,
  `doc_series_no`     VARCHAR(50) DEFAULT NULL,
  `doc_page_no`       VARCHAR(50) DEFAULT NULL,
  `doc_book_no`       VARCHAR(50) DEFAULT NULL,
  `penalty_rate`      DECIMAL(5,2) DEFAULT 3.00 COMMENT 'Penalty rate per month for late payment',
  `disclosure_statement` TEXT DEFAULT NULL COMMENT 'Truth in Lending Act disclosure',
  `promissory_note`   TEXT DEFAULT NULL COMMENT 'Promissory note text',
  `security_type`     ENUM('unsecured','chattel_mortgage','real_estate_mortgage','pledge','guarantor') NOT NULL DEFAULT 'unsecured',
  `digital_signature_hash` VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 hash for digital signature verification',
  `signed_date`       DATE DEFAULT NULL,
  `effective_date`    DATE DEFAULT NULL,
  `maturity_date`     DATE DEFAULT NULL,
  `status`            ENUM('draft','pending_signature','signed','active','defaulted','paid','cancelled') NOT NULL DEFAULT 'draft',
  `document_id`       INT DEFAULT NULL COMMENT 'Link to document management',
  `case_id`           INT DEFAULT NULL COMMENT 'Link to legal case if defaulted',
  `created_by`        INT NOT NULL,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE SET NULL,
  FOREIGN KEY (`case_id`) REFERENCES `legal_cases`(`case_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_borrower` (`borrower_name`)
) ENGINE=InnoDB;

-- Collateral & security management
CREATE TABLE IF NOT EXISTS `collateral_registry` (
  `collateral_id`     INT AUTO_INCREMENT PRIMARY KEY,
  `collateral_code`   VARCHAR(30) NOT NULL UNIQUE,
  `loan_doc_id`       INT DEFAULT NULL,
  `borrower_name`     VARCHAR(300) NOT NULL,
  `collateral_type`   ENUM('real_estate','vehicle','equipment','inventory','receivables','deposit','jewelry','other') NOT NULL,
  `description`       VARCHAR(500) NOT NULL,
  `serial_plate_no`   VARCHAR(100) DEFAULT NULL COMMENT 'For vehicles/equipment',
  `title_deed_no`     VARCHAR(100) DEFAULT NULL COMMENT 'For real estate',
  `location_address`  VARCHAR(500) DEFAULT NULL,
  `appraised_value`   DECIMAL(15,2) DEFAULT NULL,
  `appraisal_date`    DATE DEFAULT NULL,
  `appraiser_name`    VARCHAR(200) DEFAULT NULL,
  `lien_status`       ENUM('active','released','foreclosed','pending_release') NOT NULL DEFAULT 'active',
  `lien_recorded_date` DATE DEFAULT NULL,
  `lien_registry_no`  VARCHAR(100) DEFAULT NULL COMMENT 'Registry of Deeds annotation number',
  `insurance_policy`  VARCHAR(100) DEFAULT NULL,
  `insurance_expiry`  DATE DEFAULT NULL,
  `release_date`      DATE DEFAULT NULL,
  `release_authorized_by` INT DEFAULT NULL,
  `foreclosure_date`  DATE DEFAULT NULL,
  `foreclosure_case_id` INT DEFAULT NULL,
  `document_id`       INT DEFAULT NULL,
  `notes`             TEXT DEFAULT NULL,
  `created_by`        INT NOT NULL,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`loan_doc_id`) REFERENCES `loan_documentation`(`loan_doc_id`) ON DELETE SET NULL,
  FOREIGN KEY (`release_authorized_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`foreclosure_case_id`) REFERENCES `legal_cases`(`case_id`) ON DELETE SET NULL,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_lien_status` (`lien_status`),
  INDEX `idx_type` (`collateral_type`)
) ENGINE=InnoDB;

-- Demand letters (litigation & recovery)
CREATE TABLE IF NOT EXISTS `demand_letters` (
  `demand_id`       INT AUTO_INCREMENT PRIMARY KEY,
  `demand_code`     VARCHAR(30) NOT NULL UNIQUE,
  `loan_doc_id`     INT DEFAULT NULL,
  `case_id`         INT DEFAULT NULL,
  `borrower_name`   VARCHAR(300) NOT NULL,
  `borrower_address` VARCHAR(500) DEFAULT NULL,
  `amount_demanded` DECIMAL(15,2) NOT NULL,
  `demand_type`     ENUM('first_notice','second_notice','final_demand','notice_of_default','notice_of_foreclosure') NOT NULL DEFAULT 'first_notice',
  `letter_body`     LONGTEXT DEFAULT NULL,
  `attorney_name`   VARCHAR(200) DEFAULT NULL,
  `sent_date`       DATE DEFAULT NULL,
  `sent_via`        ENUM('registered_mail','personal_service','email','courier') DEFAULT NULL,
  `received_date`   DATE DEFAULT NULL,
  `response_deadline` DATE DEFAULT NULL,
  `borrower_responded` TINYINT(1) NOT NULL DEFAULT 0,
  `response_summary` TEXT DEFAULT NULL,
  `escalated_to_litigation` TINYINT(1) NOT NULL DEFAULT 0,
  `status`          ENUM('draft','sent','received','responded','expired','escalated') NOT NULL DEFAULT 'draft',
  `document_id`     INT DEFAULT NULL,
  `created_by`      INT NOT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`loan_doc_id`) REFERENCES `loan_documentation`(`loan_doc_id`) ON DELETE SET NULL,
  FOREIGN KEY (`case_id`) REFERENCES `legal_cases`(`case_id`) ON DELETE SET NULL,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_type` (`demand_type`)
) ENGINE=InnoDB;

-- KYC (Know Your Customer) records
CREATE TABLE IF NOT EXISTS `kyc_records` (
  `kyc_id`          INT AUTO_INCREMENT PRIMARY KEY,
  `kyc_code`        VARCHAR(30) NOT NULL UNIQUE,
  `client_name`     VARCHAR(300) NOT NULL,
  `client_type`     ENUM('individual','corporate','partnership','sole_proprietor') NOT NULL DEFAULT 'individual',
  `id_type`         VARCHAR(100) NOT NULL,
  `id_number`       VARCHAR(100) NOT NULL,
  `id_expiry`       DATE DEFAULT NULL,
  `tin`             VARCHAR(20) DEFAULT NULL,
  `address`         VARCHAR(500) DEFAULT NULL,
  `occupation`      VARCHAR(200) DEFAULT NULL,
  `source_of_funds` VARCHAR(300) DEFAULT NULL,
  `risk_rating`     ENUM('low','medium','high','pep') NOT NULL DEFAULT 'low',
  `verification_status` ENUM('pending','verified','rejected','expired','under_review') NOT NULL DEFAULT 'pending',
  `verified_by`     INT DEFAULT NULL,
  `verified_date`   DATE DEFAULT NULL,
  `aml_flag`        TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Anti-Money Laundering red flag',
  `aml_notes`       TEXT DEFAULT NULL,
  `sanctions_checked` TINYINT(1) NOT NULL DEFAULT 0,
  `pep_checked`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Politically Exposed Person check',
  `next_review_date` DATE DEFAULT NULL,
  `document_id`     INT DEFAULT NULL,
  `created_by`      INT NOT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`verified_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_risk` (`risk_rating`),
  INDEX `idx_status` (`verification_status`)
) ENGINE=InnoDB;

-- Board resolutions (corporate governance)
CREATE TABLE IF NOT EXISTS `board_resolutions` (
  `resolution_id`   INT AUTO_INCREMENT PRIMARY KEY,
  `resolution_code` VARCHAR(30) NOT NULL UNIQUE,
  `title`           VARCHAR(300) NOT NULL,
  `resolution_type` ENUM('policy','financial','operational','appointment','amendment','dissolution','other') NOT NULL,
  `meeting_date`    DATE NOT NULL,
  `meeting_type`    ENUM('regular','special','emergency','annual') NOT NULL DEFAULT 'regular',
  `attendees`       JSON DEFAULT NULL COMMENT 'Array of board member names',
  `quorum_present`  TINYINT(1) NOT NULL DEFAULT 1,
  `resolution_text` LONGTEXT DEFAULT NULL,
  `minutes_text`    LONGTEXT DEFAULT NULL COMMENT 'Meeting minutes',
  `votes_for`       INT DEFAULT NULL,
  `votes_against`   INT DEFAULT NULL,
  `votes_abstain`   INT DEFAULT NULL,
  `passed`          TINYINT(1) NOT NULL DEFAULT 1,
  `effective_date`  DATE DEFAULT NULL,
  `secretary_name`  VARCHAR(200) DEFAULT NULL,
  `chairman_name`   VARCHAR(200) DEFAULT NULL,
  `document_id`     INT DEFAULT NULL,
  `status`          ENUM('draft','approved','filed','superseded') NOT NULL DEFAULT 'draft',
  `created_by`      INT NOT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_type` (`resolution_type`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- Power of Attorney tracking
CREATE TABLE IF NOT EXISTS `power_of_attorney` (
  `poa_id`          INT AUTO_INCREMENT PRIMARY KEY,
  `poa_code`        VARCHAR(30) NOT NULL UNIQUE,
  `principal_name`  VARCHAR(300) NOT NULL COMMENT 'Person granting authority',
  `principal_position` VARCHAR(200) DEFAULT NULL,
  `agent_name`      VARCHAR(300) NOT NULL COMMENT 'Person receiving authority',
  `agent_position`  VARCHAR(200) DEFAULT NULL,
  `poa_type`        ENUM('general','special','limited','durable') NOT NULL DEFAULT 'special',
  `scope`           TEXT NOT NULL COMMENT 'Scope of authority granted',
  `effective_date`  DATE NOT NULL,
  `expiry_date`     DATE DEFAULT NULL,
  `notarized`       TINYINT(1) NOT NULL DEFAULT 0,
  `notary_name`     VARCHAR(200) DEFAULT NULL,
  `notary_date`     DATE DEFAULT NULL,
  `resolution_id`   INT DEFAULT NULL COMMENT 'Board resolution authorizing this POA',
  `document_id`     INT DEFAULT NULL,
  `status`          ENUM('active','expired','revoked','superseded') NOT NULL DEFAULT 'active',
  `revoked_date`    DATE DEFAULT NULL,
  `revoked_reason`  TEXT DEFAULT NULL,
  `created_by`      INT NOT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`resolution_id`) REFERENCES `board_resolutions`(`resolution_id`) ON DELETE SET NULL,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_type` (`poa_type`)
) ENGINE=InnoDB;

-- Permits & licensing
CREATE TABLE IF NOT EXISTS `permits_licenses` (
  `permit_id`       INT AUTO_INCREMENT PRIMARY KEY,
  `permit_code`     VARCHAR(30) NOT NULL UNIQUE,
  `permit_name`     VARCHAR(300) NOT NULL,
  `issuing_body`    VARCHAR(200) NOT NULL,
  `permit_type`     ENUM('business_permit','financial_license','sec_registration','cda_registration','bsp_license','fire_safety','occupancy','sanitary','other') NOT NULL,
  `permit_number`   VARCHAR(100) DEFAULT NULL,
  `issue_date`      DATE DEFAULT NULL,
  `expiry_date`     DATE DEFAULT NULL,
  `renewal_fee`     DECIMAL(12,2) DEFAULT NULL,
  `status`          ENUM('active','expired','pending_renewal','suspended','revoked') NOT NULL DEFAULT 'active',
  `document_id`     INT DEFAULT NULL,
  `notes`           TEXT DEFAULT NULL,
  `created_by`      INT NOT NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`document_id`) REFERENCES `documents`(`document_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_expiry` (`expiry_date`)
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 4: VISITOR MANAGEMENT
-- =====================================================================

-- Visitor registry
CREATE TABLE IF NOT EXISTS `visitors` (
  `visitor_id`      INT AUTO_INCREMENT PRIMARY KEY,
  `visitor_code`    VARCHAR(20)  NOT NULL UNIQUE,
  `first_name`      VARCHAR(100) NOT NULL,
  `last_name`       VARCHAR(100) NOT NULL,
  `email`           VARCHAR(150) DEFAULT NULL,
  `phone`           VARCHAR(20)  DEFAULT NULL,
  `company`         VARCHAR(200) DEFAULT NULL,
  `id_type`         ENUM('government_id','passport','drivers_license','company_id','other') DEFAULT NULL,
  `id_number`       VARCHAR(100) DEFAULT NULL,
  `photo_url`       VARCHAR(500) DEFAULT NULL,
  `is_blacklisted`  TINYINT(1)   NOT NULL DEFAULT 0,
  `blacklist_reason` TEXT        DEFAULT NULL,
  `visit_count`     INT          NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Visit logs
CREATE TABLE IF NOT EXISTS `visitor_logs` (
  `log_id`           INT AUTO_INCREMENT PRIMARY KEY,
  `visit_code`       VARCHAR(20)  NOT NULL UNIQUE,
  `visitor_id`       INT          NOT NULL,
  `host_user_id`     INT          DEFAULT NULL,
  `host_name`        VARCHAR(200) DEFAULT NULL,
  `host_department`  VARCHAR(100) DEFAULT NULL,
  `purpose`          ENUM('meeting','delivery','interview','inspection','consultation','maintenance','other') NOT NULL,
  `purpose_details`  TEXT         DEFAULT NULL,
  `facility_id`      INT          DEFAULT NULL COMMENT 'Links to facility reservation if applicable',
  `check_in_time`    DATETIME     NOT NULL,
  `check_out_time`   DATETIME     DEFAULT NULL,
  `qr_code_id`       INT          DEFAULT NULL,
  `badge_number`     VARCHAR(20)  DEFAULT NULL,
  `items_brought`    TEXT         DEFAULT NULL,
  `vehicle_plate`    VARCHAR(20)  DEFAULT NULL,
  `status`           ENUM('pre_registered','checked_in','checked_out','cancelled','no_show') NOT NULL DEFAULT 'pre_registered',
  `notes`            TEXT         DEFAULT NULL,
  `created_by`       INT          DEFAULT NULL,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`visitor_id`) REFERENCES `visitors`(`visitor_id`) ON DELETE CASCADE,
  FOREIGN KEY (`host_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`facility_id`) REFERENCES `facilities`(`facility_id`) ON DELETE SET NULL,
  FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes`(`qr_id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  INDEX `idx_date` (`check_in_time`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- Visitor pre-registration
CREATE TABLE IF NOT EXISTS `visitor_preregistrations` (
  `prereg_id`       INT AUTO_INCREMENT PRIMARY KEY,
  `prereg_code`     VARCHAR(20)  NOT NULL UNIQUE,
  `visitor_name`    VARCHAR(200) NOT NULL,
  `visitor_email`   VARCHAR(150) DEFAULT NULL,
  `visitor_phone`   VARCHAR(20)  DEFAULT NULL,
  `visitor_company` VARCHAR(200) DEFAULT NULL,
  `host_user_id`    INT          NOT NULL,
  `purpose`         VARCHAR(300) NOT NULL,
  `expected_date`   DATE         NOT NULL,
  `expected_time`   TIME         DEFAULT NULL,
  `qr_code_id`      INT          DEFAULT NULL,
  `status`          ENUM('pending','approved','checked_in','expired','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by`     INT          DEFAULT NULL,
  `visitor_id`      INT          DEFAULT NULL COMMENT 'Linked after check-in',
  `visit_log_id`    INT          DEFAULT NULL COMMENT 'Linked after check-in',
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`host_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes`(`qr_id`) ON DELETE SET NULL,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
  FOREIGN KEY (`visitor_id`) REFERENCES `visitors`(`visitor_id`) ON DELETE SET NULL,
  FOREIGN KEY (`visit_log_id`) REFERENCES `visitor_logs`(`log_id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Visitor analytics (daily summary)
CREATE TABLE IF NOT EXISTS `visitor_daily_summary` (
  `summary_id`      INT AUTO_INCREMENT PRIMARY KEY,
  `summary_date`    DATE         NOT NULL UNIQUE,
  `total_visitors`  INT          NOT NULL DEFAULT 0,
  `total_check_ins` INT          NOT NULL DEFAULT 0,
  `total_check_outs` INT         NOT NULL DEFAULT 0,
  `total_no_shows`  INT          NOT NULL DEFAULT 0,
  `peak_hour`       TINYINT      DEFAULT NULL,
  `avg_visit_duration_min` DECIMAL(6,1) DEFAULT NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
-- NOTIFICATIONS (cross-module)
-- =====================================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`         INT          NOT NULL,
  `module`          ENUM('facilities','documents','legal','visitors','system') NOT NULL,
  `title`           VARCHAR(200) NOT NULL,
  `message`         TEXT         NOT NULL,
  `link`            VARCHAR(500) DEFAULT NULL,
  `is_read`         TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB;

-- =====================================================================
-- SAMPLE DATA
-- =====================================================================

-- Default admin user (password: admin - bcrypt hash)
INSERT INTO `users` (`employee_id`, `first_name`, `last_name`, `email`, `password_hash`, `role`, `department`) VALUES
('admin', 'Admin',    '', 'admin@microfinancial.com',    '$2y$10$WLc/AHcq/TwfULcfFNqhju1150rYz6SLVGhKWeVwi6uPsStQB9oEa', 'super_admin', 'IT Department'),
('EMP-2026-002', 'Maria',     'Santos',        'maria.santos@microfinancial.com', '$2y$10$xH2n5ODv1rqPZj352SMOE.Bre57GCiQJ14FI2xdAdx1dxll5uA7Qq', 'admin',       'Administration'),
('EMP-2026-003', 'Juan',      'Dela Cruz',     'juan.delacruz@microfinancial.com','$2y$10$xH2n5ODv1rqPZj352SMOE.Bre57GCiQJ14FI2xdAdx1dxll5uA7Qq', 'manager',     'Legal Department'),
('EMP-2026-004', 'Ana',       'Reyes',         'ana.reyes@microfinancial.com',    '$2y$10$xH2n5ODv1rqPZj352SMOE.Bre57GCiQJ14FI2xdAdx1dxll5uA7Qq', 'staff',       'Finance'),
('EMP-2026-005', 'Carlos',    'Garcia',        'carlos.garcia@microfinancial.com','$2y$10$xH2n5ODv1rqPZj352SMOE.Bre57GCiQJ14FI2xdAdx1dxll5uA7Qq', 'staff',       'Operations');

-- Facilities
INSERT INTO `facilities` (`facility_code`, `name`, `type`, `location`, `capacity`, `description`, `amenities`, `hourly_rate`, `status`, `created_by`) VALUES
('FAC-001', 'Main Conference Room A',   'conference_room', 'Building 1, 3rd Floor',  30, 'Large conference room with panoramic view',     '["projector","whiteboard","video_conferencing","air_conditioning"]', 500.00, 'available', 1),
('FAC-002', 'Meeting Room B',           'meeting_room',    'Building 1, 2nd Floor',  10, 'Compact meeting room for small groups',          '["whiteboard","tv_monitor","wifi"]',                                200.00, 'available', 1),
('FAC-003', 'Training Hall',            'training_hall',   'Building 2, Ground Floor', 50, 'Spacious training hall with stage',             '["projector","microphone","stage","air_conditioning","wifi"]',       800.00, 'available', 1),
('FAC-004', 'Executive Boardroom',      'conference_room', 'Building 1, 5th Floor',  15, 'Premium boardroom with mahogany table',          '["projector","video_conferencing","mini_bar","air_conditioning"]',   1000.00, 'available', 1),
('FAC-005', 'Auditorium',               'auditorium',      'Building 3, Ground Floor', 200, 'Full auditorium with sound system',           '["stage","microphone","projector","sound_system","recording"]',      2000.00, 'maintenance', 1),
('FAC-006', 'Parking Lot A',            'parking',         'Building 1 Basement',     50, 'Underground parking for staff',                  '["security_camera","ev_charging"]',                                 0.00,    'available', 1);

-- Facility reservations
INSERT INTO `facility_reservations` (`reservation_code`, `facility_id`, `reserved_by`, `department`, `purpose`, `event_title`, `start_datetime`, `end_datetime`, `attendees_count`, `status`) VALUES
('RES-2026-0001', 1, 2, 'Administration',  'Monthly management meeting',    'Q1 Strategy Review',       '2026-02-16 09:00:00', '2026-02-16 12:00:00', 25, 'approved'),
('RES-2026-0002', 2, 4, 'Finance',         'Team huddle',                   'Weekly Finance Sync',      '2026-02-16 14:00:00', '2026-02-16 15:00:00', 8,  'approved'),
('RES-2026-0003', 3, 3, 'Legal Department','Legal training for staff',      'AML Compliance Training',  '2026-02-17 08:00:00', '2026-02-17 17:00:00', 45, 'pending'),
('RES-2026-0004', 4, 1, 'IT Department',   'System upgrade discussion',     'IT Infrastructure Review', '2026-02-18 10:00:00', '2026-02-18 12:00:00', 12, 'approved'),
('RES-2026-0005', 1, 5, 'Operations',      'Client onboarding presentation','New Client Welcome',       '2026-02-19 13:00:00', '2026-02-19 15:00:00', 20, 'pending');

-- Equipment
INSERT INTO `facility_equipment` (`equipment_code`, `name`, `category`, `serial_number`, `facility_id`, `condition_status`, `quantity`) VALUES
('EQP-001', 'Epson Projector EB-X51',     'AV Equipment',   'EP-2024-X51-001', 1, 'excellent', 1),
('EQP-002', 'Logitech Rally Camera',      'AV Equipment',   'LG-2024-RLY-001', 1, 'good',      1),
('EQP-003', 'Samsung 65" Smart TV',       'AV Equipment',   'SM-2024-TV65-001', 2, 'excellent', 1),
('EQP-004', 'Polycom Conference Phone',   'Communication',  'PL-2024-CFP-001', 4, 'good',      1),
('EQP-005', 'Portable Whiteboard',        'Office Supply',  NULL,              NULL, 'good',     3),
('EQP-006', 'Wireless Presentation Remote','AV Equipment',  'WP-2024-RMT-002', NULL, 'fair',     2);

-- Document categories
INSERT INTO `document_categories` (`name`, `code`, `description`, `sort_order`) VALUES
('Financial Documents',   'FIN',  'Microfinancial statements, reports, and records',    1),
('Human Resources',       'HR',   'Employee records, contracts, and HR policies',        2),
('Legal & Compliance',    'LEG',  'Legal documents, regulations, and compliance records', 3),
('Operations',            'OPS',  'Operational procedures, SOPs, and manuals',           4),
('Administrative',        'ADM',  'Administrative memos, circulars, and notices',        5),
('Client Records',        'CLT',  'Microfinancial client documents and applications',    6),
('Loan Documents',        'LN',   'Loan agreements, promissory notes, and amortization', 7),
('Board Resolutions',     'BRD',  'Board meeting minutes and resolutions',               8);

-- Documents
INSERT INTO `documents` (`document_code`, `title`, `category_id`, `document_type`, `description`, `file_path`, `file_name`, `file_size`, `file_type`, `uploaded_by`, `department`, `confidentiality`, `status`, `ocr_status`) VALUES
('DOC-2026-00001', 'Q4 2025 Financial Statement',            1, 'report',      'Quarterly financial report for microfinancial operations',    '/uploads/documents/fin_q4_2025.pdf',    'fin_q4_2025.pdf',    2456789, 'application/pdf', 4, 'Finance',         'confidential', 'active', 'completed'),
('DOC-2026-00002', 'Employee Handbook v3.2',                  2, 'policy',      'Updated employee handbook with new policies',                '/uploads/documents/emp_handbook_v3.pdf', 'emp_handbook_v3.pdf', 5123456, 'application/pdf', 2, 'Administration',  'internal',     'active', 'completed'),
('DOC-2026-00003', 'Anti-Money Laundering Policy 2026',       3, 'policy',      'AML compliance policy for microfinancial institution',       '/uploads/documents/aml_policy_2026.pdf', 'aml_policy_2026.pdf', 1234567, 'application/pdf', 3, 'Legal Department','restricted',   'active', 'completed'),
('DOC-2026-00004', 'Standard Operating Procedures - Loans',   4, 'form',        'SOP for processing microfinancial loan applications',        '/uploads/documents/sop_loans.pdf',       'sop_loans.pdf',       3456789, 'application/pdf', 5, 'Operations',      'internal',     'active', 'completed'),
('DOC-2026-00005', 'Board Resolution No. 2026-001',           8, 'certificate', 'Resolution approving new branch expansion',                  '/uploads/documents/br_2026_001.pdf',     'br_2026_001.pdf',     987654,  'application/pdf', 1, 'Administration',  'restricted',   'active', 'pending'),
('DOC-2026-00006', 'Vendor Service Agreement - IT Support',   3, 'contract',    'Annual IT support contract with TechServ Inc.',              '/uploads/documents/vendor_it_2026.pdf',  'vendor_it_2026.pdf',  2345678, 'application/pdf', 1, 'IT Department',   'confidential', 'active', 'completed'),
('DOC-2026-00007', 'Client Loan Application Form Template',   6, 'form',        'Standardized loan application form for microfinancial clients', '/uploads/documents/loan_app_form.pdf', 'loan_app_form.pdf',   567890,  'application/pdf', 5, 'Operations',      'public',       'active', 'not_applicable');

-- Legal cases
INSERT INTO `legal_cases` (`case_number`, `title`, `case_type`, `description`, `priority`, `status`, `filing_date`, `due_date`, `opposing_party`, `court_venue`, `assigned_lawyer`, `assigned_to`, `department`, `financial_impact`, `created_by`) VALUES
('LC-2026-001', 'Loan Default Recovery - ABC Corp',       'litigation', 'Recovery of defaulted microfinancial loan amounting to PHP 2.5M', 'high',     'in_progress', '2026-01-10', '2026-06-30', 'ABC Corporation',       'Regional Trial Court - Makati',  'Atty. Dela Rosa',   3, 'Legal Department', 2500000.00, 3),
('LC-2026-002', 'Data Privacy Complaint',                  'regulatory', 'NPC complaint regarding client data handling procedures',         'critical', 'open',        '2026-02-01', '2026-03-15', 'National Privacy Commission', 'NPC Office',                   'Atty. Santos',      3, 'Legal Department', 500000.00,  3),
('LC-2026-003', 'Employee Labor Dispute - Retrenchment',   'mediation',  'DOLE mediation for dispute on retrenchment benefits',            'medium',   'pending_review', '2026-01-20', '2026-04-01', 'Former Employee Group', 'DOLE NCR Office',             'Atty. Cruz',        3, 'Legal Department', 800000.00,  3),
('LC-2026-004', 'BSP Audit Compliance Review',             'compliance', 'Preparation for BSP scheduled audit on microfinancial operations','high',    'in_progress', '2026-02-05', '2026-03-01', 'Bangko Sentral ng Pilipinas', 'BSP Main Office',            'Atty. Dela Rosa',   3, 'Legal Department', 0.00,       1);

-- Legal contracts
INSERT INTO `legal_contracts` (`contract_number`, `title`, `contract_type`, `party_name`, `description`, `start_date`, `end_date`, `value`, `status`, `assigned_to`, `created_by`) VALUES
('CON-2026-001', 'IT Infrastructure Support Agreement',   'service',     'TechServ Solutions Inc.',     'Annual IT support and maintenance for microfinancial systems',  '2026-01-01', '2026-12-31', 1200000.00, 'active',  1, 1),
('CON-2026-002', 'Office Space Lease - Main Branch',       'lease',       'Premier Realty Corp.',        'Lease agreement for main office space',                         '2025-06-01', '2028-05-31', 3600000.00, 'active',  2, 2),
('CON-2026-003', 'Security Services Contract',            'service',     'SafeGuard Security Agency',   'Building security and guard services',                          '2026-01-01', '2027-06-30', 2400000.00, 'active',  2, 2),
('CON-2026-004', 'Employee NDA Template',                  'nda',         'All Employees',               'Standard non-disclosure agreement for microfinancial staff',    '2026-01-01', NULL,          0.00,       'active',  3, 3),
('CON-2026-005', 'Loan Partnership - Rural Bank of Taguig','partnership', 'Rural Bank of Taguig',        'Co-lending partnership for microfinancial loan disbursement',   '2026-02-01', '2027-01-31', 5000000.00, 'under_review', 3, 3);

-- Loan documentation (with fake attorney contracts)
INSERT INTO `loan_documentation` (`loan_doc_code`, `borrower_name`, `borrower_address`, `loan_amount`, `interest_rate`, `loan_term_months`, `repayment_schedule`, `purpose`, `contract_body`, `attorney_name`, `attorney_prc`, `attorney_ptr`, `attorney_ibp`, `attorney_roll`, `attorney_mcle`, `notary_name`, `notary_commission`, `doc_series_no`, `doc_page_no`, `doc_book_no`, `penalty_rate`, `security_type`, `signed_date`, `effective_date`, `maturity_date`, `status`, `created_by`) VALUES
('LD-2026-001', 'Roberto A. Mendoza', '123 Rizal Avenue, Brgy. Poblacion, Makati City', 250000.00, 2.50, 12, 'monthly', 'Working Capital for Small Business',
'LOAN AGREEMENT\n\nKNOW ALL MEN BY THESE PRESENTS:\n\nThis Loan Agreement ("Agreement") is entered into this 10th day of January 2026, by and between:\n\nMICROFINANCIAL MANAGEMENT CORPORATION, a corporation duly organized and existing under the laws of the Republic of the Philippines, with principal office at Unit 501, Finance Tower, Ayala Avenue, Makati City, represented herein by its President, MARIA C. SANTOS (hereinafter referred to as the "LENDER");\n\n— and —\n\nROBERTO A. MENDOZA, of legal age, Filipino, single, with residence at 123 Rizal Avenue, Brgy. Poblacion, Makati City (hereinafter referred to as the "BORROWER");\n\nWITNESSETH:\n\nWHEREAS, the BORROWER has applied for and the LENDER has agreed to extend a loan facility subject to the following terms and conditions:\n\n1. LOAN AMOUNT: The LENDER agrees to lend the amount of TWO HUNDRED FIFTY THOUSAND PESOS (₱250,000.00) to the BORROWER.\n\n2. INTEREST RATE: The loan shall bear interest at the rate of 2.50% per month, computed on the diminishing balance.\n\n3. LOAN TERM: The loan shall be payable within twelve (12) months from the date of release.\n\n4. REPAYMENT SCHEDULE: The BORROWER shall pay monthly amortizations as per the attached Schedule of Payments.\n\n5. PENALTY: A penalty of 3.00% per month shall be imposed on any unpaid amount past the due date.\n\n6. SECURITY: This loan is secured by a Chattel Mortgage over the borrower''s commercial vehicle (Toyota Hilux 2023, Plate No. ABC 1234).\n\n7. DEFAULT: The BORROWER shall be considered in default upon failure to pay two (2) consecutive monthly amortizations.\n\n8. ACCELERATION CLAUSE: Upon default, the entire outstanding balance shall become immediately due and demandable.\n\n9. VENUE: Any legal action arising from this Agreement shall be filed exclusively in the courts of Makati City.\n\nIN WITNESS WHEREOF, the parties have hereunto affixed their signatures this 10th day of January 2026 at Makati City, Philippines.\n\n[Signed]\n________________________\nMARIA C. SANTOS\nPresident, Microfinancial Management Corp.\n\n[Signed]\n________________________\nROBERTO A. MENDOZA\nBorrower',
'Atty. Francisco J. Dela Rosa', 'PRC-0045678', 'PTR No. 2026-001234 / 01-05-2026 / Makati City', 'IBP No. 123456 / Makati Chapter / 01-03-2026', 'Roll No. 67890', 'MCLE Compliance No. VII-0012345 / 04-15-2025',
'Atty. Ernesto B. Villareal', 'Notary Public for Makati City / Commission No. 2026-089 / Until Dec 31, 2027', 'Series of 2026', 'Page No. 45', 'Book No. XII', 3.00, 'chattel_mortgage', '2026-01-10', '2026-01-10', '2027-01-10', 'active', 3),

('LD-2026-002', 'Elena G. Fernandez', '456 Mabini Street, San Juan City', 150000.00, 2.00, 6, 'monthly', 'Educational Expenses',
'PROMISSORY NOTE\n\nDate: January 25, 2026\nPlace: Makati City, Philippines\n\nFOR VALUE RECEIVED, I, ELENA G. FERNANDEZ, of legal age, Filipino, residing at 456 Mabini Street, San Juan City, hereby promise to pay MICROFINANCIAL MANAGEMENT CORPORATION, or order, the sum of ONE HUNDRED FIFTY THOUSAND PESOS (₱150,000.00), Philippine Currency, on or before July 25, 2026.\n\nThis note shall bear interest at the rate of 2.00% per month from date hereof until fully paid.\n\nIn case of non-payment at maturity, I agree to pay a penalty charge of 3% per month on the outstanding balance.\n\nDemand, presentment, notice of dishonor, and protest are hereby waived.\n\nDone this 25th day of January 2026 at Makati City.\n\n[Signed]\n________________________\nELENA G. FERNANDEZ\nBorrower\n\nWITNESSES:\n\n________________________\nJUAN P. DELA CRUZ\n\n________________________\nANA M. REYES',
'Atty. Maria Teresa R. Santos', 'PRC-0098765', 'PTR No. 2026-005678 / 01-08-2026 / Makati City', 'IBP No. 654321 / QC Chapter / 01-06-2026', 'Roll No. 34567', 'MCLE Compliance No. VII-0054321 / 06-20-2025',
'Atty. Ernesto B. Villareal', 'Notary Public for Makati City / Commission No. 2026-089 / Until Dec 31, 2027', 'Series of 2026', 'Page No. 78', 'Book No. XII', 3.00, 'unsecured', '2026-01-25', '2026-01-25', '2026-07-25', 'active', 3),

('LD-2026-003', 'ABC Corporation (by: Pedro T. Lim, President)', '789 Commercial Drive, Taguig City', 2500000.00, 1.75, 24, 'monthly', 'Business Expansion - New Branch',
'LOAN AND MORTGAGE AGREEMENT\n\nThis Agreement made and executed on February 1, 2026 at Makati City by and between:\n\nMICROFINANCIAL MANAGEMENT CORPORATION ("LENDER")\nrepresented by MARIA C. SANTOS, President\n\n— and —\n\nABC CORPORATION ("BORROWER")\nrepresented by PEDRO T. LIM, President\n\nTERMS:\n1. Loan Amount: ₱2,500,000.00\n2. Interest: 1.75% per month, diminishing balance\n3. Term: 24 months\n4. Security: Real Estate Mortgage over TCT No. T-654321\n5. Purpose: Business expansion and new branch establishment\n\nSPECIAL COVENANTS:\na) Borrower shall maintain insurance on the mortgaged property\nb) Borrower shall not sell, assign, or encumber the mortgaged property\nc) Borrower shall allow inspection of the mortgaged property\n\n[Signed by both parties]',
'Atty. Francisco J. Dela Rosa', 'PRC-0045678', 'PTR No. 2026-001234 / 01-05-2026 / Makati City', 'IBP No. 123456 / Makati Chapter / 01-03-2026', 'Roll No. 67890', 'MCLE Compliance No. VII-0012345 / 04-15-2025',
'Atty. Ernesto B. Villareal', 'Notary Public for Makati City / Commission No. 2026-089 / Until Dec 31, 2027', 'Series of 2026', 'Page No. 112', 'Book No. XII', 3.00, 'real_estate_mortgage', '2026-02-01', '2026-02-01', '2028-02-01', 'active', 3),

('LD-2026-004', 'Grace S. Aquino', '321 Luna Street, Quezon City', 50000.00, 3.00, 6, 'monthly', 'Emergency Medical Expenses',
'DISCLOSURE STATEMENT\n(Required under R.A. 3765 — Truth in Lending Act)\n\nDate: February 10, 2026\n\n1. Name of Creditor: Microfinancial Management Corporation\n2. Name of Borrower: Grace S. Aquino\n3. Address: 321 Luna Street, Quezon City\n\n4. Principal Loan: ₱50,000.00\n5. Net Proceeds: ₱48,500.00 (after documentary stamps & service charge)\n6. Monthly Interest Rate: 3.00%\n7. Total Interest: ₱9,000.00\n8. Penalties (if applicable): 3.00% per month on overdue amount\n9. Total Amount to be Paid: ₱59,000.00\n10. Monthly Amortization: ₱9,833.33\n\nI hereby acknowledge receipt of this Disclosure Statement and a copy of the Promissory Note.\n\n[Signed]\n________________________\nGRACE S. AQUINO\nBorrower',
'Atty. Maria Teresa R. Santos', 'PRC-0098765', 'PTR No. 2026-005678 / 01-08-2026 / Makati City', 'IBP No. 654321 / QC Chapter / 01-06-2026', 'Roll No. 34567', 'MCLE Compliance No. VII-0054321 / 06-20-2025',
NULL, NULL, NULL, NULL, NULL, 3.00, 'unsecured', '2026-02-10', '2026-02-10', '2026-08-10', 'active', 3),

('LD-2026-005', 'Michael B. Tan', '567 Bonifacio Avenue, Taguig City', 500000.00, 2.25, 18, 'monthly', 'Equipment Purchase',
'CHATTEL MORTGAGE AGREEMENT\n\nThis instrument executed on February 5, 2026 by MICHAEL B. TAN ("MORTGAGOR") in favor of MICROFINANCIAL MANAGEMENT CORPORATION ("MORTGAGEE").\n\nThe Mortgagor hereby mortgages the following personal property:\n- Brand New Industrial Printing Equipment\n- Model: HP Indigo 7900 Digital Press\n- Serial No.: IND-2026-HP-001\n- Location: 567 Bonifacio Avenue, Taguig City\n\nAppraised Value: ₱800,000.00\n\nThis mortgage secures the payment of ₱500,000.00 loan.\n\n[Signed by Mortgagor]',
'Atty. Francisco J. Dela Rosa', 'PRC-0045678', 'PTR No. 2026-001234 / 01-05-2026 / Makati City', 'IBP No. 123456 / Makati Chapter / 01-03-2026', 'Roll No. 67890', 'MCLE Compliance No. VII-0012345 / 04-15-2025',
'Atty. Ernesto B. Villareal', 'Notary Public for Makati City / Commission No. 2026-089 / Until Dec 31, 2027', 'Series of 2026', 'Page No. 95', 'Book No. XII', 3.00, 'chattel_mortgage', '2026-02-05', '2026-02-05', '2027-08-05', 'defaulted', 3);

-- Collateral registry
INSERT INTO `collateral_registry` (`collateral_code`, `loan_doc_id`, `borrower_name`, `collateral_type`, `description`, `serial_plate_no`, `title_deed_no`, `location_address`, `appraised_value`, `appraisal_date`, `appraiser_name`, `lien_status`, `lien_recorded_date`, `lien_registry_no`, `insurance_policy`, `insurance_expiry`, `notes`, `created_by`) VALUES
('COL-2026-001', 1, 'Roberto A. Mendoza', 'vehicle', 'Toyota Hilux 2023 — 4x2 G AT, White', 'ABC 1234', NULL, '123 Rizal Avenue, Makati City', 980000.00, '2026-01-08', 'Philippine Appraisal Corp.', 'active', '2026-01-10', 'CM-2026-MKT-001', 'INS-POL-2026-TY-001', '2027-01-10', 'Vehicle in good condition, no prior liens', 3),
('COL-2026-002', 3, 'ABC Corporation', 'real_estate', 'Commercial Lot — 250 sqm with 2-storey building', NULL, 'TCT No. T-654321', '789 Commercial Drive, Taguig City', 8500000.00, '2026-01-28', 'Santos & Associates Appraisers', 'active', '2026-02-01', 'REM-2026-TGG-045', 'INS-POL-2026-RE-002', '2027-02-01', 'Property has existing tenants, lease disclosed', 3),
('COL-2026-003', 5, 'Michael B. Tan', 'equipment', 'HP Indigo 7900 Digital Press — Brand New Industrial Printing Equipment', 'IND-2026-HP-001', NULL, '567 Bonifacio Avenue, Taguig City', 800000.00, '2026-02-03', 'Industrial Valuers Inc.', 'active', '2026-02-05', 'CM-2026-TGG-012', NULL, NULL, 'Equipment to be delivered and inspected', 3),
('COL-2026-004', NULL, 'Patricia D. Reyes', 'jewelry', '18K Gold Necklace with Diamond Pendant (2.5 carats), Gold Bracelet', NULL, NULL, 'Pledged at Main Branch', 320000.00, '2025-11-15', 'GoldStar Appraisal Center', 'active', '2025-11-20', 'PLG-2025-MKT-089', NULL, NULL, 'Items stored in vault, safe deposit box #12', 3),
('COL-2026-005', 1, 'Roberto A. Mendoza', 'deposit', 'Time Deposit Account — Microfinancial Savings', NULL, NULL, NULL, 50000.00, '2026-01-10', NULL, 'active', '2026-01-10', 'HD-2026-TD-001', NULL, NULL, 'Hold-out on time deposit as additional security', 3);

-- Demand letters
INSERT INTO `demand_letters` (`demand_code`, `loan_doc_id`, `case_id`, `borrower_name`, `borrower_address`, `amount_demanded`, `demand_type`, `letter_body`, `attorney_name`, `sent_date`, `sent_via`, `received_date`, `response_deadline`, `borrower_responded`, `status`, `created_by`) VALUES
('DL-2026-001', 5, NULL, 'Michael B. Tan', '567 Bonifacio Avenue, Taguig City', 535000.00, 'first_notice',
'DEMAND LETTER\n\nFebruary 10, 2026\n\nMR. MICHAEL B. TAN\n567 Bonifacio Avenue\nTaguig City\n\nDear Mr. Tan,\n\nGREETINGS!\n\nWe write on behalf of our client, MICROFINANCIAL MANAGEMENT CORPORATION, to formally demand payment of your outstanding obligation in the amount of FIVE HUNDRED THIRTY-FIVE THOUSAND PESOS (₱535,000.00), inclusive of principal, interest, and penalties.\n\nRecords show that you have failed to pay your monthly amortizations due on January 5, 2026 and February 5, 2026 on your Chattel Mortgage Loan (LD-2026-005).\n\nWe hereby demand that you settle the aforementioned amount within FIFTEEN (15) days from receipt of this letter. Failure to comply shall constrain us to take the necessary legal action to protect our client''s interests, including but not limited to foreclosure of the chattel mortgage.\n\nPlease govern yourself accordingly.\n\nVery truly yours,\n\nATTY. FRANCISCO J. DELA ROSA\nCounsel for Microfinancial Management Corp.',
'Atty. Francisco J. Dela Rosa', '2026-02-10', 'registered_mail', NULL, '2026-02-25', 0, 'sent', 3),

('DL-2026-002', NULL, 1, 'ABC Corporation', '789 Commercial Drive, Taguig City', 2650000.00, 'final_demand',
'FINAL DEMAND WITH NOTICE OF FORECLOSURE\n\nFebruary 1, 2026\n\nABC CORPORATION\nAttn: Mr. Pedro T. Lim, President\n789 Commercial Drive, Taguig City\n\nDear Mr. Lim,\n\nDespite previous demands, your corporation has failed to settle its outstanding obligation of TWO MILLION SIX HUNDRED FIFTY THOUSAND PESOS (₱2,650,000.00).\n\nThis is your FINAL DEMAND. Should you fail to pay within TEN (10) days, we shall institute foreclosure proceedings on the mortgaged property covered by TCT No. T-654321.\n\nThis letter serves as the NOTICE OF DEFAULT required under Section 3 of your Loan Agreement.\n\nATTY. FRANCISCO J. DELA ROSA',
'Atty. Francisco J. Dela Rosa', '2026-02-01', 'personal_service', '2026-02-03', '2026-02-13', 1, 'responded', 3);

-- KYC records
INSERT INTO `kyc_records` (`kyc_code`, `client_name`, `client_type`, `id_type`, `id_number`, `id_expiry`, `tin`, `address`, `occupation`, `source_of_funds`, `risk_rating`, `verification_status`, `verified_by`, `verified_date`, `sanctions_checked`, `pep_checked`, `next_review_date`, `created_by`) VALUES
('KYC-2026-001', 'Roberto A. Mendoza', 'individual', 'PhilSys ID (National ID)', 'PSA-2020-12345', '2031-06-15', '123-456-789-000', '123 Rizal Avenue, Makati City', 'Small Business Owner', 'Business Income — Sari-sari Store', 'low', 'verified', 3, '2026-01-08', 1, 1, '2027-01-08', 3),
('KYC-2026-002', 'Elena G. Fernandez', 'individual', 'Driver''s License', 'DL-N01-234567', '2028-03-20', '987-654-321-000', '456 Mabini Street, San Juan City', 'Public School Teacher', 'Employment Salary', 'low', 'verified', 3, '2026-01-22', 1, 1, '2027-01-22', 3),
('KYC-2026-003', 'ABC Corporation', 'corporate', 'SEC Registration', 'CS201912345', NULL, '555-123-456-000', '789 Commercial Drive, Taguig City', 'Retail & Distribution', 'Business Revenue', 'medium', 'verified', 3, '2026-01-28', 1, 0, '2026-07-28', 3),
('KYC-2026-004', 'Grace S. Aquino', 'individual', 'Passport', 'P1234567A', '2029-11-30', '444-888-222-000', '321 Luna Street, Quezon City', 'Freelance Consultant', 'Professional Fees', 'low', 'verified', 3, '2026-02-08', 1, 1, '2027-02-08', 3),
('KYC-2026-005', 'Michael B. Tan', 'sole_proprietor', 'Company ID / DTI Registration', 'DTI-NCR-2024-56789', NULL, '777-333-111-000', '567 Bonifacio Avenue, Taguig City', 'Printing Business Owner', 'Business Income', 'high', 'under_review', NULL, NULL, 1, 0, NULL, 3);

-- Board resolutions
INSERT INTO `board_resolutions` (`resolution_code`, `title`, `resolution_type`, `meeting_date`, `meeting_type`, `attendees`, `quorum_present`, `resolution_text`, `votes_for`, `votes_against`, `votes_abstain`, `passed`, `effective_date`, `secretary_name`, `chairman_name`, `status`, `created_by`) VALUES
('BR-2026-001', 'Approval of New Branch Expansion — Taguig City', 'operational', '2026-01-15', 'special',
'["Maria C. Santos (President)", "Pedro T. Reyes (VP - Operations)", "Ana M. Garcia (VP - Finance)", "Carlos L. Mendoza (Director)", "Elena R. Cruz (Director)", "Roberto F. Tan (Independent Director)", "Grace P. Lim (Corporate Secretary)"]',
1, 'RESOLVED, as it is hereby resolved, that the Board of Directors of Microfinancial Management Corporation approves the establishment of a new branch office in Taguig City, with an initial capital outlay not exceeding Five Million Pesos (₱5,000,000.00).\n\nRESOLVED FURTHER, that the President, Ms. Maria C. Santos, is hereby authorized to execute all documents necessary for the establishment of said branch.\n\nRESOLVED FINALLY, that the Corporate Secretary is hereby directed to file the necessary reports with the Bangko Sentral ng Pilipinas and the Securities and Exchange Commission.',
6, 0, 1, 1, '2026-02-01', 'Grace P. Lim', 'Maria C. Santos', 'approved', 1),

('BR-2026-002', 'Adoption of Anti-Money Laundering Policy 2026', 'policy', '2026-01-15', 'special',
'["Maria C. Santos (President)", "Pedro T. Reyes (VP - Operations)", "Ana M. Garcia (VP - Finance)", "Carlos L. Mendoza (Director)", "Elena R. Cruz (Director)", "Roberto F. Tan (Independent Director)", "Grace P. Lim (Corporate Secretary)"]',
1, 'RESOLVED, that the Board adopts the updated Anti-Money Laundering and Counter-Terrorism Financing Policy for year 2026 in compliance with R.A. 9160 as amended.\n\nRESOLVED FURTHER, that the Compliance Officer shall ensure dissemination and training within thirty (30) days.',
7, 0, 0, 1, '2026-01-15', 'Grace P. Lim', 'Maria C. Santos', 'filed', 1),

('BR-2026-003', 'Appointment of External Auditor for FY 2026', 'appointment', '2026-02-10', 'regular',
'["Maria C. Santos (President)", "Pedro T. Reyes (VP - Operations)", "Ana M. Garcia (VP - Finance)", "Carlos L. Mendoza (Director)", "Grace P. Lim (Corporate Secretary)"]',
1, 'RESOLVED, that the Board appoints Santos, Garcia & Co., CPAs as the external auditor for fiscal year 2026 with audit fees not exceeding ₱350,000.00.',
5, 0, 0, 1, '2026-02-10', 'Grace P. Lim', 'Maria C. Santos', 'approved', 1);

-- Power of Attorney
INSERT INTO `power_of_attorney` (`poa_code`, `principal_name`, `principal_position`, `agent_name`, `agent_position`, `poa_type`, `scope`, `effective_date`, `expiry_date`, `notarized`, `notary_name`, `notary_date`, `resolution_id`, `status`, `created_by`) VALUES
('POA-2026-001', 'Maria C. Santos', 'President & CEO', 'Pedro T. Reyes', 'VP - Operations', 'special', 'Authority to sign loan agreements, promissory notes, and disclosure statements on behalf of Microfinancial Management Corporation for loan amounts not exceeding ₱1,000,000.00', '2026-01-15', '2026-12-31', 1, 'Atty. Ernesto B. Villareal', '2026-01-15', 1, 'active', 1),
('POA-2026-002', 'Maria C. Santos', 'President & CEO', 'Ana M. Garcia', 'VP - Finance', 'limited', 'Authority to represent Microfinancial Management Corporation in BSP quarterly reporting and compliance submissions for the year 2026', '2026-01-15', '2026-12-31', 1, 'Atty. Ernesto B. Villareal', '2026-01-15', NULL, 'active', 1),
('POA-2026-003', 'Board of Directors', 'Governing Body', 'Atty. Francisco J. Dela Rosa', 'External Legal Counsel', 'special', 'Authority to institute legal action, file cases, and execute settlement agreements on behalf of the corporation for loan recovery cases', '2026-02-01', '2027-01-31', 1, 'Atty. Ernesto B. Villareal', '2026-02-01', 1, 'active', 1);

-- Permits & Licenses
INSERT INTO `permits_licenses` (`permit_code`, `permit_name`, `issuing_body`, `permit_type`, `permit_number`, `issue_date`, `expiry_date`, `renewal_fee`, `status`, `notes`, `created_by`) VALUES
('PL-2026-001', 'BSP Certificate of Authority', 'Bangko Sentral ng Pilipinas', 'bsp_license', 'BSP-CA-2020-1234', '2020-03-15', '2026-03-15', 25000.00, 'pending_renewal', 'Renewal application submitted Feb 2026', 1),
('PL-2026-002', 'SEC Registration Certificate', 'Securities and Exchange Commission', 'sec_registration', 'SEC-REG-CS201912345', '2019-06-01', NULL, 0.00, 'active', 'Perpetual unless revoked', 1),
('PL-2026-003', 'CDA Certificate of Registration', 'Cooperative Development Authority', 'cda_registration', 'CDA-REG-2020-5678', '2020-01-10', NULL, 0.00, 'active', 'Applicable if operating as cooperative', 1),
('PL-2026-004', 'Business Permit — Makati City', 'City of Makati — Business Permits & Licensing Office', 'business_permit', 'BP-MKT-2026-09876', '2026-01-15', '2026-12-31', 45000.00, 'active', 'Renewed January 2026', 1),
('PL-2026-005', 'Fire Safety Inspection Certificate', 'Bureau of Fire Protection — Makati', 'fire_safety', 'FSIC-MKT-2026-0456', '2026-02-01', '2027-01-31', 5000.00, 'active', 'Annual inspection completed', 1),
('PL-2026-006', 'NPC Registration Certificate', 'National Privacy Commission', 'other', 'NPC-REG-PIC-2021-0789', '2021-05-20', NULL, 0.00, 'active', 'Data Processing System registration', 1);

-- Compliance records
INSERT INTO `legal_compliance` (`reference_code`, `requirement`, `regulatory_body`, `category`, `description`, `deadline`, `status`, `risk_level`, `assigned_to`, `next_review_date`, `created_by`) VALUES
('CMP-2026-001', 'BSP Quarterly Report Submission',       'Bangko Sentral ng Pilipinas', 'banking_regulation',      'Submit quarterly microfinancial operations report to BSP',      '2026-03-31', 'in_progress',   'medium', 4, '2026-06-30', 3),
('CMP-2026-002', 'Data Privacy Impact Assessment',        'National Privacy Commission', 'data_privacy',            'Annual DPIA for microfinancial client data processing',         '2026-04-30', 'pending_review', 'high',   1, '2027-04-30', 3),
('CMP-2026-003', 'AML/CTF Compliance Annual Review',      'AMLC',                        'anti_money_laundering',   'Anti-money laundering review for microfinancial transactions',  '2026-06-30', 'in_progress',   'critical', 3, '2027-06-30', 3),
('CMP-2026-004', 'BIR Tax Compliance Filing',             'Bureau of Internal Revenue',  'tax',                     'Annual tax filing for microfinancial institution',              '2026-04-15', 'compliant',     'medium',   4, '2027-04-15', 1),
('CMP-2026-005', 'DOLE Labor Standards Compliance',       'DOLE',                        'labor_law',               'Annual compliance with labor standards and employee benefits',   '2026-05-31', 'compliant',     'low',      2, '2027-05-31', 2);

-- =====================================================================
-- ADDITIONAL LEGAL MANAGEMENT DATA (20 Records)
-- =====================================================================

-- Additional Legal Cases (3 new)
INSERT INTO `legal_cases` (`case_number`, `title`, `case_type`, `description`, `priority`, `status`, `filing_date`, `due_date`, `opposing_party`, `court_venue`, `assigned_lawyer`, `assigned_to`, `department`, `financial_impact`, `created_by`) VALUES
('LC-2026-005', 'Foreclosure Proceedings — Tan Property', 'litigation', 'Judicial foreclosure of chattel mortgage on printing equipment due to loan default by Michael B. Tan (LD-2026-005)', 'high', 'open', '2026-02-15', '2026-08-15', 'Michael B. Tan', 'Metropolitan Trial Court - Taguig', 'Atty. Francisco J. Dela Rosa', 3, 'Legal Department', 535000.00, 3),
('LC-2026-006', 'Unfair Collection Practices Complaint', 'regulatory', 'Complaint filed by borrower alleging harassment by third-party collection agent', 'medium', 'pending_review', '2026-02-08', '2026-05-08', 'Josefina R. Bautista', 'DTI-NCR Mediation Office', 'Atty. Maria Teresa R. Santos', 3, 'Legal Department', 150000.00, 3),
('LC-2026-007', 'Internal Fraud Investigation — Taguig Branch', 'internal_investigation', 'Investigation of suspected fraudulent loan approvals at the Taguig branch involving three fictitious borrower accounts', 'critical', 'in_progress', '2026-02-12', '2026-04-12', NULL, NULL, 'Atty. Cruz', 3, 'Legal Department', 1200000.00, 1);

-- Additional Legal Contracts (3 new)
INSERT INTO `legal_contracts` (`contract_number`, `title`, `contract_type`, `party_name`, `description`, `start_date`, `end_date`, `value`, `status`, `assigned_to`, `created_by`) VALUES
('CON-2026-006', 'Janitorial Services Agreement', 'service', 'CleanPro Maintenance Corp.', 'Daily janitorial and sanitation services for all branch offices', '2026-01-01', '2026-12-31', 960000.00, 'active', 2, 2),
('CON-2026-007', 'Collection Agency Agreement', 'service', 'RecoverAll Collections Inc.', 'Third-party collection services for delinquent microfinance accounts exceeding 90 days past due', '2026-02-01', '2027-01-31', 0.00, 'active', 3, 3),
('CON-2026-008', 'Employment Contract — Branch Manager Taguig', 'employment', 'Ricardo V. Natividad', 'Employment contract for Taguig branch manager position with performance-based incentives', '2026-03-01', '2028-02-28', 720000.00, 'draft', 2, 1);

-- Additional Loan Documentation (3 new)
INSERT INTO `loan_documentation` (`loan_doc_code`, `borrower_name`, `borrower_address`, `loan_amount`, `interest_rate`, `loan_term_months`, `repayment_schedule`, `purpose`, `contract_body`, `attorney_name`, `attorney_prc`, `attorney_ptr`, `attorney_ibp`, `attorney_roll`, `attorney_mcle`, `notary_name`, `notary_commission`, `doc_series_no`, `doc_page_no`, `doc_book_no`, `penalty_rate`, `security_type`, `signed_date`, `effective_date`, `maturity_date`, `status`, `created_by`) VALUES
('LD-2026-006', 'Rosario M. Villanueva', '89 Sampaguita Street, Brgy. Holy Spirit, Quezon City', 180000.00, 2.00, 12, 'monthly', 'Micro-Enterprise — Food Cart Business',
'LOAN AGREEMENT\n\nThis Agreement is entered into this 5th day of February 2026.\n\nBETWEEN:\nMICROFINANCIAL MANAGEMENT CORPORATION ("LENDER")\n\nAND:\nROSARIO M. VILLANUEVA ("BORROWER")\n\nTERMS:\n1. Loan Amount: ₱180,000.00\n2. Interest Rate: 2.00% per month (diminishing balance)\n3. Term: 12 months\n4. Repayment: Monthly amortization\n5. Purpose: Food cart business setup in QC area\n6. Security: Guarantor (spouse — Eduardo P. Villanueva)\n\nThe BORROWER agrees to all terms and conditions set forth herein.\n\n[Signed by both parties]',
'Atty. Maria Teresa R. Santos', 'PRC-0098765', 'PTR No. 2026-005678 / 01-08-2026 / Makati City', 'IBP No. 654321 / QC Chapter / 01-06-2026', 'Roll No. 34567', 'MCLE Compliance No. VII-0054321 / 06-20-2025',
'Atty. Ernesto B. Villareal', 'Notary Public for Makati City / Commission No. 2026-089 / Until Dec 31, 2027', 'Series of 2026', 'Page No. 130', 'Book No. XIII', 3.00, 'guarantor', '2026-02-05', '2026-02-05', '2027-02-05', 'active', 3),

('LD-2026-007', 'Fernando C. Aguilar', '45 Mahogany Lane, BF Homes, Parañaque City', 750000.00, 1.50, 24, 'monthly', 'Agricultural Supply Store Expansion',
'LOAN AND REAL ESTATE MORTGAGE AGREEMENT\n\nExecuted on February 12, 2026.\n\nMICROFINANCIAL MANAGEMENT CORPORATION ("LENDER")\nvs.\nFERNANDO C. AGUILAR ("BORROWER")\n\nThe LENDER grants a loan of ₱750,000.00 secured by Real Estate Mortgage over TCT No. T-112233, a residential lot located at BF Homes, Parañaque City.\n\nInterest: 1.50% monthly, diminishing balance\nTerm: 24 months\nPenalty: 3.00% per month on overdue amounts\n\nSPECIAL CONDITIONS:\n- Property insurance shall be maintained by the Borrower\n- No further encumbrance without written consent of Lender\n\n[Signed by both parties]',
'Atty. Francisco J. Dela Rosa', 'PRC-0045678', 'PTR No. 2026-001234 / 01-05-2026 / Makati City', 'IBP No. 123456 / Makati Chapter / 01-03-2026', 'Roll No. 67890', 'MCLE Compliance No. VII-0012345 / 04-15-2025',
'Atty. Ernesto B. Villareal', 'Notary Public for Makati City / Commission No. 2026-089 / Until Dec 31, 2027', 'Series of 2026', 'Page No. 145', 'Book No. XIII', 3.00, 'real_estate_mortgage', '2026-02-12', '2026-02-12', '2028-02-12', 'pending_signature', 3),

('LD-2026-008', 'Carmen L. Soriano', '12 Acacia Road, Brgy. San Antonio, Pasig City', 100000.00, 2.75, 6, 'monthly', 'Emergency Home Repair After Typhoon',
'PROMISSORY NOTE\n\nDate: February 14, 2026\nPlace: Makati City\n\nI, CARMEN L. SORIANO, promise to pay MICROFINANCIAL MANAGEMENT CORPORATION the sum of ONE HUNDRED THOUSAND PESOS (₱100,000.00) within six (6) months.\n\nInterest: 2.75% per month\nPenalty for late payment: 3.00% per month\n\nThis loan is UNSECURED and extended on the basis of the borrower''s credit standing and employment record.\n\n[Signed]\nCARMEN L. SORIANO',
'Atty. Maria Teresa R. Santos', 'PRC-0098765', 'PTR No. 2026-005678 / 01-08-2026 / Makati City', 'IBP No. 654321 / QC Chapter / 01-06-2026', 'Roll No. 34567', 'MCLE Compliance No. VII-0054321 / 06-20-2025',
NULL, NULL, NULL, NULL, NULL, 3.00, 'unsecured', '2026-02-14', '2026-02-14', '2026-08-14', 'signed', 3);

-- Additional Collateral Registry (3 new)
INSERT INTO `collateral_registry` (`collateral_code`, `loan_doc_id`, `borrower_name`, `collateral_type`, `description`, `serial_plate_no`, `title_deed_no`, `location_address`, `appraised_value`, `appraisal_date`, `appraiser_name`, `lien_status`, `lien_recorded_date`, `lien_registry_no`, `insurance_policy`, `insurance_expiry`, `notes`, `created_by`) VALUES
('COL-2026-006', 7, 'Fernando C. Aguilar', 'real_estate', 'Residential Lot — 120 sqm with single-storey bungalow, BF Homes Subdivision', NULL, 'TCT No. T-112233', '45 Mahogany Lane, BF Homes, Parañaque City', 3200000.00, '2026-02-10', 'Metro Manila Appraisal Corp.', 'pending_release', '2026-02-12', 'REM-2026-PNQ-078', 'INS-POL-2026-RE-003', '2027-02-12', 'Clean title, no prior encumbrances, flood-free zone', 3),
('COL-2026-007', NULL, 'Josefina R. Bautista', 'receivables', 'Assignment of Receivables — Monthly rental income from 3 commercial stalls at Pasig Public Market', NULL, NULL, 'Pasig Public Market, Stalls 12A, 12B, 12C', 450000.00, '2026-01-20', NULL, 'active', '2026-01-25', 'AR-2026-PSG-015', NULL, NULL, 'Monthly rental income of ₱37,500 assigned to Lender for 12 months', 3),
('COL-2026-008', NULL, 'Ricardo V. Santos', 'vehicle', 'Mitsubishi L300 2024 Exceed — Delivery Van, Silver', 'DEF 5678', NULL, '234 Maharlika Highway, Cainta, Rizal', 650000.00, '2026-02-01', 'Philippine Appraisal Corp.', 'active', '2026-02-05', 'CM-2026-RZL-033', 'INS-POL-2026-VH-004', '2027-02-05', 'Vehicle in excellent condition, used for commercial delivery', 3);

-- Additional Demand Letters (2 new)
INSERT INTO `demand_letters` (`demand_code`, `loan_doc_id`, `case_id`, `borrower_name`, `borrower_address`, `amount_demanded`, `demand_type`, `letter_body`, `attorney_name`, `sent_date`, `sent_via`, `received_date`, `response_deadline`, `borrower_responded`, `status`, `created_by`) VALUES
('DL-2026-003', 5, 5, 'Michael B. Tan', '567 Bonifacio Avenue, Taguig City', 545000.00, 'second_notice',
'SECOND DEMAND LETTER\n\nFebruary 15, 2026\n\nMR. MICHAEL B. TAN\n567 Bonifacio Avenue, Taguig City\n\nDear Mr. Tan,\n\nThis is a SECOND DEMAND further to our letter dated February 10, 2026 which remains unheeded.\n\nYour total outstanding obligation has now increased to FIVE HUNDRED FORTY-FIVE THOUSAND PESOS (₱545,000.00) inclusive of accrued penalties.\n\nWe DEMAND payment within FIVE (5) days from receipt hereof, otherwise we shall be constrained to institute foreclosure proceedings on the mortgaged chattel and file the appropriate case in court.\n\nATTY. FRANCISCO J. DELA ROSA\nCounsel for Microfinancial Management Corp.',
'Atty. Francisco J. Dela Rosa', '2026-02-15', 'personal_service', NULL, '2026-02-20', 0, 'sent', 3),

('DL-2026-004', 6, NULL, 'Rosario M. Villanueva', '89 Sampaguita Street, Brgy. Holy Spirit, Quezon City', 195000.00, 'first_notice',
'DEMAND LETTER\n\nFebruary 14, 2026\n\nMS. ROSARIO M. VILLANUEVA\n89 Sampaguita St., Brgy. Holy Spirit, Quezon City\n\nDear Ms. Villanueva,\n\nWe write to remind you that your monthly amortization of ₱16,250.00 due on February 5, 2026 remains unpaid.\n\nKindly settle the overdue amount together with applicable penalties within FIFTEEN (15) days from receipt of this letter to avoid further legal action.\n\nPlease contact our office to discuss possible restructuring options if you are experiencing financial difficulties.\n\nATTY. MARIA TERESA R. SANTOS\nCounsel for Microfinancial Management Corp.',
'Atty. Maria Teresa R. Santos', '2026-02-14', 'registered_mail', NULL, '2026-03-01', 0, 'sent', 3);

-- Additional KYC Records (2 new)
INSERT INTO `kyc_records` (`kyc_code`, `client_name`, `client_type`, `id_type`, `id_number`, `id_expiry`, `tin`, `address`, `occupation`, `source_of_funds`, `risk_rating`, `verification_status`, `verified_by`, `verified_date`, `sanctions_checked`, `pep_checked`, `next_review_date`, `created_by`) VALUES
('KYC-2026-006', 'Rosario M. Villanueva', 'individual', 'PhilSys ID (National ID)', 'PSA-2021-78901', '2032-09-10', '222-333-444-000', '89 Sampaguita Street, Brgy. Holy Spirit, QC', 'Market Vendor / Entrepreneur', 'Business Income — Food Cart', 'low', 'verified', 3, '2026-02-03', 1, 1, '2027-02-03', 3),
('KYC-2026-007', 'Fernando C. Aguilar', 'sole_proprietor', 'Driver''s License', 'DL-N03-567890', '2029-04-18', '888-777-666-000', '45 Mahogany Lane, BF Homes, Parañaque City', 'Agricultural Supply Business Owner', 'Business Revenue — AgriSupply Store', 'medium', 'verified', 3, '2026-02-10', 1, 0, '2026-08-10', 3);

-- Additional Board Resolutions (1 new)
INSERT INTO `board_resolutions` (`resolution_code`, `title`, `resolution_type`, `meeting_date`, `meeting_type`, `attendees`, `quorum_present`, `resolution_text`, `votes_for`, `votes_against`, `votes_abstain`, `passed`, `effective_date`, `secretary_name`, `chairman_name`, `status`, `created_by`) VALUES
('BR-2026-004', 'Amendment to Loan Interest Rate Policy', 'amendment', '2026-02-10', 'regular',
'["Maria C. Santos (President)", "Pedro T. Reyes (VP - Operations)", "Ana M. Garcia (VP - Finance)", "Carlos L. Mendoza (Director)", "Grace P. Lim (Corporate Secretary)"]',
1, 'RESOLVED, that effective March 1, 2026, the maximum allowable interest rate for unsecured microfinance loans shall be reduced from 3.00% to 2.50% per month in compliance with BSP Circular No. 1098.\n\nRESOLVED FURTHER, that existing loans with rates exceeding 2.50% shall be restructured upon request of the borrower.\n\nRESOLVED FINALLY, that the Finance Department shall prepare the revised rate schedule within fifteen (15) days.',
4, 1, 0, 1, '2026-03-01', 'Grace P. Lim', 'Maria C. Santos', 'approved', 1);

-- Additional Permits & Licenses (1 new)
INSERT INTO `permits_licenses` (`permit_code`, `permit_name`, `issuing_body`, `permit_type`, `permit_number`, `issue_date`, `expiry_date`, `renewal_fee`, `status`, `notes`, `created_by`) VALUES
('PL-2026-007', 'Occupancy Permit — Taguig Branch', 'City of Taguig — Building Official', 'occupancy', 'OP-TGG-2026-01234', '2026-02-01', '2031-01-31', 15000.00, 'active', 'Issued for new Taguig branch office space, 2nd Floor Unit 201-202, BGC Corporate Center', 1);

-- Additional Compliance Records (2 new)
INSERT INTO `legal_compliance` (`reference_code`, `requirement`, `regulatory_body`, `category`, `description`, `deadline`, `status`, `risk_level`, `assigned_to`, `next_review_date`, `created_by`) VALUES
('CMP-2026-006', 'Consumer Protection Compliance Audit', 'BSP Consumer Protection Department', 'consumer_protection', 'Annual review of fair lending practices, disclosure requirements, and complaint handling procedures', '2026-07-31', 'pending_review', 'high', 3, '2027-07-31', 3),
('CMP-2026-007', 'SEC Annual Reportorial Requirements', 'Securities and Exchange Commission', 'banking_regulation', 'Submission of General Information Sheet (GIS), Audited Financial Statements, and Annual Report', '2026-04-30', 'in_progress', 'medium', 4, '2027-04-30', 1);

-- Visitors
INSERT INTO `visitors` (`visitor_code`, `first_name`, `last_name`, `email`, `phone`, `company`, `id_type`, `id_number`, `visit_count`) VALUES
('VIS-001', 'Roberto',  'Mendoza',   'r.mendoza@abccorp.com',    '09171234567', 'ABC Corporation',         'government_id',  'PSA-2020-12345', 3),
('VIS-002', 'Patricia', 'Lim',       'p.lim@techserv.com',       '09182345678', 'TechServ Solutions Inc.', 'company_id',     'TS-2024-089',    5),
('VIS-003', 'Michael',  'Tan',       'm.tan@ruralbanktaguig.com','09193456789', 'Rural Bank of Taguig',    'company_id',     'RBT-2025-112',   2),
('VIS-004', 'Elena',    'Fernandez', 'e.fernandez@gmail.com',    '09204567890', NULL,                       'drivers_license','DL-N01-234567',  1),
('VIS-005', 'David',    'Cruz',      'd.cruz@safeguard.com',     '09215678901', 'SafeGuard Security Agency','company_id',    'SG-2025-045',    8);

-- Visitor logs
INSERT INTO `visitor_logs` (`visit_code`, `visitor_id`, `host_user_id`, `host_name`, `host_department`, `purpose`, `purpose_details`, `check_in_time`, `check_out_time`, `badge_number`, `status`) VALUES
('VL-2026-0001', 1, 3, 'Juan Dela Cruz',       'Legal Department', 'meeting',      'Discussion on loan default case LC-2026-001',  '2026-02-15 09:30:00', '2026-02-15 11:45:00', 'B-001', 'checked_out'),
('VL-2026-0002', 2, 1, 'System Administrator', 'IT Department',    'maintenance',  'Server maintenance and software updates',       '2026-02-15 08:00:00', '2026-02-15 17:00:00', 'B-002', 'checked_out'),
('VL-2026-0003', 3, 3, 'Juan Dela Cruz',       'Legal Department', 'consultation', 'Partnership agreement review discussion',       '2026-02-15 14:00:00', NULL,                   'B-003', 'checked_in'),
('VL-2026-0004', 4, 4, 'Ana Reyes',            'Finance',          'consultation', 'Personal loan application inquiry',              '2026-02-16 10:00:00', NULL,                   NULL,    'pre_registered'),
('VL-2026-0005', 5, 2, 'Maria Santos',         'Administration',   'inspection',   'Monthly security inspection rounds',             '2026-02-15 07:00:00', '2026-02-15 08:30:00', 'B-004', 'checked_out');

-- Pre-registrations
INSERT INTO `visitor_preregistrations` (`prereg_code`, `visitor_name`, `visitor_email`, `visitor_phone`, `visitor_company`, `host_user_id`, `purpose`, `expected_date`, `expected_time`, `status`) VALUES
('PR-2026-001', 'Elena Fernandez',  'e.fernandez@gmail.com',     '09204567890', NULL,                    4, 'Loan application inquiry',              '2026-02-16', '10:00:00', 'approved'),
('PR-2026-002', 'Mark Villanueva',  'm.villanueva@bsp.gov.ph',   '09226789012', 'Bangko Sentral ng Pilipinas', 1, 'BSP Audit preliminary visit',     '2026-02-20', '09:00:00', 'pending'),
('PR-2026-003', 'Grace Aquino',     'g.aquino@npc.gov.ph',       '09237890123', 'National Privacy Commission', 3, 'Data privacy compliance check',   '2026-02-22', '13:00:00', 'pending');

-- Notifications
INSERT INTO `notifications` (`user_id`, `module`, `title`, `message`, `link`) VALUES
(1, 'facilities', 'New Reservation Request',         'Training Hall reservation pending for AML Compliance Training on Feb 17.',                 '/modules/facilities/'),
(1, 'legal',      'Compliance Deadline Approaching',  'BSP Quarterly Report due by March 31, 2026. Current status: In Progress.',                '/modules/legal/'),
(1, 'visitors',   'Pre-registration Pending Approval','BSP representative Mark Villanueva pre-registered for Feb 20 visit. Needs approval.',    '/modules/visitors/'),
(2, 'documents',  'Document OCR Completed',           'Board Resolution No. 2026-001 has been queued for OCR processing.',                       '/modules/documents/'),
(3, 'legal',      'Case Update Required',             'Loan Default Recovery case LC-2026-001 hearing scheduled. Update case status.',           '/modules/legal/'),
(4, 'visitors',   'Visitor Arriving Tomorrow',        'Elena Fernandez pre-registered for loan inquiry on Feb 16 at 10:00 AM.',                  '/modules/visitors/');

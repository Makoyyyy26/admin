-- =====================================================================
-- MIGRATION: Document Management Submodules Enhancement
-- Adds indexes and columns to support the 5 document submodules:
--   1. Secure Storage
--   2. OCR Scanning
--   3. Version Control
--   4. Archiving
--   5. Access Control
-- =====================================================================
-- Run this AFTER the main administrative.sql if tables already exist.
-- Safe to run multiple times (IF NOT EXISTS / ADD COLUMN IF NOT EXISTS).
-- =====================================================================

USE `administrative`;

-- ─── Secure Storage: Index on confidentiality for fast filtering ───
CREATE INDEX IF NOT EXISTS `idx_confidentiality` ON `documents` (`confidentiality`);

-- ─── OCR Scanning: Index on ocr_status for queue lookups ───
CREATE INDEX IF NOT EXISTS `idx_ocr_status` ON `documents` (`ocr_status`);

-- ─── Archiving: Index on archived_at and retained_at ───
CREATE INDEX IF NOT EXISTS `idx_archived_at` ON `documents` (`archived_at`);
CREATE INDEX IF NOT EXISTS `idx_retained_at` ON `documents` (`retained_at`);

-- ─── Access Control: Index on expires_at for expiration checks ───
CREATE INDEX IF NOT EXISTS `idx_access_expires` ON `document_access` (`expires_at`);

-- ─── OCR Queue: Unique constraint to prevent duplicate queue entries ───
-- Already handled by ON DUPLICATE KEY in the API, but add index for lookups
CREATE INDEX IF NOT EXISTS `idx_ocr_doc` ON `ocr_queue` (`document_id`, `status`);

-- ─── Sample document categories (if empty) ───
INSERT IGNORE INTO `document_categories` (`category_id`, `name`, `code`, `description`, `icon`, `sort_order`, `is_active`) VALUES
(1, 'Memos & Circulars',      'CAT-MEMO',    'Internal memos and circulars',           '📝', 1, 1),
(2, 'Contracts & Agreements',  'CAT-CONTRACT','Legal contracts and agreements',          '📋', 2, 1),
(3, 'Financial Reports',       'CAT-FINANCE', 'Financial statements and reports',        '💵', 3, 1),
(4, 'HR Documents',            'CAT-HR',      'Human resource documents',                '👥', 4, 1),
(5, 'Policies & Procedures',   'CAT-POLICY',  'Company policies and SOPs',               '📖', 5, 1),
(6, 'Certificates & Permits',  'CAT-CERT',    'Certifications and permits',              '🏅', 6, 1),
(7, 'Invoices & Receipts',     'CAT-INVOICE', 'Billing invoices and receipts',           '🧾', 7, 1),
(8, 'Correspondence',          'CAT-CORR',    'External and internal correspondence',    '✉️', 8, 1);

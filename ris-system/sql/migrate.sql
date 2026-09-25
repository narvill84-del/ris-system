-- Migration for existing RIS System databases.
-- Run after importing the original schema.

SET NAMES utf8mb4;
ALTER TABLE audit_log CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ris_forms CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ris_line_items CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Preserve deletion audit records instead of cascading them away.
ALTER TABLE audit_log DROP FOREIGN KEY audit_log_ibfk_2;
ALTER TABLE audit_log ADD CONSTRAINT audit_log_ibfk_2 FOREIGN KEY (ris_id) REFERENCES ris_forms(id) ON DELETE SET NULL;

-- Additional SQL queries for maintenance and seeding

-- View for RIS Forms with Item Count
CREATE OR REPLACE VIEW ris_forms_with_count AS
SELECT 
    rf.id,
    rf.ris_number,
    rf.office_name,
    rf.ris_date,
    rf.status,
    COUNT(rli.id) as item_count,
    SUM(rli.quantity_requested) as total_requested,
    SUM(rli.quantity_received) as total_received
FROM ris_forms rf
LEFT JOIN ris_line_items rli ON rf.id = rli.ris_id
GROUP BY rf.id, rf.ris_number, rf.office_name, rf.ris_date, rf.status;

-- Sample Data (Optional)
-- INSERT INTO ris_forms (ris_number, office_name, responsibility_center_code, ris_date, purpose, requested_by, requested_by_designation, requested_by_date, approved_by, approved_by_designation, approved_by_date, created_by, status)
-- VALUES ('RIS-2026-06-00001', 'Office of the Mayor', 'OM-001', '2026-06-01', 'Office supplies and materials', 'John Doe', 'Secretary', '2026-06-01', 'Jane Smith', 'Mayor', '2026-06-01', 1, 'DRAFT');

-- Sample Line Items
-- INSERT INTO ris_line_items (ris_id, stock_number, unit, description, quantity_requested, quantity_received, remarks)
-- VALUES (1, 'STK-001', 'BOX', 'Copy Paper A4 70GSM', 10, 0, 'For general office use');

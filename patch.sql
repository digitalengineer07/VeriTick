ALTER TABLE Users ADD COLUMN organizer_code VARCHAR(50) DEFAULT NULL UNIQUE;
ALTER TABLE Users ADD COLUMN linked_organizer_id INT DEFAULT NULL;
UPDATE Users SET organizer_code = CONCAT('ORG-', UPPER(SUBSTRING(MD5(RAND()), 1, 6))) WHERE role = 'admin' AND organizer_code IS NULL;

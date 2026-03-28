ALTER TABLE properties
ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER near_transport;

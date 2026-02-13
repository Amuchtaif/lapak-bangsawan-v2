-- Add coordinate columns to customers table
ALTER TABLE customers 
ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER address,
ADD COLUMN longitude DECIMAL(11, 8) NULL AFTER latitude;

-- Optional: Add index for geo-spatial queries if needed later
ALTER TABLE customers ADD INDEX idx_lat_long (latitude, longitude);

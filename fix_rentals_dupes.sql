-- Fix Double Rental Records - Gravetrack
-- Run these in phpMyAdmin/MySQL Workbench

-- STEP 1: DIAGNOSE (see what dupes exist)
SELECT 
    deceased_id, 
    rental_start, 
    rental_end, 
    COUNT(*) as dupes_count,
    GROUP_CONCAT(rental_id) as duplicate_ids
FROM rentals 
GROUP BY deceased_id, rental_start, rental_end 
HAVING COUNT(*) > 1 
ORDER BY dupes_count DESC;

-- STEP 2: CLEAN DUPES (keeps LOWEST rental_id, safe)
DELETE r1 FROM rentals r1
INNER JOIN rentals r2 
WHERE 
    r1.rental_id > r2.rental_id 
    AND r1.deceased_id = r2.deceased_id 
    AND r1.rental_start = r2.rental_start 
    AND r1.rental_end = r2.rental_end;

-- STEP 3: PREVENT FUTURE (UNIQUE constraint)
ALTER TABLE rentals 
ADD CONSTRAINT unique_rental_period 
UNIQUE KEY unique_rental_period (deceased_id, rental_start);

-- STEP 4: VERIFY FIXED
SELECT COUNT(*) as total_rentals FROM rentals;
SELECT COUNT(*) as unique_periods FROM (SELECT DISTINCT deceased_id, rental_start FROM rentals) t;

-- Run 1→4 in order. payment_monitoring.php will show clean data after.


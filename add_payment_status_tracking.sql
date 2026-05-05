-- SQL Update: Add Payment Status Tracking for 3-Year Rental Periods
-- This update adds triggers and views to automatically track payment status for occupied plots

-- ============================================================
-- UPDATE RENTALS TABLE TRIGGER
-- ============================================================
-- Trigger to automatically create a rental record when a burial record is added
DELIMITER $$
CREATE TRIGGER `after_deceased_insert` AFTER INSERT ON `deceased` FOR EACH ROW
BEGIN
    IF NEW.plot_id IS NOT NULL THEN
        INSERT INTO `rentals` (
            `deceased_id`, 
            `plot_id`, 
            `rental_start`, 
            `rental_end`, 
            `amount`, 
            `status`
        ) VALUES (
            NEW.deceased_id,
            NEW.plot_id,
            CURDATE(),
            DATE_ADD(CURDATE(), INTERVAL 3 YEAR),
            0.00,
            'Unpaid'
        );
    END IF;
END
$$
DELIMITER ;

-- ============================================================
-- CREATE PAYMENT STATUS VIEW
-- ============================================================
-- View to calculate payment status for each plot based on rentals and payments
DROP VIEW IF EXISTS `plot_payment_status_view`;

CREATE VIEW `plot_payment_status_view` AS
SELECT 
    p.`plot_id`,
    p.`block`,
    p.`section`,
    p.`lot`,
    p.`status`,
    CONCAT(p.`block`, ' - ', p.`section`, ' - ', p.`lot`) AS `plot_location`,
    d.`deceased_id`,
    d.`full_name`,
    d.`date_of_burial`,
    r.`rental_id`,
    r.`rental_start`,
    r.`rental_end`,
    r.`amount` AS `rental_amount`,
    COALESCE(SUM(pay.`amount`), 0) AS `total_paid`,
    r.`amount` - COALESCE(SUM(pay.`amount`), 0) AS `amount_remaining`,
    CASE 
        WHEN p.`status` = 'Vacant' THEN 'Vacant'
        WHEN r.`rental_id` IS NULL THEN 'No Rental'
        WHEN COALESCE(SUM(pay.`amount`), 0) >= r.`amount` AND r.`rental_end` >= CURDATE() THEN 'Paid'
        WHEN COALESCE(SUM(pay.`amount`), 0) > 0 AND COALESCE(SUM(pay.`amount`), 0) < r.`amount` THEN 'Partially Paid'
        WHEN r.`rental_end` < CURDATE() AND COALESCE(SUM(pay.`amount`), 0) = 0 THEN 'Overdue'
        WHEN r.`rental_end` < CURDATE() AND COALESCE(SUM(pay.`amount`), 0) > 0 AND COALESCE(SUM(pay.`amount`), 0) < r.`amount` THEN 'Overdue - Partial'
        ELSE 'Unpaid'
    END AS `payment_status`,
    CASE 
        WHEN r.`rental_end` IS NULL THEN NULL
        WHEN r.`rental_end` < CURDATE() THEN DATEDIFF(CURDATE(), r.`rental_end`)
        ELSE NULL
    END AS `days_overdue`
FROM `plots` p
LEFT JOIN `deceased` d ON p.`plot_id` = d.`plot_id`
LEFT JOIN `rentals` r ON d.`deceased_id` = r.`deceased_id`
LEFT JOIN `payments` pay ON r.`rental_id` = pay.`rental_id` AND pay.`status` = 'Paid'
GROUP BY p.`plot_id`, d.`deceased_id`, r.`rental_id`;

-- ============================================================
-- CREATE SUMMARY VIEW FOR CEMETERY MAP
-- ============================================================
-- Simplified view for the cemetery map to show payment status per plot
DROP VIEW IF EXISTS `cemetery_plot_payment_view`;

CREATE VIEW `cemetery_plot_payment_view` AS
SELECT 
    p.`plot_id`,
    p.`block`,
    p.`section`,
    p.`lot`,
    CONCAT(p.`block`, ' - ', p.`section`, ' - ', p.`lot`) AS `plot_location`,
    p.`status` AS `plot_status`,
    CASE 
        WHEN p.`status` = 'Vacant' THEN 'Vacant'
        WHEN NOT EXISTS (
            SELECT 1 FROM `rentals` r WHERE r.`plot_id` = p.`plot_id`
        ) THEN 'Vacant'
        ELSE CASE 
            WHEN COALESCE(SUM(pay.`amount`), 0) >= MAX(r.`amount`) AND MAX(r.`rental_end`) >= CURDATE() THEN 'Paid'
            WHEN COALESCE(SUM(pay.`amount`), 0) > 0 AND COALESCE(SUM(pay.`amount`), 0) < MAX(r.`amount`) THEN 'Partially Paid'
            WHEN MAX(r.`rental_end`) < CURDATE() AND COALESCE(SUM(pay.`amount`), 0) = 0 THEN 'Overdue'
            WHEN MAX(r.`rental_end`) < CURDATE() AND COALESCE(SUM(pay.`amount`), 0) > 0 AND COALESCE(SUM(pay.`amount`), 0) < MAX(r.`amount`) THEN 'Overdue - Partial'
            ELSE 'Unpaid'
        END
    END AS `payment_status`,
    COUNT(d.`deceased_id`) AS `deceased_count`
FROM `plots` p
LEFT JOIN `deceased` d ON p.`plot_id` = d.`plot_id`
LEFT JOIN `rentals` r ON d.`deceased_id` = r.`deceased_id`
LEFT JOIN `payments` pay ON r.`rental_id` = pay.`rental_id` AND pay.`status` = 'Paid'
GROUP BY p.`plot_id`;

-- ============================================================
-- CREATE PAYMENT MONITORING TABLE (Optional: for audit trail)
-- ============================================================
CREATE TABLE IF NOT EXISTS `payment_monitoring` (
  `monitoring_id` int(11) NOT NULL AUTO_INCREMENT,
  `plot_id` int(11) DEFAULT NULL,
  `rental_id` int(11) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `amount_due` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `rental_end_date` date DEFAULT NULL,
  `days_overdue` int(11) DEFAULT NULL,
  `last_checked` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`monitoring_id`),
  KEY `plot_id` (`plot_id`),
  KEY `rental_id` (`rental_id`),
  FOREIGN KEY (`plot_id`) REFERENCES `plots` (`plot_id`) ON DELETE CASCADE,
  FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- CREATE PENALTY CALCULATION TABLE (for overdue management)
-- ============================================================
CREATE TABLE IF NOT EXISTS `payment_penalties` (
  `penalty_id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) DEFAULT NULL,
  `original_amount` decimal(10,2) DEFAULT NULL,
  `penalty_percentage` decimal(5,2) DEFAULT 10.00,
  `penalty_amount` decimal(10,2) DEFAULT NULL,
  `total_due` decimal(10,2) DEFAULT NULL,
  `penalty_date` date DEFAULT CURRENT_DATE,
  `status` enum('Active','Waived','Paid') DEFAULT 'Active',
  PRIMARY KEY (`penalty_id`),
  KEY `rental_id` (`rental_id`),
  FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- STORED PROCEDURE: Update Payment Status (Run periodically)
-- ============================================================
DELIMITER $$
CREATE PROCEDURE `update_payment_status`()
BEGIN
    -- Update rental status based on payment records
    UPDATE `rentals` r
    SET r.`status` = CASE 
        WHEN EXISTS (
            SELECT 1 FROM `payments` p 
            WHERE p.`rental_id` = r.`rental_id` 
            AND p.`status` = 'Paid'
            AND p.`amount` >= r.`amount`
        ) THEN 'Paid'
        WHEN EXISTS (
            SELECT 1 FROM `payments` p 
            WHERE p.`rental_id` = r.`rental_id` 
            AND p.`status` = 'Paid'
        ) THEN 'Partial'
        WHEN r.`rental_end` < CURDATE() THEN 'Overdue'
        ELSE 'Unpaid'
    END
    WHERE r.`rental_id` IS NOT NULL;
    
    -- Auto-generate penalties for overdue rentals (if not already done)
    INSERT INTO `payment_penalties` (
        `rental_id`,
        `original_amount`,
        `penalty_amount`,
        `total_due`,
        `penalty_date`,
        `status`
    )
    SELECT 
        r.`rental_id`,
        r.`amount`,
        r.`amount` * 0.10,
        r.`amount` * 1.10,
        CURDATE(),
        'Active'
    FROM `rentals` r
    WHERE r.`rental_end` < CURDATE()
    AND NOT EXISTS (
        SELECT 1 FROM `payment_penalties` pp 
        WHERE pp.`rental_id` = r.`rental_id` 
        AND pp.`status` IN ('Active', 'Paid')
    );
END
$$
DELIMITER ;

-- ============================================================
-- STORED PROCEDURE: Get Plot Payment Summary
-- ============================================================
DELIMITER $$
CREATE PROCEDURE `get_plot_payment_summary`(IN p_plot_id INT)
BEGIN
    SELECT 
        p.`plot_id`,
        p.`block`,
        p.`section`,
        p.`lot`,
        CONCAT(p.`block`, ' - ', p.`section`, ' - ', p.`lot`) AS `plot_location`,
        d.`full_name`,
        d.`date_of_burial`,
        r.`rental_id`,
        r.`rental_start`,
        r.`rental_end`,
        r.`amount` AS `rental_amount`,
        COALESCE(SUM(pay.`amount`), 0) AS `total_paid`,
        r.`amount` - COALESCE(SUM(pay.`amount`), 0) AS `amount_due`,
        DATEDIFF(CURDATE(), r.`rental_end`) AS `days_overdue`,
        CASE 
            WHEN COALESCE(SUM(pay.`amount`), 0) >= r.`amount` THEN 'Paid'
            WHEN COALESCE(SUM(pay.`amount`), 0) > 0 THEN 'Partially Paid'
            WHEN r.`rental_end` < CURDATE() THEN 'Overdue'
            ELSE 'Unpaid'
        END AS `payment_status`
    FROM `plots` p
    LEFT JOIN `deceased` d ON p.`plot_id` = d.`plot_id`
    LEFT JOIN `rentals` r ON d.`deceased_id` = r.`deceased_id`
    LEFT JOIN `payments` pay ON r.`rental_id` = pay.`rental_id` AND pay.`status` = 'Paid'
    WHERE p.`plot_id` = p_plot_id
    GROUP BY r.`rental_id`;
END
$$
DELIMITER ;

-- ============================================================
-- INSERT SAMPLE DATA: Rentals and Payments for existing burials
-- ============================================================
-- If you have existing deceased records, create rental records for them:
INSERT INTO `rentals` (`deceased_id`, `plot_id`, `rental_start`, `rental_end`, `amount`, `status`)
SELECT 
    d.`deceased_id`,
    d.`plot_id`,
    DATE_SUB(CURDATE(), INTERVAL 2 YEAR),  -- Started 2 years ago
    DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 2 YEAR), INTERVAL 3 YEAR),  -- Ends in 1 year
    5000.00,  -- 3-year rental fee
    'Unpaid'
FROM `deceased` d
WHERE d.`plot_id` IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM `rentals` r WHERE r.`deceased_id` = d.`deceased_id`
);

-- Sample payments (optional - for testing)
-- Uncomment and modify as needed:
/*
INSERT INTO `payments` (`rental_id`, `payment_date`, `amount`, `status`)
SELECT 
    r.`rental_id`,
    CURDATE(),
    2500.00,
    'Paid'
FROM `rentals` r
WHERE NOT EXISTS (
    SELECT 1 FROM `payments` p WHERE p.`rental_id` = r.`rental_id`
);
*/

-- ============================================================
-- VERIFY SETUP
-- ============================================================
-- Run this to verify the views are working:
-- SELECT * FROM `cemetery_plot_payment_view`;
-- SELECT * FROM `plot_payment_status_view`;

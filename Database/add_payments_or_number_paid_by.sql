-- Optional: store OR number and payer on each payment row (for payment_history view / reporting).
-- Run once if your `payments` table does not yet have these columns.

ALTER TABLE `payments`
  ADD COLUMN `or_number` VARCHAR(50) NULL DEFAULT NULL AFTER `status`,
  ADD COLUMN `paid_by` VARCHAR(150) NULL DEFAULT NULL AFTER `or_number`;

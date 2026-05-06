<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';

try {
    $database = new db_connector();
    $db = $database->connect();

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $phase  = isset($_GET['phase'])  ? trim($_GET['phase'])  : '';

    // Build WHERE clause for phase filter
    $where  = [];
    $params = [];

    if (!empty($phase)) {
        switch ($phase) {
            case 'Phase 1':
                // Exclude 'AA' explicitly so it is never treated as Phase 1
                $where[] = "(block REGEXP '^[A-I]$' AND block != 'AA')";
                break;
            case 'Phase 2':
                $where[] = "block REGEXP '^[T-Z]$'";
                break;
            case 'Phase 3':
                // AA plus any other Phase-3 blocks (single-letter beyond Z, etc.)
                $where[] = "(block = 'AA' OR (block NOT REGEXP '^[A-I]$' AND block NOT REGEXP '^[T-Z]$' AND block != 'AA'))";
                // Simpler: just match AA for now
                $where  = [];
                $where[] = "block = 'AA'";
                break;
        }
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    /*
     * FIX: The CASE expression previously evaluated '^[A-I]' before checking
     * block = 'AA', so block 'AA' was incorrectly labelled 'Phase 1' because
     * MySQL's REGEXP '^[A-I]' matches 'AA' (first character is A).
     *
     * Corrected order: exact match for 'AA' → Phase 3 FIRST, then the
     * character-range checks for Phase 1 and Phase 2.
     */
    $query = "SELECT
                p.plot_id,
                p.block,
                p.section,
                p.lot,
                p.type,
                p.status,
                p.date_added,
                CASE
                    WHEN p.block = 'AA'                        THEN 'Phase 3'
                    WHEN p.block REGEXP '^[A-I]$'              THEN 'Phase 1'
                    WHEN p.block REGEXP '^[T-Z]$'              THEN 'Phase 2'
                    ELSE 'Unassigned'
                END as phase,
                COUNT(d.deceased_id) as deceased_count,
                GROUP_CONCAT(d.full_name SEPARATOR ', ') as deceased_names,
                CASE 
                    WHEN p.status = 'Vacant' THEN 'Vacant'
                    WHEN NOT EXISTS (SELECT 1 FROM rentals r WHERE r.plot_id = p.plot_id) THEN 'Vacant'
                    ELSE CASE
                        /* Highest-priority per-plot status from each deceased's latest rental. */
                        WHEN EXISTS (
                            SELECT 1
                            FROM deceased d2
                            INNER JOIN rentals r2 ON r2.deceased_id = d2.deceased_id
                            WHERE d2.plot_id = p.plot_id
                              AND r2.rental_end = (
                                  SELECT MAX(r3.rental_end)
                                  FROM rentals r3
                                  WHERE r3.deceased_id = r2.deceased_id
                              )
                              AND r2.rental_end < CURDATE()
                              AND COALESCE((
                                  SELECT SUM(pay2.amount)
                                  FROM payments pay2
                                  WHERE pay2.rental_id = r2.rental_id
                                    AND pay2.status = 'Paid'
                              ), 0) < (CASE WHEN r2.rental_end < CURDATE() THEN COALESCE(r2.amount, 0) * 1.25 ELSE COALESCE(r2.amount, 0) END)
                        ) THEN 'Overdue'
                        WHEN EXISTS (
                            SELECT 1
                            FROM deceased d2
                            INNER JOIN rentals r2 ON r2.deceased_id = d2.deceased_id
                            WHERE d2.plot_id = p.plot_id
                              AND r2.rental_end = (
                                  SELECT MAX(r3.rental_end)
                                  FROM rentals r3
                                  WHERE r3.deceased_id = r2.deceased_id
                              )
                              AND r2.rental_end >= CURDATE()
                              AND DATEDIFF(r2.rental_end, CURDATE()) <= 14
                              AND COALESCE((
                                  SELECT SUM(pay2.amount)
                                  FROM payments pay2
                                  WHERE pay2.rental_id = r2.rental_id
                                    AND pay2.status = 'Paid'
                              ), 0) < (CASE WHEN r2.rental_end < CURDATE() THEN COALESCE(r2.amount, 0) * 1.25 ELSE COALESCE(r2.amount, 0) END)
                        ) THEN 'Due Soon'
                        WHEN EXISTS (
                            SELECT 1
                            FROM deceased d2
                            INNER JOIN rentals r2 ON r2.deceased_id = d2.deceased_id
                            WHERE d2.plot_id = p.plot_id
                              AND r2.rental_end = (
                                  SELECT MAX(r3.rental_end)
                                  FROM rentals r3
                                  WHERE r3.deceased_id = r2.deceased_id
                              )
                              AND COALESCE((
                                  SELECT SUM(pay2.amount)
                                  FROM payments pay2
                                  WHERE pay2.rental_id = r2.rental_id
                                    AND pay2.status = 'Paid'
                              ), 0) > 0
                              AND COALESCE((
                                  SELECT SUM(pay2.amount)
                                  FROM payments pay2
                                  WHERE pay2.rental_id = r2.rental_id
                                    AND pay2.status = 'Paid'
                              ), 0) < (CASE WHEN r2.rental_end < CURDATE() THEN COALESCE(r2.amount, 0) * 1.25 ELSE COALESCE(r2.amount, 0) END)
                        ) THEN 'Partially Paid'
                        WHEN EXISTS (
                            SELECT 1
                            FROM deceased d2
                            INNER JOIN rentals r2 ON r2.deceased_id = d2.deceased_id
                            WHERE d2.plot_id = p.plot_id
                              AND r2.rental_end = (
                                  SELECT MAX(r3.rental_end)
                                  FROM rentals r3
                                  WHERE r3.deceased_id = r2.deceased_id
                              )
                        )
                        AND NOT EXISTS (
                            SELECT 1
                            FROM deceased d2
                            INNER JOIN rentals r2 ON r2.deceased_id = d2.deceased_id
                            WHERE d2.plot_id = p.plot_id
                              AND r2.rental_end = (
                                  SELECT MAX(r3.rental_end)
                                  FROM rentals r3
                                  WHERE r3.deceased_id = r2.deceased_id
                              )
                              AND COALESCE((
                                  SELECT SUM(pay2.amount)
                                  FROM payments pay2
                                  WHERE pay2.rental_id = r2.rental_id
                                    AND pay2.status = 'Paid'
                              ), 0) < (CASE WHEN r2.rental_end < CURDATE() THEN COALESCE(r2.amount, 0) * 1.25 ELSE COALESCE(r2.amount, 0) END)
                        ) THEN 'Paid'
                        ELSE 'Unpaid'
                    END
                END as payment_status,
                MAX(r.rental_end) as rental_end_date,
                CASE
                    WHEN MAX(r.rental_end) < CURDATE()
                    THEN DATEDIFF(CURDATE(), MAX(r.rental_end))
                    ELSE NULL
                END as days_overdue
              FROM plots p
              LEFT JOIN deceased d ON p.plot_id = d.plot_id
              LEFT JOIN rentals r ON d.deceased_id = r.deceased_id
              LEFT JOIN payments pay ON r.rental_id = pay.rental_id
              $whereClause
              GROUP BY p.plot_id
              ORDER BY p.block, p.section, p.lot";

    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $plots = $stmt->fetchAll();

    // Filter by search term if provided
    if (!empty($search)) {
        $plots = array_filter($plots, function ($plot) use ($search) {
            $s = strtolower($search);
            return strpos(strtolower($plot['block']),         $s) !== false
                || strpos(strtolower($plot['lot']),           $s) !== false
                || strpos(strtolower($plot['deceased_names'] ?? ''), $s) !== false;
        });
        $plots = array_values($plots);
    }

    // Group by phase for convenience
    $plotsByPhase = [
        'Phase 1'    => [],
        'Phase 2'    => [],
        'Phase 3'    => [],
        'Unassigned' => [],
    ];
    foreach ($plots as $plot) {
        $plotsByPhase[$plot['phase']][] = $plot;
    }

    echo json_encode([
        'success'      => true,
        'plots'        => $plots,
        'plotsByPhase' => $plotsByPhase,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
?>
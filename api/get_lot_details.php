<?php
header('Content-Type: application/json');
require_once '../Database/db_connector.php';
require_once __DIR__ . '/payment_record_service.php';
session_start();

try {
    $plot_id = isset($_GET['plot_id']) ? intval($_GET['plot_id']) : 0;

    if ($plot_id === 0) {
        throw new Exception("Plot ID is required");
    }

    $database = new db_connector();
    $db = $database->connect();

    // Get current user role from session
    $userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'Engineer';

    // Get plot information
    $plotQuery = "SELECT * FROM plots WHERE plot_id = :plot_id";
    $plotStmt = $db->prepare($plotQuery);
    $plotStmt->bindParam(':plot_id', $plot_id);
    $plotStmt->execute();
    $plot = $plotStmt->fetch();

    if (!$plot) {
        throw new Exception("Plot not found");
    }

    // Get all deceased records for this plot
    $deceasedQuery = 
    "SELECT
        d.deceased_id,
        d.full_name,
        d.birth_date,
        d.date_of_death,
        d.date_of_burial,
        d.gender,
        d.address,
        d.burial_type,

        c.contact_person,
        c.contact_number,

        r.rental_id,
        r.rental_start,
        r.rental_end,
        r.status AS rental_status,

        CASE
            WHEN r.rental_end < CURDATE() THEN 'Overdue'
            ELSE 'Active'
        END AS computed_status

    FROM deceased d

    LEFT JOIN contacts c 
        ON d.deceased_id = c.deceased_id

    LEFT JOIN rentals r 
        ON r.rental_id = (
            SELECT r2.rental_id
            FROM rentals r2
            WHERE r2.deceased_id = d.deceased_id
            ORDER BY r2.rental_end DESC
            LIMIT 1
        )

    WHERE d.plot_id = :plot_id

    ORDER BY d.date_of_burial DESC";

    $deceasedStmt = $db->prepare($deceasedQuery);
    $deceasedStmt->bindParam(':plot_id', $plot_id);
    $deceasedStmt->execute();
    $deceased_records = $deceasedStmt->fetchAll();

    // Get plot payment ledger
    $plot_payment_ledger = gravetrack_fetch_plot_payment_ledger($db, $plot_id);

    // Generate HTML for modal
    $html = '<div class="plot-details">';
    $html .= '<h4>Plot Information</h4>';
    $html .= '<p><strong>Block:</strong> ' . htmlspecialchars($plot['block']) . '</p>';
    $html .= '<p><strong>Section:</strong> ' . htmlspecialchars($plot['section']) . '</p>';
    $html .= '<p><strong>Lot:</strong> ' . htmlspecialchars($plot['lot']) . '</p>';
    $html .= '<p><strong>Type:</strong> ' . htmlspecialchars($plot['type']) . '</p>';
    $html .= '<p><strong>Status:</strong> <span class="badge badge-' .
             ($plot['status'] === 'Vacant' ? 'success' : 'danger') . '">' .
             htmlspecialchars($plot['status']) . '</span></p>';

    if (count($deceased_records) > 0) {
        $html .= '<hr><h4>Deceased Records (' . count($deceased_records) . ')</h4>';
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-sm">';
        $html .= '<thead><tr><th>Name</th><th>Date of Death</th><th>Contact Person</th><th>Contact Number</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($deceased_records as $record) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($record['full_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($record['date_of_death']) . '</td>';
            $html .= '<td>' . htmlspecialchars($record['contact_person'] ?? 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($record['contact_number'] ?? 'N/A') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
    }
    $html .= '</div>';

    echo json_encode([
        'success' => true,
        'plot' => $plot,
        'deceased_records' => $deceased_records,
        'plot_payment_ledger' => $plot_payment_ledger,
        'html' => $html,
        'userRole' => $userRole
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Combined rental payments + treasurer transactions for this plot (deduped when both exist).
 *
 * @return list<array<string, mixed>>
 */
function gravetrack_fetch_plot_payment_ledger(PDO $db, int $plot_id): array
{
    $hasPayOr = gravetrack_table_has_column($db, 'payments', 'or_number');
    $hasTxOr  = gravetrack_table_has_column($db, 'transactions', 'or_number');
    $hasTxPb  = gravetrack_table_has_column($db, 'transactions', 'paid_by');
    $hasTxDt  = gravetrack_table_has_column($db, 'transactions', 'transaction_date');

    $paySql = "SELECT 'payment' AS ledger_type, pay.payment_id AS ledger_id, pay.payment_date AS occurred_on,
        pay.amount, ";
    if ($hasPayOr) {
        $paySql .= "COALESCE(pay.or_number, '') AS or_number, COALESCE(pay.paid_by, '') AS paid_by, ";
    } else {
        $paySql .= "'' AS or_number, '' AS paid_by, ";
    }
    $paySql .= "d.full_name AS deceased_name
        FROM payments pay
        INNER JOIN rentals r ON pay.rental_id = r.rental_id
        INNER JOIN deceased d ON r.deceased_id = d.deceased_id
        WHERE d.plot_id = :plot_id";

    $txSql = "SELECT 'transaction' AS ledger_type, t.transaction_id AS ledger_id, ";
    if ($hasTxDt) {
        $txSql .= 't.transaction_date AS occurred_on, ';
    } else {
        $txSql .= 'NULL AS occurred_on, ';
    }
    $txSql .= 't.amount, ';
    if ($hasTxOr) {
        $txSql .= "COALESCE(t.or_number, '') AS or_number, ";
        $txSql .= $hasTxPb ? "COALESCE(t.paid_by, '') AS paid_by, " : "'' AS paid_by, ";
    } else {
        $txSql .= "COALESCE(t.name, '') AS or_number, '' AS paid_by, ";
    }
    $txSql .= "d.full_name AS deceased_name
        FROM transactions t
        INNER JOIN deceased d ON t.deceased_id = d.deceased_id
        WHERE d.plot_id = :plot_id";

    $rows = [];
    try {
        $p = $db->prepare($paySql);
        $p->bindParam(':plot_id', $plot_id, PDO::PARAM_INT);
        $p->execute();
        $rows = array_merge($rows, $p->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        // ignore if payments schema differs
    }

    try {
        $t = $db->prepare($txSql);
        $t->bindParam(':plot_id', $plot_id, PDO::PARAM_INT);
        $t->execute();
        $rows = array_merge($rows, $t->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        // ignore
    }

    $merged = [];
    foreach ($rows as $r) {
        $deceased = (string) ($r['deceased_name'] ?? '');
        $on       = substr((string) ($r['occurred_on'] ?? ''), 0, 10);
        $amt      = round((float) ($r['amount'] ?? 0), 2);
        $or       = trim((string) ($r['or_number'] ?? ''));
        $key      = $deceased . '|' . $on . '|' . (string) $amt;

        if (!isset($merged[$key])) {
            $merged[$key] = $r;
            continue;
        }
        $ex = $merged[$key];
        $exOr = trim((string) ($ex['or_number'] ?? ''));
        if ($or !== '' && $exOr === '') {
            $merged[$key] = $r;
        } elseif ($or !== '' && $exOr !== '' && ($r['ledger_type'] ?? '') === 'transaction') {
            $merged[$key] = $r;
        }
    }

    $out = array_values($merged);
    usort($out, function ($a, $b) {
        $da = strtotime((string) ($a['occurred_on'] ?? '')) ?: 0;
        $dbt = strtotime((string) ($b['occurred_on'] ?? '')) ?: 0;
        if ($da === $dbt) {
            return (int) ($b['ledger_id'] ?? 0) - (int) ($a['ledger_id'] ?? 0);
        }
        return $dbt <=> $da;
    });

    return $out;
}
?>

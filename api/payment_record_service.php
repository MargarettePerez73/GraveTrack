<?php
/**
 * Shared logic: insert rental payment + mirror row in transactions (OR number, paid by).
 *
 * @param PDO   $conn
 * @param array $input Keys: rental_id, deceased_id, payment_date, amount;
 *                     optional: or_number, paid_by, plot_id
 * @return array{payment_id: string|int}
 * @throws Exception
 */
function gravetrack_record_rental_payment(PDO $conn, array $input): array
{
    $rental_id     = (int) $input['rental_id'];
    $deceased_id   = (int) $input['deceased_id'];
    $payment_date  = $input['payment_date'];
    $amount        = (float) $input['amount'];
    $or_number     = isset($input['or_number']) ? trim((string) $input['or_number']) : '';
    $paid_by       = isset($input['paid_by']) ? trim((string) $input['paid_by']) : '';
    $plot_id       = isset($input['plot_id']) ? (int) $input['plot_id'] : null;

    $checkSql = "
        SELECT r.rental_id, r.amount, r.rental_start, r.rental_end, r.deceased_id, r.plot_id
        FROM rentals r
        WHERE r.rental_id = :rental_id AND r.deceased_id = :deceased_id
    ";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':rental_id', $rental_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':deceased_id', $deceased_id, PDO::PARAM_INT);
    $checkStmt->execute();
    $rental = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        throw new Exception('Invalid rental period or deceased ID');
    }

    if (!$plot_id && !empty($rental['plot_id'])) {
        $plot_id = (int) $rental['plot_id'];
    }

    $user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

    $insertPayment = gravetrack_build_payments_insert($conn, $or_number, $paid_by);
    $insertPayment['stmt']->bindValue(':rental_id', $rental_id, PDO::PARAM_INT);
    $insertPayment['stmt']->bindValue(':payment_date', $payment_date, PDO::PARAM_STR);
    $insertPayment['stmt']->bindValue(':amount', $amount);
    if ($insertPayment['has_or']) {
        $insertPayment['stmt']->bindValue(':or_number', $or_number !== '' ? $or_number : null);
        $insertPayment['stmt']->bindValue(':paid_by', $paid_by !== '' ? $paid_by : null);
    }
    if (!$insertPayment['stmt']->execute()) {
        throw new Exception('Failed to insert payment record');
    }

    $payment_id = $conn->lastInsertId();

    $updateSql = 'UPDATE rentals SET status = \'Paid\' WHERE rental_id = :rental_id';
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindParam(':rental_id', $rental_id, PDO::PARAM_INT);
    $updateStmt->execute();

    gravetrack_insert_payment_transaction_row($conn, [
        'payment_date' => $payment_date,
        'amount'       => $amount,
        'or_number'    => $or_number,
        'paid_by'      => $paid_by,
        'user_id'      => $user_id,
        'deceased_id'  => $deceased_id,
        'plot_id'      => $plot_id,
    ]);

    return ['payment_id' => $payment_id];
}

/**
 * @return array{stmt: \PDOStatement, has_or: bool}
 */
function gravetrack_build_payments_insert(PDO $conn, string $or_number, string $paid_by): array
{
    $hasOr = gravetrack_table_has_column($conn, 'payments', 'or_number')
        && gravetrack_table_has_column($conn, 'payments', 'paid_by');

    if ($hasOr) {
        $sql = 'INSERT INTO payments (
            rental_id, payment_date, amount, status, or_number, paid_by
        ) VALUES (
            :rental_id, :payment_date, :amount, \'Paid\', :or_number, :paid_by
        )';
    } else {
        $sql = 'INSERT INTO payments (
            rental_id, payment_date, amount, status
        ) VALUES (
            :rental_id, :payment_date, :amount, \'Paid\'
        )';
    }

    return [
        'stmt'   => $conn->prepare($sql),
        'has_or' => $hasOr,
    ];
}

function gravetrack_table_has_column(PDO $conn, string $table, string $column): bool
{
    static $cache = [];

    $key = $table . '.' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $conn->prepare(
        'SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1'
    );
    $stmt->bindValue(':t', $table);
    $stmt->bindValue(':c', $column);
    $stmt->execute();

    $cache[$key] = (bool) $stmt->fetchColumn();

    return $cache[$key];
}

function gravetrack_insert_payment_transaction_row(PDO $conn, array $p): void
{
    $payment_date = $p['payment_date'];
    $amount       = $p['amount'];
    $or_number    = $p['or_number'] ?? '';
    $paid_by      = $p['paid_by'] ?? '';
    $user_id      = $p['user_id'];
    $deceased_id  = $p['deceased_id'];
    $plot_id      = $p['plot_id'];

    $hasExtended = gravetrack_table_has_column($conn, 'transactions', 'or_number')
        && gravetrack_table_has_column($conn, 'transactions', 'paid_by')
        && gravetrack_table_has_column($conn, 'transactions', 'transaction_date');

    if ($hasExtended) {
        $hasPlot = gravetrack_table_has_column($conn, 'transactions', 'plot_id');
        if ($hasPlot) {
            $sql = 'INSERT INTO transactions (
                transaction_date, or_number, paid_by, amount, type, user_id, deceased_id, plot_id
            ) VALUES (
                :transaction_date, :or_number, :paid_by, :amount, \'Payment\', :user_id, :deceased_id, :plot_id
            )';
        } else {
            $sql = 'INSERT INTO transactions (
                transaction_date, or_number, paid_by, amount, type, user_id, deceased_id
            ) VALUES (
                :transaction_date, :or_number, :paid_by, :amount, \'Payment\', :user_id, :deceased_id
            )';
        }
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':transaction_date', $payment_date, PDO::PARAM_STR);
            $stmt->bindValue(':or_number', $or_number !== '' ? $or_number : null);
            $stmt->bindValue(':paid_by', $paid_by !== '' ? $paid_by : null);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':user_id', $user_id, $user_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindParam(':deceased_id', $deceased_id, PDO::PARAM_INT);
            if ($hasPlot) {
                $stmt->bindValue(':plot_id', $plot_id ?: null, $plot_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
            }
            $stmt->execute();
            return;
        } catch (\PDOException $e) {
            // fall through to legacy insert
        }
    }

    $label = trim($or_number . ($or_number && $paid_by ? ' — ' : '') . $paid_by);
    if ($label === '') {
        $label = 'Payment';
    }

    $sql = 'INSERT INTO transactions (
        name, transaction_date, amount, type, user_id, deceased_id
    ) VALUES (
        :name, :transaction_date, :amount, \'Payment\', :user_id, :deceased_id
    )';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $label, PDO::PARAM_STR);
    $stmt->bindValue(':transaction_date', $payment_date, PDO::PARAM_STR);
    $stmt->bindValue(':amount', $amount);
    $stmt->bindValue(':user_id', $user_id, $user_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindParam(':deceased_id', $deceased_id, PDO::PARAM_INT);
    $stmt->execute();
}

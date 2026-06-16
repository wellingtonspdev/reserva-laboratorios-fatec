<?php
/**
 * Extend development bookings from recurring rules up to a target date.
 *
 * Usage:
 *   php seed/extend_test_bookings.php 2026-07-31
 */

$targetDate = $argv[1] ?? getenv('EXTEND_SEED_UNTIL') ?: '2026-07-31';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $targetDate)) {
    fwrite(STDERR, "Data-alvo invalida: {$targetDate}. Use YYYY-MM-DD.\n");
    exit(1);
}

$configPath = __DIR__ . '/../local/config.php';
if (!file_exists($configPath)) {
    fwrite(STDERR, "local/config.php nao encontrado. Gere a configuracao antes de estender o seed.\n");
    exit(1);
}

if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__ . '/../crbs-core/system/');
}

$localConfig = require $configPath;
$dbConfig = $localConfig['database'] ?? [];

$host = $dbConfig['hostname'] ?? getenv('DB_HOST') ?: 'db';
$port = (int)($dbConfig['port'] ?? getenv('DB_PORT') ?: 3306);
$user = $dbConfig['username'] ?? getenv('DB_USER') ?: 'classroombookings';
$password = $dbConfig['password'] ?? getenv('DB_PASSWORD') ?: '';
$database = $dbConfig['database'] ?? getenv('DB_NAME') ?: 'classroombookings';

$mysqli = mysqli_init();
mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

if (!$mysqli->real_connect($host, $user, $password, $database, $port)) {
    fwrite(STDERR, "Falha ao conectar ao banco: " . mysqli_connect_error() . "\n");
    exit(1);
}

$mysqli->set_charset('utf8mb4');

$stats = [
    'dates_inserted' => 0,
    'bookings_inserted' => 0,
    'bookings_skipped' => 0,
];

$session = fetch_one($mysqli, 'SELECT session_id, date_start, date_end FROM sessions ORDER BY session_id LIMIT 1');
if (!$session) {
    fwrite(STDERR, "Nenhuma sessao encontrada. Importe o seed base antes de estender agendamentos.\n");
    exit(1);
}

$target = new DateTimeImmutable($targetDate);
$sessionEnd = new DateTimeImmutable($session['date_end']);
if ($sessionEnd < $target) {
    $stmt = $mysqli->prepare('UPDATE sessions SET date_end = ? WHERE session_id = ?');
    $sid = (int)$session['session_id'];
    $stmt->bind_param('si', $targetDate, $sid);
    $stmt->execute();
}

$maxDateRow = fetch_one($mysqli, 'SELECT MAX(`date`) AS max_date FROM dates');
$startDate = $maxDateRow && $maxDateRow['max_date']
    ? (new DateTimeImmutable($maxDateRow['max_date']))->modify('+1 day')
    : new DateTimeImmutable($session['date_start']);

if ($startDate <= $target) {
    $insertDate = $mysqli->prepare(
        'INSERT IGNORE INTO dates (`date`, weekday, session_id, week_id, holiday_id) VALUES (?, ?, ?, 1, NULL)'
    );
    $sid = (int)$session['session_id'];

    for ($date = $startDate; $date <= $target; $date = $date->modify('+1 day')) {
        $dateString = $date->format('Y-m-d');
        $weekday = (int)$date->format('N');
        $insertDate->bind_param('sii', $dateString, $weekday, $sid);
        $insertDate->execute();
        $stats['dates_inserted'] += $insertDate->affected_rows > 0 ? 1 : 0;
    }
}

$firstBookingDateRow = fetch_one(
    $mysqli,
    "SELECT COALESCE(MAX(`date`), (SELECT date_start FROM sessions ORDER BY session_id LIMIT 1)) AS max_date FROM bookings"
);
$firstBookingDate = (new DateTimeImmutable($firstBookingDateRow['max_date']))->modify('+1 day');
if ($firstBookingDate < $startDate) {
    $firstBookingDate = $startDate;
}

$repeats = $mysqli->query(
    'SELECT repeat_id, session_id, period_id, room_id, user_id, department_id, weekday, status, notes, created_by, updated_by ' .
    'FROM bookings_repeat WHERE status = 10 ORDER BY repeat_id'
);

$exists = $mysqli->prepare('SELECT booking_id FROM bookings WHERE repeat_id = ? AND `date` = ? LIMIT 1');
$insertBooking = $mysqli->prepare(
    'INSERT INTO bookings ' .
    '(repeat_id, session_id, period_id, room_id, user_id, department_id, `date`, status, notes, cancel_reason, cancelled_at, cancelled_by, created_at, created_by, updated_at, updated_by) ' .
    'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, NOW(), ?, NOW(), ?)'
);

while ($repeat = $repeats->fetch_assoc()) {
    $weekday = (int)$repeat['weekday'];

    for ($date = $firstBookingDate; $date <= $target; $date = $date->modify('+1 day')) {
        if ((int)$date->format('N') !== $weekday) {
            continue;
        }

        $dateString = $date->format('Y-m-d');
        $repeatId = (int)$repeat['repeat_id'];
        $exists->bind_param('is', $repeatId, $dateString);
        $exists->execute();
        $existsResult = $exists->get_result();

        if ($existsResult->num_rows > 0) {
            $stats['bookings_skipped']++;
            continue;
        }

        $sessionId = (int)$repeat['session_id'];
        $periodId = (int)$repeat['period_id'];
        $roomId = (int)$repeat['room_id'];
        $userId = nullable_int($repeat['user_id']);
        $departmentId = nullable_int($repeat['department_id']);
        $status = (int)$repeat['status'];
        $notes = $repeat['notes'];
        $createdBy = nullable_int($repeat['created_by']);
        $updatedBy = nullable_int($repeat['updated_by']);

        $insertBooking->bind_param(
            'iiiiiisisii',
            $repeatId,
            $sessionId,
            $periodId,
            $roomId,
            $userId,
            $departmentId,
            $dateString,
            $status,
            $notes,
            $createdBy,
            $updatedBy
        );
        $insertBooking->execute();
        $stats['bookings_inserted'] += $insertBooking->affected_rows > 0 ? 1 : 0;
    }
}

echo "Seed de teste estendido ate {$targetDate}.\n";
echo "  Datas inseridas: {$stats['dates_inserted']}\n";
echo "  Agendamentos inseridos: {$stats['bookings_inserted']}\n";
echo "  Agendamentos ja existentes ignorados: {$stats['bookings_skipped']}\n";

function fetch_one(mysqli $mysqli, string $sql): ?array
{
    $result = $mysqli->query($sql);
    if (!$result) {
        throw new RuntimeException($mysqli->error);
    }

    $row = $result->fetch_assoc();
    return $row ?: null;
}

function nullable_int($value): ?int
{
    return $value === null ? null : (int)$value;
}

<?php
/**
 * ClassroomBookings FATEC — Seed Generator
 *
 * Lê o CSV de exportação e gera seed.sql completo na ordem correta de FKs.
 *
 * Uso: php generate_seed.php
 * Output: seed/seed.sql
 */

$csv_candidates = [
    getenv('SEED_CSV_PATH') ?: null,
    __DIR__ . '/importacao-privada.csv',
];
$room_details_path = __DIR__ . '/salas_labs_detalhes_mockados.json';
$output_path = __DIR__ . '/seed.sql';

$csv_path = null;
foreach ($csv_candidates as $candidate) {
    if (!empty($candidate) && file_exists($candidate)) {
        $csv_path = $candidate;
        break;
    }
}

if ($csv_path === null) {
    die("CSV não encontrado: {$csv_path}\n");
}

$seed_password = getenv('SEED_USER_PASSWORD');
if (empty($seed_password)) {
    die("Defina SEED_USER_PASSWORD para gerar usuarios de seed.\n");
}

if (!file_exists($room_details_path)) {
    die("JSON de detalhes das salas nao encontrado: {$room_details_path}\n");
}

$room_details = load_room_details($room_details_path);

// ============================================================================
// 1. LER CSV E EXTRAIR ENTIDADES ÚNICAS
// ============================================================================

echo "Lendo CSV...\n";

$f = fopen($csv_path, 'r');
$header = fgetcsv($f);
$rows = [];

while (($row = fgetcsv($f)) !== false) {
    if (count($row) < 20) continue;
    $rows[] = $row;
}
fclose($f);

echo "  " . count($rows) . " registros lidos.\n";

// Entidades únicas
$schedules = [];
$room_groups = [];
$rooms_map = []; // room_name => room_group_name
$periods_map = []; // "period_name|schedule_name" => [start, end]
$users_map = []; // username => [displayname, department]
$departments = [];
$recurring_groups = []; // recurring_id => first row data

foreach ($rows as $row) {
    $schedules[$row[9]] = true;
    $room_groups[$row[13]] = true;
    $rooms_map[$row[12]] = $row[13];
    $pkey = $row[8] . '|' . $row[9];
    if (!isset($periods_map[$pkey])) {
        $periods_map[$pkey] = ['name' => $row[8], 'schedule' => $row[9], 'start' => $row[10], 'end' => $row[11]];
    }
    if (!empty($row[15]) && !isset($users_map[$row[15]])) {
        $users_map[$row[15]] = ['displayname' => $row[16], 'department' => $row[17]];
    }
    if (!empty($row[19]) && !isset($users_map[$row[19]])) {
        $users_map[$row[19]] = ['displayname' => $row[19], 'department' => ''];
    }
    if (!empty($row[21]) && !isset($users_map[$row[21]])) {
        $users_map[$row[21]] = ['displayname' => $row[21], 'department' => ''];
    }
    if (!empty($row[17])) {
        $departments[$row[17]] = true;
    }
    // Recurring groups
    if ($row[2] === 'Recurring' && !empty($row[1]) && !isset($recurring_groups[$row[1]])) {
        $recurring_groups[$row[1]] = $row;
    }
}

$missing_room_details = array_values(array_diff(array_keys($rooms_map), array_keys($room_details)));
if (!empty($missing_room_details)) {
    die("Detalhes mockados ausentes para salas: " . implode(', ', $missing_room_details) . "\n");
}

echo "  " . count($room_details) . " detalhes de salas carregados.\n";

// ============================================================================
// 2. ATRIBUIR IDs SEQUENCIAIS
// ============================================================================

// Weeks
$week_ids = ['Normal' => 1];

// Schedules
$schedule_ids = [];
$sid = 1;
foreach (array_keys($schedules) as $name) {
    $schedule_ids[$name] = $sid++;
}

// Room Groups
$rg_ids = [];
$rgid = 1;
foreach (array_keys($room_groups) as $name) {
    $rg_ids[$name] = $rgid++;
}

// Departments
$dept_ids = [];
$did = 1;
foreach (array_keys($departments) as $name) {
    $dept_ids[$name] = $did++;
}

// Rooms
$room_ids = [];
$rid = 1;
ksort($rooms_map);
foreach (array_keys($rooms_map) as $name) {
    $room_ids[$name] = $rid++;
}

$room_fields = [
    1 => ['name' => 'Capacidade', 'type' => 'TEXT'],
    2 => ['name' => 'Computadores', 'type' => 'TEXT'],
    3 => ['name' => 'Tipo de ambiente', 'type' => 'TEXT'],
    4 => ['name' => 'Recursos', 'type' => 'TEXT'],
    5 => ['name' => 'Equipamentos', 'type' => 'TEXT'],
    6 => ['name' => 'Softwares', 'type' => 'TEXT'],
    7 => ['name' => 'Restricoes de uso', 'type' => 'TEXT'],
    8 => ['name' => 'Manutencao', 'type' => 'TEXT'],
    9 => ['name' => 'Fonte dos detalhes', 'type' => 'TEXT'],
];

// Periods
$period_ids = [];
$pid = 1;
ksort($periods_map);
foreach (array_keys($periods_map) as $key) {
    $period_ids[$key] = $pid++;
}

// Session
$session_id = 1;

// Users — definir roles
$admin_users = array_filter(array_map('trim', explode(',', getenv('SEED_ADMIN_USERS') ?: 'aux_coord,aux_doc')));
$role_admin = 1;
$role_teacher = 2;

$user_ids = [];
$uid = 1;
ksort($users_map);
foreach (array_keys($users_map) as $username) {
    $user_ids[$username] = $uid++;
}

// Recurring
$repeat_ids = [];
$rpid = 1;
foreach (array_keys($recurring_groups) as $rec_id) {
    $repeat_ids[$rec_id] = $rpid++;
}

// ============================================================================
// 3. GERAR SQL
// ============================================================================

echo "Gerando seed.sql...\n";

$sql = [];
$sql[] = "-- ==========================================================================";
$sql[] = "-- ClassroomBookings FATEC — Seed Data";
$sql[] = "-- Gerado em: " . date('Y-m-d H:i:s');
$sql[] = "-- Fonte: CSV privado informado via SEED_CSV_PATH";
$sql[] = "-- ==========================================================================";
$sql[] = "";
$sql[] = "SET NAMES utf8mb4;";
$sql[] = "SET time_zone = '+00:00';";
$sql[] = "SET foreign_key_checks = 0;";
$sql[] = "SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';";
$sql[] = "";

// --- WEEKS ---
$sql[] = "-- ========== WEEKS ==========";
$sql[] = "INSERT INTO `weeks` (`week_id`, `name`, `fgcol`, `bgcol`, `icon`) VALUES";
$sql[] = "(1, 'Normal', '', '71AAE3', NULL);";
$sql[] = "";

// --- SCHEDULES ---
$sql[] = "-- ========== SCHEDULES ==========";
$vals = [];
foreach ($schedule_ids as $name => $id) {
    $vals[] = "({$id}, 'periods', " . esc($name) . ", NULL)";
}
$sql[] = "INSERT INTO `schedules` (`schedule_id`, `type`, `name`, `description`) VALUES";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- AUTH PERMISSIONS ---
$sql[] = "-- ========== AUTH PERMISSIONS ==========";
$sql[] = "INSERT INTO `auth_permissions` (`permission_id`, `name`) VALUES";
$perms = [
    [24, 'book_recur.cancel_other_booking'], [22, 'book_recur.create'], [23, 'book_recur.edit_other_booking'],
    [26, 'book_recur.set_department'], [25, 'book_recur.set_user'], [28, 'book_recur.view_other_notes'],
    [27, 'book_recur.view_other_users'], [17, 'book_single.cancel_other_booking'], [15, 'book_single.create'],
    [16, 'book_single.edit_other_booking'], [19, 'book_single.set_department'], [18, 'book_single.set_user'],
    [21, 'book_single.view_other_notes'], [20, 'book_single.view_other_users'], [14, 'room.view'],
    [4, 'setup.authentication'], [5, 'setup.departments'], [6, 'setup.roles'], [7, 'setup.rooms'],
    [8, 'setup.rooms_acl'], [9, 'setup.schedules'], [10, 'setup.sessions'], [11, 'setup.settings'],
    [12, 'setup.timetable_weeks'], [13, 'setup.users'], [1, 'system.bypass_maintenance_mode'],
    [2, 'system.export_bookings'], [3, 'system.view_all_sessions'],
];
$vals = [];
foreach ($perms as $p) $vals[] = "({$p[0]}, '{$p[1]}')";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- AUTH ROLES ---
$sql[] = "-- ========== AUTH ROLES ==========";
$sql[] = "INSERT INTO `auth_roles` (`role_id`, `name`, `description`, `max_active_bookings`, `range_min`, `range_max`, `recur_max_instances`) VALUES";
$sql[] = "(1, 'Administrator', 'Administrator', NULL, NULL, NULL, NULL),";
$sql[] = "(2, 'Teacher', 'Teacher', NULL, NULL, NULL, NULL);";
$sql[] = "";

// --- AUTH ROLES PERMISSIONS ---
$sql[] = "-- ========== AUTH ROLES PERMISSIONS ==========";
$sql[] = "INSERT INTO `auth_roles_permissions` (`role_id`, `permission_id`) VALUES";
$rp_vals = [
    [1,1],[1,2],[1,3],[1,4],[1,5],[1,6],[1,7],[1,8],[1,9],[1,10],[1,11],[1,12],[1,13],[1,14],
    [2,14],[1,15],[2,15],[1,16],[1,17],[1,18],[1,19],[1,20],[1,21],[2,21],[1,22],[1,23],[1,24],
    [1,25],[1,26],[1,27],[1,28],[2,28],
];
$vals = [];
foreach ($rp_vals as $rp) $vals[] = "({$rp[0]}, {$rp[1]})";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- ROOM GROUPS ---
$sql[] = "-- ========== ROOM GROUPS ==========";
$vals = [];
$pos = 0;
foreach ($rg_ids as $name => $id) {
    $vals[] = "({$id}, {$pos}, " . esc($name) . ", NULL)";
    $pos++;
}
$sql[] = "INSERT INTO `room_groups` (`room_group_id`, `pos`, `name`, `description`) VALUES";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- DEPARTMENTS ---
$sql[] = "-- ========== DEPARTMENTS ==========";
$vals = [];
foreach ($dept_ids as $name => $id) {
    $vals[] = "({$id}, " . esc($name) . ", NULL, NULL)";
}
if (!empty($vals)) {
    $sql[] = "INSERT INTO `departments` (`department_id`, `name`, `description`, `icon`) VALUES";
    $sql[] = implode(",\n", $vals) . ";";
} else {
    $sql[] = "-- No departments found in CSV.";
}
$sql[] = "";

// --- ROOMS ---
$sql[] = "-- ========== ROOMS ==========";
$vals = [];
$pos = 0;
foreach ($rooms_map as $name => $group_name) {
    $rid_val = $room_ids[$name];
    $rgid_val = $rg_ids[$group_name];
    $details = $room_details[$name];
    $location = esc_or_null($details['localizacao'] ?? null);
    $notes = esc_or_null(room_notes($details));
    $vals[] = "({$rid_val}, {$rgid_val}, NULL, " . esc($name) . ", {$location}, 1, NULL, {$notes}, NULL, {$pos})";
    $pos++;
}
$sql[] = "INSERT INTO `rooms` (`room_id`, `room_group_id`, `user_id`, `name`, `location`, `bookable`, `icon`, `notes`, `photo`, `pos`) VALUES";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- ROOM CUSTOM FIELDS ---
$sql[] = "-- ========== ROOM CUSTOM FIELDS ==========";
$vals = [];
foreach ($room_fields as $field_id => $field) {
    $vals[] = "({$field_id}, " . esc($field['name']) . ", " . esc($field['type']) . ")";
}
$sql[] = "INSERT INTO `roomfields` (`field_id`, `name`, `type`) VALUES";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- ROOM CUSTOM FIELD VALUES ---
$sql[] = "-- ========== ROOM CUSTOM FIELD VALUES ==========";
$vals = [];
$value_id = 1;
foreach ($rooms_map as $name => $group_name) {
    $rid_val = $room_ids[$name];
    $field_values = room_field_values($room_details[$name]);

    foreach ($room_fields as $field_id => $field) {
        $field_name = $field['name'];
        $value = $field_values[$field_name] ?? null;
        $vals[] = "({$value_id}, {$rid_val}, {$field_id}, " . esc_or_null($value) . ")";
        $value_id++;
    }
}
$sql[] = "INSERT INTO `roomvalues` (`value_id`, `room_id`, `field_id`, `value`) VALUES";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- PERIODS ---
$sql[] = "-- ========== PERIODS ==========";
$vals = [];
foreach ($periods_map as $key => $p) {
    $pid_val = $period_ids[$key];
    $sid_val = $schedule_ids[$p['schedule']];
    $vals[] = "({$pid_val}, {$sid_val}, '{$p['start']}:00', '{$p['end']}:00', " . esc($p['name']) . ", 1, 1, 1, 1, 1, 1, 1, 0)";
}
$sql[] = "INSERT INTO `periods` (`period_id`, `schedule_id`, `time_start`, `time_end`, `name`, `bookable`, `day_1`, `day_2`, `day_3`, `day_4`, `day_5`, `day_6`, `day_7`) VALUES";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- SESSIONS ---
$sql[] = "-- ========== SESSIONS ==========";
$default_sched = $schedule_ids['Período Segmentado'] ?? 1;
$sql[] = "INSERT INTO `sessions` (`session_id`, `default_schedule_id`, `name`, `date_start`, `date_end`, `is_current`, `is_selectable`) VALUES";
$sql[] = "({$session_id}, {$default_sched}, '1/2026', '2026-02-09', '2026-06-27', 1, 1);";
$sql[] = "";

// --- SESSION SCHEDULES ---
$sql[] = "-- ========== SESSION SCHEDULES ==========";
$sql[] = "INSERT INTO `session_schedules` (`session_id`, `room_group_id`, `schedule_id`) VALUES";
$vals = [];
foreach ($rg_ids as $group_name => $rgid_val) {
    // Sala: * → "Período - Sala comum", Lab: * → "Período Segmentado"
    if (strpos($group_name, 'Sala:') === 0) {
        $sched_id = $schedule_ids['Período - Sala comum'];
    } else {
        $sched_id = $schedule_ids['Período Segmentado'];
    }
    $vals[] = "({$session_id}, {$rgid_val}, {$sched_id})";
}
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- USERS ---
$sql[] = "-- ========== USERS ==========";
$password_hash = password_hash($seed_password, PASSWORD_BCRYPT);
$vals = [];
foreach ($users_map as $username => $info) {
    $uid_val = $user_ids[$username];
    $role = in_array($username, $admin_users) ? $role_admin : $role_teacher;
    $dept_id = !empty($info['department']) && isset($dept_ids[$info['department']]) ? $dept_ids[$info['department']] : 'NULL';

    // Tentar separar displayname em firstname + lastname
    $display = $info['displayname'];
    $parts = explode(' ', trim($display), 2);
    $firstname = $parts[0] ?? '';
    $lastname = $parts[1] ?? '';

    $vals[] = "({$uid_val}, {$role}, {$dept_id}, " . esc($username) . ", " . esc($firstname) . ", " . esc($lastname) . ", " . esc($username . '@fatec.sp.gov.br') . ", " . esc($password_hash) . ", " . esc($display) . ", NULL, NULL, 1, NOW(), 0)";
}
$sql[] = "INSERT INTO `users` (`user_id`, `role_id`, `department_id`, `username`, `firstname`, `lastname`, `email`, `password`, `displayname`, `ext`, `lastlogin`, `enabled`, `created`, `force_password_reset`) VALUES";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- DATES ---
$sql[] = "-- ========== DATES ==========";
$sql[] = "-- Gerar datas de 2026-02-09 a 2026-06-27";
$date_start = new DateTime('2026-02-09');
$date_end = new DateTime('2026-06-27');
$interval = new DateInterval('P1D');
$date_range = new DatePeriod($date_start, $interval, $date_end->modify('+1 day'));

// Feriados brasileiros 2026 para lookup
$holidays_2026 = [
    ['name' => 'Carnaval', 'start' => '2026-02-16', 'end' => '2026-02-18'],
    ['name' => 'Quarta-feira de Cinzas', 'start' => '2026-02-18', 'end' => '2026-02-18'],
    ['name' => 'Sexta-feira Santa', 'start' => '2026-04-03', 'end' => '2026-04-03'],
    ['name' => 'Tiradentes', 'start' => '2026-04-21', 'end' => '2026-04-21'],
    ['name' => 'Dia do Trabalho', 'start' => '2026-05-01', 'end' => '2026-05-01'],
    ['name' => 'Corpus Christi', 'start' => '2026-06-04', 'end' => '2026-06-04'],
];

// Holidays SQL
$sql[] = "-- ========== HOLIDAYS ==========";
$vals = [];
$hid = 1;
$holiday_dates = []; // date => holiday_id
foreach ($holidays_2026 as $h) {
    $vals[] = "({$hid}, {$session_id}, " . esc($h['name']) . ", '{$h['start']}', '{$h['end']}')";
    // Map all dates in range
    $hs = new DateTime($h['start']);
    $he = new DateTime($h['end']);
    $he->modify('+1 day');
    $hp = new DatePeriod($hs, new DateInterval('P1D'), $he);
    foreach ($hp as $hd) {
        $holiday_dates[$hd->format('Y-m-d')] = $hid;
    }
    $hid++;
}
$sql[] = "INSERT INTO `holidays` (`holiday_id`, `session_id`, `name`, `date_start`, `date_end`) VALUES";
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// Weekday mapping: PHP date('N') → 1=Mon..7=Sun → DB uses 1=Mon..6=Sat, 7=Sun
$vals = [];
// Reset date_end after modify
$date_end = new DateTime('2026-06-27');
$date_range = new DatePeriod(new DateTime('2026-02-09'), new DateInterval('P1D'), (clone $date_end)->modify('+1 day'));
foreach ($date_range as $d) {
    $ds = $d->format('Y-m-d');
    $wd = (int)$d->format('N'); // 1=Mon..7=Sun
    $hol = isset($holiday_dates[$ds]) ? $holiday_dates[$ds] : 'NULL';
    $vals[] = "('{$ds}', {$wd}, {$session_id}, 1, {$hol})";
}
$sql[] = "INSERT INTO `dates` (`date`, `weekday`, `session_id`, `week_id`, `holiday_id`) VALUES";
$chunks = array_chunk($vals, 50);
foreach ($chunks as $i => $chunk) {
    if ($i === 0) {
        $sql[] = implode(",\n", $chunk);
    } else {
        $sql[] = "," . implode(",\n", $chunk);
    }
}
$sql[] = ";";
$sql[] = "";

// --- BOOKINGS REPEAT ---
$sql[] = "-- ========== BOOKINGS REPEAT ==========";
$weekday_map = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];

$vals = [];
foreach ($recurring_groups as $csv_rec_id => $row) {
    $rpid_val = $repeat_ids[$csv_rec_id];
    $pkey = $row[8] . '|' . $row[9];
    $period_id = $period_ids[$pkey] ?? 'NULL';
    $room_id = $room_ids[$row[12]] ?? 'NULL';
    $user_id_val = !empty($row[15]) && isset($user_ids[$row[15]]) ? $user_ids[$row[15]] : 'NULL';
    $dept_id = !empty($row[17]) && isset($dept_ids[$row[17]]) ? $dept_ids[$row[17]] : 'NULL';
    $week_id = 1;
    $weekday = $weekday_map[$row[6]] ?? 1;
    $notes = esc_or_null($row[14]);
    $created_at = esc_or_null($row[18]);
    $created_by = !empty($row[19]) && isset($user_ids[$row[19]]) ? $user_ids[$row[19]] : 'NULL';
    $updated_at = esc_or_null($row[20]);
    $updated_by = !empty($row[21]) && isset($user_ids[$row[21]]) ? $user_ids[$row[21]] : 'NULL';

    $vals[] = "({$rpid_val}, {$session_id}, {$period_id}, {$room_id}, {$user_id_val}, {$dept_id}, {$week_id}, {$weekday}, 10, {$notes}, NULL, NULL, NULL, {$created_at}, {$created_by}, {$updated_at}, {$updated_by})";
}

$sql[] = "INSERT INTO `bookings_repeat` (`repeat_id`, `session_id`, `period_id`, `room_id`, `user_id`, `department_id`, `week_id`, `weekday`, `status`, `notes`, `cancel_reason`, `cancelled_at`, `cancelled_by`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES";
$chunks = array_chunk($vals, 100);
foreach ($chunks as $i => $chunk) {
    if ($i === 0) {
        $sql[] = implode(",\n", $chunk);
    } else {
        $sql[] = "," . implode(",\n", $chunk);
    }
}
$sql[] = ";";
$sql[] = "";

// --- BOOKINGS ---
$sql[] = "-- ========== BOOKINGS ==========";
$vals = [];
foreach ($rows as $row) {
    $booking_id = (int)$row[0];
    $repeat_id = ($row[2] === 'Recurring' && !empty($row[1]) && isset($repeat_ids[$row[1]])) ? $repeat_ids[$row[1]] : 'NULL';
    $pkey = $row[8] . '|' . $row[9];
    $period_id = $period_ids[$pkey] ?? 'NULL';
    $room_id = $room_ids[$row[12]] ?? 'NULL';
    $user_id_val = !empty($row[15]) && isset($user_ids[$row[15]]) ? $user_ids[$row[15]] : 'NULL';
    $dept_id = !empty($row[17]) && isset($dept_ids[$row[17]]) ? $dept_ids[$row[17]] : 'NULL';
    $date = $row[5];
    $status = ($row[3] === 'Booked') ? 10 : 15;
    $notes = esc_or_null($row[14]);
    $created_at = esc_or_null($row[18]);
    $created_by = !empty($row[19]) && isset($user_ids[$row[19]]) ? $user_ids[$row[19]] : 'NULL';
    $updated_at = esc_or_null($row[20]);
    $updated_by = !empty($row[21]) && isset($user_ids[$row[21]]) ? $user_ids[$row[21]] : 'NULL';
    $cancelled_at = esc_or_null($row[22] ?? '');
    $cancelled_by = (!empty($row[23]) && isset($user_ids[$row[23]])) ? $user_ids[$row[23]] : 'NULL';

    $vals[] = "({$booking_id}, {$repeat_id}, {$session_id}, {$period_id}, {$room_id}, {$user_id_val}, {$dept_id}, '{$date}', {$status}, {$notes}, NULL, {$cancelled_at}, {$cancelled_by}, {$created_at}, {$created_by}, {$updated_at}, {$updated_by})";
}

$sql[] = "INSERT INTO `bookings` (`booking_id`, `repeat_id`, `session_id`, `period_id`, `room_id`, `user_id`, `department_id`, `date`, `status`, `notes`, `cancel_reason`, `cancelled_at`, `cancelled_by`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES";
$chunks = array_chunk($vals, 200);
foreach ($chunks as $i => $chunk) {
    if ($i === 0) {
        $sql[] = implode(",\n", $chunk);
    } else {
        $sql[] = "," . implode(",\n", $chunk);
    }
}
$sql[] = ";";
$sql[] = "";

// --- SETTINGS ---
$sql[] = "-- ========== SETTINGS ==========";
$settings = [
    ['auth', 'crbs', '1'],
    ['auth', 'preauth', '0'],
    ['auth', 'ldap_host', ''],
    ['auth', 'ldap_port', '389'],
    ['auth', 'ldap_base_dn', ''],
    ['auth', 'ldap_filter', ''],
    ['auth', 'ldap_login', 'uid'],
    ['auth', 'ldap_firstname', 'givenname'],
    ['auth', 'ldap_lastname', 'sn'],
    ['auth', 'ldap_email', 'mail'],
    ['auth', 'ldap_default_role_id', '2'],
    ['general', 'school_name', 'FATEC - Faculdade de Tecnologia'],
    ['general', 'school_url', ''],
    ['general', 'date_format', 'd/m/Y'],
    ['general', 'date_format_short', 'd/m'],
    ['general', 'time_format', 'H:i'],
    ['general', 'timezone', 'America/Sao_Paulo'],
    ['general', 'language', 'portuguese-brazilian'],
    ['general', 'maintenance_mode', '0'],
    ['general', 'maintenance_message', ''],
    ['crbs', 'displaytype', 'day'],
    ['crbs', 'd_columns', 'periods'],
    ['booking', 'allow_overlap', '0'],
    ['booking', 'default_view', 'room'],
    ['booking', 'week_starts_on', '1'],
];
$sql[] = "INSERT INTO `settings` (`group`, `name`, `value`) VALUES";
$vals = [];
foreach ($settings as $s) {
    $vals[] = "(" . esc($s[0]) . ", " . esc($s[1]) . ", " . esc($s[2]) . ")";
}
$sql[] = implode(",\n", $vals) . ";";
$sql[] = "";

// --- MIGRATIONS ---
$sql[] = "-- ========== MIGRATIONS ==========";
$sql[] = "INSERT INTO `migrations` (`version`) VALUES (20250421122200);";
$sql[] = "";

$sql[] = "SET foreign_key_checks = 1;";
$sql[] = "";
$sql[] = "-- Fim do seed. Total de bookings: " . count($rows);

// ============================================================================
// 4. ESCREVER ARQUIVO
// ============================================================================

$content = implode("\n", $sql);
file_put_contents($output_path, $content);

$size = filesize($output_path);
echo "seed.sql gerado: " . number_format($size) . " bytes\n";
echo "  Weeks: 1\n";
echo "  Schedules: " . count($schedule_ids) . "\n";
echo "  Room Groups: " . count($rg_ids) . "\n";
echo "  Departments: " . count($dept_ids) . "\n";
echo "  Rooms: " . count($room_ids) . "\n";
echo "  Room Fields: " . count($room_fields) . "\n";
echo "  Room Values: " . ($value_id - 1) . "\n";
echo "  Periods: " . count($period_ids) . "\n";
echo "  Sessions: 1\n";
echo "  Users: " . count($user_ids) . "\n";
echo "  Holidays: " . count($holidays_2026) . "\n";
echo "  Bookings Repeat: " . count($repeat_ids) . "\n";
echo "  Bookings: " . count($rows) . "\n";
echo "  Settings: " . count($settings) . "\n";
echo "\nPronto!\n";

// ============================================================================
// HELPERS
// ============================================================================

function esc($val) {
    if ($val === null || $val === '') return "''";
    return "'" . str_replace("'", "\\'", $val) . "'";
}

function esc_or_null($val) {
    if ($val === null || $val === '') return 'NULL';
    return "'" . str_replace("'", "\\'", $val) . "'";
}

function load_room_details($path) {
    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (!is_array($data) || !isset($data['ambientes']) || !is_array($data['ambientes'])) {
        die("JSON de detalhes das salas invalido ou sem a chave 'ambientes'.\n");
    }

    $details = [];
    foreach ($data['ambientes'] as $item) {
        if (empty($item['nome'])) {
            continue;
        }

        if (isset($details[$item['nome']])) {
            die("JSON de detalhes contem sala duplicada: {$item['nome']}\n");
        }

        $details[$item['nome']] = $item;
    }

    return $details;
}

function room_notes($details) {
    $notes = join_list($details['observacoes'] ?? []);
    if ($notes === '') {
        $notes = join_list($details['restricoesUso'] ?? []);
    }

    return limit_text($notes, 255);
}

function room_field_values($details) {
    return [
        'Capacidade' => isset($details['capacidadeAlunos']) ? "{$details['capacidadeAlunos']} alunos" : null,
        'Computadores' => isset($details['computadores']) ? "{$details['computadores']} computadores" : null,
        'Tipo de ambiente' => format_label($details['tipoAmbiente'] ?? null),
        'Recursos' => room_resources($details['recursos'] ?? []),
        'Equipamentos' => room_equipment($details['equipamentos'] ?? []),
        'Softwares' => room_software($details['softwares'] ?? []),
        'Restricoes de uso' => limit_text(join_list($details['restricoesUso'] ?? []), 255),
        'Manutencao' => room_maintenance($details['manutencao'] ?? []),
        'Fonte dos detalhes' => room_source($details),
    ];
}

function room_resources($resources) {
    if (!is_array($resources)) {
        return null;
    }

    $parts = [];
    $map = [
        'espelhamentoPor' => 'Espelhamento',
        'lousa' => 'Lousa',
        'internet' => 'Internet',
        'climatizacao' => 'Climatizacao',
    ];

    foreach ($map as $key => $label) {
        if (!empty($resources[$key])) {
            $parts[] = "{$label}: {$resources[$key]}";
        }
    }

    if (!empty($resources['mobiliario']) && is_array($resources['mobiliario'])) {
        $parts[] = 'Mobiliario: ' . join_list($resources['mobiliario']);
    }

    return limit_text(implode('; ', $parts), 255);
}

function room_equipment($equipment) {
    if (!is_array($equipment)) {
        return null;
    }

    $parts = [];
    foreach ($equipment as $item) {
        if (empty($item['nome'])) {
            continue;
        }

        $prefix = !empty($item['quantidade']) ? "{$item['quantidade']}x " : '';
        $parts[] = $prefix . $item['nome'];
    }

    return limit_text(implode('; ', $parts), 255);
}

function room_software($software) {
    if (!is_array($software)) {
        return null;
    }

    $parts = [];
    foreach ($software as $item) {
        if (!empty($item['nome'])) {
            $parts[] = $item['nome'];
        }
    }

    return limit_text(implode('; ', $parts), 255);
}

function room_maintenance($maintenance) {
    if (!is_array($maintenance)) {
        return null;
    }

    $parts = [];
    if (!empty($maintenance['status'])) {
        $parts[] = 'Status: ' . format_label($maintenance['status']);
    }
    if (!empty($maintenance['prioridadePreventiva'])) {
        $parts[] = 'Prioridade: ' . format_label($maintenance['prioridadePreventiva']);
    }
    if (!empty($maintenance['proximaRevisaoSimulada'])) {
        $parts[] = 'Proxima revisao: ' . $maintenance['proximaRevisaoSimulada'];
    }
    if (!empty($maintenance['observacao'])) {
        $parts[] = $maintenance['observacao'];
    }

    return limit_text(implode('; ', $parts), 255);
}

function room_source($details) {
    $parts = [];
    if (!empty($details['fonteDetalhes'])) {
        $parts[] = 'Fonte: ' . format_label($details['fonteDetalhes']);
    }
    if (!empty($details['nivelConfiancaDetalhes'])) {
        $parts[] = 'Confianca: ' . format_label($details['nivelConfiancaDetalhes']);
    }

    return limit_text(implode('; ', $parts), 255);
}

function join_list($items) {
    if (!is_array($items)) {
        return '';
    }

    return implode('; ', array_values(array_filter($items, function ($item) {
        return $item !== null && $item !== '';
    })));
}

function format_label($value) {
    if ($value === null || $value === '') {
        return null;
    }

    return str_replace('_', ' ', $value);
}

function limit_text($value, $max) {
    if ($value === null || $value === '') {
        return null;
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($value, 'UTF-8') > $max
            ? mb_substr($value, 0, $max - 3, 'UTF-8') . '...'
            : $value;
    }

    return strlen($value) > $max ? substr($value, 0, $max - 3) . '...' : $value;
}

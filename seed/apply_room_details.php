<?php
/**
 * Apply mocked room details from seed/salas_labs_detalhes_mockados.json
 * into an existing local ClassroomBookings database.
 */

error_reporting(E_ALL);

$root_path = dirname(__DIR__);
define('BASEPATH', true);

$config = include $root_path . '/local/config.php';
$db = $config['database'];
$json_path = __DIR__ . '/salas_labs_detalhes_mockados.json';
$csv_path = getenv('SEED_CSV_PATH') ?: __DIR__ . '/importacao-privada.csv';

if (!file_exists($json_path)) {
    die("JSON de detalhes das salas nao encontrado: {$json_path}\n");
}

$payload = json_decode(file_get_contents($json_path), true);
if (!is_array($payload) || empty($payload['ambientes']) || !is_array($payload['ambientes'])) {
    die("JSON de detalhes das salas invalido ou sem a chave 'ambientes'.\n");
}

$details = [];
foreach ($payload['ambientes'] as $item) {
    if (empty($item['nome'])) {
        continue;
    }
    $details[$item['nome']] = $item;
}

$canonical_rooms = load_seed_room_names($csv_path);

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

$mysqli = new mysqli(
    $db['hostname'],
    $db['username'],
    $db['password'],
    $db['database'],
    isset($db['port']) ? (int) $db['port'] : 3306
);

if ($mysqli->connect_error) {
    die("Erro ao conectar no banco: {$mysqli->connect_error}\n");
}

$mysqli->set_charset('utf8mb4');

$rooms = [];
$result = $mysqli->query('SELECT room_id, name FROM rooms ORDER BY room_id');
if (!$result) {
    die("Erro ao ler salas: {$mysqli->error}\n");
}

while ($row = $result->fetch_assoc()) {
    $room_id = (int) $row['room_id'];
    $canonical_name = isset($details[$row['name']])
        ? $row['name']
        : ($canonical_rooms[$room_id] ?? $row['name']);

    $rooms[$canonical_name] = $room_id;
}

$missing = array_values(array_diff(array_keys($rooms), array_keys($details)));
if (!empty($missing)) {
    die("Detalhes mockados ausentes para salas: " . implode(', ', $missing) . "\n");
}

$mysqli->begin_transaction();

try {
    $update_room = $mysqli->prepare('UPDATE rooms SET name = ?, location = ?, notes = ? WHERE room_id = ?');
    foreach ($rooms as $name => $room_id) {
        $item = $details[$name];
        $location = $item['localizacao'] ?? null;
        $notes = room_notes($item);
        $update_room->bind_param('sssi', $name, $location, $notes, $room_id);
        $update_room->execute();
    }

    $field_stmt = $mysqli->prepare(
        'INSERT INTO roomfields (field_id, name, type) VALUES (?, ?, ?) ' .
        'ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type)'
    );
    foreach ($room_fields as $field_id => $field) {
        $field_stmt->bind_param('iss', $field_id, $field['name'], $field['type']);
        $field_stmt->execute();
    }

    $field_ids = implode(',', array_keys($room_fields));
    $mysqli->query("DELETE FROM roomvalues WHERE field_id IN ({$field_ids})");

    $value_stmt = $mysqli->prepare('INSERT INTO roomvalues (room_id, field_id, value) VALUES (?, ?, ?)');
    foreach ($rooms as $name => $room_id) {
        $values = room_field_values($details[$name]);
        foreach ($room_fields as $field_id => $field) {
            $value = $values[$field['name']] ?? null;
            $value_stmt->bind_param('iis', $room_id, $field_id, $value);
            $value_stmt->execute();
        }
    }

    $mysqli->commit();
} catch (Throwable $e) {
    $mysqli->rollback();
    die("Erro ao aplicar detalhes: {$e->getMessage()}\n");
}

$counts = $mysqli->query(
    "SELECT 'rooms_with_location' AS metric, COUNT(*) AS total FROM rooms WHERE location IS NOT NULL AND location <> '' " .
    "UNION ALL SELECT 'roomfields', COUNT(*) FROM roomfields " .
    "UNION ALL SELECT 'roomvalues', COUNT(*) FROM roomvalues"
);

while ($row = $counts->fetch_assoc()) {
    echo "{$row['metric']}: {$row['total']}\n";
}

function room_notes($details) {
    $notes = join_list($details['observacoes'] ?? []);
    if ($notes === '') {
        $notes = join_list($details['restricoesUso'] ?? []);
    }

    return limit_text($notes, 255);
}

function load_seed_room_names($csv_path) {
    if (!file_exists($csv_path)) {
        die("CSV base nao encontrado: {$csv_path}\n");
    }

    $handle = fopen($csv_path, 'r');
    if (!$handle) {
        die("Nao foi possivel abrir o CSV base: {$csv_path}\n");
    }

    fgetcsv($handle);
    $rooms = [];
    while (($row = fgetcsv($handle)) !== false) {
        if (isset($row[12]) && $row[12] !== '') {
            $rooms[$row[12]] = true;
        }
    }
    fclose($handle);

    $names = array_keys($rooms);
    sort($names);

    $indexed = [];
    $room_id = 1;
    foreach ($names as $name) {
        $indexed[$room_id] = $name;
        $room_id++;
    }

    return $indexed;
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

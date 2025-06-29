<?php
include("configuration.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Acceso no permitido.";
    exit;
}

$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$url .= "://".$_SERVER['HTTP_HOST']."/";

// Obtener datos del formulario con valores por defecto
$data = [
    'board_type'          => $_POST['board_type'] ?? '',
    'cpu_name'            => $_POST['cpu_name'] ?? '',
    'cpu_other_name'      => $_POST['cpu_other_name'] ?? '',
    'ram_capacity'        => $_POST['ram_capacity'] ?? '',
    'ram_other_capacity'  => $_POST['ram_other_capacity'] ?? '',
    'ram_type'            => $_POST['ram_type'] ?? '',
    'disc_capacity'       => $_POST['disc_capacity'] ?? '',
    'disc_other_capacity' => $_POST['disc_other_capacity'] ?? '',
    'disc_type'           => $_POST['disc_type'] ?? '',
    'gpu_name'            => $_POST['gpu_name'] ?? '',
    'gpu_other_name'      => $_POST['gpu_other_name'] ?? '',
    'gpu_type'            => $_POST['gpu_type'] ?? '',
    'wifi'                => $_POST['wifi'] ?? 'false',
    'bluetooth'           => $_POST['bluetooth'] ?? 'false',
    'sn_prefix'           => strtoupper($_POST['sn_prefix'] ?? ''),
    'sn_prefix_other'     => strtoupper($_POST['sn_prefix_other'] ?? ''),
    'num_pag'             => $_POST['num_pag'] ?? '',
    'checkbox_save'       => $_POST['checkbox_save'] ?? '',
    'ticket_name'         => $_POST['ticket_name'] ?? '',
    'observaciones'       => $_POST['observaciones'] ?? ''
];

$manualFields = [
    'cpu'  => ['field' => 'cpu_other_name', 'target' => 'cpu_name', 'column' => 'name'],
    'ram'  => ['field' => 'ram_other_capacity', 'target' => 'ram_capacity', 'column' => 'capacity'],
    'disc' => ['field' => 'disc_other_capacity', 'target' => 'disc_capacity', 'column' => 'capacity'],
    'gpu'  => ['field' => 'gpu_other_name', 'target' => 'gpu_name', 'column' => 'name']
];

// Variables para almacenar IDs de componentes
$cpu_id = null;
$ram_id = null;
$disc_id = null;
$gpu_id = null;

foreach ($manualFields as $table => $info) {
    if (!empty($data[$info['field']])) {
        $data[$info['target']] = $data[$info['field']];
        $stmt = $conn->prepare("INSERT INTO $table ({$info['column']}) VALUES (?)");
        $stmt->execute([$data[$info['target']]]);
        ${$table.'_id'} = $conn->lastInsertId(); // Guardar el ID insertado
    } else {
        // Obtener ID del componente seleccionado si no es manual
        $stmt = $conn->prepare("SELECT id FROM $table WHERE {$info['column']} = ? LIMIT 1");
        $stmt->execute([$data[$info['target']]]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            ${$table.'_id'} = $row['id'];
        }
    }
}

// Priorizar prefijo manual
if (!empty($data['sn_prefix_other'])) {
    $data['sn_prefix'] = $data['sn_prefix_other'];
}

// Función para escapar los argumentos para el shell
function escapeArgs(array $args): array {
    return array_map('escapeshellarg', $args);
}

// Lógica para generación de PDF
$prefix = $data['sn_prefix'];
$num_pag = (int)$data['num_pag'];

$clean = "true";

$is_single = $num_pag < 2 ? "true" : "false";
$total_pages = $is_single=="true" ? 1 : $num_pag;

// Insertar datos en la tabla pc para cada etiqueta generada
for ($i = 1; $i <= $num_pag; $i++) {
    $is_last = ($i === $total_pages);
    $end = $is_last ? "true" : "false";

    $name = "pdf/raid/generado{$i}.pdf";
    $qrcode_name = "pdf/qrcodes/qrcode{$i}.png";

    // Obtener el siguiente número de serie
    $stmt = $conn->prepare("SELECT MAX(num) AS last_num FROM sn WHERE prefix = ?");
    $stmt->execute([$data['sn_prefix']]);
    $last_num = $stmt->fetch(PDO::FETCH_ASSOC)['last_num'] ?? 0;
    $sn_num = $last_num + 1;

    // Insertar nuevo SN
    $stmt = $conn->prepare("INSERT INTO sn (prefix, num) VALUES (?, ?)");
    $stmt->execute([$data['sn_prefix'], $sn_num]);
    $sn_id = $conn->lastInsertId();
    
    // Insertar en la tabla pc
    $stmt = $conn->prepare("INSERT INTO pc (board_type, cpu_name, ram_capacity, ram_type, disc_capacity, disc_type, gpu_name, gpu_type, wifi, bluetooth, obser) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['board_type'],
        $cpu_id,
        $ram_id,
        $data['ram_type'],
        $disc_id,
        $data['disc_type'],
        $gpu_id,
        $data['gpu_type'],
        $data['wifi'],
        $data['bluetooth'],
        $data['observaciones']
    ]);
    $pc_id = $conn->lastInsertId();
    $pc_url = "$url"."render_model.php?modelId=$pc_id";

    //Asociar el sn con el pc
    $stmt = $conn->prepare("INSERT INTO sn_pc (sn_id, pc_id) VALUES (?, ?)");
    $stmt->execute([$sn_id, $pc_id]);

    // Escapar solo una vez en la primera iteración (optimización)
    if ($i === 1) {
        $escaped = escapeArgs([
            $data['board_type'],
            $data['cpu_name'],
            $data['ram_capacity'],
            $data['ram_type'],
            $data['disc_type'],
            $data['disc_capacity'],
            $data['gpu_name'],
            $data['gpu_type'],
            $data['wifi'],
            $data['bluetooth'],
            $prefix,
            $sn_num,
            $name,
            $qrcode_name,
            $end,
            $is_single,
            $clean,
            $pc_url,
            $data['observaciones']
        ]);
    } else {
        $escaped[10] = escapeshellarg($prefix);     // prefix
        $escaped[11] = escapeshellarg($sn_num);     // sn_num
        $escaped[12] = escapeshellarg($name);       // name
        $escaped[13] = escapeshellarg($qrcode_name);// qrcode_name
        $escaped[14] = escapeshellarg($end);        // end
        $escaped[15] = escapeshellarg($pc_id);      // pc_id
        $escaped[16] = escapeshellarg($clean);      // clean
    }

    // Ejecutar comando
    $command = "python3 scripts/pdfgenerator.py " . implode(' ', $escaped);
    $output = shell_exec($command);
    $clean = "false"; // Solo la primera vez es true

}


if ($data['checkbox_save'] == 'True') {

    $stmt = $conn->prepare("SELECT MAX(id) AS last_num FROM pc");
    $stmt->execute();
    $last_num = $stmt->fetch(PDO::FETCH_ASSOC)['last_num'];

    $stmt = $conn->prepare("INSERT INTO models (name, model) VALUES (?, ?)");
    $stmt->execute([$data['ticket_name'], $last_num]);
}


sleep(0.1);
header("Location: index.php"); // Comenta esta línea si estás haciendo pruebas

?>


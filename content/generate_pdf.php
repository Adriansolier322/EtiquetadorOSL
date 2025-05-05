<?php
include("configuration.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obtener datos del formulario con valores por defecto
    $board_type          = $_POST['board_type'] ?? '';

    $cpu_name            = $_POST['cpu_name'] ?? '';
    $cpu_other_name      = $_POST['cpu_other_name'] ?? '';

    $ram_capacity        = $_POST['ram_capacity'] ?? '';
    $ram_other_capacity  = $_POST['ram_other_capacity'] ?? '';
    $ram_type            = $_POST['ram_type'] ?? '';

    $disc_capacity       = $_POST['disc_capacity'] ?? '';
    $disc_other_capacity = $_POST['disc_other_capacity'] ?? '';
    $disc_type           = $_POST['disc_type'] ?? '';

    $gpu_name            = $_POST['gpu_name'] ?? '';
    $gpu_other_name      = $_POST['gpu_other_name'] ?? '';
    $gpu_type            = $_POST['gpu_type'] ?? '';

    $wifi                = $_POST['wifi'] ?? 'false';
    $bluetooth           = $_POST['bluetooth'] ?? 'false';
    
    $sn_prefix           = $_POST['sn_prefix'] ?? '';
    $sn_prefix           = strtoupper($sn_prefix);
    $sn_prefix_other     = $_POST['sn_prefix_other'] ?? '';
    $sn_prefix_other     = strtoupper($sn_prefix_other);

    $observaciones       = $_POST['observaciones'] ?? '';

    // Priorizar valores introducidos manualmente si existen
    if (!empty($cpu_other_name)) {
        $cpu_name = $cpu_other_name;
        $stmt = $conn->prepare("INSERT INTO cpu (name) VALUES (?)");
        $stmt->execute([$cpu_name]);
    }
    if (!empty($ram_other_capacity)) {
        $ram_capacity = $ram_other_capacity;
        $stmt = $conn->prepare("INSERT INTO ram (capacity) VALUES (?)");
        $stmt->execute([$ram_capacity]);
    }
    if (!empty($disc_other_capacity)) {
        $disc_capacity = $disc_other_capacity;
        $stmt = $conn->prepare("INSERT INTO disc (capacity) VALUES (?)");
        $stmt->execute([$disc_capacity]);
    }
    if (!empty($gpu_other_name)) {
        $gpu_name = $gpu_other_name;
        $stmt = $conn->prepare(query: "INSERT INTO gpu (name) VALUES (?)");
        $stmt->execute([$gpu_name]);
    }
    if (!empty($sn_prefix_other)) {
        $sn_prefix = $sn_prefix_other;
    }
    if ($sn_prefix != "INDEFINIDO") {
        $stmt = $conn->prepare("SELECT MAX(num) AS last_num FROM sn WHERE prefix = ?");
        $stmt->execute([$sn_prefix]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $last_num = $result['last_num'] ?? 0;
    
        $sn_num = $last_num + 1;
        $stmt = $conn->prepare("INSERT INTO sn (prefix, num) VALUES (?, ?)");
        $stmt->execute([$sn_prefix, $sn_num]);
    }

    // Escapar para línea de comandos (seguridad)
    $sn_prefix       = escapeshellarg($sn_prefix);
    $sn_num          = escapeshellarg($sn_num);
    $board_type      = escapeshellarg($board_type);
    $cpu_name        = escapeshellarg($cpu_name);
    $ram_capacity    = escapeshellarg($ram_capacity);
    $ram_type        = escapeshellarg($ram_type);
    $disc_type       = escapeshellarg($disc_type);
    $disc_capacity   = escapeshellarg($disc_capacity);
    $gpu_name        = escapeshellarg($gpu_name);
    $gpu_type        = escapeshellarg($gpu_type);
    $wifi            = escapeshellarg($wifi);
    $bluetooth       = escapeshellarg($bluetooth);
    $observaciones   = escapeshellarg($observaciones);


    // Comando para ejecutar el script de generacion PDF
    $command = "python3 scripts/pdfgenerator.py $board_type $cpu_name $ram_capacity $ram_type $disc_type $disc_capacity $gpu_name $gpu_type $wifi $bluetooth $observaciones $sn_prefix $sn_num";
    $output = shell_exec($command);

    // Debug
    echo $command; // Puedes descomentar para pruebas
    //echo $output;  // Ver salida del script


    // Crear una carpeta de guardado
    $savedPdfDir = 'scripts/pdf/saved/';
    if (!file_exists($savedPdfDir)) {
        mkdir($savedPdfDir, 0777, true);
    }
 

    // Redireccion
    sleep(0.1);
    header("Location: index.php"); // Puedes comentar para pruebas
    exit;

} else {
    echo "Acceso no permitido.";
}
?>

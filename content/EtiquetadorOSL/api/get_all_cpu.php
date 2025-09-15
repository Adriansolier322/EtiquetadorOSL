<?php
include ("../configuration.php");
header('Content-Type: application/json');

// Conexión a la base de datos
$stmt = $conn->prepare("SELECT * FROM cpu");
// Ejecutar la consulta y devolver los resultados en formato JSON
if($stmt-> execute()){
    $result = $stmt->fetchAll();
    echo json_encode($result);
    header("HTTP/1.1 201 OK");
} 
// Si hay un error en la consulta 
else {
    header("HTTP/1.1 404 NOT FOUND");
}
?>
<?php
include ("../configuration.php");
$stmt = $conn->prepare("SELECT * FROM cpu");
if($stmt-> execute()){
    $result = $stmt->fetchAll();
    echo json_encode($result);
    header("HTTP/1.1 201 OK");
} else {
    header("HTTP/1.1 404 NOT FOUND");
}
?>
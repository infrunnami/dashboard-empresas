<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include './conexion.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión"]));
}

$result = $conn->query("SELECT id, nombre, descripcion FROM temas");
echo json_encode($result->fetch_all(MYSQLI_ASSOC));

$conn->close();
?>
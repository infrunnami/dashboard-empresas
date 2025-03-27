<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "miapp");

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Obtener todas las roles
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT id, nombre FROM roles");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

$conn->close();
?>

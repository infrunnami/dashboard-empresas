<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "miapp");

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Obtener todas las empresas
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT * FROM empresas");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// Agregar empresa
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["create"])) {
    $data = json_decode(file_get_contents("php://input"));
    $nombre = $conn->real_escape_string($data->nombre);

    $conn->query("INSERT INTO empresas (nombre, estado) VALUES ('$nombre', 1)");
    echo json_encode(["message" => "Empresa agregada"]);
}

// Editar empresa
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["update"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);
    $nombre = $conn->real_escape_string($data->nombre);

    $conn->query("UPDATE empresas SET nombre='$nombre' WHERE id=$id");
    echo json_encode(["message" => "Empresa actualizada"]);
}

// Activar/Desactivar empresa
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["toggle"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);
    $estado = intval($data->estado);

    $conn->query("UPDATE empresas SET estado=$estado WHERE id=$id");
    echo json_encode(["message" => "Estado actualizado"]);
}

$conn->close();
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include './conexion.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Obtener todos los roles
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT * FROM roles where nombre<>'SuperUser'");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// Agregar rol
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["create"])) {
    $data = json_decode(file_get_contents("php://input"));
    $nombre = $conn->real_escape_string($data->nombre);

    $conn->query("INSERT INTO roles (nombre, estado) VALUES ('$nombre', 1)");
    echo json_encode(["message" => "Rol agregado"]);
}

// Editar rol
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["update"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);
    $nombre = $conn->real_escape_string($data->nombre);

    $conn->query("UPDATE roles SET nombre='$nombre' WHERE id=$id");
    echo json_encode(["message" => "Rol actualizado"]);
}

// Activar/Desactivar rol
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["toggle"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);
    $estado = intval($data->estado);

    $conn->query("UPDATE roles SET estado=$estado WHERE id=$id");
    echo json_encode(["message" => "Estado actualizado"]);
}

$conn->close();
?>


<?php

session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include './conexion.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}


$user_id = intval($_SESSION["user_id"] ?? 0);

$rol_nombre = '';
$qryRol = "SELECT r.nombre FROM users u INNER JOIN roles r ON u.rol_id = r.id WHERE u.id = $user_id";
$resRol = $conn->query($qryRol);

if ($resRol && $resRol->num_rows > 0) {
    $rowRol = $resRol->fetch_assoc();
    $rol_nombre = $rowRol["nombre"];
}

// Obtener todos los ítems
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if ($rol_nombre === 'SuperAdmin') {
        $result = $conn->query("SELECT * FROM items ORDER BY id ASC");
    } else {
        $result = $conn->query("SELECT * FROM items WHERE id >= 9 ORDER BY id ASC");
    }

    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// Agregar ítem
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["create"])) {
    $data = json_decode(file_get_contents("php://input"));
    $nombre = $conn->real_escape_string($data->nombre);
    $tipo = $conn->real_escape_string($data->tipo);
    $url = $conn->real_escape_string($data->url);

    $conn->query("INSERT INTO items (nombre, tipo, url) VALUES ('$nombre', '$tipo', '$url')");
    echo json_encode(["message" => "Ítem agregado"]);
}

// Editar ítem
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["update"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);
    $nombre = $conn->real_escape_string($data->nombre);
    $tipo = $conn->real_escape_string($data->tipo);
    $url = $conn->real_escape_string($data->url);

    $conn->query("UPDATE items SET nombre='$nombre', tipo='$tipo', url='$url' WHERE id=$id");
    echo json_encode(["message" => "Ítem actualizado"]);
}

// Eliminar ítem
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["delete"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);

    $conn->query("DELETE FROM items WHERE id=$id");
    echo json_encode(["message" => "Ítem eliminado"]);
}

$conn->close();
?>

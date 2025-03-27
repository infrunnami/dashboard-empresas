<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "miapp");

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Obtener todos los ítems
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT * FROM items");
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

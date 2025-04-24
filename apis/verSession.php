<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include './conexion.php';

if ($conn->connect_error) {
    echo json_encode(["error" => "Conexión fallida"]);
    exit;
}

$usuario_id = $_SESSION["user_id"] ?? null;
$rol = $_SESSION["rol"] ?? "no definido";

if (!$usuario_id) {
    echo json_encode([
        "rol" => $rol,
        "user_id" => null,
        "tema_nombre" => "light" // por defecto
    ]);
    exit;
}

$query = "SELECT t.nombre AS tema_nombre
          FROM users u
          LEFT JOIN empresas e ON u.empresa_id = e.id
          LEFT JOIN temas t ON e.tema_id = t.id
          WHERE u.id = $usuario_id";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        "rol" => $rol,
        "id_usuario" => $usuario_id,
        "empresa_id" => $_SESSION["empresa_id"] ?? null,
        "tema_nombre" => !empty($data['tema_nombre']) ? $data['tema_nombre'] : 'light'
    ]);
} else {
    echo json_encode([
        "rol" => $rol,
        "id_usuario" => $usuario_id,
        "tema_nombre" => "light"
    ]);
}

$conn->close();
?>

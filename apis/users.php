<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "miapp");

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Obtener todos los usuarios
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query("SELECT id, username, role FROM users");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// Agregar usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["create"])) {
    $data = json_decode(file_get_contents("php://input"));
    $username = $conn->real_escape_string($data->username);
    $password = md5($data->password);
    $role = $conn->real_escape_string($data->role);

    $conn->query("INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
    echo json_encode(["message" => "Usuario agregado"]);
}

// Editar usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["update"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = $conn->real_escape_string($data->id);
    $username = $conn->real_escape_string($data->username);
    $role = $conn->real_escape_string($data->role);
    $password = !empty($data->password) ? "password = '".md5($data->password)."'," : "";

    $conn->query("UPDATE users SET username='$username', $password role='$role' WHERE id=$id");
    echo json_encode(["message" => "Usuario actualizado"]);
}

// Eliminar usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["delete"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = $conn->real_escape_string($data->id);
    
    $conn->query("DELETE FROM users WHERE id=$id");
    echo json_encode(["message" => "Usuario eliminado"]);
}

$conn->close();
?>

<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "miapp");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->token) || !isset($data->password)) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

$token = $conn->real_escape_string($data->token);
$password = md5($data->password);

$conn->query("UPDATE users SET password='$password', reset_token=NULL WHERE reset_token='$token'");

echo json_encode(["status" => "success", "message" => "Contraseña actualizada"]);
$conn->close();
?>

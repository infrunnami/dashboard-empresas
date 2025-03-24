<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "miapp");

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

$data = json_decode(file_get_contents("php://input"));

if (isset($data->email) && isset($data->password)) {
    $email = $conn->real_escape_string($data->email);
    $password = md5($data->password);

    $sql = "SELECT id, role FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $_SESSION["user_id"] = $row["id"];
        $_SESSION["email"] = $email;
        $_SESSION["role"] = $row["role"];

        echo json_encode(["status" => "success", "role" => $row["role"]]);
    } else {
        echo json_encode(["status" => "error", "message" => "Correo o contraseña incorrectos"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
}

$conn->close();
?>

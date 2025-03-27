<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include './conexion.php';


if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

$data = json_decode(file_get_contents("php://input"));

if (isset($data->email) && isset($data->password)) {
    $email = $conn->real_escape_string($data->email);
    $password = md5($data->password);

    $sql = "SELECT users.id, users.rol_id, users.empresa_id, empresas.nombre AS empresa , roles.nombre AS rol
    FROM users 
    LEFT JOIN empresas ON users.empresa_id = empresas.id 
    LEFT JOIN roles ON roles.id=users.rol_id
    WHERE users.email='$email' AND users.password='$password'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $_SESSION["user_id"] = $row["id"];
        $_SESSION["email"] = $email;
        $_SESSION["role_id"] = $row["rol_id"];
        $_SESSION["empresa_id"] = $row["empresa_id"];
        $_SESSION["empresa"] = $row["empresa"];
        $_SESSION["rol"] = $row["rol"];

        echo json_encode(["status" => "success", "rol_id" => $row["rol_id"],  "empresa" => $row["empresa"]]);
    } else {
        echo json_encode(["status" => "error", "message" => "Correo o contraseña incorrectos"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
}

$conn->close();
?>

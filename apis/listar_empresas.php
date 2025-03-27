<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$empresa_id = intval($_SESSION["empresa_id"]);
$role = $_SESSION["rol"];

$conn = new mysqli("localhost", "root", "", "miapp");

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Obtener todas las empresas
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if ($role == "SuperUser"){
        $result = $conn->query("SELECT id, nombre FROM empresas ");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));

    }else{
        $result = $conn->query("SELECT id, nombre FROM empresas where id=$empresa_id");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    }
  
}

$conn->close();
?>

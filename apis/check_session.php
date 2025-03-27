<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Incluye los headers que usas
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Incluye los métodos que usas

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit; // Termina la petición OPTIONS
}
session_start();
header("Content-Type: application/json");

if (isset($_SESSION["user_id"])) {
    echo json_encode(["loggedIn" => true, "rol_id" => $_SESSION["role_id"] ,"empresa"=> $_SESSION["empresa"]]);
} else {
    echo json_encode(["loggedIn" => false]);
}
?>

<?php
session_start();
header("Content-Type: application/json");

if (isset($_SESSION["user_id"])) {
    echo json_encode(["loggedIn" => true, "rol_id" => $_SESSION["role_id"] ,"empresa"=> $_SESSION["empresa"]]);
} else {
    echo json_encode(["loggedIn" => false]);
}
?>

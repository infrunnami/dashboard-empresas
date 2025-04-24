<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

include './conexion.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// ← ESTA es la línea correcta: usamos el ID del rol guardado en la sesión
$user_id = intval($_SESSION["user_id"] ?? 0);
$empresa_id = intval($_SESSION["empresa_id"] ?? 0);


$rol_nombre = '';
$qryRol = "SELECT r.nombre FROM users u INNER JOIN roles r ON u.rol_id = r.id WHERE u.id = $user_id";
$resRol = $conn->query($qryRol);

if ($resRol && $resRol->num_rows > 0) {
    $rowRol = $resRol->fetch_assoc();
    $rol_nombre = $rowRol["nombre"];
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if ($rol_nombre === 'SuperAdmin') {
        // SuperAdmin puede ver todos los roles
        $qry = "SELECT id, nombre FROM roles WHERE estado = 1";
    } elseif ($rol_nombre === 'AdminEmpresa' || $rol_nombre === 'SubAdminEmpresa') {
        $qry = "
            SELECT id, nombre 
            FROM roles 
            WHERE estado = 1 
              AND nombre <> 'SuperAdmin'
              AND (empresa_id = $empresa_id OR empresa_id = 1)
        ";
    } elseif ($rol_nombre === 'UsuarioComun') {
        // UsuarioComun no puede ver SuperAdmin ni AdminEmpresa
        $qry = "SELECT id, nombre FROM roles WHERE nombre = 'UsuarioComun' AND estado = 1";
    } else {
        // Para cualquier otro rol no reconocido
        $qry = "SELECT id, nombre FROM roles WHERE nombre = 'UsuarioComun' AND estado = 1";
    }

    $result = $conn->query($qry);
    $roles = [];

    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }

    echo json_encode($roles);
}

$conn->close();

<?php
session_start(); // ← Agregado para acceder a la sesión

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include './conexion.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

$empresa_id = intval($_SESSION["empresa_id"] ?? 0);
$rol = $_SESSION["rol"] ?? "";

// Obtener todos los roles
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if ($rol === "SuperAdmin") {
        $qry_roles = "SELECT * FROM roles WHERE nombre <> 'SuperAdmin'";
    } else {
        $qry_roles = "
            SELECT r.id, r.nombre, r.estado
                FROM roles r
                WHERE (r.empresa_id = $empresa_id OR r.empresa_id = 1)
                AND r.nombre <> 'SuperAdmin'
                AND r.nombre <> 'AdminEmpresa'
                AND r.nombre <> 'SubAdminEmpresa'
                AND r.nombre <> 'UsuarioComun'
    ";
    }

    $result = $conn->query($qry_roles);
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit;
}

// Agregar rol
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["create"])) {
    $data = json_decode(file_get_contents("php://input"));
    $nombre = $conn->real_escape_string($data->nombre);
    $empresa_id = intval($_SESSION["empresa_id"] ?? null);

    $conn->query("INSERT INTO roles (nombre, estado, empresa_id) VALUES ('$nombre', 1, $empresa_id)");
    echo json_encode(["message" => "Rol agregado"]);
}

// Editar rol
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["update"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);
    $nombre = $conn->real_escape_string($data->nombre);

    $conn->query("UPDATE roles SET nombre='$nombre' WHERE id=$id");
    echo json_encode(["message" => "Rol actualizado"]);
}

// Activar/Desactivar rol
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["toggle"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);
    $estado = intval($data->estado);

    $conn->query("UPDATE roles SET estado=$estado WHERE id=$id");
    echo json_encode(["message" => "Estado actualizado"]);
}

$conn->close();
?>

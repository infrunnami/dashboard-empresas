<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include './conexion.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

$empresa_id = intval($_SESSION["empresa_id"] ?? 0);
$rol = $_SESSION["rol"] ?? "";

// ✅ OBTENER PERMISOS DE ROLES{-}
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if ($rol === "SuperAdmin") {
        $sql = "SELECT role_items.id, roles.nombre AS role, items.nombre AS item 
                FROM role_items 
                JOIN roles ON role_items.role_id = roles.id 
                JOIN items ON role_items.item_id = items.id";
    } else {
        $sql = "SELECT role_items.id, roles.nombre AS role, items.nombre AS item 
                FROM role_items 
                JOIN roles ON role_items.role_id = roles.id 
                JOIN items ON role_items.item_id = items.id 
                WHERE items.creado_por_empresa_id = $empresa_id";
    }

    $result = $conn->query($sql);
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// ✅ ASIGNAR PERMISO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["create"])) {
    $data = json_decode(file_get_contents("php://input"));
    $role_id = intval($data->role_id);
    $item_id = intval($data->item_id);

    if ($rol !== "SuperAdmin") {
        // Validar que el ítem pertenezca a la empresa del usuario
        $check = $conn->query("SELECT id FROM items WHERE id = $item_id AND creado_por_empresa_id = $empresa_id");
        if (!$check || $check->num_rows === 0) {
            echo json_encode(["error" => "No puedes asignar este ítem"]);
            exit;
        }
    }

    $conn->query("INSERT INTO role_items (role_id, item_id) VALUES ($role_id, $item_id)");
    echo json_encode(["message" => "Permiso asignado"]);
}

// ✅ ELIMINAR PERMISO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["delete"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);

    $conn->query("DELETE FROM role_items WHERE id=$id");
    echo json_encode(["message" => "Permiso eliminado"]);
}

$conn->close();
?>

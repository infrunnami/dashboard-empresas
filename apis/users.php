<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include './conexion.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION["empresa_id"])) {
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$empresa_id = intval($_SESSION["empresa_id"]);
$role = $_SESSION["rol"];

// Obtener todos los usuarios
$qry = "
SELECT users.id,
       users.username, 
       users.rol_id, 
       users.email,  
       users.empresa_id, 
       empresas.nombre AS empresa,
       roles.nombre AS rol
FROM users 
LEFT JOIN empresas ON users.empresa_id = empresas.id
LEFT JOIN roles ON users.rol_id = roles.id
WHERE empresas.estado=1";

// Filtrar por empresa si no es SuperAdmin
if ($role !== "SuperAdmin") {
    $qry .= " AND users.empresa_id = $empresa_id
AND roles.nombre <> 'SuperAdmin' ";
}


// Ejecutar la consulta para obtener los usuarios
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query($qry);
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

// Obtener roles disponibles
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["roles"])) {
    $roles = [];

    // Mostrar todos los roles solo si es SuperAdmin
    if ($role === "SuperAdmin") {
        $qry_roles = "SELECT id, nombre FROM roles";
    } else {
        // No permitir mostrar el rol de SuperAdmin
        $qry_roles = "SELECT id, nombre FROM roles WHERE nombre <> 'SuperAdmin'";
    }

    $result = $conn->query($qry_roles);

    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }

    echo json_encode($roles);
    exit;
}

// Agregar usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["create"])) {
    $data = json_decode(file_get_contents("php://input"));
    $username = $conn->real_escape_string($data->username);
    $password = md5($data->password);
    $rol_id = $conn->real_escape_string($data->role_id);
    $empresa_id = $data->empresa_id ? intval($data->empresa_id) : "NULL";
    $email = $conn->real_escape_string($data->email);

    // Asegurarse de que no se pueda asignar el rol de SuperAdmin si no es SuperAdmin
    if ($rol_id == 1 && $role !== "SuperAdmin") {
        echo json_encode(["error" => "No tienes permisos para asignar el rol de SuperAdmin"]);
        exit;
    }

    $conn->query("INSERT INTO users (username, password, rol_id, empresa_id, email) VALUES ('$username', '$password', '$rol_id', $empresa_id, '$email')");
    echo json_encode(["message" => "Usuario agregado"]);
}

// Editar usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["update"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = intval($data->id);
    $empresa_id = intval($data->empresa_id);
    $username = $conn->real_escape_string($data->username);
    $rol_id = intval($data->role_id);
    $email = $conn->real_escape_string($data->email);

    $password_set = "";
    if (!empty($data->password)) {
        $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);
        $password_set = "password = '" . $conn->real_escape_string($hashedPassword) . "', ";
    }

    // Asegurarse de que no se pueda asignar el rol de SuperAdmin si no es SuperAdmin
    if ($rol_id == 1 && $role !== "SuperAdmin") {
        echo json_encode(["error" => "No tienes permisos para asignar el rol de SuperAdmin"]);
        exit;
    }

    $conn->query("UPDATE users SET username='$username', " . $password_set . "rol_id='$rol_id', email='$email', empresa_id='$empresa_id' WHERE id=$id");

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

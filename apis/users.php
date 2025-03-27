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

$qry="
    
SELECT users.id,
users.username, 
users.rol_id , 
users.email,  
users.empresa_id, 
empresas.nombre AS empresa ,
roles.nombre as rol


FROM users 
left JOIN 
empresas ON users.empresa_id = empresas.id
LEFT JOIN
roles ON users.rol_id = roles.id -- 
where empresas.estado=1 && roles.nombre <> 'SuperUser'";

// Obtener todos los usuarios
if ($_SERVER["REQUEST_METHOD"] === "GET") {
     if ($role=="SuperUser"){

        $result = $conn->query($qry);
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));

     }else {
        $qry=$qry."&& users.empresa_id = $empresa_id";
            
           
                 
           
        $result = $conn->query($qry);
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));


     }
    
}

// Agregar usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["create"])) {
    $data = json_decode(file_get_contents("php://input"));
    $username = $conn->real_escape_string($data->username);
    $password = md5($data->password);
    $rol_id = $conn->real_escape_string($data->role_id);
    $empresa_id = $data->empresa_id ? intval($data->empresa_id) : "NULL";
    $email = $conn->real_escape_string($data->email);

    $conn->query("INSERT INTO users (username, password, rol_id, empresa_id, email) VALUES ('$username', '$password', '$rol_id', $empresa_id, '$email')");
    echo json_encode(["message" => "Usuario agregado"]);
}

// Editar usuario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["update"])) {
    $data = json_decode(file_get_contents("php://input"));
    $id = $conn->real_escape_string($data->id);
    $empresa_id = $conn->real_escape_string($data->empresa_id);
    $username = $conn->real_escape_string($data->username);
    $rol_id = $conn->real_escape_string($data->role_id);
    $email = $conn->real_escape_string($data->email);

    $password_set = "";
    if (!empty($data->password)) {
        $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);
        $password_set = "password = '" . $conn->real_escape_string($hashedPassword) . "', ";
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

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

header("Content-Type: application/json");

//$conn = new mysqli("localhost", "root", "", "miapp");
include './conexion.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email)) {
    echo json_encode(["status" => "error", "message" => "Correo requerido"]);
    exit;
}

$email = $conn->real_escape_string($data->email);

// Verificar si el correo existe en la base de datos
$result = $conn->query("SELECT id FROM users WHERE email='$email'");

if ($result->num_rows === 0) {
    // No indicamos que el correo no existe para evitar enumeración de usuarios
    echo json_encode(["status" => "success", "message" => "Este correo no se encuentra en nuestros registros"]);
    exit;
}

// Generar token único
$token = bin2hex(random_bytes(50));

// Guardar token en la base de datos
$conn->query("UPDATE users SET reset_token='$token' WHERE email='$email'");

$mail = new PHPMailer(true);

try {
  

    $mail->isSMTP();
   
    $mail->Host       = $dotenv['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $dotenv['MAIL_USER'];
    $mail->Password   = $dotenv['MAIL_PASS'];
    $mail->SMTPSecure = $dotenv['MAIL_SECURE'];
    $mail->Port       = $dotenv['MAIL_PORT'];


    $mail->setFrom('tu-email@example.com', 'Soporte');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = utf8_encode('Recuperacion de contrasena');
    $mail->Body = "Haz clic en el siguiente enlace para restablecer tu contraseña: <a href='http://localhost/inicio/reset_password.html?token=$token'>Restablecer contraseña</a>";

    $mail->send();
    echo json_encode(["status" => "success", "message" => "Correo enviado con éxito"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al enviar el correo"]);
}

$conn->close();
?>

<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict',
]);
session_start();

function respond(bool $success, string $message, int $status = 200, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Método no permitido.', 405);
}

define('TERMAIR_CONTACT_FORM', true);
$config = require __DIR__ . '/config/config.php';

require __DIR__ . '/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/src/SMTP.php';

$token = (string)($_POST['csrf_token'] ?? '');
if ($token === '' || empty($_SESSION['contact_csrf']) || !hash_equals($_SESSION['contact_csrf'], $token)) {
    respond(false, 'La sesión del formulario venció. Recargue la página e inténtelo nuevamente.', 403);
}

if (!empty($_POST['website'])) {
    respond(true, 'Gracias. Su consulta fue enviada correctamente.');
}

$loadedAt = (int)($_SESSION['contact_form_loaded_at'] ?? 0);
if ($loadedAt <= 0 || (time() - $loadedAt) < (int)$config['security']['minimum_seconds']) {
    respond(false, 'El formulario se envió demasiado rápido. Espere unos segundos e inténtelo nuevamente.', 429);
}

$nombre = trim((string)($_POST['nombre'] ?? ''));
$apellido = trim((string)($_POST['apellido'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$telefono = trim((string)($_POST['telefono'] ?? ''));
$mensaje = trim((string)($_POST['mensaje'] ?? ''));

if ($nombre === '' || $apellido === '' || $email === '' || $telefono === '' || $mensaje === '') {
    respond(false, 'Complete todos los campos obligatorios.', 422);
}
if (mb_strlen($nombre) > 80 || mb_strlen($apellido) > 80) {
    respond(false, 'El nombre o apellido es demasiado largo.', 422);
}
if (!preg_match('/^[\p{L}\p{M} .\'’-]+$/u', $nombre) || !preg_match('/^[\p{L}\p{M} .\'’-]+$/u', $apellido)) {
    respond(false, 'Revise el nombre y el apellido ingresados.', 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) {
    respond(false, 'Ingrese una dirección de email válida.', 422);
}
if (mb_strlen($telefono) > 40 || !preg_match('/^[0-9+()\s.\/-]+$/', $telefono)) {
    respond(false, 'Ingrese un teléfono válido.', 422);
}
if (mb_strlen($mensaje) > (int)$config['security']['maximum_message_length']) {
    respond(false, 'El mensaje es demasiado largo.', 422);
}

$safe = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$fullName = trim($nombre . ' ' . $apellido);
$body = '<div style="font-family:Arial,sans-serif;color:#263238;line-height:1.6">'
    . '<h2 style="color:#063f5c;border-bottom:3px solid #ffa900;padding-bottom:10px">Nueva consulta desde termair.com</h2>'
    . '<p><strong>Nombre:</strong> ' . $safe($fullName) . '</p>'
    . '<p><strong>Email:</strong> ' . $safe($email) . '</p>'
    . '<p><strong>Teléfono:</strong> ' . $safe($telefono) . '</p>'
    . '<p><strong>Mensaje:</strong><br>' . nl2br($safe($mensaje)) . '</p></div>';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $config['smtp']['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp']['username'];
    $mail->Password = $config['smtp']['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = (int)$config['smtp']['port'];
    $mail->SMTPDebug = 0;
    $mail->Timeout = 15;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config['smtp']['username'], $config['smtp']['from_name']);
    $mail->addAddress($config['smtp']['recipient']);
    $mail->addReplyTo($email, $fullName);
    $mail->isHTML(true);
    $mail->Subject = 'Nueva consulta web - ' . $fullName;
    $mail->Body = $body;
    $mail->AltBody = "Nueva consulta desde termair.com\n\nNombre: {$fullName}\nEmail: {$email}\nTeléfono: {$telefono}\n\nMensaje:\n{$mensaje}";
    $mail->send();

    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
    $_SESSION['contact_form_loaded_at'] = time();
    respond(true, 'Gracias. Su consulta fue enviada correctamente.', 200, ['csrf_token' => $_SESSION['contact_csrf']]);
} catch (Exception $exception) {
    error_log('Termair contact form SMTP error: ' . $mail->ErrorInfo);
    respond(false, 'No se pudo enviar la consulta. Inténtelo nuevamente en unos minutos.', 500);
}

<?php
// index.php — front controller (pegar en la raíz)
// antes de session_start()
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => 'clinica-api-u24q.onrender.com', // opcional, ajustar según dominio
  'secure' => true,            // requiere HTTPS
  'httponly' => true,
  'samesite' => 'None'
]);

// START — no imprimir nada antes de esto
if (session_status() === PHP_SESSION_NONE) {
    ob_start();
    session_start();
}

// Lista blanca de orígenes
$allowed_origins = [
    'https://clinica-frontend-react.vercel.app',
    'http://localhost:5173'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    // eliminar posibles cabeceras duplicadas
    header_remove('Access-Control-Allow-Origin');
    header_remove('Access-Control-Allow-Credentials');
    header_remove('Access-Control-Allow-Methods');
    header_remove('Access-Control-Allow-Headers');

    header("Access-Control-Allow-Origin: {$origin}");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
}

// Responder preflight con PHP y salir (antes de incluir rutas)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// ahora incluir boot/router (estos archivos NO deben imprimir nada)
require_once __DIR__ . '/rutas/api.php';

try {
    $router->dispatch();
} catch (Throwable $e) {
    http_response_code(500);
    if (class_exists('Response')) {
        Response::error('Error interno del servidor: ' . $e->getMessage(), 500);
    } else {
        echo json_encode(['error' => 'Error interno del servidor', 'message' => $e->getMessage()]);
    }
}
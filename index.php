<?php
// Mostrar errores en logs; no mostrar en producción
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

// Orígenes permitidos
$allowed_origins = [
    'https://clinica-frontend-react.vercel.app',
    'http://localhost:5173',
    'https://clinicaproxdomg.free.nf'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}

header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Ajustar REQUEST_URI para que el router vea rutas relativas a /api
$base = '/';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
if (strpos($uri, $base) === 0) {
    // recorta el prefijo /api, pero preserva query string en $_SERVER['REQUEST_URI'] no es necesario aquí
    $_SERVER['REQUEST_URI'] = substr($uri, strlen($base)) ?: '/';
}

// Respuesta por defecto JSON
header('Content-Type: application/json; charset=utf-8');

// Incluir rutas y boot
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

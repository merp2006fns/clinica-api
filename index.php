<?php
// Inicia output buffering y/o session antes de cualquier salida si usas session

// CORS: lista blanca de orígenes
$allowed_origins = [
    'https://clinica-frontend-react.vercel.app',
    'http://localhost:5173'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    // Elimina posibles cabeceras duplicadas previamente seteadas
    header_remove('Access-Control-Allow-Origin');
    header_remove('Access-Control-Allow-Credentials');
    header_remove('Access-Control-Allow-Methods');
    header_remove('Access-Control-Allow-Headers');

    header("Access-Control-Allow-Origin: {$origin}");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
}

// Responder preflight rapido y salir
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// boot del router
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
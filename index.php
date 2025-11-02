<?php
header("Access-Control-Allow-Origin: https://clinica-frontend-react.vercel.app");
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/rutas/api.php';

try {
    $router->dispatch();
} catch (Exception $e) {
    Response::error('Error interno del servidor: ' . $e->getMessage(), 500);
}
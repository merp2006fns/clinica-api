<?php
require_once __DIR__ . '/../controladores/SaludoController.php';

function getRutasEjemplo() {
    $controlador = new SaludoController();
    return [
        "GET" => [
            "/" => [$controlador, 'getSaludo'],
            "/{saludo}" => [$controlador, 'getSaludo']
        ]
    ];
}

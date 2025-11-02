<?php
require_once __DIR__ . '/../controladores/MedicosController.php';

function getMedicosRoutes()
{
    $medicosController = new MedicosController();

    return [
        'GET' => [
            '/medicos/search' => [$medicosController, 'search'],
            '/medicos/{id}' => [$medicosController, 'getById'],
        ]
    ];
}
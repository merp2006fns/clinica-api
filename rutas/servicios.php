<?php
require_once __DIR__ . '/../controladores/ServiciosController.php';

function getServiciosRoutes()
{
    $serviciosController = new ServiciosController();

    return [
        'GET' => [
            '/servicios' => [$serviciosController, 'getAll'],
            '/servicios/{id}' => [$serviciosController, 'getById'],
            '/servicios/search' => [$serviciosController, 'search'],
        ],
        'POST' => [
            '/servicios' => [$serviciosController, 'create'],
        ],
        'PUT' => [
            '/servicios/{id}' => [$serviciosController, 'update'],
        ],
        'DELETE' => [
            '/servicios/{id}' => [$serviciosController, 'delete'],
        ]
    ];
}


<?php
require_once __DIR__ . '/../controladores/CitasController.php';

function getCitasRoutes()
{
    $citasController = new CitasController();

    return [
        'GET' => [
            '/citas' => [$citasController, 'getAll'],
            '/citas/{id}' => [$citasController, 'getById'],
            '/citas/buscar' => [$citasController, 'buscar'],
        ],
        'POST' => [
            '/citas' => [$citasController, 'create'],
        ],
        'PUT' => [
            '/citas/{id}' => [$citasController, 'update'],
        ],
        'DELETE' => [
            '/citas/{id}' => [$citasController, 'delete'],
        ]
    ];
}


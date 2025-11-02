<?php
require_once __DIR__ . '/../controladores/UsuariosController.php';

function getUsuariosRoutes()
{
    $usuariosController = new UsuariosController();

    return [
        'GET' => [
            '/usuarios' => [$usuariosController, 'getAll'],
            '/usuarios/{id}' => [$usuariosController, 'getById'],
        ],
        'POST' => [
            '/usuarios' => [$usuariosController, 'create'],
        ],
        'PUT' => [
            '/usuarios/{id}' => [$usuariosController, 'update'],
        ],
        'DELETE' => [
            '/usuarios/{id}' => [$usuariosController, 'delete'],
        ]
    ];
}


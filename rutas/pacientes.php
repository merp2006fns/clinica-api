<?php
require_once __DIR__ . '/../controladores/PacientesController.php';

function getPacientesRoutes()
{
    $pacientesController = new PacientesController();

    return [
        'GET' => [
            '/pacientes' => [$pacientesController, 'getAll'],
            '/pacientes/{id}' => [$pacientesController, 'getById'],
            '/pacientes/search' => [$pacientesController, 'search'],
        ],
        'POST' => [
            '/pacientes' => [$pacientesController, 'create'],
        ],
        'PUT' => [
            '/pacientes/{id}' => [$pacientesController, 'update'],
        ],
        'DELETE' => [
            '/pacientes/{id}' => [$pacientesController, 'delete'],
        ]
    ];
}
<?php
require_once __DIR__ . '/../controladores/AuthController.php';

function getAuthRoutes()
{
    $authController = new AuthController();

    return [
        'POST' => [
            '/auth/login' => [$authController, 'login'],
            '/auth/logout' => [$authController, 'logout'],
            '/auth/registrar' => [$authController, 'registrarUsuario'],
        ],
        'GET' => [
            '/auth/verificar' => [$authController, 'verificarSesion'],
        ]
    ];
}

<?php
require_once __DIR__ . '/../utilidades/Router.php';
require_once __DIR__.'/rutaejemplo.php';
require_once __DIR__.'/auth.php';
require_once __DIR__.'/citas.php';
require_once __DIR__.'/pacientes.php';
require_once __DIR__.'/servicios.php';
require_once __DIR__.'/usuarios.php';
require_once __DIR__.'/medicos.php';

function getAllRoutes()
{
    $allRoutes = [];

    $allRoutes = array_merge_recursive(
        getRutasEjemplo(),
        getAuthRoutes(),
        getCitasRoutes(),
        getPacientesRoutes(),
        getServiciosRoutes(),
        getUsuariosRoutes(),
        getMedicosRoutes()
    );

    return $allRoutes;
}

$router = new Router();
$routes = getAllRoutes();

foreach ($routes as $method => $endpoints) {
    foreach ($endpoints as $path => $handler) {
        switch ($method) {
            case 'GET':
                $router->get($path, $handler);
                break;
            case 'POST':
                $router->post($path, $handler);
                break;
            case 'PUT':
                $router->put($path, $handler);
                break;
            case 'DELETE':
                $router->delete($path, $handler);
                break;
        }
    }
}

return $router;

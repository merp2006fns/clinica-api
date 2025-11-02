<?php
require_once __DIR__ . '/../modelos/UsuarioModel.php';
require_once __DIR__ . '/../utilidades/Auth.php';

/**
 * Controlador para manejar la autenticación y registro de usuarios
 * Gestiona login, logout, verificación de sesión y registro de nuevos usuarios
 */
class AuthController
{
    private $model;

    public function __construct()
    {
        $this->model = new UsuarioModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Inicia sesión de usuario verificando credenciales
     * @return JSON Respuesta con datos del usuario o error
     */
    public function login()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['correo']) || empty($data['password'])) {
                Response::error('Correo y contraseña son requeridos', 400);
                return;
            }

            $usuario = $this->model->getByCorreo($data['correo']);

            if (!$usuario || !$this->model->verificarPassword($data['password'], $usuario['password'])) {
                Response::error('Correo o contraseña incorrectos', 401);
                return;
            }

            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['logueado'] = true;
            $_SESSION['timestamp'] = time();

            Response::json([
                'success' => true,
                'message' => 'Login exitoso',
                'usuario' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'],
                    'rol' => $usuario['rol']
                ]
            ]);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Cierra la sesión del usuario actual
     * @return JSON Confirmación de cierre de sesión o error
     */
    public function logout()
    {
        Auth::requiereAuth();
        try {
            if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
                session_unset();
                session_destroy();
                Response::json(['success' => true, 'message' => 'Sesión cerrada']);
            } else {
                Response::error('No hay sesión activa', 400);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Verifica si existe una sesión activa y devuelve datos del usuario
     * @return JSON Estado de la sesión y datos del usuario si está logueado
     */
    public function verificarSesion()
    {
        try {
            if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
                Response::json([
                    'logueado' => true,
                    'usuario' => [
                        'id' => $_SESSION['id'],
                        'nombre' => $_SESSION['nombre'],
                        'correo' => $_SESSION['correo'],
                        'rol' => $_SESSION['rol']
                    ]
                ]);
            } else {
                Response::json(['logueado' => false]);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Registra un nuevo usuario (solo administradores)
     * @return JSON Confirmación de registro o error
     */
    public function registrarUsuario()
    {
        Auth::requiereAdmin();
        try {
            if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['rol'] !== 'admin') {
                Response::error('Acceso denegado. Se requieren privilegios de administrador.', 403);
                return;
            }

            $input = file_get_contents('php://input');

            $data = json_decode($input, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Response::error('Error en el formato de datos', 400);
                return;
            }

            $camposRequeridos = [
                'nombre' => $data['nombre'] ?? '',
                'password' => $data['password'] ?? '',
                'correo' => $data['correo'] ?? '',
                'rol' => $data['rol'] ?? ''
            ];

            $errores = [];

            if (empty(trim($camposRequeridos['correo']))) {
                $errores[] = 'correo';
            }
            if (empty(trim($camposRequeridos['password']))) {
                $errores[] = 'password';
            }
            if (empty(trim($camposRequeridos['nombre']))) {
                $errores[] = 'nombre';
            }
            if (empty(trim($camposRequeridos['rol']))) {
                $errores[] = 'rol';
            }

            if (!empty($errores)) {
                Response::error('Todos los campos son requeridos y no pueden estar vacíos. Problema con: ' . implode(', ', $errores), 400);
                return;
            }

            $usuarioExistente = $this->model->getByNombre(trim($data['nombre']));

            if ($usuarioExistente) {
                Response::error('El usuario ya existe', 400);
                return;
            }

            $emailExistente = $this->model->getByCorreo(trim($data['correo']));

            if ($emailExistente) {
                Response::error('El correo ya está registrado', 400);
                return;
            }

            $data['password'] = $this->model->hashPassword(trim($data['password']));

            $id = $this->model->insert($data);

            Response::json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'id' => $id
            ], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
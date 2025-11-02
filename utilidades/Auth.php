<?php
require_once __DIR__ . '/Response.php';

/**
 * Maneja la autenticación y autorización de usuarios en la aplicación
 * Proporciona métodos para gestionar sesiones y verificar permisos
 */
class Auth
{
    /**
     * Inicia sesión estableciendo los datos del usuario en la sesión
     * @param array $usuario Datos del usuario (id, nombre, correo, rol)
     */
    public static function iniciarSesion($usuario)
    {
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['logueado'] = true;
        $_SESSION['timestamp'] = time();
    }

    /**
     * Cierra la sesión actual eliminando todos los datos de sesión
     */
    public static function cerrarSesion()
    {
        session_unset();
        session_destroy();
    }

    /**
     * Verifica si hay un usuario autenticado en la sesión
     * @return bool True si el usuario está logueado, false en caso contrario
     */
    public static function estaLogueado()
    {
        return isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
    }

    /**
     * Obtiene el ID del usuario actualmente autenticado
     * @return int|null ID del usuario o null si no está autenticado
     */
    public static function getUsuarioId()
    {
        return $_SESSION['id'] ?? null;
    }

    /**
     * Obtiene el rol del usuario actualmente autenticado
     * @return string|null Rol del usuario o null si no está autenticado
     */
    public static function getRol()
    {
        return $_SESSION['rol'] ?? null;
    }

    /**
     * Verifica si el usuario actual tiene rol de administrador
     * @return bool True si es admin, false en caso contrario
     */
    public static function esAdmin()
    {
        return self::getRol() === 'admin';
    }

    /**
     * Requiere que el usuario esté autenticado
     * Termina la ejecución con error 401 si no hay sesión activa
     */
    public static function requiereAuth()
    {
        if (!self::estaLogueado()) {
            Response::error('No autorizado. Inicie sesión.', 401);
            exit;
        }
    }

    /**
     * Requiere que el usuario sea administrador
     * Termina la ejecución con error 401/403 si no cumple los requisitos
     */
    public static function requiereAdmin()
    {
        self::requiereAuth();
        if (!self::esAdmin()) {
            Response::error('Acceso denegado. Se requieren privilegios de administrador.', 403);
            exit;
        }
    }
}
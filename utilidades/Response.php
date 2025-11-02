<?php

/**
 * Maneja las respuestas HTTP en formato JSON
 * Proporciona métodos para enviar respuestas exitosas y errores estandarizados
 */
class Response
{
    /**
     * Envía una respuesta JSON con los datos proporcionados
     * @param mixed $data Datos a enviar en la respuesta
     * @param int $statusCode Código de estado HTTP (por defecto 200)
     */
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Envía una respuesta de error en formato JSON
     * @param string $message Mensaje de error descriptivo
     * @param int $statusCode Código de estado HTTP (por defecto 400)
     */
    public static function error($message, $statusCode = 400)
    {
        self::json(['error' => $message], $statusCode);
    }
}
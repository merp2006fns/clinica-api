<?php

/**
 * Clase singleton para gestionar la conexión a la base de datos
 * Implementa el patrón Singleton para asegurar una única instancia de conexión
 */
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $host = getenv("DB_HOST") ?: 'localhost';
        $user = getenv("DB_USER") ?: 'root';
        $pass = getenv("DB_PASSWORD") ?: '';
        $db = getenv("DB_NAME") ?: 'clinica';

        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new Exception('Error de conexión: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene la instancia única de la clase Database
     * @return Database Instancia singleton de la base de datos
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Obtiene la conexión PDO a la base de datos
     * @return PDO Objeto de conexión PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }
}
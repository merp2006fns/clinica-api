<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Modelo para gestionar las operaciones de la tabla usuarios
 * Maneja autenticación, búsqueda y gestión de usuarios del sistema
 */
class UsuarioModel extends BaseModel
{
    protected $table = "usuarios";
    protected $campos = [
        'id',
        'nombre',
        'correo',
        'password',
        'rol'
    ];

    /**
     * Obtiene un usuario por su nombre
     * @param string $nombre Nombre del usuario a buscar
     * @return array|bool Datos del usuario o false si no existe
     */
    public function getByNombre($nombre)
    {
        $sql = "SELECT * FROM {$this->table} WHERE nombre = :nombre";
        $result = $this->query($sql, ['nombre' => $nombre]);
        return $result ? $result[0] : false;
    }

    /**
     * Obtiene un usuario por su correo electrónico
     * @param string $correo Correo del usuario a buscar
     * @return array|bool Datos del usuario o false si no existe
     */
    public function getByCorreo($correo)
    {
        $sql = "SELECT * FROM {$this->table} WHERE correo = :correo";
        $result = $this->query($sql, ['correo' => $correo]);
        return $result ? $result[0] : false;
    }

    /**
     * Busca usuarios por término en nombre, correo o rol
     * @param string $termino Término de búsqueda
     * @param int|null $page Número de página para paginación
     * @param int|null $perPage Registros por página
     * @param array $conditions Condiciones adicionales de filtrado
     * @return array Usuarios encontrados con paginación
     */
    public function buscarByDatos($termino, $page = null, $perPage = null, $conditions)
    {
        return parent::buscarByTermino(
            $termino,
            ['nombre', 'correo', 'rol'],
            false,
            $conditions,
            'nombre ASC',
            [],
            '*',
            $page,
            $perPage
        );
    }

    /**
     * Obtiene usuarios paginados con condiciones opcionales
     * @param int $page Número de página
     * @param int $perPage Registros por página
     * @param array $conditions Condiciones de filtrado
     * @param string $orderBy Ordenamiento de resultados
     * @return array Usuarios paginados
     */
    public function getAllPaginated($page = 1, $perPage = 10, $conditions = [], $orderBy = 'nombre ASC')
    {
        return parent::getAllPaginated($page, $perPage, $conditions, $orderBy);
    }

    /**
     * Verifica si una contraseña coincide con su hash
     * @param string $password Contraseña en texto plano
     * @param string $passwordHash Hash de contraseña almacenado
     * @return bool True si la contraseña es válida
     */
    public function verificarPassword($password, $passwordHash)
    {
        return password_verify($password, $passwordHash);
    }

    /**
     * Genera hash seguro de una contraseña
     * @param string $password Contraseña en texto plano
     * @return string Hash de la contraseña
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Modelo para gestionar las operaciones de la tabla pacientes
 * Maneja la consulta y búsqueda de pacientes del sistema
 */
class PacientesModel extends BaseModel
{
    protected $table = "pacientes";
    protected $campos = [
        'id',
        'nombre',
        'telefono',
        'correo',
        'fecha_registro'
    ];

    /**
     * Busca pacientes por término en nombre, teléfono o correo
     * @param string $termino Término de búsqueda
     * @param int|null $page Número de página para paginación
     * @param int|null $perPage Registros por página
     * @return array Pacientes encontrados con paginación
     */
    public function buscarByDatos(string $termino, $page = null, $perPage = null)
    {
        return $this->buscarByTermino($termino, ['nombre', 'telefono', 'correo','id'], false, [], '', [], '*', $page, $perPage);
    }

    /**
     * Obtiene pacientes paginados con condiciones opcionales
     * @param int $page Número de página
     * @param int $perPage Registros por página
     * @param array $conditions Condiciones de filtrado
     * @param string $orderBy Ordenamiento de resultados
     * @return array Pacientes paginados
     */
    public function getAllPaginated($page = 1, $perPage = 10, $conditions = [], $orderBy = 'nombre ASC')
    {
        return parent::getAllPaginated($page, $perPage, $conditions, $orderBy);
    }
}
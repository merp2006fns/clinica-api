<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Modelo para gestionar las operaciones de la tabla servicios
 * Maneja la consulta y búsqueda de servicios médicos
 */
class ServiciosModel extends BaseModel
{
    protected $table = "servicios";
    protected $campos = [
        'id',
        'nombre',
        'precio'
    ];

    /**
     * Busca servicios por término en el nombre
     * @param string $termino Término de búsqueda
     * @param int|null $page Número de página para paginación
     * @param int|null $perPage Registros por página
     * @return array Servicios encontrados con paginación
     */
    public function buscarByDatos(string $termino, $page = null, $perPage = null)
    {
        return $this->buscarByTermino($termino, ['nombre', 'id'], false, [], '', [], '*', $page, $perPage);
    }

    /**
     * Obtiene servicios paginados con condiciones opcionales
     * @param int $page Número de página
     * @param int $perPage Registros por página
     * @param array $conditions Condiciones de filtrado
     * @param string $orderBy Ordenamiento de resultados
     * @return array Servicios paginados
     */
    public function getAllPaginated($page = 1, $perPage = 10, $conditions = [], $orderBy = 'nombre ASC')
    {
        return parent::getAllPaginated($page, $perPage, $conditions, $orderBy);
    }
}
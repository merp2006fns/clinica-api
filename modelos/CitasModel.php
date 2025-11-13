<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Modelo para gestionar las operaciones de la tabla citas
 * Proporciona métodos específicos para citas con joins y búsquedas avanzadas
 */
class CitasModel extends BaseModel
{
    protected $table = "citas";
    protected $campos = [
        'id',
        'paciente_id',
        'servicio_id',
        'medico_usuario_id',
        'fecha_hora',
        'estado',
        'notas'
    ];

    /**
     * Obtiene todas las citas con información de paciente, médico y servicio
     * @param array $conditions Condiciones de filtrado
     * @param string $orderBy Ordenamiento de resultados
     * @param int|null $page Número de página para paginación
     * @param int|null $perPage Registros por página
     * @return array Citas con datos relacionados y paginación
     */
    public function getAllWithJoin($conditions = [], $orderBy = 'c.fecha_hora DESC', $page = null, $perPage = null)
    {
        $joins = [
            "INNER JOIN pacientes p ON c.paciente_id = p.id",
            "INNER JOIN servicios s ON c.servicio_id = s.id",
            "INNER JOIN usuarios u ON c.medico_usuario_id = u.id"
        ];

        $selectFields = "c.*, 
                        p.nombre AS paciente_nombre,
                        u.nombre AS medico_nombre,
                        s.nombre AS servicio_nombre";

        $config = [
            'joins' => $joins,
            'selectFields' => $selectFields,
            'conditions' => $conditions,
            'orderBy' => $orderBy,
            'tableAlias' => 'c'
        ];

        if ($page !== null && $perPage !== null) {
            $config['page'] = $page;
            $config['perPage'] = $perPage;
        }

        return parent::getAllWithJoin($config);
    }

    /**
     * Busca citas por término en campos relacionados con joins
     * @param string $termino Término de búsqueda
     * @param bool $exacto Si la búsqueda debe ser exacta
     * @param array $conditions Condiciones adicionales de filtrado
     * @param string $orderBy Ordenamiento de resultados
     * @param int|null $page Número de página para paginación
     * @param int|null $perPage Registros por página
     * @return array Citas encontradas con datos relacionados
     */
    public function buscarByTerminoWithJoin($termino, $exacto = false, $conditions = [], $orderBy = 'c.fecha_hora DESC', $page = null, $perPage = null)
    {
        if (empty($termino)) {
            return $this->getAllWithJoin($conditions, $orderBy, $page, $perPage);
        }

        $joins = [
            "INNER JOIN pacientes p ON c.paciente_id = p.id",
            "INNER JOIN servicios s ON c.servicio_id = s.id",
            "INNER JOIN usuarios u ON c.medico_usuario_id = u.id"
        ];

        $selectFields = "c.*, 
                        p.nombre AS paciente_nombre,
                        u.nombre AS medico_nombre,
                        s.nombre AS servicio_nombre";

        $camposBusqueda = [
            'c.notas',
            'c.id',
            'c.medico_usuario_id',
            'c.paciente_id',
            'c.estado',
            'p.nombre',
            'u.nombre',
            's.nombre'
        ];

        return $this->buscarByTermino(
            $termino,
            $camposBusqueda,
            $exacto,
            $conditions,
            $orderBy,
            $joins,
            $selectFields,
            $page,
            $perPage,
            'c'
        );
    }

    /**
     * Busca citas por nombre del paciente
     * @param string $nombrePaciente Nombre o parte del nombre del paciente
     * @param int|null $page Número de página para paginación
     * @param int|null $perPage Registros por página
     * @return array Citas del paciente encontradas
     */
    public function buscarPorPaciente($nombrePaciente, $page = null, $perPage = null)
    {
        return $this->buscarByTerminoWithJoin(
            $nombrePaciente,
            false,
            [],
            'c.fecha_hora DESC',
            $page,
            $perPage
        );
    }

    /**
     * Busca citas por nombre del médico
     * @param string $nombreMedico Nombre o parte del nombre del médico
     * @param int|null $page Número de página para paginación
     * @param int|null $perPage Registros por página
     * @return array Citas del médico encontradas
     */
    public function buscarPorMedico($nombreMedico, $page = null, $perPage = null)
    {
        return $this->buscarByTerminoWithJoin(
            $nombreMedico,
            false,
            [],
            'c.fecha_hora DESC',
            $page,
            $perPage
        );
    }

    /**
     * Busca citas por término y fecha específica
     * @param string $termino Término de búsqueda
     * @param string|null $fecha Fecha específica para filtrar
     * @param int|null $page Número de página para paginación
     * @param int|null $perPage Registros por página
     * @return array Citas encontradas ordenadas por fecha ascendente
     */
    public function buscarCitasPorFechaYTermino($termino, $fecha = null, $page = null, $perPage = null)
    {
        $conditions = [];
        if ($fecha) {
            $conditions['DATE(c.fecha_hora)'] = $fecha;
        }

        return $this->buscarByTerminoWithJoin(
            $termino,
            false,
            $conditions,
            'c.fecha_hora ASC',
            $page,
            $perPage
        );
    }

    /**
     * Obtiene citas filtradas por múltiples criterios
     * @param array $filtros Array con criterios de filtrado
     * @return array Citas filtradas con datos relacionados
     */
    public function getCitasFiltradas($filtros = [])
    {
        $conditions = [];

        if (!empty($filtros['medico_id'])) {
            $conditions['c.medico_usuario_id'] = $filtros['medico_id'];
        }

        if (!empty($filtros['estado'])) {
            $conditions['c.estado'] = $filtros['estado'];
        }

        if (!empty($filtros['fecha'])) {
            $conditions['DATE(c.fecha_hora)'] = $filtros['fecha'];
        }

        if (!empty($filtros['paciente_id'])) {
            $conditions['c.paciente_id'] = $filtros['paciente_id'];
        }

        $orderBy = 'c.fecha_hora ' . ($filtros['orden'] ?? 'DESC');

        $page = $filtros['page'] ?? null;
        $perPage = $filtros['per_page'] ?? null;

        return $this->getAllWithJoin($conditions, $orderBy, $page, $perPage);
    }

    /**
     * Obtiene citas paginadas con condiciones opcionales
     * @param int $page Número de página
     * @param int $perPage Registros por página
     * @param array $conditions Condiciones de filtrado
     * @param string $orderBy Ordenamiento de resultados
     * @return array Citas paginadas con datos relacionados
     */
    public function getAllPaginated($page = 1, $perPage = 10, $conditions = [], $orderBy = 'c.fecha_hora DESC')
    {
        return $this->getAllWithJoin($conditions, $orderBy, $page, $perPage);
    }
}
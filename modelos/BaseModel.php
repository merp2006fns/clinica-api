<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Modelo base abstracto que proporciona operaciones CRUD básicas
 * y métodos avanzados para consultas con joins, paginación y búsqueda
 */
abstract class BaseModel
{
    protected $pdo;
    protected $table;
    protected $campos = [];

    public function __construct()
    {
        $database = Database::getInstance();
        $this->pdo = $database->getConnection();
    }

    /**
     * Ejecuta una consulta SQL personalizada
     * @param string $sql Consulta SQL a ejecutar
     * @param array $params Parámetros para la consulta preparada
     * @return array|bool Resultados para SELECT, true para otras consultas
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            if (stripos($sql, 'SELECT') === 0) {
                return $stmt->fetchAll();
            }

            return true;
        } catch (PDOException $e) {
            throw new Exception("Error en query: " . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los registros de la tabla con condiciones opcionales
     * @param array $conditions Condiciones WHERE como array clave-valor
     * @param string $orderBy Ordenamiento para los resultados
     * @return array Array con todos los registros encontrados
     */
    public function getAll($conditions = [], $orderBy = '')
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $key => $value) {
                $whereParts[] = "$key = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        return $this->query($sql, $params);
    }

    /**
     * Obtiene registros paginados con condiciones opcionales
     * @param int $page Número de página
     * @param int $perPage Cantidad de registros por página
     * @param array $conditions Condiciones WHERE como array clave-valor
     * @param string $orderBy Ordenamiento para los resultados
     * @return array Datos paginados con información de paginación
     */
    public function getAllPaginated($page = 1, $perPage = 10, $conditions = [], $orderBy = '')
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $key => $value) {
                $whereParts[] = "$key = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                if ($key === 'limit' || $key === 'offset') {
                    $stmt->bindValue(":$key", (int)$value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total = $this->count($conditions);
            $totalPages = ceil($total / $perPage);

            return [
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ];
        } catch (PDOException $e) {
            throw new Exception("Error en getAllPaginated: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un registro por su ID
     * @param int $id ID del registro a buscar
     * @return array|bool Array con datos del registro o false si no existe
     */
    public function getById(int $id)
    {
        $result = $this->getAll(['id' => $id]);
        return $result ? $result[0] : false;
    }

    /**
     * Inserta un nuevo registro en la tabla
     * @param array $data Datos del registro a insertar
     * @return string ID del registro insertado
     */
    public function insert(array $data)
    {
        $filtered_data = array_intersect_key($data, array_flip($this->campos));

        if (empty($filtered_data)) {
            throw new Exception("No hay datos válidos para insertar");
        }

        $campos_list = implode(', ', array_keys($filtered_data));
        $placeholders = ':' . implode(', :', array_keys($filtered_data));

        $sql = "INSERT INTO {$this->table} ({$campos_list}) VALUES ({$placeholders})";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($filtered_data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error en insert: " . $e->getMessage());
        }
    }

    /**
     * Actualiza un registro por su ID
     * @param int $id ID del registro a actualizar
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function updateById(int $id, array $data)
    {
        $set_parts = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $this->campos)) {
                $set_parts[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
        }

        if (empty($set_parts)) {
            throw new Exception("No hay campos válidos para actualizar");
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set_parts) . " WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Error en updateById: " . $e->getMessage());
        }
    }

    /**
     * Elimina un registro por su ID
     * @param int $id ID del registro a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function deleteById(int $id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Error en deleteById: " . $e->getMessage());
        }
    }

    /**
     * Cuenta el total de registros que cumplen con las condiciones
     * @param array $conditions Condiciones WHERE como array clave-valor
     * @return int Número total de registros
     */
    public function count($conditions = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereParts = [];
            foreach ($conditions as $key => $value) {
                $whereParts[] = "$key = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        $result = $this->query($sql, $params);
        return $result[0]['total'];
    }

    /**
     * Obtiene registros con joins entre tablas y paginación
     * @param array $config Configuración para joins, condiciones, paginación, etc.
     * @return array Datos con información de paginación si se solicita
     */
    public function getAllWithJoin($config = [])
    {
        $defaultConfig = [
            'joins' => [],
            'selectFields' => '*',
            'conditions' => [],
            'orderBy' => '',
            'groupBy' => '',
            'limit' => null,
            'offset' => null,
            'page' => null,
            'perPage' => null,
            'tableAlias' => null
        ];

        $config = array_merge($defaultConfig, $config);

        if ($config['page'] !== null && $config['perPage'] !== null) {
            $config['offset'] = ($config['page'] - 1) * $config['perPage'];
            $config['limit'] = $config['perPage'];
        }

        $tableName = $config['tableAlias'] ? "{$this->table} {$config['tableAlias']}" : $this->table;
        $sql = "SELECT {$config['selectFields']} FROM {$tableName}";

        foreach ($config['joins'] as $join) {
            $sql .= " $join";
        }

        $params = [];
        $whereParts = [];
        $paramCounter = 0;

        if (!empty($config['conditions'])) {
            foreach ($config['conditions'] as $key => $value) {
                $cleanKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
                $paramName = 'cond_' . $cleanKey . '_' . $paramCounter++;
                $whereParts[] = "$key = :$paramName";
                $params[$paramName] = $value;
            }
            if (!empty($whereParts)) {
                $sql .= " WHERE " . implode(' AND ', $whereParts);
            }
        }

        if ($config['groupBy']) {
            $sql .= " GROUP BY {$config['groupBy']}";
        }

        if ($config['orderBy']) {
            $sql .= " ORDER BY {$config['orderBy']}";
        }

        if ($config['limit'] !== null) {
            $sql .= " LIMIT :limit";
            $params['limit'] = $config['limit'];
            if ($config['offset'] !== null) {
                $sql .= " OFFSET :offset";
                $params['offset'] = $config['offset'];
            }
        }

        if ($config['page'] !== null && $config['perPage'] !== null) {
            try {
                $stmt = $this->pdo->prepare($sql);
                foreach ($params as $key => $value) {
                    if ($key === 'limit' || $key === 'offset') {
                        $stmt->bindValue(":$key", (int)$value, PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue(":$key", $value);
                    }
                }
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $countTableName = $config['tableAlias'] ? "{$this->table} {$config['tableAlias']}" : $this->table;
                $countSql = "SELECT COUNT(*) as total FROM {$countTableName}";
                foreach ($config['joins'] as $join) {
                    $countSql .= " $join";
                }
                if (!empty($whereParts)) {
                    $countSql .= " WHERE " . implode(' AND ', $whereParts);
                }
                $countParams = [];
                foreach ($params as $key => $value) {
                    if ($key !== 'limit' && $key !== 'offset') {
                        $countParams[$key] = $value;
                    }
                }
                if ($config['groupBy']) {
                    $countSql = "SELECT COUNT(*) as total FROM (" . $countSql . ") as count_table";
                }

                $countResult = $this->query($countSql, $countParams);
                $total = $countResult[0]['total'] ?? 0;
                $totalPages = ceil($total / $config['perPage']);

                return [
                    'data' => $data,
                    'pagination' => [
                        'current_page' => $config['page'],
                        'per_page' => $config['perPage'],
                        'total' => $total,
                        'total_pages' => $totalPages,
                        'has_next' => $config['page'] < $totalPages,
                        'has_prev' => $config['page'] > 1
                    ]
                ];
            } catch (PDOException $e) {
                throw new Exception("Error en getAllWithJoin: " . $e->getMessage());
            }
        }

        return $this->query($sql, $params);
    }

    /**
     * Busca registros por término en múltiples campos con opciones avanzadas
     * @param string $termino Término de búsqueda
     * @param array $camposBusqueda Campos donde buscar el término
     * @param bool $exacto Si la búsqueda debe ser exacta (true) o parcial (false)
     * @param array $conditions Condiciones adicionales WHERE
     * @param string $orderBy Ordenamiento de resultados
     * @param array $joins Joins con otras tablas
     * @param string $selectFields Campos a seleccionar
     * @param int|null $page Número de página para paginación
     * @param int|null $perPage Registros por página para paginación
     * @param string|null $tableAlias Alias para la tabla principal
     * @return array Datos encontrados con paginación si se solicita
     */
    public function buscarByTermino($termino, $camposBusqueda = [], $exacto = false, $conditions = [], $orderBy = '', $joins = [], $selectFields = '*', $page = null, $perPage = null, $tableAlias = null)
    {
        if (empty($termino)) {
            if ($page !== null && $perPage !== null) {
                return $this->getAllPaginated($page, $perPage, $conditions, $orderBy);
            }
            return $this->getAll($conditions, $orderBy);
        }

        if (empty($camposBusqueda)) {
            $camposBusqueda = $this->campos;
        }

        if (empty($joins)) {
            $camposValidos = array_intersect($camposBusqueda, $this->campos);
            if (empty($camposValidos)) {
                throw new Exception("No hay campos válidos para búsqueda");
            }
        } else {
            $camposValidos = $camposBusqueda;
        }

        $whereParts = [];
        $params = [];
        $paramCounter = 0;

        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                $cleanKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
                $paramName = 'cond_' . $cleanKey . '_' . $paramCounter++;
                $whereParts[] = "$key = :$paramName";
                $params[$paramName] = $value;
            }
        }

        $busquedaParts = [];
        $searchParamCounter = 0;
        foreach ($camposValidos as $campo) {
            $paramName = 'busq_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $campo) . '_' . $searchParamCounter++;
            if ($exacto) {
                $busquedaParts[] = "$campo = :$paramName";
                $params[$paramName] = $termino;
            } else {
                $busquedaParts[] = "$campo LIKE :$paramName";
                $params[$paramName] = "%$termino%";
            }
        }

        if (!empty($busquedaParts)) {
            $whereParts[] = "(" . implode(' OR ', $busquedaParts) . ")";
        }

        $tableName = $tableAlias ? "{$this->table} {$tableAlias}" : $this->table;
        $sql = "SELECT $selectFields FROM {$tableName}";

        if (!empty($joins)) {
            foreach ($joins as $join) {
                $sql .= " $join";
            }
        }

        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($page !== null && $perPage !== null) {
            $offset = ($page - 1) * $perPage;
            $sql .= " LIMIT :limit OFFSET :offset";
            $params['limit'] = $perPage;
            $params['offset'] = $offset;

            try {
                $stmt = $this->pdo->prepare($sql);
                foreach ($params as $key => $value) {
                    if ($key === 'limit' || $key === 'offset') {
                        $stmt->bindValue(":$key", (int)$value, PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue(":$key", $value);
                    }
                }
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $countTableName = $tableAlias ? "{$this->table} {$tableAlias}" : $this->table;
                $countSql = "SELECT COUNT(*) as total FROM {$countTableName}";
                if (!empty($joins)) {
                    foreach ($joins as $join) {
                        $countSql .= " $join";
                    }
                }
                if (!empty($whereParts)) {
                    $countSql .= " WHERE " . implode(' AND ', $whereParts);
                }

                $countParams = [];
                foreach ($params as $key => $value) {
                    if ($key !== 'limit' && $key !== 'offset') {
                        $countParams[$key] = $value;
                    }
                }

                $countResult = $this->query($countSql, $countParams);
                $total = $countResult[0]['total'] ?? 0;
                $totalPages = ceil($total / $perPage);

                return [
                    'data' => $data,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'total_pages' => $totalPages,
                        'has_next' => $page < $totalPages,
                        'has_prev' => $page > 1
                    ]
                ];
            } catch (PDOException $e) {
                throw new Exception("Error en buscarByTermino: " . $e->getMessage());
            }
        }

        return $this->query($sql, $params);
    }
}
<?php
require_once __DIR__ . '/../utilidades/Auth.php';

/**
 * Controlador base abstracto que proporciona operaciones CRUD básicas
 * Define métodos comunes para todos los controladores con autenticación
 */
abstract class BaseController
{
    protected $model;
    protected $modelClass;
    protected $requiredFields = [];
    protected $idField = 'id';

    public function __construct()
    {
        if ($this->modelClass) {
            $this->model = new $this->modelClass();
        }
    }

    /**
     * Obtiene todos los registros del modelo
     * @return JSON Lista de todos los registros
     */
    public function getAll()
    {
        Auth::requiereAuth();
        try {
            $method = method_exists($this->model, 'getAllWithOwner')
                ? 'getAllWithOwner'
                : 'getAll';

            $data = $this->model->$method();
            Response::json($data);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Obtiene un registro específico por su ID
     * @param int $id ID del registro a buscar
     * @return JSON Datos del registro encontrado
     */
    public function getById($id)
    {
        Auth::requiereAuth();
        try {
            $this->validateId($id);

            $data = $this->model->getById((int)$id);
            if ($data) {
                Response::json($data);
            } else {
                Response::error($this->getNotFoundMessage(), 404);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Crea un nuevo registro en la base de datos
     * @return JSON Confirmación de creación con ID del nuevo registro
     */
    public function create()
    {
        Auth::requiereAuth();
        try {
            $data = $this->getInputData();
            $this->validateRequiredFields($data);

            $id = $this->model->insert($data);
            Response::json([
                'id' => $id,
                'message' => $this->getCreatedMessage()
            ], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Actualiza un registro existente por su ID
     * @param int $id ID del registro a actualizar
     * @return JSON Confirmación de actualización
     */
    public function update($id)
    {
        Auth::requiereAuth();
        try {
            $this->validateId($id);
            $data = $this->getInputData();

            $success = $this->model->updateById((int)$id, $data);
            if ($success) {
                Response::json(['message' => $this->getUpdatedMessage()]);
            } else {
                Response::error($this->getNotFoundMessage(), 404);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Elimina un registro por su ID (física o lógicamente)
     * @param int $id ID del registro a eliminar
     * @return JSON Confirmación de eliminación
     */
    public function delete($id)
    {
        Auth::requiereAuth();
        try {
            $this->validateId($id);

            if (method_exists($this->model, 'softDeleteById')) {
                $success = $this->model->softDeleteById((int)$id);
            } else {
                $success = $this->model->deleteById((int)$id);
            }

            if ($success) {
                Response::json(['message' => $this->getDeletedMessage()]);
            } else {
                Response::error($this->getNotFoundMessage(), 404);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Valida que el ID proporcionado sea numérico
     * @param mixed $id ID a validar
     */
    protected function validateId($id)
    {
        if (!is_numeric($id)) {
            Response::error('ID debe ser numérico', 400);
            exit;
        }
    }

    /**
     * Obtiene y decodifica los datos de entrada JSON
     * @return array Datos decodificados del request
     */
    protected function getInputData()
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    /**
     * Valida que todos los campos requeridos estén presentes en los datos
     * @param array $data Datos a validar
     */
    protected function validateRequiredFields($data)
    {
        foreach ($this->requiredFields as $field) {
            if (empty($data[$field])) {
                Response::error("El campo '$field' es requerido", 400);
                exit;
            }
        }
    }

    /**
     * Mensaje para cuando no se encuentra un registro
     * @return string Mensaje de error
     */
    protected function getNotFoundMessage()
    {
        return 'Registro no encontrado';
    }

    /**
     * Mensaje para cuando se crea un registro exitosamente
     * @return string Mensaje de confirmación
     */
    protected function getCreatedMessage()
    {
        return 'Registro creado exitosamente';
    }

    /**
     * Mensaje para cuando se actualiza un registro exitosamente
     * @return string Mensaje de confirmación
     */
    protected function getUpdatedMessage()
    {
        return 'Registro actualizado exitosamente';
    }

    /**
     * Mensaje para cuando se elimina un registro exitosamente
     * @return string Mensaje de confirmación
     */
    protected function getDeletedMessage()
    {
        return 'Registro eliminado exitosamente';
    }
}
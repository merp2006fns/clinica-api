<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../modelos/UsuarioModel.php';
require_once __DIR__ . '/../utilidades/Response.php';
require_once __DIR__ . '/../utilidades/Auth.php';

/**
 * Controlador para gestionar operaciones relacionadas con médicos
 * Proporciona búsqueda y consulta de usuarios con rol de médico
 */
class MedicosController extends BaseController
{
    protected $modelClass = 'UsuarioModel';

    /**
     * Obtiene un médico específico por su ID
     * @param int $id ID del médico a buscar
     * @return JSON Datos del médico encontrado
     */
    public function getById($id)
    {
        Auth::requiereAuth();
        try {
            $this->validateId($id);
            $medico = $this->model->getById($id);
            Response::json($medico);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Busca médicos por término o obtiene lista limitada
     * @return JSON Lista de médicos filtrados por rol y término de búsqueda
     */
    public function search()
    {
        Auth::requiereAuth();

        try {
            $search = $_GET['termino'] ?? '';
            $limit = $_GET['limit'] ?? 0;

            if (empty($search) && $limit >= 1) {
                $medicos = $this->model->getAll();

                Response::json(array_slice($medicos, 0, $limit));
                return;
            }

            if (empty($search) || strlen($search) < 2) {
                Response::json([]);
                return;
            }

            $result = $this->model->buscarByDatos($search, null, null);

            if (isset($result['data'])) {
                $medicos = $result['data'];
            } else {
                $medicos = $result;
            }

            $medicos = array_filter($medicos, function ($u) {
                return isset($u['rol']) && $u['rol'] === 'medico';
            });

            $medicos = array_slice($medicos, 0, (int)$limit);

            foreach ($medicos as &$medico) {
                unset($medico['password']);
            }

            Response::json(array_values($medicos));
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
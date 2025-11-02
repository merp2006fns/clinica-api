<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../modelos/UsuarioModel.php';
require_once __DIR__ . '/../utilidades/Response.php';
require_once __DIR__ . '/../utilidades/Auth.php';

/**
 * Controlador para gestionar operaciones CRUD de usuarios
 * Maneja listado, búsqueda, creación, actualización y eliminación de usuarios
 */
class UsuariosController extends BaseController
{
    protected $modelClass = 'UsuarioModel';
    protected $requiredFields = ['nombre', 'correo', 'password', 'rol'];

    /**
     * Obtiene todos los usuarios con opciones de búsqueda, filtrado y paginación
     * @return JSON Lista de usuarios o resultados de búsqueda
     */
    public function getAll()
    {
        Auth::requiereAdmin();

        try {
            $page = $_GET['page'] ?? null;
            $perPage = $_GET['per_page'] ?? null;
            $search = $_GET['search'] ?? '';
            $rol = $_GET['rol'] ?? '';

            $conditions = [];
            if (!empty($rol)) {
                $conditions['rol'] = $rol;
            }

            if (!empty($search)) {
                $result = $this->model->buscarByDatos($search, $page, $perPage, $conditions);
                Response::json($result);
            } elseif ($page !== null && $perPage !== null) {
                $result = $this->model->getAllPaginated((int)$page, (int)$perPage, $conditions);
                Response::json($result);
            } else {
                $data = $this->model->getAll($conditions, 'nombre ASC');
                foreach ($data as &$usuario) {
                    unset($usuario['password']);
                }
                Response::json($data);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Obtiene un usuario específico por su ID
     * @param int $id ID del usuario a buscar
     * @return JSON Datos del usuario sin contraseña
     */
    public function getById($id)
    {
        Auth::requiereAuth();

        try {
            $this->validateId($id);

            $rol = Auth::getRol();
            $usuarioId = Auth::getUsuarioId();

            if ($rol !== 'admin' && (int)$id != $usuarioId) {
                Response::error('No tienes permiso para ver este usuario', 403);
                return;
            }

            $usuario = $this->model->getById((int)$id);

            if ($usuario) {
                unset($usuario['password']);
                Response::json($usuario);
            } else {
                Response::error('Usuario no encontrado', 404);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * Crea un nuevo usuario en el sistema
     * @return JSON Confirmación de creación con ID del nuevo usuario
     */
    public function create()
    {
        Auth::requiereAdmin();

        try {
            $data = $this->getInputData();
            $this->validateRequiredFields($data);

            if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                Response::error('Formato de correo inválido', 400);
                return;
            }

            $rolesValidos = ['admin', 'medico', 'recepcion'];
            if (!in_array($data['rol'], $rolesValidos)) {
                Response::error('Rol inválido', 400);
                return;
            }

            $usuarioExistente = $this->model->getByCorreo($data['correo']);
            if ($usuarioExistente) {
                Response::error('El correo ya está registrado', 400);
                return;
            }

            $usuarioExistente = $this->model->getByNombre($data['nombre']);
            if ($usuarioExistente) {
                Response::error('El nombre de usuario ya existe', 400);
                return;
            }

            $data['password'] = $this->model->hashPassword($data['password']);

            $id = $this->model->insert($data);
            Response::json([
                'id' => $id,
                'message' => 'Usuario creado exitosamente'
            ], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Actualiza un usuario existente
     * @param int $id ID del usuario a actualizar
     * @return JSON Confirmación de actualización
     */
    public function update($id)
    {
        Auth::requiereAuth();

        try {
            $this->validateId($id);

            $rol = Auth::getRol();
            $usuarioId = Auth::getUsuarioId();

            if ($rol !== 'admin' && (int)$id != $usuarioId) {
                Response::error('No tienes permiso para actualizar este usuario', 403);
                return;
            }

            $data = $this->getInputData();

            if (isset($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                Response::error('Formato de correo inválido', 400);
                return;
            }

            if (isset($data['rol'])) {
                if ($rol !== 'admin') {
                    Response::error('Solo el administrador puede cambiar roles', 403);
                    return;
                }
                $rolesValidos = ['admin', 'medico', 'recepcion'];
                if (!in_array($data['rol'], $rolesValidos)) {
                    Response::error('Rol inválido', 400);
                    return;
                }
            }

            if (isset($data['correo'])) {
                $usuarioExistente = $this->model->getByCorreo($data['correo']);
                if ($usuarioExistente && $usuarioExistente['id'] != $id) {
                    Response::error('El correo ya está registrado', 400);
                    return;
                }
            }

            if (isset($data['password'])) {
                $data['password'] = $this->model->hashPassword($data['password']);
            }

            $success = $this->model->updateById((int)$id, $data);
            if ($success) {
                Response::json(['message' => 'Usuario actualizado exitosamente']);
            } else {
                Response::error('Usuario no encontrado', 404);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * Elimina un usuario del sistema
     * @param int $id ID del usuario a eliminar
     * @return JSON Confirmación de eliminación
     */
    public function delete($id)
    {
        Auth::requiereAdmin();

        try {
            $this->validateId($id);

            $usuarioId = Auth::getUsuarioId();
            if ((int)$id == $usuarioId) {
                Response::error('No puedes eliminar tu propio usuario', 400);
                return;
            }

            require_once __DIR__ . '/../modelos/CitasModel.php';
            $citasModel = new CitasModel();
            $citas = $citasModel->getAll(['medico_usuario_id' => $id]);

            if (!empty($citas)) {
                Response::error('No se puede eliminar el usuario porque tiene citas asociadas', 400);
                return;
            }

            $success = $this->model->deleteById((int)$id);

            if ($success) {
                Response::json(['message' => 'Usuario eliminado exitosamente']);
            } else {
                Response::error('Usuario no encontrado', 404);
            }
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
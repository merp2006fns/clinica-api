<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../modelos/SaludoModel.php';
class SaludoController extends BaseController
{
    protected $modelClass = 'SaludoModel';
    protected $requiredFields = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function getSaludo($saludo = 'usuario')
    {
        try {
            $data = [
                "saludo" => "hola $saludo"
            ];
            Response::json($data);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}

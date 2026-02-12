<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SitModel;
use App\Models\ValidationModel;
use App\Libraries\DbUtils;

class Sits extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->sitModel = new SitModel();
	}

    public function index()
    {
        return redirect()->to('users/explore');
    }

// Exploración de usuarios
//-----------------------------------------------------------------------------

    /**
     * AJAX - JSON
     * Insertar un nuevo post en la tabla posts
     * 2026-01-19
     */
    public function create()
    {
        //Preparar array del registro
        $input = $this->request->getPost();
        $aRow = $this->sitModel->inputToRow($input);
        $aRow['type_id'] = '101';

        //Guardar
        $data['savedId'] = $this->sitModel->insert($aRow);

        //Si se creó, datos complementarios
        if ($data['savedId']) {
            $data['idcode'] = DbUtils::setIdCode('sits', $data['savedId']);
        }
        
        $data['errors'] = $this->sitModel->errors();

        return $this->response->setJSON($data);
    }
}
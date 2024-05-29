<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->userModel = new UserModel();
	}

    public function index()
    {
        return redirect()->to('users/explore');
    }

// Exploración de usuarios
//-----------------------------------------------------------------------------

    /**
     * JSON
     * Buscar usuarios según filtros y condiciones solicitadas
     */
    public function search()
    {   
        $request = \Config\Services::request();
        $input = $request->getPost();
        
        $search = new \App\Libraries\Search();

        $data = $this->userModel->search($input);
        //unset($data['settings']);
        //unset($data['filters']);

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Validar formularios de creación y actualización de datos de usuarios
     * 2023-02-04
     */
    public function validateForm()
    {
        $data = ['status' => 1, 'error' => null];

        $request = \Config\Services::request();
        $userId = $request->getPost('id');
        
        $emailValidation = \App\Models\ValidationModel::email($userId, $request->getPost('email'));
        $documentNumberValidation = \App\Models\ValidationModel::documentNumber($userId, $request->getPost('document_number'));
        $usernameValidation = \App\Models\ValidationModel::username($userId, $request->getPost('username'));

        $validation = array_merge($emailValidation, $documentNumberValidation, $usernameValidation);
        $data['validation'] = (array) $validation;

        if ( $documentNumberValidation['documentNumberUnique'] == 0 ) {
            $data['error'] .= 'El número de documento ya está registrado. ';
        }

        if ( $emailValidation['emailUnique'] == 0 ) {
            $data['error'] .= 'El e-mail escrito ya está registrado. ';
        }

        if ( $usernameValidation['usernameUnique'] == 0 ) {
            $data['error'] .= 'El username escrito ya está registrado. ';
        }

        $data['status'] = ( $data['error'] ) ? 0 : 1 ;

        return $this->response->setJSON($data);
    }

    /**
     * AJAX - JSON
     * Insertar un nuevo usuario en la tabla users
     * 2023-01-29
     */
    public function create()
    {
        //Preparar array del registro
        $input = $this->request->getPost();
        $aRow = $this->userModel->inputToRow($input);

        //Guardar
        $data['savedId'] = $this->userModel->insert($aRow);

        //Si se creó, datos complementarios
        if ($data['savedId']) {
            $data['idcode'] = $this->dbTools->setIdCode('users', $data['savedId']);
            //$data['aRow'] = $aRow;
        }
        
        $data['errors'] = $this->userModel->errors();

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Actualizar los datos de un usuario, tabla users
     * 2023-03-12
     */
    public function update($idCode)
    {
        $input = $this->request->getPost();
        $aRow = $this->userModel->inputToRow($input);

        $data['saved'] = $this->userModel->where('idcode',$idCode)
                            ->set($aRow)->update();

        if ( $data['saved'] ) {
            $data['savedId'] = $aRow['id'];
        } else {
            $data['errors'] = $this->userModel->errors();
        }

        return $this->response->setJSON($data);
    }

// Eliminación
//-----------------------------------------------------------------------------

    /**
     * Eliminación de usuarios seleccionados
     * 2023-02-18
     */
    public function deleteSelected()
    {
        $selected = explode(',', $this->request->getPost('selected'));
        $results = [];
        
        foreach ($selected as $idCode) {
            $results[$idCode] = $this->userModel->deleteByIdCode($idCode);
        }

        $data['results'] = $results;

        return $this->response->setJSON($data);
    }
}
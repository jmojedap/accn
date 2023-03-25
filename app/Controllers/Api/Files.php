<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\FileModel;

class Files extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->fileModel = new FileModel();
	}

    public function index()
    {
        return redirect()->to('files/explore');
    }

// Exploración de archivos
//-----------------------------------------------------------------------------

    /**
     * JSON
     * Buscar archivos según filtros y condiciones solicitadas
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
     * Validar formularios de creación y actualización de datos de archivos
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
     * Insertar un nuevo usuario en la tabla files
     * 2023-01-29
     */
    public function create()
    {
        //Preparar array del registro
        $aRow = $this->request->getPost();
        $aRow['display_name'] = $aRow['first_name'] . ' ' . $aRow['last_name'];
        $aRow['username'] = $this->userModel->emailToUsername($aRow['email']);
        if ( isset($aRow['password']) ) {
            $aRow['password'] = $this->userModel->cryptPassword($aRow['password']);
        }

        //Control de rol de usuario, no administrador
        if ( intval($aRow['role']) <= 2 ) {
            $aRow['role'] = 21;
        }

        //Guardar
        $data['savedId'] = $this->userModel->insert($aRow);

        //Si se creó, datos complementarios
        if ($data['savedId']) {
            $data['idcode'] = $this->dbTools->setIdCode('files', $data['savedId']);
            $data['aRow'] = $aRow;
        }
        
        $data['errors'] = $this->userModel->errors();

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Actualizar los datos de un usuario, tabla files
     * 2023-03-12
     */
    public function update($idCode)
    {
        $aRow = $this->request->getPost();
        $aRow['display_name'] = $aRow['first_name'] . ' ' . $aRow['last_name'];

        $data['saved'] = $this->userModel->where('idcode',$idCode)
                            ->set($aRow)->update();

        if ( $data['saved'] ) {
            $data['savedId'] = $aRow['id'];
            $data['savedId'] = $aRow['id'];
        } else {
            $data['errors'] = $this->userModel->errors();
        }

        return $this->response->setJSON($data);
    }

// Eliminación
//-----------------------------------------------------------------------------

    /**
     * Eliminación de archivos seleccionados
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

// UPLOAD
//-----------------------------------------------------------------------------

	/**
	 * Cargar un archivo y crear registro en la tabla files
	 * 2023-03-25
	 */
	public function upload()
	{	
		//Guardar archivo y preparar array para registro
            $userId = $_SESSION['user_id'];
			$aRow = $this->fileModel->upload($this->request, $userId);

		//Guardar registro en tabla files
			$this->fileModel->save($aRow);
			$savedId = $this->fileModel->insertID();
			$data['row'] = $this->fileModel->get($savedId);
			//$data['aRow'] = $aRow;
		
		//Ajustar imagen y minuatura
		if ( $aRow['is_image'] )
		{	
			$data['thumbnail'] = $this->fileModel->createThumbnail($data['row']);		//Crear miniatura
			//$data['resized'] = $this->fileModel->resize_image($data['row']);			//Reducir dimensiones a un máximo permitido
			//$this->fileModel->update_dimensions($data['row']);	//Actualizar los campos de dimensiones y tamaño
		}

		if ( $savedId > 0 ) { $data['status'] = 1; }

		return $this->response->setJSON($data);
	}
}
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\PostModel;
use App\Models\ValidationModel;
use App\Libraries\DbUtils;

class Posts extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->postModel = new PostModel();
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
     * 2025-10-13
     */
    public function search()
    {
        $request = service('request');
        $input = $request->getPost();
        $search = new \App\Libraries\Search();
        $data = $this->postModel->search($input);

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Validar formularios de creación y actualización de datos de usuarios
     * 2025-10-13
     */
    public function validateForm()
    {
        $data = ['status' => 1, 'error' => null];

        $request = service('request');
        $postId = $request->getPost('id');
        
        $emailValidation = ValidationModel::email($request->getPost('email'), $postId);
        $documentNumberValidation = ValidationModel::documentNumber($request->getPost('document_number'), $postId);
        $usernameValidation = ValidationModel::username($request->getPost('username'), $postId);

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
     * Insertar un nuevo post en la tabla posts
     * 2026-01-19
     */
    public function create()
    {
        //Preparar array del registro
        $input = $this->request->getPost();
        $aRow = $this->postModel->inputToRow($input);

        //Guardar
        $data['savedId'] = $this->postModel->insert($aRow);

        //Si se creó, datos complementarios
        if ($data['savedId']) {
            $data['idcode'] = DbUtils::setIdCode('posts', $data['savedId']);
        }
        
        $data['errors'] = $this->postModel->errors();

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Actualizar los datos de un post, tabla posts
     * 2026-01-19
     */
    public function update($idCode)
    {
        $input = $this->request->getPost();
        $aRow = $this->postModel->inputToRow($input);

        $data['saved'] = $this->postModel->where('idcode',$idCode)
                            ->set($aRow)->update();

        if ( $data['saved'] ) {
            $data['savedId'] = $aRow['id'];
        } else {
            $data['errors'] = $this->postModel->errors();
        }

        return $this->response->setJSON($data);
    }

// Eliminación
//-----------------------------------------------------------------------------

    /**
     * Eliminación de posts seleccionados
     * 2023-02-18
     */
    public function deleteSelected()
    {
        $selected = explode(',', $this->request->getPost('selected'));
        $results = [];
        
        foreach ($selected as $idCode) {
            $results[$idCode] = $this->postModel->deleteByIdCode($idCode);
        }

        $data['results'] = $results;

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Obtener las imágenes de un post
     * 2026-01-28
     */
    public function images($postId)
    {
        $images = $this->postModel->images($postId);
        $data['images'] = $images->getResult();
        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Establecer imagen principal de un post
     * 2026-01-28
     */
    public function setMainImage($postId, $fileId)
    {
        $data = $this->postModel->setMainImage($postId, $fileId);
        return $this->response->setJSON($data);
    }
}
<?php

namespace App\Controllers;

use App\Models\UserModel;

class Users extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->userModel = new UserModel();
        $this->viewsFolder = 'users/';
	}

    public function index()
    {
        return redirect()->to('users/explore');
    }

// Exploración de usuarios
//-----------------------------------------------------------------------------

    /**
     * HTML
     * Listado de exploración y búsqueda de usuarios
     * 2022-06-22
     * @param int $numPage
     */
    public function explore($numPage = 1)
    {
        //Filtros y detalle de la búsqueda
        $search = new \App\Libraries\Search();
        $filters = $search->filters();

        //Datos básicos de la exploración
        $data = $this->userModel->exploreData($filters, $numPage);

        $data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');

        $data['numPage'] = $numPage;
        $data['filters'] = $filters;
        $data['filtersStr'] = $search->filtersStr($filters);

        return view(TPL_ADMIN . 'main', $data);
    }

    /**
     * JSON
     * Listado de usuarios
     */
    public function get($numPage = 1)
    {
        $search = new \App\Libraries\Search();
        
        $filters = $search->filters();
        $perPage = $search->per_page($this->request->getPostGet('per_page'));
        
        $data = $this->userModel->search($filters, $numPage, $perPage);

        $data['filters'] = $filters;
        $data['filtersStr'] = $search->filtersStr($filters);

        return $this->response->setJSON($data);
    }

    /**
     * HTML VIEW
     * Vista perfil del usuario
     * 2023-01-28
     */
    public function profile($idCode)
    {
        $user = $this->userModel->get($idCode, 'admin');
        $data = $this->userModel->basic($user);

        $data['arrDocumentTypes'] = $this->itemModel->arrOptions('category_id = 53', 'optionsAbbreviation');
        $data['arrGenders'] = $this->itemModel->arrOptions('category_id = 59');

        $data['viewA'] = 'users/profile';

        return view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML FORM
     * Vista con formulario para crear un nuevo usuario
     * 2023-01-28
     */
    public function add()
    {
        $data['headTitle'] = 'Crear usuario';
        $data['viewA'] = $this->viewsFolder . 'add/add';
        $data['viewsFolder'] = $this->viewsFolder;

        $data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');

        return view(TPL_ADMIN . 'main', $data);
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
        $aRow = $this->request->getPost();
        $aRow['display_name'] = $aRow['first_name'] . ' ' . $aRow['last_name'];
        $aRow['username'] = $this->userModel->emailToUsername($aRow['email']);
        if ( isset($aRow['password']) ) {
            $aRow['password'] = $this->userModel->cryptPassword($aRow['password']);
        }

        //Control de rol de usuario, no administrador
        if (intval($aRow['role']) <= 2) {
            $aRow['role'] = 21;
        }

        $this->userModel->save($aRow);
        $data['savedId'] = $this->userModel->getInsertID();

        //Si se creó, datos complementarios
        if ($data['savedId']) {
            $data['idcode'] = $this->dbTools->setIdCode('users', $data['savedId']);
            $data['aRow'] = $aRow;
        }
        
        $data['errors'] = $this->userModel->errors();

        return $this->response->setJSON($data);
    }

    /**
     * HTML VIEW
     * Formulario para la edición de usuarios
     * 2023-02-04
     */
    public function edit($idCode)
    {
        $user = $this->userModel->get($idCode, 'admin');
        $data = $this->userModel->basic($user);

        $data['arrDocumentTypes'] = $this->itemModel->arrOptions('category_id = 53', 'optionsAbbreviation');
        $data['arrGenders'] = $this->itemModel->arrOptions('category_id = 59');
        $data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');

        $data['viewA'] = 'users/edit/edit';

        return view(TPL_ADMIN . 'main', $data);
    }

    /**
     * JSON
     * Actualizar los datos de un usuario, tabla users
     * 2023-02-04
     */
    public function update()
    {
        $aRow = $this->request->getPost();
        $aRow['display_name'] = $aRow['first_name'] . ' ' . $aRow['last_name'];

        $data['saved'] = $this->userModel->save($aRow);
        if ( ! $data['saved'] ) {
            $data['errors'] = $this->userModel->errors();
        }

        return $this->response->setJSON($data);
    }
}
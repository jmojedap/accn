<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->userModel = new UserModel();
        $this->viewsFolder = 'admin/users/';
        $this->viewsFolderAdmin = 'admin/users/';
	}

    public function index()
    {
        return redirect()->to('admin/users/explore');
    }

// Exploración de usuarios
//-----------------------------------------------------------------------------

    /**
     * HTML
     * Listado de exploración y búsqueda de usuarios
     * 2022-06-22
     */
    public function explore()
    {
        //Filtros y detalle de la búsqueda
        $request = \Config\Services::request();
        $input = $request->getGet();

        //Datos básicos de la exploración
        $data = $this->userModel->exploreData($input);

        $data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');

        return view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Vista perfil del usuario
     * 2023-01-28
     */
    public function profile($idCode)
    {
        $user = $this->userModel->get($idCode, 'basic');
        $data = $this->userModel->basic($user);

        $data['arrDocumentTypes'] = $this->itemModel->arrOptions('category_id = 53', 'optionsAbbreviation');
        $data['arrGenders'] = $this->itemModel->arrOptions('category_id = 59');

        $data['viewA'] = 'admin/users/profile';

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
     * HTML VIEW
     * Formulario para la edición de usuarios
     * 2023-02-04
     */
    public function edit($idCode)
    {
        $user = $this->userModel->get($idCode);
        $data = $this->userModel->basic($user);

        $data['arrDocumentTypes'] = $this->itemModel->arrOptions('category_id = 53', 'optionsAbbreviation');
        $data['arrGenders'] = $this->itemModel->arrOptions('category_id = 59');
        $data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');

        $data['viewA'] = $this->viewsFolderAdmin . 'edit/edit';

        return view(TPL_ADMIN . 'main', $data);
    }
}
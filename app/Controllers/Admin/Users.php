<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    public function __construct()
	{
		$this->userModel = new UserModel();
        $this->viewsFolder = 'admin/users/';
        $this->backLink = URL_ADMIN . 'users/explore';
        $this->entityInfo = [
            'controller' => 'users',
            'singular' => 'Usuario',
            'plural' => 'Usuarios',
            'isMale' => 1,
        ];
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
     * 2023-03-05
     */
    public function explore()
    {
        //Filtros y detalle de la búsqueda
        $input = $this->request->getPostGet();
        $data['search'] = $this->userModel->search($input);
        
        $data['entityInfo'] = $this->entityInfo;
        $data['headTitle'] = $this->entityInfo['plural'];
        $data['viewsFolder'] = $this->viewsFolder . 'explore/';
        $data['viewA'] = $this->viewsFolder . 'explore/explore';
        $data['nav2'] = $this->viewsFolder . 'menus/general';

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Detalle del registro en la tabla users
     * @param int $idCode :: users.idcode
     * 2024-05-24
     */
    public function details($idCode)
    {
        $user = $this->userModel->getRow($idCode, 'admin');
        $data = $this->userModel->basic($user);

        $data['row'] = $user;
        $data['viewA'] = 'common/bs5/row_details';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
    /**
     * HTML VIEW
     * Vista perfil del usuario
     * 2023-01-28
     */
    public function profile($idCode)
    {
        $user = $this->userModel->getRow($idCode, 'basic');
        $data = $this->userModel->basic($user);

        $data['arrDocumentTypes'] = $this->itemModel->arrOptions('category_id = 53', 'optionsAbbreviation');
        $data['arrGenders'] = $this->itemModel->arrOptions('category_id = 59');

        $data['viewA'] = 'admin/users/profile';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
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
        $data['nav2'] = $this->viewsFolder . 'menus/general';
        $data['entityInfo'] = $this->entityInfo;

        $data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Formulario para la edición de usuarios
     * 2023-02-04
     */
    public function edit($idCode)
    {
        $user = $this->userModel->getRow($idCode);
        $data = $this->userModel->basic($user);

        $data['arrDocumentTypes'] = $this->itemModel->arrOptions('category_id = 53', 'optionsAbbreviation');
        $data['arrGenders'] = $this->itemModel->arrOptions('category_id = 59');
        $data['arrRoles'] = $this->itemModel->arrOptions('category_id = 58');

        $data['viewA'] = $this->viewsFolder . 'edit/edit';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
}
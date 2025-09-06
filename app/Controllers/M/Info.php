<?php

namespace App\Controllers\M;

use App\Controllers\BaseController;

class Info extends BaseController
{
    public function __construct()
	{
        $this->viewsFolder = 'm/info/';
		/*$this->db = \Config\Database::connect();
		$this->userModel = new UserModel();
        $this->viewsFolder = 'admin/users/';
        $this->backLink = URL_ADMIN . 'users/explore';
        $this->entityInfo = [
            'controller' => 'users',
            'singular' => 'Usuario',
            'plural' => 'Usuarios',
            'isMale' => 1,
        ];*/
	}

    public function index()
    {
        return redirect()->to('admin/users/explore');
    }

    public function welcome()
    {
        $data['headTitle'] = 'Bienvenidos a la APP';
        $data['viewA'] = $this->viewsFolder . 'welcome';
        return view(TPL_PUBLIC . 'public', $data);
    }

    public function noPermitido(){
        return view('m/info/no_permitido');
    }
}
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

    public function inicio()
    {
        $data['headTitle'] = 'MultiSits';
        $data['viewA'] = $this->viewsFolder . 'inicio';
        return view(TPL_PUBLIC . 'main', $data);
    }

    public function noPermitido(){
        return view('m/info/no_permitido');
    }

    public function exploreSits()
    {
        $data['headTitle'] = 'Explorar sitios y perfiles';
        $data['viewA'] = $this->viewsFolder . 'explore_sits';

        $file_path = PATH_CONTENT . 'multisits/data/sits.json';
        if ( ! file_exists($file_path) ) {
            die('El archivo de datos no existe. ' . $file_path);
        }
        $data['sits'] = file_get_contents($file_path);

        return view(TPL_PUBLIC . 'main', $data);
    }
}
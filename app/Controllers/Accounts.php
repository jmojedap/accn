<?php

namespace App\Controllers;

use App\Models\AccountModel;

class Accounts extends BaseController
{
	function __construct() 
    {
        $this->db = \Config\Database::connect();
        $this->accountModel = new AccountModel();
		$this->viewsFolder = 'accounts/';
        
        //Local time set
        date_default_timezone_set("America/Bogota");
    }

    public function index(){
		$this->login();
	}

    /**
	 * Vista para realizar inicio de sesión
	 * 2022-06-22
	 */
	public function login()
	{
		if ( isset($_SESSION['logged']) ) {
			return redirect()->to(base_url('accounts/logged'));
		} else {
			$data['headTitle'] = 'Iniciar sesión';
			$data['viewA'] = $this->viewsFolder . 'login';
			return view(TPL_PUBLIC . 'public', $data);
		}
	}

	/**
	 * REDIRECT
	 * Redireccionamiento cuando un usuario ya ha iniciado sesión
	 * el destino depende del rol del usuario
	 * 2022-06-20
	 */
	public function logged()
	{
		$role = $_SESSION['role'];
		$destination = 'accounts/profile';
		if ( $role == 1 ) {
			$destination = 'admin/users/explore';
		}

		return redirect()->to(base_url($destination));
	}

	/**
	 * JSON / REDIRECT
	 * Destruye la sesión de usuario, y redirige a Login
	 * 2020-11-21
	 */
	public function logout($type = 'redirect')
	{
		$this->session->destroy();

		if ( $type == 'ajax' ) {
			$data['status'] = 1;
			return $this->response->setJSON($data);
		} else {
			return redirect()->to(base_url('accounts/login'));
		}
	}

// PERFIL DE USUARIO
//-----------------------------------------------------------------------------

	/**
	 * Vista perfil de usuario
	 * 2023-04-30
	 */
	public function profile()
	{
		$idcode = $_SESSION['idcode'];
		$data['user'] = $this->accountModel->getRow($idcode);

		$data['arrDocumentTypes'] = $this->itemModel->arrOptions('category_id = 53', 'optionsAbbreviation');
        $data['arrGenders'] = $this->itemModel->arrOptions('category_id = 59');

		$data['headTitle'] = 'Mauricio Ojeda Pepinosa';
		$data['viewA'] = $this->viewsFolder . 'profile/profile';
		return view(TPL_PUBLIC . 'main', $data);
	}
}

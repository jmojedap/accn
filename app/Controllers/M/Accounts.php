<?php

namespace App\Controllers\M;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Libraries\DbUtils;

class Accounts extends BaseController
{
	function __construct() 
    {
        $this->db = \Config\Database::connect();
        $this->accountModel = new AccountModel();
		$this->viewsFolder = 'm/accounts/';
        
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
	public function login($type = 'password')
	{
		if ( isset($_SESSION['logged']) ) {
			return redirect()->to(base_url('m/accounts/logged'));
		} else {
			$data['headTitle'] = 'Iniciar sesión';
			$data['viewA'] = $this->viewsFolder . 'login';
			if ($type == 'link') {
				// Obtener access key por variables GET
    			$data['accessKey'] = $this->request->getGet('access_key')?? '';
				$data['viewA'] = $this->viewsFolder . 'login_link';
			}
			return view(TPL_PUBLIC . 'main', $data);
		}
	}

	/**
	 * Vista para crear cuenta de usuario
	 * 2025-07-20
	 */
	public function signup()
	{
		if ( isset($_SESSION['logged']) ) {
			return redirect()->to(base_url('m/accounts/logged'));
		} else {
			$data['headTitle'] = 'Crea tu cuenta';
			$data['viewA'] = $this->viewsFolder . 'signup';
			return view(TPL_PUBLIC . 'main', $data);
		}
	}

	/**
	 * REDIRECT
	 * Redireccionamiento cuando un usuario ya ha iniciado sesión
	 * el destino depende del rol del usuario
	 * 2025-09-07
	 */
	public function logged()
	{
		$role = $_SESSION['role'];
		$destination = 'm/accounts/profile';
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
			return redirect()->to(base_url('m/accounts/login'));
		}
	}

	/**
     * Validar un accessKey para login de usuario.
     * Si es válido, inicia sesión; si no, redirecciona.
     * 2025-08-23
     */
    public function validateLoginLink(string $accessKey)
    {
        // Buscar usuario por accessKey
		$now = date('Y-m-d H:i:s');
		$condition = "access_key = '{$accessKey}' AND access_key != '' AND access_key_expiry > '{$now}'";
        $user = DbUtils::row('users', $condition);

        if ($user) {
            // Crear sesión
            $this->accountModel->createSession($user->email, true);

			//Fecha y hora del último ingreso
			$this->accountModel->updateLastLogin($user->id);

            // Inhabilitar el key actual generando uno nuevo
            $this->accountModel->setAccessKey($user->id);

            // Redirigir al dashboard (o método logged())
            return redirect()->to(base_url('m/accounts/logged'));
        } else {
            // Redirigir a pantalla de error/login
            return redirect()->to(base_url("m/accounts/login/link"));
        }
    }

// PERFIL DE USUARIO
//-----------------------------------------------------------------------------

	/**
	 * Vista perfil de usuario, con formulario para edición de datos básicos
	 * 2025-09-07
	 */
	public function profile()
	{
		$idcode = $this->session->idcode;
		$data['user'] = $this->accountModel->getRow($idcode);

		$data['headTitle'] = $data['user']->display_name . ' - Perfil';
		$data['viewA'] = $this->viewsFolder . 'profile/profile';
		$data['nav2'] = $this->viewsFolder . 'menu';
		return $this->pml->view(TPL_PUBLIC . 'main', $data);
	}

	/**
	 * Vista perfil de usuario, con formulario para edición de la foto de perfil
	 * 2026-01-30
	 */
	public function picture()
	{	
		$idcode = $this->session->idcode;
		$data['user'] = $this->accountModel->getRow($idcode);

		$data['headTitle'] = $data['user']->display_name . ' - Foto';
		$data['viewA'] = $this->viewsFolder . 'picture/picture';
		$data['nav2'] = $this->viewsFolder . 'menu';
		return $this->pml->view(TPL_PUBLIC . 'main', $data);
	}
}

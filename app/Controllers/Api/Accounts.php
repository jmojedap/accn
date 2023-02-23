<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AccountModel;

class Accounts extends BaseController
{
	function __construct() 
    {
        $this->db = \Config\Database::connect();
        $this->accountModel = new AccountModel();
        
        //Local time set
        date_default_timezone_set("America/Bogota");
    }

    public function index(){
		$destination = 'accounts/login';
		return redirect()->to(base_url($destination));
	}

	/**
	 * JSON
	 * Validar usuario y contraseña, si son válidos iniciar sesión
	 * 2022-07-02
	 */
	public function validateLogin()
	{
		//Setting variables
		$userlogin = $this->request->getPost('username');
		$password = $this->request->getPost('password');

		$data = $this->accountModel->validateLogin($userlogin, $password);

		if ( $data['status'] == 1 )
		{
			$data['sessionData'] = $this->accountModel->createSession($userlogin);
			
			$jwt = new \App\Libraries\JWT_Library();
			$data['token'] = $jwt->encode($data['sessionData'], SKAPP);
		}

		return $this->response->setJSON($data);
	}
}

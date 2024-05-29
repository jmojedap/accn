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

	/**
     * JSON
     * Validar formularios de creación y actualización de datos de cuenta de un
	 * usuario
     * 2023-04-30
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
}

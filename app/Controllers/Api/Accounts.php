<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\UserModel;
use App\Models\NotificationModel;
use App\Models\ValidationModel;
use App\Libraries\DbUtils;
use App\Libraries\MailPml;


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
		$destination = 'm/accounts/login';
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
     * 2025-07-20
     */
    public function validateForm()
    {
        $data = ['status' => 1, 'error' => '', 'validation' => []];

        $userId = $this->request->getPost('id');
        $email = $this->request->getPost('email');
        $documentNumber = $this->request->getPost('document_number');
        $username = $this->request->getPost('username');

        // Usando métodos estáticos directamente
        $emailValidation = ValidationModel::email($email, $userId);
        $documentNumberValidation = ValidationModel::documentNumber($documentNumber, $userId);
        $usernameValidation = ValidationModel::username($username, $userId);

        $data['validation'] = array_merge($emailValidation, $documentNumberValidation, $usernameValidation);

        $errors = [];

        if ($documentNumberValidation['documentNumberUnique'] == 0) {
            $errors[] = 'El número de documento ya está registrado.';
        }
        if ($emailValidation['emailUnique'] == 0) {
            $errors[] = 'El correo electrónico escrito ya está registrado.';
        }
        if ($usernameValidation['usernameUnique'] == 0) {
            $errors[] = 'El username escrito ya está registrado.';
        }

        $data['errors'] = $errors;
        $data['status'] = empty($errors) ? 1 : 0;

        return $this->response->setJSON($data);
    }

    /**
     * Enviar por correo electrónico un link para iniciar sesión en la aplicación
     * Retorna JSON
     * 2025-07-19
     */
    public function getLoginLink(): ResponseInterface
    {
        $email = $this->request->getPost('email');
        $appName = $this->request->getPost('app_name') ?? 'main';

        // Respuesta por defecto
        $data = [
            'status' => 0,
            'message' => "No existe ningún usuario con el correo electrónico '{$email}'",
            'app_name' => $appName
        ];

        if ($email) {
            $user = DbUtils::row('users', "email = '{$email}'");

            if ($user) {
                $data = $this->accountModel->sendLoginLink($user, $appName);
            }
        }

        return $this->response->setJSON($data);
    }

    /**
     * Crea un usuario en la base de datos, tabla user
     * 2025-07-26
     */
    public function create(): ResponseInterface
    {
        $data['status'] = 0;
        $errors = [];

        //Validar correo electrónico
        $emailValidation = ValidationModel::email($this->request->getPost('email'));
        if ($emailValidation['emailUnique'] == 0) {
            $errors[] = 'El correo electrónico escrito ya está registrado.';
        }

        //Validar ReCaptcha
        $recaptchaResponse = $this->request->getPost('g-recaptcha-response');
        $recaptcha = ValidationModel::recaptcha($recaptchaResponse);

        if ($recaptcha != 1) {
            $errors[] = 'Recaptcha no validado';
        }

        if ( count($errors) == 0) {
            $input = $this->request->getPost();
            $aRow = $this->accountModel->inputToRow($input);
            $data['savedId'] = $this->accountModel->insert($aRow);
            
            //Si se creó, datos complementarios
            if ($data['savedId']) {
                $data['idcode'] = DbUtils::setIdCode('users', $data['savedId']);

                $data['message'] = 'Usuario creado exitosamente';
                $data['status'] = 1;
            }
        } else {
            $data['errors'] = $errors;
        }

        return $this->response->setJSON($data);
    }

    /**
     * JSON
     * Actualizar los datos de un usuario, tabla users
     * 2025-09-07
     */
    public function update()
    {
        $idcode = $this->session->idcode;
        $input = $this->request->getPost();
        $userModel = new UserModel();
        $aRow = $userModel->inputToRow($input);

        $data['saved'] = DbUtils::saveRow('users', "idcode = {$idcode}", $aRow);

        return $this->response->setJSON($data);
    }

    public function test(): ResponseInterface
    {
        $mailer = new MailPml();

        // Cambia esto a un correo real que controles para probar
        $result = $mailer->send([
            'to' => 'ipialesenlinea@gmail.com',
            'subject' => 'Correo de prueba desde CodeIgniter 4',
            'html_message' => '<p>Hola, este es un correo de prueba enviado desde la función <strong>test()</strong>.</p>'
        ]);

        // Resultado combinado
        $data = [
            'status' => $result['status'],
            'message' => $result['status'] === 1 ? 'Correo enviado correctamente' : 'Error al enviar correo',
            'error' => $result['error'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return $this->response->setJSON($data);
    }
}

<?php namespace App\Models;

use CodeIgniter\Model;
use App\Models\UserModel;
use App\Libraries\DbUtils;
use App\Libraries\MailPml;

class AccountModel extends UserModel
{
    protected $table = 'users';

// Validación inicio de sesión
//-----------------------------------------------------------------------------

    /**
     * Verificar la contraseña de usuaro. Verifica que la combinación de
     * user y contraseña existan en un mismo registro en la tabla users.
     * 2022-07-02
     */
    function validateLogin($username, $password)
    {
        //Valor inicial
        $data['status'] = 3;     //Valor inicial, 3 => inexistente
        $data['message'] = "No existe un usuario identificado con '{$username}'";

        $condition = "username = '{$username}' OR email='{$username}' OR document_number='{$username}'";
        $user = DbUtils::row('users',$condition);
        
        if ( ! is_null($user) )
        {
            if ( $user->status == 0 ) { $data['message'] = "El ususario '{$username}' está inactivo, comuníquese el administrador."; }
            if ( $user->status == 2 ) { $data['message'] = "El usuario '{$username}' está registrado, e-mail no confirmado."; }

            //Si el usuario está activo, verificar contraseña
            if ( $user->status >= 1 && $user->status <= 2 )
            {
                $encryptedPassword = crypt($password, $user->password);
            
                if ( $user->password == $encryptedPassword )
                {
                    $data['status'] = 1;    //Contraseña válida
                    $data['message'] = 'Contraseña válida para el usuario.';
                } else {
                    $data['status'] = 0;    //Contraseña no válida
                    $data['message'] = "Contraseña NO válida para el usuario '{$username}'.";
                }
            }
        }
        
        return $data;
    }

    public function inputToRow($input)
    {
        $aRow = $input;
        $aRow['role'] = 21;
        
        //Creación de usuario
        if ( !isset($aRow['id']) ) {
            $aRow['creator_id'] = 0;
            $aRow['updater_id'] = 0;
            $aRow['username'] = UserModel::emailToUsername($aRow['email']);
        }

        return $aRow;
    }

// Información
//-----------------------------------------------------------------------------



// Inicio de sesión
//-----------------------------------------------------------------------------

    public function createSession($username)
    {
        $sessionData = $this->sessionData($username);

        $session = \Config\Services::session();
        $session->set($sessionData);

        return $sessionData;
    }

    public function sessionData($username)
    {
        $user = \App\Libraries\DbUtils::row(
            'users',
            "username = '{$username}' OR email='{$username}' OR document_number='{$username}'"
        );

        //$data general
            $data = [
                'logged' =>   TRUE,
                'display_name'    =>  $user->display_name,
                'short_name'    =>  explode(' ', $user->display_name)[0],
                'username'    =>  $user->username,
                'user_id'    =>  $user->id,
                'idcode'    =>  $user->idcode,
                'role'    => $user->role,
                'last_login'    => $user->last_login
            ];
                
        //Datos específicos para la aplicación
            //$app_session_data = $this->App_model->app_session_data($user);
            //$data = array_merge($data, $app_session_data);
        
        //Devolver array
            return $data;
    }

    /**
     * Enviar por correo electrónico un link de inicio de sesión
     * 
     * @param array|object $user
     * @param string $appName
     * @return array
     */
    public function sendLoginLink($user, string $appName = 'main'): array
    {
        $data = [
            'status' => 0,
            'message' => 'No fue posible enviar el correo electrónico',
            'link' => ''
        ];

        $accessKey = $this->setAccessKey($user->id, 'key');

        if ( ENV == 'production' ){
            // Enviar correo de prueba
            $dataMessage['user'] = $user;
            $dataMessage['link'] = base_url("m/accounts/validate_login_link/{$accessKey}");
            $mailer = new MailPml();
            $result = $mailer->send([
                'to' => $user->email,
                'subject' => 'Ingresa a ' . $appName,
                'html_message' => $this->loginLinkMessage($dataMessage)
                //'html_message' => '<p>Hola ' . $user->display_name . ',</p><p>Haz clic en el siguiente enlace para acceder a ' . $appName . ':</p><p><a href="' . $link . '">' . $link . '</a></p>'
            ]);
            if ( $result['status'] == 1 ) {
                $data['status'] = 1;
                $data['message'] = "El link fue enviado al correo electrónico {$user->email}";
            }
        } elseif ( ENV == 'development') {
            // No se envía correo, se envía datos por ajax
            $data['link'] = base_url("m/accounts/validate_login_link/{$accessKey}");
            $data['status'] = 1;
            $data['message'] = 'Versión de desarrollo: No se envía email';
            $data['access_key'] = $accessKey;
        }

        return $data;
    }

    public function loginLinkMessage($data): string
    {
        helper('email'); // Carga el helper email_helper.php
        $data['styles'] = email_styles(); // Obtiene el array
		$data['viewA'] = 'm/email/login_link';
        return view('templates/easypml/email', $data);
    }

    /**
     * Establece un código/llave de acceso para ingreso, activación o cambio de contraseña.
     * $format: 'key' => 32 letras minúsculas; 'code' => 8 alfanumérico en mayúsculas
     * 2025-08-23
     */
    public function setAccessKey(int $userId, string $format = 'key'): ?string
    {
        helper('text');

        $key = strtolower(random_string('alnum', 32));
        if ($format === 'code') {
            $key = strtoupper(random_string('alnum', 8));
        }

        // Actualizar y verificar
        $aRow['access_key'] = $key;
        $aRow['access_key_expiry'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $savedId = DbUtils::saveRow('users', "id = {$userId}", $aRow);

        if ($savedId > 0) {
            return $key; // ← devolver la clave correcta
        }

        return null; // No se pudo actualizar (id inexistente u otro fallo)
    }
}
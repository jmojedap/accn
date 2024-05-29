<?php namespace App\Models;

use CodeIgniter\Model;
use App\Models\UserModel;

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
        $user = \App\Models\DbTools::row('users',$condition);
        
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
        $user = \App\Models\DbTools::row(
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
}
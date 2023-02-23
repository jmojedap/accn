<?php namespace App\Models;

use CodeIgniter\Model;

class ValidationModel extends Model
{

    /**
     * Valida que username sea único, si se incluye un ID User existente
     * lo excluye de la comparación cuando se realiza edición
     * 2022-05-07
     */
    public static function email($userId = null, $email)
    {
        //Valores por defecto
        $validation['emailUnique'] = -1;

        //Si tiene algún valor escrito
        if ( strlen($email) > 0 )
        {
            $validation['emailUnique'] = \App\Models\DbTools::isUnique('users', 'email', $email, $userId);
        }

        return $validation;
    }

    /**
     * Valida que número de identificacion (documentNumber) sea único, 
     * si se incluye un ID User existente y lo excluye de la comparación 
     * para proceso de edición
     * 2022-05-07
     */
    public static function documentNumber($userId = null, $documentNumber)
    {
        $validation['documentNumberUnique'] = -1;
        if ( strlen($documentNumber) > 0 ) {
            $validation['documentNumberUnique'] = \App\Models\DbTools::isUnique('users', 'document_number', $documentNumber, $userId);
        }
        
        return $validation;
    }

    /**
     * Valida que número de identificacion (documentNumber) sea único, 
     * si se incluye un ID User existente y lo excluye de la comparación 
     * para proceso de edición
     * 2023-01-29
     */
    public static function username($userId = null, $username)
    {
        $validation['usernameUnique'] = -1;
        if ( strlen($username) > 0 ) {
            $validation['usernameUnique'] = \App\Models\DbTools::isUnique('users', 'username', $username, $userId);
        }
        
        return $validation;
    }
}
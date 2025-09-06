<?php namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\DbUtils;

class ValidationModel extends Model
{

    /**
     * Valida que username sea único, si se incluye un ID User existente
     * lo excluye de la comparación cuando se realiza edición
     * 2022-05-07
     */
    public static function email($email, $userId = null)
    {
        //Valores por defecto
        $validation['emailUnique'] = -1;

        //Si tiene algún valor escrito
        if ( strlen($email) > 0 )
        {
            $validation['emailUnique'] = DbUtils::isUnique('users', 'email', $email, $userId);
        }

        return $validation;
    }

    /**
     * Valida que número de identificacion (documentNumber) sea único, 
     * si se incluye un ID User existente y lo excluye de la comparación 
     * para proceso de edición
     * 2022-05-07
     */
    public static function documentNumber($documentNumber, $userId = null)
    {
        $validation['documentNumberUnique'] = -1;
        if ( strlen($documentNumber) > 0 ) {
            $validation['documentNumberUnique'] = DbUtils::isUnique('users', 'document_number', $documentNumber, $userId);
        }
        
        return $validation;
    }

    /**
     * Valida que número de identificacion (documentNumber) sea único, 
     * si se incluye un ID User existente y lo excluye de la comparación 
     * para proceso de edición
     * 2023-01-29
     */
    public static function username($username, $userId = null): array
    {
        $validation['usernameUnique'] = -1;
        if ( strlen($username) > 0 ) {
            $validation['usernameUnique'] = DbUtils::isUnique('users', 'username', $username, $userId);
        }
        
        return $validation;
    }

    /**
     * Validación de Google Recaptcha V3, la validación se realiza considerando el valor de
     * $recaptcha->score, que va de 0 a 1.
     * 2025-07-20
     */
    public static function recaptcha($response): int
    {
        $recaptcha = -1;

        $secret = K_RCSC;   //Ver app/Config/Constants.php
        //$response = $this->input->post('g-recaptcha-response');
        $json_recaptcha = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}");
        $recaptcha_content = json_decode($json_recaptcha);
        //echo $json_recaptcha;
        if ( $recaptcha_content->success ) {
            $recaptcha = 0;
            if ( $recaptcha_content->score > 0.7 ) $recaptcha = 1;
        }
        
        return $recaptcha;
    }
}
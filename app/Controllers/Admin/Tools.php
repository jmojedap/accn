<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Libraries\DbUtils;

class Tools extends BaseController
{
	function __construct() 
    {
        $this->db = \Config\Database::connect();
        //$this->accountModel = new AccountModel();
		$this->viewsFolder = 'admin/tools/';
        
        //Local time set
        date_default_timezone_set("America/Bogota");
    }

    public function index(){
		$this->login();
	}

    public function masterLogin($idCode)
	{
        if ( in_array($_SESSION['role'], [1,2]) ) {
            $user = DbUtils::row('users', "idcode = {$idCode}");

            $accountModel = new AccountModel();
            $accountModel->createSession($user->email, true);
            $data['user'] = DbUtils::row('users', "idcode = {$idCode}");
        }
		
		return redirect()->to(base_url('m/accounts/logged'));
	}

    public function previewEmails($type = 'login_link', $param1 = 0)
    {
        if ($type == 'login_link')
        {
            $data['user'] = $this->dbTools->rowId('users', $param1);
        }

        helper('email'); // Carga el helper email_helper.php
        $data['styles'] = email_styles(); // Obtiene el array
		$data['viewA'] = 'm/email/login_link';
        return view('templates/easypml/email', $data);
    }


}

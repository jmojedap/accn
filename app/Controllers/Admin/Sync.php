<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AppModel;
use App\Libraries\DbUtils;

class Sync extends BaseController
{
	function __construct() 
    {
        $this->db = \Config\Database::connect();
        //$this->accountModel = new AccountModel();
		$this->viewsFolder = 'admin/sync/';
        
        //Local time set
        date_default_timezone_set("America/Bogota");
    }

    public function index(){
		$this->login();
	}

    public function panel()
    {
        $data['headTitle'] = 'Sincronizar Base de Datos';
        $data['viewA'] = $this->viewsFolder . 'panel/panel';
        
        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
}

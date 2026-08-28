<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AppModel;
use App\Libraries\DbUtils;
use App\Models\SyncModel;

class Sync extends BaseController
{
	function __construct() 
    {
        $this->db = \Config\Database::connect();
        //$this->accountModel = new AccountModel();
		$this->viewsFolder = 'admin/sync/';
        $this->syncModel = new SyncModel();
        
        //Local time set
        date_default_timezone_set("America/Bogota");
    }

    public function index(){
		$this->login();
	}

    /**
     * Panel para sincronización de base de datos. Permite generar archivos JSON con los datos de las tablas seleccionadas, para luego ser descargados y usados en otras instancias de la aplicación.
     * 2026-05-22
     */
    public function panel()
    {
        //Calcular datos antes de cargar la vista
        //$this->syncModel->updateServerTablesRowsCount();
        $this->syncModel->updateTablesRowsCount();

        $data['headTitle'] = 'Sincronizar Base de Datos';
        $data['viewA'] = $this->viewsFolder . 'panel/panel';

        // Cargar el listado de tablas desde la tabla sis_tables
        $data['tables'] = $this->syncModel->tablesInfo();

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
}
